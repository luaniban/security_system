<?php

use App\Livewire\Account\Addresses;
use App\Livewire\Account\Cards;
use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Products as AdminProducts;
use App\Livewire\Admin\Users;
use App\Livewire\Cart\ShowCart;
use App\Livewire\Orders\ShowOrder;
use App\Livewire\Seller\ListProducts;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Shop\ShowProduct;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('guests are redirected to login from private areas', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['dashboard', 'cart', 'checkout', 'orders.index', 'addresses.index', 'seller.products.index', 'admin.users']);

test('a user can not view another user\'s order', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('orders.show', $order))->assertForbidden();
});

test('buyer and seller can both view the order', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->buyer)->get(route('orders.show', $order))->assertOk();
    $this->actingAs($order->seller)->get(route('orders.show', $order))->assertOk();
});

test('orders are addressed by an unguessable code instead of the sequential id', function () {
    $order = Order::factory()->create();

    expect(route('orders.show', $order))->toContain($order->code)
        ->and($order->code)->toHaveLength(26);

    $this->actingAs($order->buyer)->get('/pedidos/'.$order->id)->assertNotFound();
});

test('a user can not edit or delete another user\'s address', function () {
    $victimAddress = Address::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Addresses::class)->call('edit', $victimAddress->id)->assertNotFound();
    Livewire::test(Addresses::class)->call('delete', $victimAddress->id)->assertNotFound();

    expect($victimAddress->fresh())->not->toBeNull();
});

test('a user can not delete another user\'s card', function () {
    $victimCard = PaymentCard::factory()->create();

    $this->actingAs(User::factory()->create())->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(Cards::class)->call('delete', $victimCard->id)->assertNotFound();
    expect($victimCard->fresh())->not->toBeNull();
});

test('a seller can not edit or delete another seller\'s product', function () {
    $product = Product::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('seller.products.edit', $product))->assertForbidden();

    Livewire::actingAs($intruder)->test(ListProducts::class)->call('delete', $product->id)->assertNotFound();
    expect($product->fresh())->not->toBeNull();
});

test('only the seller can mark an order as shipped', function () {
    $order = Order::factory()->create();

    Livewire::actingAs($order->buyer)
        ->test(ShowOrder::class, ['order' => $order])
        ->set('trackingCode', 'AA123456789BR')
        ->call('ship')
        ->assertForbidden();

    Livewire::actingAs($order->seller)
        ->test(ShowOrder::class, ['order' => $order])
        ->set('trackingCode', 'AA123456789BR')
        ->call('ship')
        ->assertHasNoErrors();

    expect($order->fresh()->tracking_code)->toBe('AA123456789BR');
});

test('the order bound to the component can not be swapped from the browser', function () {
    $order = Order::factory()->create();

    Livewire::actingAs($order->buyer)
        ->test(ShowOrder::class, ['order' => $order])
        ->set('order', Order::factory()->create()->id);
})->throws(CannotUpdateLockedPropertyException::class);

test('a user can not change or remove another user\'s cart item', function () {
    $victimItem = CartItem::factory()->create(['quantity' => 1]);
    $intruder = User::factory()->create();

    Livewire::actingAs($intruder)->test(ShowCart::class)->call('updateQuantity', $victimItem->id, 5)->assertNotFound();
    Livewire::actingAs($intruder)->test(ShowCart::class)->call('remove', $victimItem->id);

    expect($victimItem->fresh())->not->toBeNull()
        ->quantity->toBe(1);
});

test('inactive products are hidden from everyone but their seller', function () {
    $product = Product::factory()->inactive()->create();

    $this->get(route('products.show', $product))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('products.show', $product))->assertForbidden();
    $this->actingAs($product->seller)->get(route('products.show', $product))->assertOk();
});

test('guests are sent to the login page instead of adding to the cart', function () {
    Livewire::test(ShowProduct::class, ['product' => Product::factory()->create()])
        ->call('addToCart')
        ->assertRedirect(route('login'));

    expect(CartItem::count())->toBe(0);
});

test('unverified users are sent to verify their e-mail instead of adding to the cart', function () {
    Livewire::actingAs(User::factory()->unverified()->create())
        ->test(ShowProduct::class, ['product' => Product::factory()->create()])
        ->call('addToCart')
        ->assertRedirect(route('verification.notice'));

    expect(CartItem::count())->toBe(0);
});

test('unverified users can not open the product form even when it is mounted directly', function () {
    Livewire::actingAs(User::factory()->unverified()->create())
        ->test(ProductForm::class)
        ->assertForbidden();
});

test('admin components refuse non admins even when mounted directly', function (string $component) {
    Livewire::actingAs(User::factory()->create())
        ->test($component)
        ->assertForbidden();
})->with([Users::class, AuditLogs::class, AdminProducts::class, AdminOrders::class]);

test('non admins can not reach the admin area', function (string $route) {
    $this->actingAs(User::factory()->create())->get(route($route))->assertForbidden();
})->with(['admin.users', 'admin.products', 'admin.orders', 'admin.audit']);

test('admins only see masked cpf and phone', function () {
    $customer = User::factory()->create(['cpf' => '52998224725', 'phone' => '41998765432']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('***.982.247-**')
        ->assertDontSee('52998224725')
        ->assertDontSee('41998765432');
});
