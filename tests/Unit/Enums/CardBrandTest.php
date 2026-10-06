<?php

use App\Enums\CardBrand;

test('the brand is detected from the card number prefix', function (string $number, CardBrand $brand) {
    expect(CardBrand::detect($number))->toBe($brand);
})->with([
    'visa' => ['4111111111111111', CardBrand::Visa],
    'mastercard 51-55' => ['5555555555554444', CardBrand::Mastercard],
    'mastercard 2-series' => ['2223000048400011', CardBrand::Mastercard],
    'amex 34' => ['341111111111111', CardBrand::Amex],
    'amex 37' => ['378282246310005', CardBrand::Amex],
    'elo 5066' => ['5066991111111118', CardBrand::Elo],
    'elo 6362' => ['6362970000457013', CardBrand::Elo],
    'discover is not supported' => ['6011111111111117', CardBrand::Other],
    'jcb is not supported' => ['3530111333300000', CardBrand::Other],
]);

test('elo bins that start with 4 are not mistaken for visa', function (string $number) {
    expect(CardBrand::detect($number))->toBe(CardBrand::Elo);
})->with(['4011780000000000', '4389350000000000', '4576000000000000']);
