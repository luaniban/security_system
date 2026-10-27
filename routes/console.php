<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fake demo accounts are only created automatically in production when GENERATE_FAKE_USERS is enabled.
// One account every five minutes keeps a steady trickle of new users instead of hourly bursts.
Schedule::command('app:generate-users', [1, '--force'])
    ->everyFiveMinutes()
    ->withoutOverlapping()
    // Keeps each run's password (and when it ran), so the generated accounts can be used to log in.
    ->appendOutputTo(storage_path('logs/generated-users.log'))
    ->skip(fn (): bool => app()->isProduction() && ! config('app.generate_fake_users'));
