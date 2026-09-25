<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Services\AuditLogger;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowOrder extends Component
{
    #[Locked]
    public Order $order;

    public string $trackingCode = '';

    public function mount(Order $order): void
    {
        // Only the buyer or the seller of this order may see it (prevents IDOR).
        $this->authorize('view', $order);

        $this->order = $order;
    }

    public function ship(AuditLogger $audit): void
    {
        $this->order->refresh();
        $this->authorize('ship', $this->order);

        $this->trackingCode = mb_strtoupper(trim($this->trackingCode));

        $this->validate([
            'trackingCode' => ['required', 'string', 'regex:/^[A-Z]{2}\d{9}[A-Z]{2}$/'],
        ], [
            'trackingCode.regex' => 'Use o formato dos Correios, ex.: AA123456789BR.',
        ]);

        $this->order->forceFill([
            'status' => OrderStatus::Shipped,
            'tracking_code' => $this->trackingCode,
            'shipped_at' => now(),
        ])->save();

        $audit->log('order.shipped', Auth::user(), ['order' => $this->order->code]);
        $this->reset('trackingCode');
    }

    public function confirmDelivery(AuditLogger $audit): void
    {
        $this->order->refresh();
        $this->authorize('confirmDelivery', $this->order);

        $this->order->forceFill([
            'status' => OrderStatus::Delivered,
            'delivered_at' => now(),
        ])->save();

        $audit->log('order.delivered', Auth::user(), ['order' => $this->order->code]);
    }

    public function cancel(CheckoutService $checkout): void
    {
        $this->order->refresh();
        $this->authorize('cancel', $this->order);

        try {
            $checkout->cancel($this->order, Auth::user());
        } catch (CheckoutException $exception) {
            $this->addError('order', $exception->getMessage());
        }

        $this->order->refresh();
    }

    public function render(): View
    {
        $this->order->loadMissing(['items.product', 'buyer:id,name', 'seller:id,name']);

        return view('livewire.orders.show-order', [
            'isSeller' => Auth::id() === $this->order->seller_id,
            'isBuyer' => Auth::id() === $this->order->buyer_id,
        ])->title('Pedido #'.$this->order->code);
    }
}
