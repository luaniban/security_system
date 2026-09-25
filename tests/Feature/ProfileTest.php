<?php

use App\Livewire\Account\DeleteAccount;
use App\Livewire\Account\UpdateProfileInformation;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed with masked cpf', function () {
    $user = User::factory()->create(['cpf' => '52998224725']);

    $this->actingAs($user)
        ->get('/conta/perfil')
        ->assertOk()
        ->assertSee('***.982.247-**')
        ->assertDontSee('52998224725');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdateProfileInformation::class)
        ->set('name', 'Novo Nome')
        ->set('phone', '(21) 98888-7777')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Novo Nome')
        ->and($user->phone)->toBe('21988887777');
});

test('changing the e-mail requires the current password and resets verification', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdateProfileInformation::class)
        ->set('email', 'novo@example.com')
        ->call('updateProfileInformation')
        ->assertHasErrors('current_password');

    Livewire::test(UpdateProfileInformation::class)
        ->set('email', 'novo@example.com')
        ->set('current_password', 'password')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email)->toBe('novo@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('account with open orders can not be deleted', function () {
    $user = User::factory()->create();
    Order::factory()->create(['buyer_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});
