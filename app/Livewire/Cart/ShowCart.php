<?php

namespace App\Livewire\Cart;

use App\Models\CartItem;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Carrinho')]
class ShowCart extends Component
{
    public function updateQuantity(int $itemId, int|string $quantity): void
    {
        $item = Auth::user()->cartItems()->with('product')->findOrFail($itemId);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        if ($quantity === false || $quantity < 1) {
            $this->remove($itemId);

            return;
        }

        $item->update(['quantity' => min($quantity, $item->product->stock, CartItem::MAX_QUANTITY)]);
        $this->dispatch('cart-updated');
    }

    public function remove(int $itemId): void
    {
        Auth::user()->cartItems()->whereKey($itemId)->delete();
        $this->dispatch('cart-updated');
    }

    public function render(CheckoutService $checkout): View
    {
        $groups = $checkout->summary(Auth::user());

        return view('livewire.cart.show-cart', [
            'groups' => $groups,
            'subtotal' => $groups->sum('subtotal_cents'),
        ]);
    }
}
