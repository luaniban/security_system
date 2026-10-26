<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fake demo accounts must never be created automatically in production.
Schedule::command('app:generate-users')
    ->hourly()
    ->withoutOverlapping()
    ->skip(fn (): bool => app()->isProduction());
