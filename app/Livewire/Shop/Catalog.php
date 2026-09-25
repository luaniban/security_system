<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Loja')]
class Catalog extends Component
{
    use WithPagination;

    /**
     * Allow-list for ORDER BY: user input never reaches the query as a column name.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SORTS = [
        'recentes' => ['created_at', 'desc'],
        'menor-preco' => ['price_cents', 'asc'],
        'maior-preco' => ['price_cents', 'desc'],
        'nome' => ['name', 'asc'],
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'ordem', except: 'recentes')]
    public string $sort = 'recentes';

    public function updatedSearch(): void
    {
        $this->search = mb_substr($this->search, 0, 100);
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        [$column, $direction] = self::SORTS[$this->sort] ?? self::SORTS['recentes'];

        $products = Product::query()
            ->available()
            ->search(mb_substr($this->search, 0, 100))
            ->with('seller:id,name')
            ->orderBy($column, $direction)
            ->paginate(12);

        return view('livewire.shop.catalog', [
            'products' => $products,
            'sorts' => array_keys(self::SORTS),
        ]);
    }
}
