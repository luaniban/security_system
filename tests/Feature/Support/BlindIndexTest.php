<?php

use App\Support\BlindIndex;

test('the same value always produces the same index, so lookups work', function () {
    expect(BlindIndex::make('cpf', '52998224725'))->toBe(BlindIndex::make('cpf', '52998224725'));
});

test('the same value produces different indexes for different purposes', function () {
    expect(BlindIndex::make('cpf', '52998224725'))->not->toBe(BlindIndex::make('card', '52998224725'));
});

test('the index is keyed by the app key and is not a plain hash of the value', function () {
    $index = BlindIndex::make('cpf', '52998224725');

    config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

    expect(BlindIndex::make('cpf', '52998224725'))->not->toBe($index)
        ->and($index)->not->toBe(hash('sha256', '52998224725'))
        ->and($index)->not->toBe(hash('sha256', 'cpf|52998224725'));
});
