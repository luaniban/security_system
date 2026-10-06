<?php

use App\Enums\BrazilianState;
use App\Livewire\Account\Addresses;
use App\Models\Address;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array<string, string>
 */
function validAddressInput(array $overrides = []): array
{
    return array_merge([
        'label' => 'Casa',
        'recipient_name' => 'Ana Souza',
        'zip_code' => '01001-000',
        'street' => 'Praça da Sé',
        'number' => '100',
        'complement' => '',
        'district' => 'Sé',
        'city' => 'São Paulo',
        'state' => 'SP',
    ], $overrides);
}

test('the addresses page lists only the user\'s own addresses', function () {
    $user = User::factory()->create();
    Address::factory()->for($user)->create(['street' => 'Rua Minha']);
    Address::factory()->create(['street' => 'Rua Alheia']);

    $this->actingAs($user)
        ->get(route('addresses.index'))
        ->assertSee('Rua Minha')
        ->assertDontSee('Rua Alheia');
});

test('a user can add an address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('create')
        ->assertSet('recipient_name', $user->name)
        ->set(validAddressInput())
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $address = $user->addresses()->sole();

    expect($address->zip_code)->toBe('01001000')
        ->and($address->complement)->toBeNull()
        ->and($address->state)->toBe(BrazilianState::SP)
        ->and($address->street)->toBe('Praça da Sé');

    $this->assertDatabaseHas('audit_logs', ['event' => 'address.created', 'user_id' => $user->id]);
});

test('a user can edit an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create(['zip_code' => '01001000']);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('edit', $address->id)
        ->assertSet('editingId', $address->id)
        ->assertSet('zip_code', '01001-000')
        ->set(['city' => 'Curitiba', 'state' => 'PR'])
        ->call('save')
        ->assertHasNoErrors();

    expect($address->fresh())
        ->city->toBe('Curitiba')
        ->state->toBe(BrazilianState::PR);

    expect($user->addresses()->count())->toBe(1);
});

test('a user can delete an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    Livewire::actingAs($user)->test(Addresses::class)->call('delete', $address->id);

    $this->assertModelMissing($address);
    $this->assertDatabaseHas('audit_logs', ['event' => 'address.deleted', 'user_id' => $user->id]);
});

test('an empty address form reports every required field', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Addresses::class)
        ->call('save')
        ->assertHasErrors(['label', 'recipient_name', 'zip_code', 'street', 'number', 'district', 'city', 'state']);
});

test('malformed address fields are rejected with a clear message', function (string $field, string $value, string $message) {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set(validAddressInput([$field => $value]))
        ->call('save')
        ->assertHasErrors([$field => $message]);

    expect($user->addresses()->count())->toBe(0);
})->with([
    'short zip code' => ['zip_code', '0100-000', 'Informe o CEP no formato 00000-000.'],
    'zip code with letters' => ['zip_code', 'ABCDE-FGH', 'Informe o CEP no formato 00000-000.'],
    'html in the recipient name' => ['recipient_name', 'Ana <b>Souza</b>', 'O nome deve conter apenas letras.'],
    'digits in the city' => ['city', 'São Paulo 2', 'A cidade deve conter apenas letras.'],
]);

test('values outside the allowed set are rejected', function (string $field, string $value) {
    Livewire::actingAs(User::factory()->create())
        ->test(Addresses::class)
        ->set(validAddressInput([$field => $value]))
        ->call('save')
        ->assertHasErrors($field);
})->with([
    'unknown state' => ['state', 'XX'],
    'symbols in the number' => ['number', '12; DROP'],
    'number too long' => ['number', '12345678901'],
    'label too long' => ['label', str_repeat('a', 51)],
]);

test('a user can not keep more than 10 addresses', function () {
    $user = User::factory()->create();
    Address::factory()->for($user)->count(10)->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set(validAddressInput())
        ->call('save')
        ->assertHasErrors(['label' => 'Limite de 10 endereços atingido.']);

    expect($user->addresses()->count())->toBe(10);
});
