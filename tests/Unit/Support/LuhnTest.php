<?php

use App\Support\Luhn;

test('numbers with a valid checksum are accepted', function (string $number) {
    expect(Luhn::isValid($number))->toBeTrue();
})->with([
    'visa 16 digits' => '4111111111111111',
    'visa 13 digits' => '4222222222222',
    'mastercard' => '5555555555554444',
    'amex 15 digits' => '378282246310005',
    'discover' => '6011111111111117',
]);

test('numbers with an invalid checksum are rejected', function (string $number) {
    expect(Luhn::isValid($number))->toBeFalse();
})->with([
    'last digit changed' => '4111111111111112',
    'two digits swapped' => '4111111111111141',
]);

test('lengths outside 13 to 19 digits are rejected even when the checksum matches', function (string $number) {
    expect(Luhn::isValid($number))->toBeFalse();
})->with([
    '12 digits' => str_repeat('0', 12),
    '20 digits' => str_repeat('0', 20),
]);

test('anything other than plain digits is rejected', function (string $number) {
    expect(Luhn::isValid($number))->toBeFalse();
})->with([
    'spaces' => '4111 1111 1111 1111',
    'dashes' => '4111-1111-1111-1111',
    'letters' => '4111a11111111111',
    'empty' => '',
]);
