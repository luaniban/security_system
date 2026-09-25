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
            'recentPurchases' => $user->purchases()->latest()->limit(5)->get(),
            'salesToShip' => $user->sales()->where('status', OrderStatus::Paid)->count(),
            'activeProducts' => $user->products()->where('is_active', true)->count(),
            'addressCount' => $user->addresses()->count(),
            'cardCount' => $user->paymentCards()->count(),
        ]);
    }
}
