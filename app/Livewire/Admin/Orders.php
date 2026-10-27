<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only view of every order (transaction) between any buyer and seller.
 * The shipping address is left out on purpose (data minimisation).
 */
#[Title('Admin · Pedidos')]
class Orders extends Component
{
    use WithPagination;

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $status = OrderStatus::tryFrom($this->status);

        return view('livewire.admin.orders', [
            'orders' => Order::query()
                ->with(['buyer:id,name,email', 'seller:id,name', 'items'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest('id')
                ->paginate(25),
            'statuses' => OrderStatus::cases(),
            'orderCount' => Order::count(),
            'revenueCents' => (int) Order::where('status', '!=', OrderStatus::Cancelled)->sum('total_cents'),
        ]);
    }
}
