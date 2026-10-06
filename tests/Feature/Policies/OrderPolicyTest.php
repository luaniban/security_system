<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

function orderActor(Order $order, string $role): User
{
    return match ($role) {
        'buyer' => $order->buyer,
        'seller' => $order->seller,
        'stranger' => User::factory()->create(),
    };
}

test('each order action is allowed only for the right party and status', function (string $ability, string $role, OrderStatus $status, bool $allowed) {
    $order = Order::factory()->create(['status' => $status]);

    expect(orderActor($order, $role)->can($ability, $order))->toBe($allowed);
})->with([
    'buyer views' => ['view', 'buyer', OrderStatus::Paid, true],
    'seller views' => ['view', 'seller', OrderStatus::Paid, true],
    'stranger views' => ['view', 'stranger', OrderStatus::Paid, false],

    'seller ships a paid order' => ['ship', 'seller', OrderStatus::Paid, true],
    'seller ships again' => ['ship', 'seller', OrderStatus::Shipped, false],
    'seller ships a delivered order' => ['ship', 'seller', OrderStatus::Delivered, false],
    'seller ships a cancelled order' => ['ship', 'seller', OrderStatus::Cancelled, false],
    'buyer ships' => ['ship', 'buyer', OrderStatus::Paid, false],
    'stranger ships' => ['ship', 'stranger', OrderStatus::Paid, false],

    'buyer confirms a shipped order' => ['confirmDelivery', 'buyer', OrderStatus::Shipped, true],
    'buyer confirms before shipping' => ['confirmDelivery', 'buyer', OrderStatus::Paid, false],
    'buyer confirms twice' => ['confirmDelivery', 'buyer', OrderStatus::Delivered, false],
    'buyer confirms a cancelled order' => ['confirmDelivery', 'buyer', OrderStatus::Cancelled, false],
    'seller confirms delivery' => ['confirmDelivery', 'seller', OrderStatus::Shipped, false],
    'stranger confirms delivery' => ['confirmDelivery', 'stranger', OrderStatus::Shipped, false],

    'buyer cancels a paid order' => ['cancel', 'buyer', OrderStatus::Paid, true],
    'seller cancels a paid order' => ['cancel', 'seller', OrderStatus::Paid, true],
    'buyer cancels after shipping' => ['cancel', 'buyer', OrderStatus::Shipped, false],
    'buyer cancels after delivery' => ['cancel', 'buyer', OrderStatus::Delivered, false],
    'buyer cancels twice' => ['cancel', 'buyer', OrderStatus::Cancelled, false],
    'stranger cancels' => ['cancel', 'stranger', OrderStatus::Paid, false],
]);
