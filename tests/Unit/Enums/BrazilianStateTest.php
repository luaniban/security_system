<?php

use App\Enums\BrazilianState;

test('delivery lead time depends on the destination region', function (BrazilianState $state, int $days) {
    expect($state->deliveryDays())->toBe($days);
})->with([
    'southeast' => [BrazilianState::SP, 5],
    'south' => [BrazilianState::RS, 5],
    'federal district' => [BrazilianState::DF, 7],
    'center-west' => [BrazilianState::MT, 7],
    'northeast' => [BrazilianState::BA, 9],
    'north' => [BrazilianState::AM, 12],
    'far north' => [BrazilianState::RR, 12],
]);
