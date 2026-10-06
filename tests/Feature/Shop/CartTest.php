<?php

use App\Livewire\Cart\ShowCart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('the cart groups items per seller and totals them with database prices', function () {
    $buyer = User::factory()->create();
    $sellerA = User::factory()->create();
    CartItem::factory()->for($buyer)->create([
        'product_id' => Product::factory()->for($sellerA, 'seller')->create(['price_cents' => 2500]),
        'quantity' => 2,
    ]);
    CartItem::factory()->for($buyer)->create([
        'product_id' => Product::factory()->for($sellerA, 'seller')->create(['price_cents' => 1000]),
        'quantity' => 1,
    ]);
    CartItem::factory()->for($buyer)->create([
        'product_id' => Product::factory()->create(['price_cents' => 4000]),
        'quantity' => 1,
    ]);

    Livewire::actingAs($buyer)
        ->test(ShowCart::class)
        ->assertViewHas('groups', fn ($groups) => $groups->count() === 2)
        ->assertViewHas('subtotal', 10000);
});

test('the cart page shows only the user\'s own items', function () {
    $buyer = User::factory()->create();
    CartItem::factory()->for($buyer)->create(['product_id' => Product::factory()->create(['name' => 'Meu item'])]);
    CartItem::factory()->create(['product_id' => Product::factory()->create(['name' => 'Item de outro'])]);

    $this->actingAs($buyer)
        ->get(route('cart'))
        ->assertSee('Meu item')
        ->assertDontSee('Item de outro');
});

test('updating the quantity is capped by the stock and by the per-item limit', function (int $stock, int $requested, int $expected) {
    $buyer = User::factory()->create();
    $item = CartItem::factory()->for($buyer)->create(['product_id' => Product::factory()->create(['stock' => $stock])]);

    Livewire::actingAs($buyer)
        ->test(ShowCart::class)
        ->call('updateQuantity', $item->id, $requested)
        ->assertDispatched('cart-updated');

    expect($item->fresh()->quantity)->toBe($expected);
})->with([
    'within stock' => [10, 4, 4],
    'above stock' => [3, 10, 3],
    'above the 20 per item limit' => [50, 99, 20],
]);

test('a quantity below one or that is not a number removes the item', function (int|string $quantity) {
    $buyer = User::factory()->create();
    $item = CartItem::factory()->for($buyer)->create(['quantity' => 2]);

    Livewire::actingAs($buyer)
        ->test(ShowCart::class)
        ->call('updateQuantity', $item->id, $quantity);

    $this->assertModelMissing($item);
})->with(['zero' => 0, 'negative' => -3, 'text' => 'abc', 'decimal' => '1.5']);

test('an item can be removed from the cart', function () {
    $buyer = User::factory()->create();
    $item = CartItem::factory()->for($buyer)->create();

    Livewire::actingAs($buyer)
        ->test(ShowCart::class)
        ->call('remove', $item->id)
        ->assertDispatched('cart-updated');

    $this->assertModelMissing($item);
});
