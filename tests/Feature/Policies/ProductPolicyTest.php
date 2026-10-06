<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('active products are public', function () {
    $product = Product::factory()->create();

    expect(Gate::allows('view', $product))->toBeTrue()
        ->and(User::factory()->create()->can('view', $product))->toBeTrue();
});

test('inactive products are visible only to their seller', function () {
    $product = Product::factory()->inactive()->create();

    expect(Gate::allows('view', $product))->toBeFalse()
        ->and(User::factory()->create()->can('view', $product))->toBeFalse()
        ->and($product->seller->can('view', $product))->toBeTrue();
});

test('only verified users may create products', function () {
    expect(User::factory()->create()->can('create', Product::class))->toBeTrue()
        ->and(User::factory()->unverified()->create()->can('create', Product::class))->toBeFalse();
});

test('only the seller may update or delete a product', function (string $ability) {
    $product = Product::factory()->create();

    expect($product->seller->can($ability, $product))->toBeTrue()
        ->and(User::factory()->create()->can($ability, $product))->toBeFalse();
})->with(['update', 'delete']);

test('a product can be purchased only when active, in stock and from someone else', function (array $attributes, string $buyer, bool $allowed) {
    $product = Product::factory()->create($attributes);
    $user = $buyer === 'seller' ? $product->seller : User::factory()->create();

    expect($user->can('purchase', $product))->toBe($allowed);
})->with([
    'active with stock' => [['stock' => 1], 'stranger', true],
    'out of stock' => [['stock' => 0], 'stranger', false],
    'inactive' => [['is_active' => false], 'stranger', false],
    'own product' => [['stock' => 5], 'seller', false],
]);
