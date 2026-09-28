<?php

namespace App\Livewire\Shop;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowProduct extends Component
{
    #[Locked]
    public Product $product;

    public int|string $quantity = 1;

    public ?string $status = null;

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);

        $this->product = $product;
    }

    public function addToCart(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        if (! Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'));

            return;
        }

        $product = $this->product->fresh();
        $this->authorize('purchase', $product);

        $maxQuantity = min($product->stock, CartItem::MAX_QUANTITY);

        $this->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$maxQuantity],
        ]);

        DB::transaction(function () use ($product, $maxQuantity): void {
            $item = Auth::user()->cartItems()->lockForUpdate()->firstOrNew(['product_id' => $product->id]);
            $item->quantity = min(($item->exists ? $item->quantity : 0) + (int) $this->quantity, $maxQuantity);
            $item->save();
        });

        $this->quantity = 1;
        $this->status = 'Produto adicionado ao carrinho.';
        $this->dispatch('cart-updated');
    }

    /**
     * Adds to the cart and goes straight to checkout.
     */
    public function buyNow(): void
    {
        $this->status = null;

        $this->addToCart();

        if ($this->status !== null) {
            $this->redirect(route('checkout'));
        }
    }

    public function incrementQuantity(): void
    {
        $this->quantity = min((int) $this->quantity + 1, $this->maxQuantity());
    }

    public function decrementQuantity(): void
    {
        $this->quantity = max((int) $this->quantity - 1, 1);
    }

    private function maxQuantity(): int
    {
        return max(1, min($this->product->stock, CartItem::MAX_QUANTITY));
    }

    public function render(): View
    {
        return view('livewire.shop.show-product', [
            'canPurchase' => Auth::guest() || Auth::user()->can('purchase', $this->product),
            'isOwner' => Auth::id() === $this->product->seller_id,
        ])->title($this->product->name);
    }
}
