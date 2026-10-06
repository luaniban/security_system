<?php

use App\Rules\ValidCardNumber;

function cardNumberRuleError(mixed $value): ?string
{
    $error = null;

    (new ValidCardNumber)->validate('card_number', $value, function (string $message) use (&$error): void {
        $error = $message;
    });

    return $error;
}

test('a valid number typed with spaces passes', function () {
    expect(cardNumberRuleError('4111 1111 1111 1111'))->toBeNull();
});

test('a number with a wrong checksum fails with a friendly message', function () {
    expect(cardNumberRuleError('4111 1111 1111 1112'))->toBe('O número do cartão é inválido.');
});

test('values that are not strings are rejected (type juggling)', function (mixed $value) {
    expect(cardNumberRuleError($value))->toBe('O número do cartão é inválido.');
})->with([
    'integer' => 4111111111111111,
    'array' => [['4111111111111111']],
    'null' => null,
]);
