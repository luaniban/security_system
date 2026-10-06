<?php

use App\Livewire\Account\UpdatePassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('passwords that break the policy are refused', function (string $password) {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set(['current_password' => 'password', 'password' => $password, 'password_confirmation' => $password])
        ->call('updatePassword')
        ->assertHasErrors('password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->with([
    '11 characters' => 'Abcdefgh1!x',
    'no uppercase letter' => 'abcdefgh1!xyz',
    'no lowercase letter' => 'ABCDEFGH1!XYZ',
    'no number' => 'Abcdefghi!xyz',
    'no symbol' => 'Abcdefghi1xyz',
    'more than 128 characters' => str_repeat('Aa1!', 33),
]);

test('a 12 character password that meets every rule is accepted', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set(['current_password' => 'password', 'password' => 'Abcdefgh1!xy', 'password_confirmation' => 'Abcdefgh1!xy'])
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('Abcdefgh1!xy', $user->fresh()->password))->toBeTrue();
});

test('the new password must be different from the current one', function () {
    $user = User::factory()->create(['password' => 'Str0ng!Passw0rd']);

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set(['current_password' => 'Str0ng!Passw0rd', 'password' => 'Str0ng!Passw0rd', 'password_confirmation' => 'Str0ng!Passw0rd'])
        ->call('updatePassword')
        ->assertHasErrors(['password' => 'A nova senha deve ser diferente da atual.']);
});

test('the password confirmation must match', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set(['current_password' => 'password', 'password' => 'N3w!SecurePassword', 'password_confirmation' => 'N3w!SecurePasswordX'])
        ->call('updatePassword')
        ->assertHasErrors('password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
