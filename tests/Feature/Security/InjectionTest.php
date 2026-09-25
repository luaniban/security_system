<?php

use App\Livewire\Seller\ProductForm;
use App\Livewire\Shop\Catalog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('product names and descriptions are rendered escaped (stored XSS)', function () {
    $product = Product::factory()->create([
        'name' => '<script>alert("xss")</script>',
        'description' => '<img src=x onerror=alert(1)>',
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertSee('&lt;script&gt;', false);
});

test('search terms are escaped when reflected (reflected XSS)', function () {
    $this->get('/?q='.urlencode('"><svg onload=alert(1)>'))
        ->assertOk()
        ->assertDontSee('<svg onload=alert(1)>', false);
});

test('sql injection payloads in the search are treated as plain text', function (string $payload) {
    Product::factory()->count(3)->create();

    Livewire::test(Catalog::class)
        ->set('search', $payload)
        ->assertViewHas('products', fn ($products) => $products->total() === 0);

    expect(Product::count())->toBe(3);
})->with([
    "' OR '1'='1",
    "'; DROP TABLE products; --",
    '% _',
    "1' UNION SELECT password FROM users --",
]);

test('an arbitrary sort parameter can not reach ORDER BY', function () {
    Product::factory()->count(2)->create();

    Livewire::withQueryParams(['ordem' => '(SELECT SLEEP(5))'])
        ->test(Catalog::class)
        ->assertOk();
});

test('uploaded images are re-encoded to webp with a random name', function () {
    Storage::fake('public');
    $seller = User::factory()->create();

    Livewire::actingAs($seller)
        ->test(ProductForm::class)
        ->set('name', 'Produto com imagem')
        ->set('description', 'Descrição longa o suficiente.')
        ->set('price', '49,90')
        ->set('stock', 3)
        ->set('image', UploadedFile::fake()->image('../../evil.php.png', 400, 400))
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::firstWhere('name', 'Produto com imagem');

    expect($product->price_cents)->toBe(4990)
        ->and($product->seller_id)->toBe($seller->id)
        ->and($product->image_path)->toMatch('#^products/[0-9a-f-]{36}\.webp$#');

    Storage::disk('public')->assertExists($product->image_path);
});

test('non-image uploads are rejected and never stored', function (UploadedFile $file) {
    Storage::fake('public');

    Livewire::actingAs(User::factory()->create())
        ->test(ProductForm::class)
        ->set('image', $file)
        ->assertHasErrors('image')
        ->assertSet('image', null);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'php shell' => fn () => UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>'),
    'svg with script' => fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
]);
