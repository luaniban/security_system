<?php

use App\Enums\OrderStatus;
use App\Livewire\Checkout\PlaceOrder;
use App\Livewire\Orders\ShowOrder;
use App\Livewire\Shop\ShowProduct;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->buyer = User::factory()->create();
    $this->address = Address::factory()->for($this->buyer)->create();
    $this->card = PaymentCard::factory()->for($this->buyer)->create();
});

function checkout(User $buyer, Address $address, PaymentCard $card, string $cvv = '123')
{
    return Livewire::actingAs($buyer)
        ->test(PlaceOrder::class)
        ->set('addressId', $address->id)
        ->set('cardId', $card->id)
        ->set('cvv', $cvv)
        ->call('placeOrder');
}

test('buyer can add a product to the cart', function () {
    $product = Product::factory()->create(['stock' => 5]);

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertHasNoErrors()
        ->assertDispatched('cart-updated');

    expect($this->buyer->cartItems()->first()->quantity)->toBe(2);
});

test('quantity can not be negative or above the stock', function (int $quantity) {
    $product = Product::factory()->create(['stock' => 3]);

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->set('quantity', $quantity)
        ->call('addToCart')
        ->assertHasErrors('quantity');

    expect($this->buyer->cartItems()->count())->toBe(0);
})->with([-5, 0, 4, 999999]);

test('sellers can not buy their own product', function () {
    $product = Product::factory()->for($this->buyer, 'seller')->create();

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->call('addToCart')
        ->assertForbidden();
});

test('checkout charges the database price and splits orders per seller', function () {
    $productA = Product::factory()->create(['price_cents' => 10000, 'stock' => 5]);
    $productB = Product::factory()->create(['price_cents' => 5000, 'stock' => 5]);
    CartItem::factory()->for($this->buyer)->create(['product_id' => $productA->id, 'quantity' => 2]);
    CartItem::factory()->for($this->buyer)->create(['product_id' => $productB->id, 'quantity' => 1]);

    checkout($this->buyer, $this->address, $this->card)
        ->assertHasNoErrors()
        ->assertRedirect(route('orders.index'));

    $orders = Order::where('buyer_id', $this->buyer->id)->get();

    expect($orders)->toHaveCount(2)
        ->and($orders->sum('subtotal_cents'))->toBe(25000)
        ->and($orders->every(fn (Order $order) => $order->status === OrderStatus::Paid))->toBeTrue()
        ->and($productA->fresh()->stock)->toBe(3)
        ->and($productB->fresh()->stock)->toBe(4)
        ->and($this->buyer->cartItems()->count())->toBe(0);
});

test('checkout can not use another user\'s address or card', function () {
    CartItem::factory()->for($this->buyer)->create();
    $foreignAddress = Address::factory()->create();
    $foreignCard = PaymentCard::factory()->create();

    checkout($this->buyer, $foreignAddress, $foreignCard)->assertHasErrors(['addressId', 'cardId']);

    expect(Order::count())->toBe(0);
});

test('checkout fails when stock was sold out in the meantime', function () {
    $product = Product::factory()->create(['stock' => 1]);
    CartItem::factory()->for($this->buyer)->create(['product_id' => $product->id, 'quantity' => 1]);
    $product->update(['stock' => 0]);

    checkout($this->buyer, $this->address, $this->card)->assertHasErrors('payment');

    expect(Order::count())->toBe(0)->and($product->fresh()->stock)->toBe(0);
});

test('declined payments do not create orders nor change stock', function () {
    $declinedCard = PaymentCard::factory()->for($this->buyer)->declined()->create();
    $product = Product::factory()->create(['stock' => 2]);
    CartItem::factory()->for($this->buyer)->create(['product_id' => $product->id]);

    checkout($this->buyer, $this->address, $declinedCard)->assertHasErrors('payment');

    expect(Order::count())->toBe(0)->and($product->fresh()->stock)->toBe(2);
});

test('the cvv is required, validated and never kept in the component state', function () {
    CartItem::factory()->for($this->buyer)->create();

    checkout($this->buyer, $this->address, $this->card, 'abc')
        ->assertHasErrors('cvv')
        ->assertSet('cvv', '');
});

test('cancelling a paid order returns the stock', function () {
    $product = Product::factory()->create(['stock' => 3]);
    CartItem::factory()->for($this->buyer)->create(['product_id' => $product->id, 'quantity' => 2]);
    checkout($this->buyer, $this->address, $this->card);

    $order = Order::firstWhere('buyer_id', $this->buyer->id);

    Livewire::actingAs($this->buyer)
        ->test(ShowOrder::class, ['order' => $order])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(3);
});

test('delivery flow: seller ships, buyer confirms delivery', function () {
    $order = Order::factory()->create(['buyer_id' => $this->buyer->id]);

    Livewire::actingAs($order->seller)
        ->test(ShowOrder::class, ['order' => $order])
        ->set('trackingCode', 'invalid')
        ->call('ship')
        ->assertHasErrors('trackingCode')
        ->set('trackingCode', 'br123456789br')
        ->call('ship')
        ->assertHasNoErrors();

    Livewire::actingAs($this->buyer)
        ->test(ShowOrder::class, ['order' => $order->fresh()])
        ->call('confirmDelivery');

    expect($order->fresh()->status)->toBe(OrderStatus::Delivered)
        ->and($order->fresh()->tracking_code)->toBe('BR123456789BR');
});

test('buy now adds the product to the cart and goes to checkout', function () {
    $product = Product::factory()->create(['stock' => 5]);

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->call('incrementQuantity')
        ->call('buyNow')
        ->assertHasNoErrors()
        ->assertRedirect(route('checkout'));

    expect($this->buyer->cartItems()->first()->quantity)->toBe(2);
});

test('buy now does not redirect when the purchase is not allowed', function () {
    $product = Product::factory()->for($this->buyer, 'seller')->create();

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->call('buyNow')
        ->assertForbidden();

    expect($this->buyer->cartItems()->count())->toBe(0);
});

test('quantity stepper stays between 1 and the available stock', function () {
    $product = Product::factory()->create(['stock' => 2]);

    Livewire::actingAs($this->buyer)
        ->test(ShowProduct::class, ['product' => $product])
        ->call('decrementQuantity')
        ->assertSet('quantity', 1)
        ->call('incrementQuantity')
        ->call('incrementQuantity')
        ->call('incrementQuantity')
        ->assertSet('quantity', 2);
});
