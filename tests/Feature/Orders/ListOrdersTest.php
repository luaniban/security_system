<?php

use App\Livewire\Orders\ListOrders;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('a buyer sees only their own purchases', function () {
    $buyer = User::factory()->create();
    $purchase = Order::factory()->for($buyer, 'buyer')->create();
    Order::factory()->for($buyer, 'seller')->create();
    Order::factory()->create();

    Livewire::actingAs($buyer)
        ->test(ListOrders::class)
        ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$purchase->id]);
});

test('purchases are paginated ten per page', function () {
    $buyer = User::factory()->create();
    Order::factory()->for($buyer, 'buyer')->count(11)->create();

    Livewire::actingAs($buyer)
        ->test(ListOrders::class)
        ->assertViewHas('orders', fn ($orders) => $orders->count() === 10 && $orders->total() === 11);
});
