<?php

use App\Exceptions\PaymentDeclinedException;
use App\Models\PaymentCard;
use App\Services\PaymentGateway;

test('a valid card with a correct cvv is charged', function (string $cvv) {
    $card = PaymentCard::factory()->create();

    expect(fn () => (new PaymentGateway)->charge($card, $cvv, 1000))->not->toThrow(PaymentDeclinedException::class);
})->with(['3 digits' => '123', '4 digits (amex)' => '1234']);

test('a card is valid until the last day of its expiry month', function () {
    $card = PaymentCard::factory()->create(['exp_month' => 10, 'exp_year' => 2026]);

    $this->travelTo('2026-10-31 23:59:59');

    expect(fn () => (new PaymentGateway)->charge($card, '123', 1000))->not->toThrow(PaymentDeclinedException::class);
});

test('an expired card is declined', function () {
    $card = PaymentCard::factory()->create(['exp_month' => 10, 'exp_year' => 2026]);

    $this->travelTo('2026-11-01 00:00:00');

    expect(fn () => (new PaymentGateway)->charge($card, '123', 1000))->toThrow(PaymentDeclinedException::class, 'Cartão vencido.');
});

test('a malformed cvv is declined', function (string $cvv) {
    $card = PaymentCard::factory()->create();

    expect(fn () => (new PaymentGateway)->charge($card, $cvv, 1000))->toThrow(PaymentDeclinedException::class, 'CVV inválido.');
})->with(['too short' => '12', 'too long' => '12345', 'letters' => 'abc', 'empty' => '']);

test('the test card ending in 0002 is always declined', function () {
    $card = PaymentCard::factory()->declined()->create();

    expect(fn () => (new PaymentGateway)->charge($card, '123', 1000))->toThrow(PaymentDeclinedException::class, 'Pagamento recusado pela operadora.');
});

test('zero or negative amounts are never charged', function (int $amountCents) {
    $card = PaymentCard::factory()->create();

    expect(fn () => (new PaymentGateway)->charge($card, '123', $amountCents))->toThrow(PaymentDeclinedException::class, 'Valor inválido.');
})->with([0, -100]);
