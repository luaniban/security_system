<?php

use App\Livewire\Auth\Login;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk()->assertSeeLivewire(Login::class);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect(AuditLog::where('event', 'auth.login')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('users can not authenticate with invalid password and get a generic message', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['email' => __('auth.failed')])
        ->assertSet('password', '');

    $this->assertGuest();
});

test('unknown e-mails receive exactly the same message as wrong passwords', function () {
    Livewire::test(Login::class)
        ->set('email', 'nobody@example.com')
        ->set('password', 'whatever')
        ->call('login')
        ->assertHasErrors(['email' => __('auth.failed')]);
});

test('account is locked after five failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        RateLimiter::hit('login|'.$user->email.'|127.0.0.1', 300);
    }

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
    expect(AuditLog::where('event', 'auth.lockout')->exists())->toBeTrue();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));

    $this->assertGuest();
});
