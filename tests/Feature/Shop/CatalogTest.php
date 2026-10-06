<?php

use App\Livewire\Shop\Catalog;
use App\Models\Product;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('the catalog shows the first batch and reports how many are left', function () {
    Product::factory()->count(20)->create();

    Livewire::test(Catalog::class)
        ->assertViewHas('products', fn ($products) => $products->count() === 12)
        ->assertViewHas('total', 20)
        ->assertViewHas('hasMore', true);
});

test('loading more appends the next batch without dropping what is loaded', function () {
    Product::factory()->count(20)->create();

    Livewire::test(Catalog::class)
        ->call('loadMore')
        ->assertViewHas('products', fn ($products) => $products->count() === 20)
        ->assertViewHas('hasMore', false);
});

test('loadMore stops at the total instead of failing when nothing is left', function () {
    Product::factory()->count(3)->create();

    Livewire::test(Catalog::class)
        ->assertViewHas('hasMore', false)
        ->call('loadMore')
        ->call('loadMore')
        ->assertViewHas('products', fn ($products) => $products->count() === 3);
});

test('products loaded across batches are all distinct', function () {
    // Same created_at for every row: the ordering tiebreaker is what keeps a product
    // from appearing in two batches (or being skipped entirely) as the window grows.
    Product::factory()->count(30)->create(['created_at' => now()]);

    $component = Livewire::test(Catalog::class)->call('loadMore')->call('loadMore');

    $ids = $component->viewData('products')->pluck('id');

    expect($ids)->toHaveCount(30)
        ->and($ids->unique())->toHaveCount(30);
});

test('a tampered loaded count can not widen the query', function () {
    Product::factory()->count(20)->create();

    Livewire::test(Catalog::class)
        ->set('loaded', 5000);
})->throws(CannotUpdateLockedPropertyException::class);

test('searching and sorting reset the window to the first batch', function () {
    Product::factory()->count(20)->create(['name' => 'Produto de teste']);

    Livewire::test(Catalog::class)
        ->call('loadMore')
        ->assertViewHas('products', fn ($products) => $products->count() === 20)
        ->set('search', 'Produto')
        ->assertViewHas('products', fn ($products) => $products->count() === 12)
        ->call('loadMore')
        ->call('sortBy', 'menor-preco')
        ->assertViewHas('products', fn ($products) => $products->count() === 12);
});

test('sortBy ignores a value outside the allow-list', function () {
    Product::factory()->count(3)->create();

    Livewire::test(Catalog::class)
        ->call('sortBy', 'price_cents; DROP TABLE products')
        ->assertSet('sort', 'recentes')
        ->assertOk();

    expect(Product::count())->toBe(3);
});

test('inactive and sold out products are not listed', function () {
    $available = Product::factory()->create(['stock' => 1]);
    Product::factory()->create(['stock' => 0]);
    Product::factory()->inactive()->create();

    Livewire::test(Catalog::class)
        ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$available->id])
        ->assertViewHas('total', 1);
});

test('search matches the product description as well as the name', function () {
    $match = Product::factory()->create(['name' => 'Camiseta', 'description' => 'Tecido de algodão orgânico.']);
    Product::factory()->create(['name' => 'Caneca', 'description' => 'Porcelana branca.']);

    Livewire::test(Catalog::class)
        ->set('search', 'algodão')
        ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$match->id]);
});

test('products can be sorted by price', function (string $sort, array $expectedPrices) {
    Product::factory()->count(3)->sequence(['price_cents' => 3000], ['price_cents' => 1000], ['price_cents' => 2000])->create();

    Livewire::test(Catalog::class)
        ->call('sortBy', $sort)
        ->assertViewHas('products', fn ($products) => $products->pluck('price_cents')->all() === $expectedPrices);
})->with([
    'cheapest first' => ['menor-preco', [1000, 2000, 3000]],
    'most expensive first' => ['maior-preco', [3000, 2000, 1000]],
]);

test('infinite scroll stops at 240 products so one visitor can not load the whole table', function () {
    Product::factory()->count(250)->recycle(User::factory()->create())->create();

    $component = Livewire::test(Catalog::class);

    foreach (range(1, 25) as $scroll) {
        $component->call('loadMore');
    }

    $component->assertViewHas('products', fn ($products) => $products->count() === 240)
        ->assertViewHas('hasMore', false)
        ->assertViewHas('reachedLimit', true);
});
