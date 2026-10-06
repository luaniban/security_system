<?php

use App\Enums\OrderStatus;
use App\Livewire\Account\Addresses;
use App\Livewire\Orders\ShowOrder;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Shop\ShowProduct;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentCard;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('a seller can not point the edit form at another seller\'s product', function () {
    $product = Product::factory()->create();

    Livewire::actingAs($product->seller)
        ->test(ProductForm::class, ['product' => $product])
        ->set('productId', Product::factory()->create()->id);
})->throws(CannotUpdateLockedPropertyException::class);

test('a user can not point the address form at another user\'s address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('editingId', Address::factory()->create()->id);
})->throws(CannotUpdateLockedPropertyException::class);

test('the product bound to the product page can not be swapped from the browser', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(ShowProduct::class, ['product' => Product::factory()->create()])
        ->set('product', Product::factory()->inactive()->create()->id);
})->throws(CannotUpdateLockedPropertyException::class);

test('quantities that are not whole numbers are rejected when adding to the cart', function (string $quantity) {
    $buyer = User::factory()->create();

    Livewire::actingAs($buyer)
        ->test(ShowProduct::class, ['product' => Product::factory()->create(['stock' => 50])])
        ->set('quantity', $quantity)
        ->call('addToCart')
        ->assertHasErrors('quantity');

    expect($buyer->cartItems()->count())->toBe(0);
})->with(['decimal' => '1.5', 'scientific notation' => '1e3', 'sql payload' => '1 OR 1=1', 'hexadecimal' => '0x10']);

test('repeated adds can not push one cart line past 20 units', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['stock' => 100]);

    $component = Livewire::actingAs($buyer)->test(ShowProduct::class, ['product' => $product]);
    $component->set('quantity', 15)->call('addToCart')->assertHasNoErrors();
    $component->set('quantity', 15)->call('addToCart')->assertHasNoErrors();
    $component->set('quantity', 21)->call('addToCart')->assertHasErrors('quantity');

    expect($buyer->cartItems()->sole()->quantity)->toBe(20);
});

test('ownership and status columns can not be mass assigned', function (string $model, string $attribute) {
    (new $model)->fill([$attribute => 1]);
})->with([
    'product owner' => [Product::class, 'seller_id'],
    'cart owner' => [CartItem::class, 'user_id'],
    'address owner' => [Address::class, 'user_id'],
    'card owner' => [PaymentCard::class, 'user_id'],
    'e-mail verification' => [User::class, 'email_verified_at'],
    'order tracking code' => [Order::class, 'tracking_code'],
    'order cancellation date' => [Order::class, 'cancelled_at'],
])->throws(MassAssignmentException::class);

test('cancelling twice does not give the stock back twice', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2]);

    $component = Livewire::actingAs($order->buyer)->test(ShowOrder::class, ['order' => $order]);
    $component->call('cancel')->assertHasNoErrors();
    $component->call('cancel')->assertForbidden();

    expect($product->fresh()->stock)->toBe(7)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('a buyer can not cancel an order that was already shipped', function () {
    $order = Order::factory()->shipped()->create();

    Livewire::actingAs($order->buyer)
        ->test(ShowOrder::class, ['order' => $order])
        ->call('cancel')
        ->assertForbidden();

    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});

test('a seller can not confirm the delivery of their own sale', function () {
    $order = Order::factory()->shipped()->create();

    Livewire::actingAs($order->seller)
        ->test(ShowOrder::class, ['order' => $order])
        ->call('confirmDelivery')
        ->assertForbidden();

    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});

test('a verification link with a tampered signature is refused', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    $this->actingAs($user)->get($url.'0')->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('an expired verification link is refused', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    $this->actingAs($user)->get($url)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a verification link issued to another account can not verify the signed-in user', function () {
    $user = User::factory()->unverified()->create();
    $other = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $other->id, 'hash' => sha1($other->email)]);

    $this->actingAs($user)->get($url)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($other->fresh()->hasVerifiedEmail())->toBeFalse();
});
