<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Loja')]
class Catalog extends Component
{
    /**
     * Products added to the grid per batch (first render and each scroll load).
     */
    private const BATCH = 12;

    /**
     * Hard ceiling on the growing window. Infinite scroll keeps every loaded row in
     * one query, so this bounds the work a single visitor can ask the database for.
     */
    private const MAX_LOADED = 240;

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

    /**
     * How many products the grid currently shows. Locked so only loadMore() can grow
     * it — a tampered payload cannot ask for the whole table in one request.
     */
    #[Locked]
    public int $loaded = self::BATCH;

    public function updatedSearch(): void
    {
        $this->search = mb_substr($this->search, 0, 100);
        $this->resetWindow();
    }

    public function sortBy(string $sort): void
    {
        $this->sort = isset(self::SORTS[$sort]) ? $sort : 'recentes';
        $this->resetWindow();
    }

    /**
     * Grow the window by one batch. Called by the sentinel entering the viewport and
     * by the fallback button, so it must stay safe to call when nothing is left.
     */
    public function loadMore(): void
    {
        $this->loaded = min($this->loaded + self::BATCH, self::MAX_LOADED);
    }

    private function resetWindow(): void
    {
        $this->loaded = self::BATCH;
    }

    public function render(): View
    {
        [$column, $direction] = self::SORTS[$this->sort] ?? self::SORTS['recentes'];

        $query = Product::query()
            ->available()
            ->search(mb_substr($this->search, 0, 100));

        $total = (clone $query)->count();

        $products = $query
            ->with('seller:id,name')
            ->orderBy($column, $direction)
            // Tiebreaker: without it, rows sharing a created_at or price could come
            // back in a different order between batches, duplicating or skipping
            // products as the window grows.
            ->orderBy('id', 'desc')
            ->take($this->loaded)
            ->get();

        return view('livewire.shop.catalog', [
            'products' => $products,
            'total' => $total,
            'hasMore' => $products->count() < $total && $this->loaded < self::MAX_LOADED,
            'reachedLimit' => $this->loaded >= self::MAX_LOADED && $total > $products->count(),
            'sorts' => array_keys(self::SORTS),
        ]);
    }
}
