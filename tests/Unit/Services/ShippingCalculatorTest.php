<?php

use App\Enums\BrazilianState;
use App\Services\ShippingCalculator;

test('shipping is free from R$ 300,00 upwards', function (int $subtotalCents) {
    expect((new ShippingCalculator)->priceFor(BrazilianState::AM, 10, $subtotalCents))->toBe(0);
})->with([
    'exactly the threshold' => 30000,
    'above the threshold' => 50000,
]);

test('one cent below the threshold is charged', function () {
    expect((new ShippingCalculator)->priceFor(BrazilianState::SP, 1, 29999))->toBe(2250);
});

test('farther destinations cost more', function (BrazilianState $state, int $expectedCents) {
    expect((new ShippingCalculator)->priceFor($state, 1, 1000))->toBe($expectedCents);
})->with([
    'southeast' => [BrazilianState::SP, 2250],
    'center-west' => [BrazilianState::DF, 2250],
    'northeast' => [BrazilianState::BA, 2750],
    'north' => [BrazilianState::AM, 3250],
]);

test('each extra item adds R$ 2,50', function () {
    $calculator = new ShippingCalculator;

    expect($calculator->priceFor(BrazilianState::SP, 4, 1000))->toBe(3000)
        ->and($calculator->priceFor(BrazilianState::SP, 5, 1000))->toBe(3250);
});
