<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\CheckoutException;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private ShippingCalculator $shipping,
        private PaymentGateway $gateway,
        private AuditLogger $audit,
    ) {}

    /**
     * Summarise the buyer's cart grouped by seller. Prices always come from the database.
     *
     * @return Collection<int, array{seller: User, items: Collection<int, CartItem>, subtotal_cents: int, shipping_cents: int, total_cents: int}>
     */
    public function summary(User $buyer, ?Address $address = null): Collection
    {
        $items = $buyer->cartItems()->with('product.seller')->get()
            ->filter(fn (CartItem $item): bool => $item->product !== null);

        return $items->groupBy(fn (CartItem $item): int => $item->product->seller_id)
            ->map(function (Collection $items) use ($address): array {
                $subtotal = $items->sum(fn (CartItem $item): int => $item->product->price_cents * $item->quantity);
                $shipping = $address
                    ? $this->shipping->priceFor($address->state, (int) $items->sum('quantity'), $subtotal)
                    : 0;

                return [
                    'seller' => $items->first()->product->seller,
                    'items' => $items->values(),
                    'subtotal_cents' => $subtotal,
                    'shipping_cents' => $shipping,
                    'total_cents' => $subtotal + $shipping,
                ];
            })
            ->values();
    }

    /**
     * Place one order per seller atomically: stock is locked, re-validated and
     * decremented inside a transaction, so concurrent purchases can't oversell.
     *
     * @return Collection<int, Order>
     *
     * @throws CheckoutException
     */
    public function placeOrder(User $buyer, Address $address, PaymentCard $card, string $cvv): Collection
    {
        if ($address->user_id !== $buyer->id || $card->user_id !== $buyer->id) {
            throw new CheckoutException('Endereço ou cartão inválido.');
        }

        $orders = DB::transaction(function () use ($buyer, $address, $card, $cvv): Collection {
            $cartItems = $buyer->cartItems()->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw new CheckoutException('Seu carrinho está vazio.');
            }

            $products = Product::query()
                ->whereKey($cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw new CheckoutException('Um produto do carrinho não está mais disponível.');
                }

                if ($product->seller_id === $buyer->id) {
                    throw new CheckoutException('Você não pode comprar o seu próprio produto.');
                }

                if ($item->quantity < 1 || $item->quantity > $product->stock) {
                    throw new CheckoutException("Estoque insuficiente para \"{$product->name}\".");
                }
            }

            $grandTotal = 0;
            $pendingOrders = [];

            foreach ($cartItems->groupBy(fn (CartItem $item): int => $products[$item->product_id]->seller_id) as $sellerId => $sellerItems) {
                $subtotal = $sellerItems->sum(fn (CartItem $item): int => $products[$item->product_id]->price_cents * $item->quantity);
                $shipping = $this->shipping->priceFor($address->state, (int) $sellerItems->sum('quantity'), $subtotal);
                $grandTotal += $subtotal + $shipping;

                $pendingOrders[] = compact('sellerId', 'sellerItems', 'subtotal', 'shipping');
            }

            $this->gateway->charge($card, $cvv, $grandTotal);

            $created = collect();

            foreach ($pendingOrders as ['sellerId' => $sellerId, 'sellerItems' => $sellerItems, 'subtotal' => $subtotal, 'shipping' => $shipping]) {
                $order = Order::create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'status' => OrderStatus::Paid,
                    'subtotal_cents' => $subtotal,
                    'shipping_cents' => $shipping,
                    'total_cents' => $subtotal + $shipping,
                    'shipping_address' => $address->toSnapshot(),
                    'card_brand' => $card->brand->value,
                    'card_last_four' => $card->last_four,
                    'estimated_delivery_date' => $this->shipping->estimatedDeliveryDate($address->state),
                    'paid_at' => now(),
                ]);

                foreach ($sellerItems as $item) {
                    $product = $products[$item->product_id];

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price_cents' => $product->price_cents,
                        'quantity' => $item->quantity,
                    ]);

                    $product->decrement('stock', $item->quantity);
                }

                $created->push($order);
            }

            $buyer->cartItems()->delete();

            return $created;
        });

        foreach ($orders as $order) {
            $this->audit->log('order.placed', $buyer, ['order' => $order->code, 'total_cents' => $order->total_cents]);
        }

        return $orders;
    }

    /**
     * Cancel a paid order and give the stock back.
     */
    public function cancel(Order $order, User $actor): void
    {
        DB::transaction(function () use ($order): void {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! $order->canBeCancelled()) {
                throw new CheckoutException('Este pedido não pode mais ser cancelado.');
            }

            foreach ($order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $order->forceFill(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()])->save();
        });

        $this->audit->log('order.cancelled', $actor, ['order' => $order->code]);
    }
}
