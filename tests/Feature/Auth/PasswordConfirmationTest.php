<?php

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Livewire\Livewire;

test('saved cards require the password to be confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('cards.index'))->assertRedirect(route('password.confirm'));
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ConfirmPassword::class)
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertHasNoErrors()
        ->assertRedirect();

    $this->get(route('cards.index'))->assertOk();
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(ConfirmPassword::class)
        ->set('password', 'wrong-password')
        ->call('confirmPassword')
        ->assertHasErrors('password');
});
