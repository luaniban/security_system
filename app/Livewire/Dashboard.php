<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Painel')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.dashboard', [
            'recentPurchases' => $user->purchases()->with('items:id,order_id,product_name,quantity')->latest()->limit(5)->get(),
            'openPurchases' => $user->purchases()->whereIn('status', [OrderStatus::Paid, OrderStatus::Shipped])->count(),
            'cartCount' => (int) $user->cartItems()->sum('quantity'),
            'salesToShip' => $user->sales()->where('status', OrderStatus::Paid)->count(),
            'activeProducts' => $user->products()->where('is_active', true)->count(),
            'addressCount' => $user->addresses()->count(),
            'cardCount' => $user->paymentCards()->count(),
        ]);
    }
}
