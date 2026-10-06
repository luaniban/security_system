<?php

use App\Enums\CardBrand;
use App\Livewire\Account\Cards;
use App\Models\PaymentCard;
use App\Models\User;
use App\Support\BlindIndex;
use Livewire\Livewire;

/**
 * @return array<string, string>
 */
function validCardInput(array $overrides = []): array
{
    return array_merge([
        'holder_name' => 'ana souza',
        'card_number' => '5555 5555 5555 4444',
        'exp_month' => '12',
        'exp_year' => '2028',
    ], $overrides);
}

function cardsComponentFor(User $user)
{
    test()->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    return Livewire::test(Cards::class);
}

test('a user can save a card and only the masked number is shown', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();

    cardsComponentFor($user)
        ->set(validCardInput())
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('card_number', '')
        ->assertSee('•••• •••• •••• 4444')
        ->assertDontSee('5555555555554444');

    $card = $user->paymentCards()->sole();

    expect($card)
        ->holder_name->toBe('ANA SOUZA')
        ->number->toBe('5555555555554444')
        ->last_four->toBe('4444')
        ->brand->toBe(CardBrand::Mastercard)
        ->exp_month->toBe(12)
        ->exp_year->toBe(2028);

    $this->assertDatabaseHas('audit_logs', ['event' => 'card.added', 'user_id' => $user->id]);
});

test('a card number with a wrong checksum is rejected and not echoed back', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();

    cardsComponentFor($user)
        ->set(validCardInput(['card_number' => '5555 5555 5555 4445']))
        ->call('save')
        ->assertHasErrors(['card_number' => 'O número do cartão é inválido.'])
        ->assertSet('card_number', '');

    expect($user->paymentCards()->count())->toBe(0);
});

test('a card that expired last month is rejected', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();

    cardsComponentFor($user)
        ->set(validCardInput(['exp_month' => '9', 'exp_year' => '2026']))
        ->call('save')
        ->assertHasErrors(['exp_month' => 'Este cartão está vencido.']);

    expect($user->paymentCards()->count())->toBe(0);
});

test('a card expiring this month is still accepted', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();

    cardsComponentFor($user)
        ->set(validCardInput(['exp_month' => '10', 'exp_year' => '2026']))
        ->call('save')
        ->assertHasNoErrors();

    expect($user->paymentCards()->count())->toBe(1);
});

test('expiry years outside the accepted window are rejected', function (string $year) {
    $this->travelTo('2026-10-05');

    cardsComponentFor(User::factory()->create())
        ->set(validCardInput(['exp_year' => $year]))
        ->call('save')
        ->assertHasErrors('exp_year');
})->with(['last year' => '2025', 'more than 20 years ahead' => '2047']);

test('the holder name only accepts letters, as printed on the card', function () {
    $this->travelTo('2026-10-05');

    cardsComponentFor(User::factory()->create())
        ->set(validCardInput(['holder_name' => 'Ana 1234']))
        ->call('save')
        ->assertHasErrors(['holder_name' => 'Use apenas letras, como impresso no cartão.']);
});

test('the same card can not be saved twice', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();
    PaymentCard::factory()->for($user)->create();

    cardsComponentFor($user)
        ->set(validCardInput(['card_number' => '4111 1111 1111 1111']))
        ->call('save')
        ->assertHasErrors(['card_number' => 'Este cartão já está cadastrado.']);

    expect($user->paymentCards()->count())->toBe(1);
});

test('a user can not keep more than 5 cards', function () {
    $this->travelTo('2026-10-05');
    $user = User::factory()->create();
    PaymentCard::factory()->for($user)->count(5)
        ->sequence(fn ($sequence) => ['fingerprint' => BlindIndex::make('card', 'card-'.$sequence->index)])
        ->create();

    cardsComponentFor($user)
        ->set(validCardInput())
        ->call('save')
        ->assertHasErrors(['card_number' => 'Limite de 5 cartões atingido.']);

    expect($user->paymentCards()->count())->toBe(5);
});

test('a user can delete a card', function () {
    $user = User::factory()->create();
    $card = PaymentCard::factory()->for($user)->create();

    cardsComponentFor($user)->call('delete', $card->id);

    $this->assertModelMissing($card);
    $this->assertDatabaseHas('audit_logs', ['event' => 'card.removed', 'user_id' => $user->id]);
});
