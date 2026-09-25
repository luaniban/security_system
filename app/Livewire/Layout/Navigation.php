<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Navigation extends Component
{
    public int $cartCount = 0;

    public function mount(): void
    {
        $this->refreshCartCount();
    }

    #[On('cart-updated')]
    public function refreshCartCount(): void
    {
        $this->cartCount = Auth::check() ? (int) Auth::user()->cartItems()->sum('quantity') : 0;
    }

    public function render(): View
    {
        return view('livewire.layout.navigation');
    }
}
