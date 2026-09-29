<?php

use App\Livewire\Shop\Catalog;
use App\Models\Product;
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
