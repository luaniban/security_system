<?php

use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Users;
use App\Models\AuditLog;
use App\Models\User;
use Livewire\Livewire;

test('admins can search users by name or e-mail', function (string $term) {
    $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@example.com']);
    $ana = User::factory()->create(['name' => 'Ana Souza', 'email' => 'ana@example.com']);
    User::factory()->create(['name' => 'Bruno Lima', 'email' => 'bruno@example.com']);

    Livewire::actingAs($admin)
        ->test(Users::class)
        ->set('search', $term)
        ->assertViewHas('users', fn ($users) => $users->pluck('id')->all() === [$ana->id]);
})->with(['by name' => 'souza', 'by e-mail' => 'ana@']);

test('admins can filter the audit trail by event', function () {
    AuditLog::factory()->create(['event' => 'auth.login']);
    $failure = AuditLog::factory()->create(['event' => 'auth.failed']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AuditLogs::class)
        ->set('event', 'auth.failed')
        ->assertViewHas('logs', fn ($logs) => $logs->pluck('id')->all() === [$failure->id]);
});

test('an event that was never logged is ignored by the audit filter', function (string $event) {
    AuditLog::factory()->count(2)->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(AuditLogs::class)
        ->set('event', $event)
        ->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
})->with(['unknown event' => 'auth.nothing', 'sql payload' => "auth.login' OR '1'='1"]);
