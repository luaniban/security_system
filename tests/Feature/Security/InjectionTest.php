<?php

use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Shop\Catalog;
use App\Models\AuditLog;
use App\Models\Order;
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
        ->assertViewHas('products', fn ($products) => $products->isEmpty());

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

test('code appended to a valid image is stripped by re-encoding (polyglot upload)', function () {
    Storage::fake('public');
    $seller = User::factory()->create();

    ob_start();
    imagepng(imagecreatetruecolor(200, 200));
    $png = (string) ob_get_clean();

    Livewire::actingAs($seller)
        ->test(ProductForm::class)
        ->set(['name' => 'Produto poliglota', 'description' => 'Descrição longa o suficiente.', 'price' => '10,00', 'stock' => 1])
        ->set('image', UploadedFile::fake()->createWithContent('foto.png', $png.'<?php system($_GET["c"]); ?>'))
        ->call('save')
        ->assertHasNoErrors();

    $stored = Storage::disk('public')->get($seller->products()->sole()->image_path);

    expect($stored)->toStartWith('RIFF')->not->toContain('<?php');
});

test('login rejects html hidden in a quoted e-mail, so it never reaches the audit trail', function () {
    Livewire::test(Login::class)
        ->set('email', '"<script>alert(1)</script>"@example.com')
        ->set('password', 'whatever')
        ->call('login')
        ->assertHasErrors('email');

    expect(AuditLog::count())->toBe(0);
});

test('audit trail details are shown escaped to admins (stored XSS)', function () {
    AuditLog::factory()->create([
        'event' => 'auth.failed',
        'context' => ['email' => '"<script>alert(1)</script>"@example.com'],
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.audit'))
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('user names are escaped on every page that shows them to someone else', function () {
    $mallory = User::factory()->create(['name' => '<script>alert("name")</script>']);
    $product = Product::factory()->for($mallory, 'seller')->create();
    $order = Order::factory()->for($mallory, 'buyer')->create();

    $responses = [
        'public product page' => $this->get(route('products.show', $product)),
        'seller order page' => $this->actingAs($order->seller)->get(route('orders.show', $order)),
        'admin user list' => $this->actingAs(User::factory()->admin()->create())->get(route('admin.users')),
    ];

    foreach ($responses as $response) {
        $response->assertSee('&lt;script&gt;alert(&quot;name&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("name")</script>', false);
    }
});

test('like wildcards and sql payloads in the admin user search are matched literally', function (string $term) {
    $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@example.com']);
    User::factory()->create(['name' => 'Ana', 'email' => 'ana@example.com']);

    Livewire::actingAs($admin)
        ->test(Users::class)
        ->set('search', $term)
        ->assertViewHas('users', fn ($users) => $users->isEmpty());

    expect(User::count())->toBe(2);
})->with([
    'percent' => '%',
    'underscore' => '_',
    'boolean injection' => "' OR '1'='1",
    'stacked query' => "'; DELETE FROM users; --",
]);
