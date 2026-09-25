<?php

namespace App\Livewire\Orders;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Minhas compras')]
class ListOrders extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.orders.list-orders', [
            'orders' => Auth::user()->purchases()->with(['seller:id,name', 'items'])->latest()->paginate(10),
        ]);
    }
}
