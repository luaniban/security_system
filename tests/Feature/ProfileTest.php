<?php

use App\Livewire\Account\DeleteAccount;
use App\Livewire\Account\UpdateProfileInformation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

test('changing the e-mail sends a new verification link', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdateProfileInformation::class)
        ->set('email', 'novo@example.com')
        ->set('current_password', 'password')
        ->call('updateProfileInformation')
        ->assertRedirect(route('verification.notice'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('malformed profile data is rejected with a clear message', function (string $field, string $value, string $message) {
    $user = User::factory()->create(['name' => 'Nome Original']);

    $this->actingAs($user);

    Livewire::test(UpdateProfileInformation::class)
        ->set($field, $value)
        ->call('updateProfileInformation')
        ->assertHasErrors([$field => $message]);

    expect($user->fresh()->name)->toBe('Nome Original');
})->with([
    'short phone' => ['phone', '1234', 'Informe um telefone no formato (11) 91234-5678.'],
    'digits in the name' => ['name', 'Ana 123', 'O nome deve conter apenas letras.'],
]);

test('the e-mail must be lowercase and not used by another account', function (string $email) {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdateProfileInformation::class)
        ->set('email', $email)
        ->set('current_password', 'password')
        ->call('updateProfileInformation')
        ->assertHasErrors('email');

    expect($user->fresh()->email)->not->toBe($email);
})->with(['uppercase' => 'NOVO@EXAMPLE.COM', 'already taken' => 'taken@example.com']);

test('deleting an account also removes the images of the user\'s products', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/mine.webp', 'image');
    $user = User::factory()->create();
    Product::factory()->for($user, 'seller')->create(['image_path' => 'products/mine.webp']);

    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('products/mine.webp');
    $this->assertDatabaseHas('audit_logs', ['event' => 'account.deleted']);
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
