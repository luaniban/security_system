<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use App\Support\Cpf;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function validRegistration(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ana Souza',
        'email' => 'ana@example.com',
        'cpf' => '529.982.247-25',
        'phone' => '(41) 99876-5432',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
        'terms' => true,
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $this->get('/register')->assertOk()->assertSeeLivewire(Register::class);
});

test('new users can register and their personal data is encrypted at rest', function () {
    $component = Livewire::test(Register::class);

    foreach (validRegistration() as $field => $value) {
        $component->set($field, $value);
    }

    $component->call('register')->assertHasNoErrors()->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();

    $user = User::firstWhere('email', 'ana@example.com');
    $raw = DB::table('users')->where('id', $user->id)->first();

    expect($user->cpf)->toBe('52998224725')
        ->and($user->phone)->toBe('41998765432')
        ->and($raw->cpf)->not->toContain('52998224725')
        ->and($raw->phone)->not->toContain('41998765432')
        ->and($raw->cpf_hash)->toBe(Cpf::blindIndex('52998224725'))
        ->and($user->is_admin)->toBeFalse();
});

test('registration rejects invalid cpf, weak password and missing consent', function () {
    $component = Livewire::test(Register::class);

    foreach (validRegistration(['cpf' => '111.111.111-11', 'password' => 'fraca', 'password_confirmation' => 'fraca', 'terms' => false]) as $field => $value) {
        $component->set($field, $value);
    }

    $component->call('register')
        ->assertHasErrors(['cpf', 'password', 'terms'])
        ->assertSet('password', '');

    $this->assertGuest();
});

test('the same cpf can not be registered twice', function () {
    User::factory()->create(['cpf' => '52998224725']);

    $component = Livewire::test(Register::class);

    foreach (validRegistration() as $field => $value) {
        $component->set($field, $value);
    }

    $component->call('register')->assertHasErrors('cpf');
});

test('users can not grant themselves admin through mass assignment', function () {
    $user = User::create([
        'name' => 'Mallory',
        'email' => 'mallory@example.com',
        'cpf' => Cpf::generate(),
        'phone' => '41999999999',
        'password' => 'Str0ng!Passw0rd',
        'is_admin' => true,
    ]);

    expect($user->fresh()->is_admin)->toBeFalse();
})->throws(MassAssignmentException::class);
