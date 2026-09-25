<?php

namespace App\Livewire\Seller;

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Minhas vendas')]
class ListSales extends Component
{
    use WithPagination;

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $status = OrderStatus::tryFrom($this->status);

        return view('livewire.seller.list-sales', [
            'orders' => Auth::user()->sales()
                ->with(['buyer:id,name', 'items'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(10),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
