<?php

use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Checkout\PlaceOrder;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentCard;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('five wrong passwords lock the account even when the sixth one is right', function () {
    $this->freezeTime();
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email' => __('auth.failed')]);
    }

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email' => 'Muitas tentativas de login. Tente novamente em 300 segundos.']);

    $this->assertGuest();
});

test('the account lock is lifted after five minutes', function () {
    $this->freezeTime();
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        RateLimiter::hit('login|'.$user->email.'|127.0.0.1', 300);
    }

    $this->travel(301)->seconds();

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('one ip trying many accounts (credential stuffing) is blocked after 20 failures', function () {
    $this->freezeTime();
    $user = User::factory()->create();

    foreach (range(1, 19) as $attempt) {
        RateLimiter::hit('login-ip|127.0.0.1', 60);
    }

    Livewire::test(Login::class)
        ->set('email', 'someone-else@example.com')
        ->set('password', 'guess')
        ->call('login')
        ->assertHasErrors(['email' => __('auth.failed')]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors(['email' => 'Muitas tentativas de login. Tente novamente em 60 segundos.']);

    $this->assertGuest();
    $this->assertDatabaseHas('audit_logs', ['event' => 'auth.lockout']);
});

test('step-up password confirmation locks after five wrong passwords', function () {
    $this->freezeTime();
    $user = User::factory()->create();

    $this->actingAs($user);

    foreach (range(1, 5) as $attempt) {
        Livewire::test(ConfirmPassword::class)
            ->set('password', 'wrong-password')
            ->call('confirmPassword')
            ->assertHasErrors(['password' => __('auth.password')]);
    }

    Livewire::test(ConfirmPassword::class)
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertHasErrors(['password' => 'Muitas tentativas de login. Tente novamente em 300 segundos.']);

    $this->get(route('cards.index'))->assertRedirect(route('password.confirm'));
});

test('password reset e-mails are limited to three per minute per ip', function () {
    Notification::fake();
    $user = User::factory()->create();

    foreach (range(1, 3) as $attempt) {
        RateLimiter::hit('password-reset|127.0.0.1', 60);
    }

    Livewire::test(ForgotPassword::class)
        ->set('email', $user->email)
        ->call('sendResetLink')
        ->assertHasErrors(['email' => 'Muitas solicitações. Aguarde um minuto e tente novamente.']);

    Notification::assertNothingSent();
});

test('one ip can create at most ten accounts per hour', function () {
    foreach (range(1, 10) as $attempt) {
        RateLimiter::hit('register|127.0.0.1', 3600);
    }

    Livewire::test(Register::class)
        ->set([
            'name' => 'Ana Souza',
            'email' => 'ana@example.com',
            'cpf' => '529.982.247-25',
            'phone' => '(41) 99876-5432',
            'password' => 'Str0ng!Passw0rd',
            'password_confirmation' => 'Str0ng!Passw0rd',
            'terms' => true,
        ])
        ->call('register')
        ->assertHasErrors(['email' => 'Muitos cadastros a partir deste endereço. Tente mais tarde.']);

    expect(User::count())->toBe(0);
    $this->assertGuest();
});

test('card testing is blocked after five payment attempts per minute', function () {
    $this->freezeTime();
    $buyer = User::factory()->create();
    $address = Address::factory()->for($buyer)->create();
    $declinedCard = PaymentCard::factory()->for($buyer)->declined()->create();
    $validCard = PaymentCard::factory()->for($buyer)->create();
    CartItem::factory()->for($buyer)->create();

    $component = Livewire::actingAs($buyer)->test(PlaceOrder::class)->set('addressId', $address->id);

    foreach (range(1, 5) as $attempt) {
        $component->set('cardId', $declinedCard->id)
            ->set('cvv', '123')
            ->call('placeOrder')
            ->assertHasErrors(['payment' => 'Pagamento recusado pela operadora.']);
    }

    $component->set('cardId', $validCard->id)
        ->set('cvv', '123')
        ->call('placeOrder')
        ->assertHasErrors(['payment' => 'Muitas tentativas de pagamento. Aguarde 60 segundos.']);

    expect(Order::count())->toBe(0);
});

test('verification e-mails can be resent at most three times in five minutes', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user);

    $component = Livewire::test(VerifyEmail::class);

    foreach (range(1, 3) as $attempt) {
        $component->call('sendVerification')->assertSet('status', 'Um novo link de confirmação foi enviado para o seu e-mail.');
    }

    $component->call('sendVerification')->assertSet('status', 'Muitos envios. Aguarde alguns minutos.');

    Notification::assertSentToTimes($user, VerifyEmailNotification::class, 3);
});

test('the e-mail verification link can be hit at most six times per minute', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($user)->get($url)->assertRedirect();
    }

    $this->actingAs($user)->get($url)->assertTooManyRequests();
});
