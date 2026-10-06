<?php

use App\Support\Money;

test('prices typed by the user are converted to integer cents', function (string $input, int $cents) {
    expect(Money::toCents($input))->toBe($cents);
})->with([
    'comma decimal' => ['1234,56', 123456],
    'dot decimal' => ['1234.56', 123456],
    'no decimals' => ['10', 1000],
    'one decimal digit means tens of cents' => ['10,5', 1050],
    'leading zero cents' => ['0,05', 5],
    'surrounding spaces' => [' 49,90 ', 4990],
    'largest accepted value' => ['999999,99', 99999999],
]);

test('malformed prices are refused instead of being guessed', function (string $input) {
    expect(Money::toCents($input))->toBeNull();
})->with([
    'thousands separator' => '1.234,56',
    'negative' => '-10',
    'three decimals' => '10,999',
    'seven integer digits' => '1234567',
    'scientific notation' => '1e5',
    'trailing separator' => '10,',
    'letters' => 'abc',
    'empty' => '',
]);

test('cents are formatted as brazilian real', function (int $cents, string $formatted) {
    expect(Money::format($cents))->toBe($formatted);
})->with([
    [123456, 'R$ 1.234,56'],
    [5, 'R$ 0,05'],
    [0, 'R$ 0,00'],
    [100000000, 'R$ 1.000.000,00'],
]);

test('cents are converted back to the form input format', function (int $cents, string $input) {
    expect(Money::toInput($cents))->toBe($input);
})->with([
    [123456, '1234,56'],
    [5, '0,05'],
    [1000, '10,00'],
]);

test('a value shown in the form parses back to the same cents', function (int $cents) {
    expect(Money::toCents(Money::toInput($cents)))->toBe($cents);
})->with([1, 99, 100, 4990, 99999999]);
