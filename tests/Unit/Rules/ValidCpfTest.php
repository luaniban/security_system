<?php

use App\Rules\ValidCpf;

function cpfRuleError(mixed $value): ?string
{
    $error = null;

    (new ValidCpf)->validate('cpf', $value, function (string $message) use (&$error): void {
        $error = $message;
    });

    return $error;
}

test('a valid formatted cpf passes', function () {
    expect(cpfRuleError('529.982.247-25'))->toBeNull();
});

test('an invalid cpf fails with a friendly message', function () {
    expect(cpfRuleError('529.982.247-24'))->toBe('O CPF informado é inválido.');
});

test('values that are not strings are rejected (type juggling)', function (mixed $value) {
    expect(cpfRuleError($value))->toBe('O CPF informado é inválido.');
})->with([
    'integer' => 52998224725,
    'array' => [['52998224725']],
    'null' => null,
    'boolean' => true,
]);
