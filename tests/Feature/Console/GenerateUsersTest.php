<?php

use App\Models\PaymentCard;
use App\Models\User;
use App\Support\Luhn;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

test('it creates buyers with an address and valid, unexpired cards but no products', function () {
    $this->artisan('app:generate-users', ['count' => 3])->assertSuccessful();

    $users = User::with(['addresses', 'paymentCards', 'products'])->get();

    expect($users)->toHaveCount(3);

    $users->each(function (User $user) {
        expect($user)
            ->is_admin->toBeFalse()
            ->email_verified_at->not->toBeNull()
            ->addresses->toHaveCount(1)
            ->products->toBeEmpty()
            ->and($user->paymentCards->count())->toBeBetween(1, 2);

        $user->paymentCards->each(fn (PaymentCard $card) => expect($card)
            ->and(Luhn::isValid($card->number))->toBeTrue()
            ->and($card->isExpired())->toBeFalse()
            ->and($card->last_four)->toBe(substr($card->number, -4))
            ->and($card->holder_name)->toBe(mb_strtoupper($user->name)));
    });
});

test('it creates sellers with products when --sellers is given', function () {
    $this->artisan('app:generate-users', ['count' => 2, '--sellers' => true])->assertSuccessful();

    User::withCount(['products', 'addresses', 'paymentCards'])->get()->each(fn (User $seller) => expect($seller)
        ->products_count->toBeGreaterThanOrEqual(3)
        ->addresses_count->toBe(1)
        ->payment_cards_count->toBeGreaterThanOrEqual(1));
});

test('generated accounts can log in with the password printed by the command', function () {
    Artisan::call('app:generate-users', ['count' => 1]);

    preg_match('/Senha das contas geradas: (\S+)/', Artisan::output(), $matches);

    expect(Hash::check($matches[1], User::sole()->password))->toBeTrue();
});

test('an invalid count is rejected without creating users', function (string $count) {
    $this->artisan('app:generate-users', ['count' => $count])->assertFailed();

    expect(User::count())->toBe(0);
})->with(['zero' => '0', 'negative' => '-5', 'not a number' => 'abc']);

test('in production it asks for confirmation and creates nothing when declined', function () {
    $this->app['env'] = 'production';

    $this->artisan('app:generate-users', ['count' => 1])
        ->expectsConfirmation('Are you sure you want to run this command?', 'no')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

function generateUsersScheduleEvent(): Event
{
    return collect(app(Schedule::class)->events())
        ->sole(fn (Event $event) => str_contains($event->command, 'app:generate-users'));
}

test('the command is scheduled hourly and runs outside production', function () {
    $event = generateUsersScheduleEvent();

    expect($event->expression)->toBe('0 * * * *')
        ->and($event->filtersPass($this->app))->toBeTrue();
});

test('the scheduled run skips the production confirmation prompt, which would otherwise abort it', function () {
    expect(generateUsersScheduleEvent()->command)->toContain('--force');
});

test('the schedule is skipped in production by default', function () {
    $this->app['env'] = 'production';

    expect(generateUsersScheduleEvent()->filtersPass($this->app))->toBeFalse();
});

test('the schedule runs in production when GENERATE_FAKE_USERS is enabled', function () {
    $this->app['env'] = 'production';
    config(['app.generate_fake_users' => true]);

    expect(generateUsersScheduleEvent()->filtersPass($this->app))->toBeTrue();
});

test('in production --force creates the users without asking', function () {
    $this->app['env'] = 'production';

    $this->artisan('app:generate-users', ['count' => 2, '--force' => true])->assertSuccessful();

    expect(User::count())->toBe(2);
});
