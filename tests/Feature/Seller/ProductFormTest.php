<?php

use App\Livewire\Seller\ProductForm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function validProductInput(array $overrides = []): array
{
    return array_merge([
        'name' => '  Camiseta básica  ',
        'description' => 'Camiseta 100% algodão, tamanho M.',
        'price' => '49,90',
        'stock' => 7,
        'is_active' => true,
    ], $overrides);
}

test('a seller can publish a product', function () {
    $seller = User::factory()->create();

    Livewire::actingAs($seller)
        ->test(ProductForm::class)
        ->set(validProductInput())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('seller.products.index'));

    $product = $seller->products()->sole();

    expect($product)
        ->name->toBe('Camiseta básica')
        ->price_cents->toBe(4990)
        ->stock->toBe(7)
        ->is_active->toBeTrue()
        ->image_path->toBeNull();

    $this->assertDatabaseHas('audit_logs', ['event' => 'product.created', 'user_id' => $seller->id]);
});

test('the edit form is filled with the current values', function () {
    $product = Product::factory()->create(['price_cents' => 123456, 'stock' => 4]);

    Livewire::actingAs($product->seller)
        ->test(ProductForm::class, ['product' => $product])
        ->assertSet('productId', $product->id)
        ->assertSet('name', $product->name)
        ->assertSet('price', '1234,56')
        ->assertSet('stock', 4);
});

test('a seller can update a product without creating a new one', function () {
    $product = Product::factory()->create();

    Livewire::actingAs($product->seller)
        ->test(ProductForm::class, ['product' => $product])
        ->set(['price' => '10', 'stock' => 0, 'is_active' => false])
        ->call('save')
        ->assertHasNoErrors();

    expect($product->fresh())
        ->price_cents->toBe(1000)
        ->stock->toBe(0)
        ->is_active->toBeFalse();

    expect(Product::count())->toBe(1);
    $this->assertDatabaseHas('audit_logs', ['event' => 'product.updated', 'user_id' => $product->seller_id]);
});

test('uploading a new image deletes the previous file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/old.webp', 'old');
    $product = Product::factory()->create(['image_path' => 'products/old.webp']);

    Livewire::actingAs($product->seller)
        ->test(ProductForm::class, ['product' => $product])
        ->set('image', UploadedFile::fake()->image('nova.jpg', 300, 300))
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('products/old.webp');
    Storage::disk('public')->assertExists($product->fresh()->image_path);
});

test('an empty product form reports every required field', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->call('save')
        ->assertHasErrors(['name', 'description', 'price']);

    expect(Product::count())->toBe(0);
});

test('prices must use the 1234,56 format', function (string $price) {
    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set(validProductInput(['price' => $price]))
        ->call('save')
        ->assertHasErrors(['price' => 'Informe o preço no formato 1234,56.']);

    expect(Product::count())->toBe(0);
})->with(['negative' => '-5', 'thousands separator' => '1.234,56', 'letters' => 'abc', 'three decimals' => '9,999']);

test('prices below R$ 1,00 are refused', function (string $price) {
    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set(validProductInput(['price' => $price]))
        ->call('save')
        ->assertHasErrors(['price' => 'O preço mínimo é R$ 1,00.']);

    expect(Product::count())->toBe(0);
})->with(['zero' => '0', 'ninety nine cents' => '0,99']);

test('stock must be a whole number between 0 and 10000', function (int|string $stock) {
    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set(validProductInput(['stock' => $stock]))
        ->call('save')
        ->assertHasErrors('stock');
})->with(['negative' => -1, 'too large' => 10001, 'text' => 'abc']);

test('name and description lengths are enforced', function (string $field, string $value) {
    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set(validProductInput([$field => $value]))
        ->call('save')
        ->assertHasErrors($field);
})->with([
    'short name' => ['name', 'ab'],
    'long name' => ['name', str_repeat('a', 121)],
    'short description' => ['description', 'curta'],
    'long description' => ['description', str_repeat('a', 2001)],
]);

test('images with dimensions outside 100px to 5000px are refused on save', function (UploadedFile $file) {
    Storage::fake('public');

    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set(validProductInput())
        ->set('image', $file)
        ->call('save')
        ->assertHasErrors('image');

    expect(Product::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'smaller than 100px' => fn () => UploadedFile::fake()->image('tiny.png', 50, 50),
    'wider than 5000px' => fn () => UploadedFile::fake()->image('wide.png', 5001, 100),
]);

test('heavy or non-allowed image types are refused as soon as they are uploaded', function (UploadedFile $file) {
    Storage::fake('public');

    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set('image', $file)
        ->assertHasErrors('image')
        ->assertSet('image', null);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'heavier than 2MB' => fn () => UploadedFile::fake()->image('heavy.jpg', 300, 300)->size(3000),
    'gif' => fn () => UploadedFile::fake()->image('animated.gif', 300, 300),
]);
