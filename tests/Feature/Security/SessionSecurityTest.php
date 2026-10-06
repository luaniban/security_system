<?php

use App\Livewire\Account\UpdatePassword;
use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('logging in issues a new session id (session fixation)', function () {
    $user = User::factory()->create();
    $this->startSession();
    $sessionId = session()->getId();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    expect(session()->getId())->not->toBe($sessionId);
});

test('changing the password issues a new session id', function () {
    $this->actingAs(User::factory()->create());
    $this->startSession();
    $sessionId = session()->getId();

    Livewire::test(UpdatePassword::class)
        ->set([
            'current_password' => 'password',
            'password' => 'N3w!SecurePassword',
            'password_confirmation' => 'N3w!SecurePassword',
        ])
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(session()->getId())->not->toBe($sessionId);
});

test('logging out destroys the session data and rotates the csrf token', function () {
    $this->actingAs(User::factory()->create())->withSession(['private' => 'value']);
    $csrfToken = session()->token();

    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
    expect(session()->has('private'))->toBeFalse()
        ->and(session()->token())->not->toBe($csrfToken);
});

test('logout only accepts POST, so a link or image tag can not sign users out', function () {
    $this->actingAs(User::factory()->create())
        ->get('/logout')
        ->assertMethodNotAllowed();

    $this->assertAuthenticated();
});

test('the session cookie is http-only, same-site and expires after 30 idle minutes', function () {
    $this->freezeTime();
    config(['session.driver' => 'database']);

    $cookie = $this->get('/')->getCookie(config('session.cookie'), decrypt: false);

    expect($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax')
        ->and($cookie->getExpiresTime())->toBe(now()->addMinutes(30)->getTimestamp());
});
