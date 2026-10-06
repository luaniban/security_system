<?php

use App\Support\Cpf;

test('valid cpfs are accepted with or without punctuation', function (string $cpf) {
    expect(Cpf::isValid($cpf))->toBeTrue();
})->with([
    'digits only' => '52998224725',
    'formatted' => '529.982.247-25',
    'zero check digit' => '12345678909',
]);

test('invalid cpfs are rejected', function (string $cpf) {
    expect(Cpf::isValid($cpf))->toBeFalse();
})->with([
    'wrong first check digit' => '52998224715',
    'wrong second check digit' => '52998224724',
    'all digits repeated' => '11111111111',
    'all zeros' => '00000000000',
    'too short' => '5299822472',
    'too long' => '529982247250',
    'letters only' => 'abcdefghijk',
    'empty' => '',
]);

test('normalize keeps only the digits', function () {
    expect(Cpf::normalize(' 529.982.247-25 '))->toBe('52998224725');
});

test('mask hides the first three and the check digits', function (string $cpf) {
    expect(Cpf::mask($cpf))->toBe('***.982.247-**');
})->with(['52998224725', '529.982.247-25']);

test('generate always returns a valid cpf', function () {
    $cpf = Cpf::generate();

    expect($cpf)->toMatch('/^\d{11}$/')
        ->and(Cpf::isValid($cpf))->toBeTrue();
})->repeat(25);
