<?php

use App\Enums\OrderStatus;
use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Products as AdminProducts;
use App\Livewire\Admin\Users;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('admins can search users by name or e-mail', function (string $term) {
    $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@example.com']);
    $ana = User::factory()->create(['name' => 'Ana Souza', 'email' => 'ana@example.com']);
    User::factory()->create(['name' => 'Bruno Lima', 'email' => 'bruno@example.com']);

    Livewire::actingAs($admin)
        ->test(Users::class)
        ->set('search', $term)
        ->assertViewHas('users', fn ($users) => $users->pluck('id')->all() === [$ana->id]);
})->with(['by name' => 'souza', 'by e-mail' => 'ana@']);

test('admins see every product from every seller, including paused ones', function () {
    $active = Product::factory()->create(['name' => 'Produto ativo']);
    $paused = Product::factory()->inactive()->create(['name' => 'Produto pausado']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.products'))
        ->assertSee('Produto ativo')
        ->assertSee('Produto pausado')
        ->assertSee($active->seller->name)
        ->assertSee($paused->seller->name);
});

test('admins can search products and filter them by situation', function (array $filters, array $expectedNames) {
    Product::factory()->create(['name' => 'Camiseta azul']);
    Product::factory()->inactive()->create(['name' => 'Camiseta verde']);
    Product::factory()->create(['name' => 'Caneca']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AdminProducts::class)
        ->set($filters)
        ->assertViewHas('products', fn ($products) => $products->pluck('name')->sort()->values()->all() === $expectedNames);
})->with([
    'by name' => [['search' => 'camiseta'], ['Camiseta azul', 'Camiseta verde']],
    'only paused' => [['situation' => 'pausados'], ['Camiseta verde']],
    'name and active' => [['search' => 'camiseta', 'situation' => 'ativos'], ['Camiseta azul']],
]);

test('admins see every order with buyer, seller and the amount moved', function () {
    $paid = Order::factory()->create(['total_cents' => 10000]);
    Order::factory()->create(['total_cents' => 5000, 'status' => OrderStatus::Cancelled]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.orders'))
        ->assertSee($paid->code)
        ->assertSee($paid->buyer->email)
        ->assertSee($paid->seller->name);

    Livewire::actingAs($admin)
        ->test(AdminOrders::class)
        ->assertViewHas('orderCount', 2)
        ->assertViewHas('revenueCents', 10000);
});

test('admins can filter orders by status', function () {
    Order::factory()->create();
    $shipped = Order::factory()->shipped()->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AdminOrders::class)
        ->set('status', OrderStatus::Shipped->value)
        ->assertViewHas('orders', fn ($orders) => $orders->pluck('id')->all() === [$shipped->id]);
});

test('the orders page does not reveal shipping addresses', function () {
    Order::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.orders'))
        ->assertDontSee('Praça da Sé');
});

test('admins can filter the audit trail by event', function () {
    AuditLog::factory()->create(['event' => 'auth.login']);
    $failure = AuditLog::factory()->create(['event' => 'auth.failed']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AuditLogs::class)
        ->set('event', 'auth.failed')
        ->assertViewHas('logs', fn ($logs) => $logs->pluck('id')->all() === [$failure->id]);
});

test('an event that was never logged is ignored by the audit filter', function (string $event) {
    AuditLog::factory()->count(2)->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AuditLogs::class)
        ->set('event', $event)
        ->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
})->with(['unknown event' => 'auth.nothing', 'sql payload' => "auth.login' OR '1'='1"]);
