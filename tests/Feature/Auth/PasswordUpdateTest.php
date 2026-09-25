<?php

use App\Livewire\Account\UpdatePassword;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('password can be updated and other sessions are revoked', function () {
    $user = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'password')
        ->set('password', 'N3w!SecurePassword')
        ->set('password_confirmation', 'N3w!SecurePassword')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('N3w!SecurePassword', $user->refresh()->password))->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdatePassword::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'N3w!SecurePassword')
        ->set('password_confirmation', 'N3w!SecurePassword')
        ->call('updatePassword')
        ->assertHasErrors('current_password');
});
