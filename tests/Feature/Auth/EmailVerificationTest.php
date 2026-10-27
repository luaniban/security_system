<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get('/verify-email')->assertOk();
});

test('unverified users can not reach buying and selling areas', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('checkout'))->assertRedirect(route('verification.notice'));
    $this->actingAs($user)->get(route('seller.products.create'))->assertRedirect(route('verification.notice'));
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('the verification e-mail is written in portuguese', function () {
    app()->setLocale('pt_BR');

    $mail = (new VerifyEmail)->toMail(User::factory()->unverified()->create());

    expect($mail->subject)->toBe('Confirme seu e-mail')
        ->and($mail->actionText)->toBe('Confirmar e-mail')
        ->and($mail->introLines)->toBe(['Clique no botão abaixo para confirmar seu endereço de e-mail.']);
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
