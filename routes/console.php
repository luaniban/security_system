<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fake demo accounts are only created automatically in production when GENERATE_FAKE_USERS is enabled.
Schedule::command('app:generate-users', ['--force'])
    ->hourly()
    ->withoutOverlapping()
    ->skip(fn (): bool => app()->isProduction() && ! config('app.generate_fake_users'));
