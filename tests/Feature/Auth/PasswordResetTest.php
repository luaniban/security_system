<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('reset password link screen can be rendered', function () {
    $this->get('/forgot-password')->assertOk()->assertSeeLivewire(ForgotPassword::class);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendResetLink')
        ->assertSet('status', __('passwords.sent'));

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('the password reset e-mail is written in portuguese', function () {
    app()->setLocale('pt_BR');

    $mail = (new ResetPasswordNotification('token'))->toMail(User::factory()->create());

    expect($mail->subject)->toBe('Redefina sua senha')
        ->and($mail->actionText)->toBe('Redefinir senha');
});

test('unknown e-mails get the same answer, so accounts can not be enumerated', function () {
    Notification::fake();

    Livewire::test(ForgotPassword::class)
        ->set('email', 'ghost@example.com')
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('status', __('passwords.sent'));

    Notification::assertNothingSent();
});

test('password can be reset with a valid token, only once, and all sessions are revoked', function () {
    Notification::fake();

    $user = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'stolen-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendResetLink');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))->assertOk();

        Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'N3w!SecurePassword')
            ->set('password_confirmation', 'N3w!SecurePassword')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        expect(Hash::check('N3w!SecurePassword', $user->fresh()->password))->toBeTrue()
            ->and(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);

        // Token is single-use.
        Livewire::test(ResetPassword::class, ['token' => $notification->token])
            ->set('email', $user->email)
            ->set('password', 'An0ther!Password')
            ->set('password_confirmation', 'An0ther!Password')
            ->call('resetPassword')
            ->assertHasErrors('email');

        return true;
    });
});

test('password can not be reset with a forged token', function () {
    $user = User::factory()->create();

    Livewire::test(ResetPassword::class, ['token' => 'forged-token'])
        ->set('email', $user->email)
        ->set('password', 'N3w!SecurePassword')
        ->set('password_confirmation', 'N3w!SecurePassword')
        ->call('resetPassword')
        ->assertHasErrors('email');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('the reset token can not be tampered with from the browser', function () {
    Livewire::test(ResetPassword::class, ['token' => 'original'])
        ->set('token', 'tampered');
})->throws(CannotUpdateLockedPropertyException::class);
