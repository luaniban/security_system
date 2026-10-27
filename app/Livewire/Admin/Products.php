<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only view of every product in the store, from every seller, including paused ones.
 */
#[Title('Admin · Produtos')]
class Products extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'situacao', except: '')]
    public string $situation = '';

    public function mount(): void
    {
        $this->authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSituation(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.products', [
            'products' => Product::query()
                ->with('seller:id,name,email')
                ->search(mb_substr($this->search, 0, 100))
                ->when($this->situation === 'ativos', fn ($query) => $query->where('is_active', true))
                ->when($this->situation === 'pausados', fn ($query) => $query->where('is_active', false))
                ->latest('id')
                ->paginate(25),
        ]);
    }
}
