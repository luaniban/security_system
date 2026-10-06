<?php

use App\Enums\OrderStatus;
use App\Livewire\Seller\ListSales;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('a seller sees only their own sales', function () {
    $seller = User::factory()->create();
    $mine = Order::factory()->for($seller, 'seller')->create();
    Order::factory()->create();
    Order::factory()->for($seller, 'buyer')->create();

    Livewire::actingAs($seller)
        ->test(ListSales::class)
        ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$mine->id]);
});

test('sales can be filtered by status', function () {
    $seller = User::factory()->create();
    Order::factory()->for($seller, 'seller')->create();
    $shipped = Order::factory()->for($seller, 'seller')->shipped()->create();

    Livewire::actingAs($seller)
        ->test(ListSales::class)
        ->set('status', OrderStatus::Shipped->value)
        ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$shipped->id]);
});

test('an unknown status filter is ignored and every sale is listed', function (string $status) {
    $seller = User::factory()->create();
    Order::factory()->for($seller, 'seller')->count(2)->create();

    Livewire::actingAs($seller)
        ->test(ListSales::class)
        ->set('status', $status)
        ->assertViewHas('orders', fn ($orders) => $orders->total() === 2);
})->with(['unknown value' => 'refunded', 'sql payload' => "paid' OR '1'='1"]);
