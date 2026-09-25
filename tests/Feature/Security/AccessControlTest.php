<?php

use App\Livewire\Account\Addresses;
use App\Livewire\Account\Cards;
use App\Livewire\Orders\ShowOrder;
use App\Livewire\Seller\ListProducts;
use App\Models\Address;
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

test('non admins can not reach the admin area', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.users'))->assertForbidden();
    $this->actingAs(User::factory()->create())->get(route('admin.audit'))->assertForbidden();
});

test('admins only see masked cpf and phone', function () {
    $customer = User::factory()->create(['cpf' => '52998224725', 'phone' => '41998765432']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('***.982.247-**')
        ->assertDontSee('52998224725')
        ->assertDontSee('41998765432');
});
