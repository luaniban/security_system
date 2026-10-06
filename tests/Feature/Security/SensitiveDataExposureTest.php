<?php

use App\Enums\BrazilianState;
use App\Livewire\Auth\Login;
use App\Livewire\Checkout\PlaceOrder;
use App\Models\Address;
use App\Models\AuditLog;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('card numbers and holder names are encrypted at rest', function () {
    $card = PaymentCard::factory()->create(['holder_name' => 'ANA SOUZA']);

    $raw = DB::table('payment_cards')->find($card->id);

    expect($raw->number)->not->toContain('4111111111111111')
        ->and(Crypt::decryptString($raw->number))->toBe('4111111111111111')
        ->and(Crypt::decryptString($raw->holder_name))->toBe('ANA SOUZA')
        ->and($raw->fingerprint)->not->toContain('4111111111111111')
        ->and($raw->last_four)->toBe('1111');
});

test('addresses are encrypted at rest except the city and state used for shipping', function () {
    $address = Address::factory()->create([
        'recipient_name' => 'Ana Souza',
        'zip_code' => '01001000',
        'street' => 'Rua das Flores',
        'number' => '742',
        'district' => 'Jardins',
        'city' => 'São Paulo',
        'state' => BrazilianState::SP,
    ]);

    $raw = DB::table('addresses')->find($address->id);

    expect(Crypt::decryptString($raw->recipient_name))->toBe('Ana Souza')
        ->and(Crypt::decryptString($raw->zip_code))->toBe('01001000')
        ->and(Crypt::decryptString($raw->street))->toBe('Rua das Flores')
        ->and(Crypt::decryptString($raw->number))->toBe('742')
        ->and(Crypt::decryptString($raw->district))->toBe('Jardins')
        ->and($raw->city)->toBe('São Paulo')
        ->and($raw->state)->toBe('SP');
});

test('the shipping address copied to an order is encrypted at rest', function () {
    $order = Order::factory()->create();

    $raw = DB::table('orders')->find($order->id);

    expect($raw->shipping_address)->not->toContain('Praça da Sé')
        ->and(json_decode(Crypt::decryptString($raw->shipping_address), true)['street'])->toBe('Praça da Sé');
});

test('passwords are stored as bcrypt hashes', function () {
    $user = User::factory()->create(['password' => 'Str0ng!Passw0rd']);

    $raw = DB::table('users')->where('id', $user->id)->value('password');

    expect($raw)->not->toBe('Str0ng!Passw0rd')
        ->and(Hash::info($raw)['algoName'])->toBe('bcrypt')
        ->and(Hash::check('Str0ng!Passw0rd', $raw))->toBeTrue();
});

test('secrets are left out when models are serialized', function () {
    $user = User::factory()->create()->fresh();
    $card = PaymentCard::factory()->for($user)->create()->fresh();

    expect(array_intersect(array_keys($user->toArray()), ['password', 'remember_token', 'cpf', 'cpf_hash', 'phone']))->toBeEmpty()
        ->and(array_intersect(array_keys($card->toArray()), ['number', 'fingerprint']))->toBeEmpty();
});

test('the saved cards page never renders the full card number', function () {
    $user = User::factory()->create();
    PaymentCard::factory()->for($user)->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('cards.index'))
        ->assertSee('•••• •••• •••• 1111')
        ->assertDontSee('4111111111111111');
});

test('failed logins are audited without the typed password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'Sup3r-Secret-Guess')
        ->call('login');

    expect(AuditLog::where('event', 'auth.failed')->sole()->context)->toBe(['email' => $user->email])
        ->and(AuditLog::all()->toJson())->not->toContain('Sup3r-Secret-Guess');
});

test('payment audit entries never contain the card number or the cvv', function () {
    $buyer = User::factory()->create();
    $address = Address::factory()->for($buyer)->create();
    $card = PaymentCard::factory()->for($buyer)->create();
    $declinedCard = PaymentCard::factory()->for($buyer)->declined()->create();
    CartItem::factory()->for($buyer)->create();

    $component = Livewire::actingAs($buyer)->test(PlaceOrder::class)->set('addressId', $address->id);
    $component->set('cardId', $declinedCard->id)->set('cvv', '987')->call('placeOrder');
    $component->set('cardId', $card->id)->set('cvv', '987')->call('placeOrder')->assertHasNoErrors();

    expect(array_keys(AuditLog::where('event', 'payment.declined')->sole()->context))->toBe(['card_id'])
        ->and(array_keys(AuditLog::where('event', 'order.placed')->sole()->context))->toBe(['order', 'total_cents'])
        ->and(AuditLog::all()->toJson())->not->toContain('4111111111111111')->not->toContain('4000000000000002');
});
