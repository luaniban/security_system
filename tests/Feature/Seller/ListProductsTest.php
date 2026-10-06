<?php

use App\Livewire\Seller\ListProducts;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a seller sees only their own products, including inactive ones', function () {
    $seller = User::factory()->create();
    Product::factory()->for($seller, 'seller')->create(['name' => 'Produto ativo']);
    Product::factory()->for($seller, 'seller')->inactive()->create(['name' => 'Produto pausado']);
    Product::factory()->create(['name' => 'Produto de outra loja']);

    $this->actingAs($seller)
        ->get(route('seller.products.index'))
        ->assertSee('Produto ativo')
        ->assertSee('Produto pausado')
        ->assertDontSee('Produto de outra loja');
});

test('a seller can pause and reactivate a product', function () {
    $product = Product::factory()->create();

    $component = Livewire::actingAs($product->seller)->test(ListProducts::class);

    $component->call('toggleActive', $product->id);
    expect($product->fresh()->is_active)->toBeFalse();

    $component->call('toggleActive', $product->id);
    expect($product->fresh()->is_active)->toBeTrue();
});

test('deleting a product also removes its image and is audited', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/photo.webp', 'image');
    $product = Product::factory()->create(['image_path' => 'products/photo.webp']);

    Livewire::actingAs($product->seller)->test(ListProducts::class)->call('delete', $product->id);

    $this->assertModelMissing($product);
    Storage::disk('public')->assertMissing('products/photo.webp');
    $this->assertDatabaseHas('audit_logs', ['event' => 'product.deleted', 'user_id' => $product->seller_id]);
});

test('a seller can not pause another seller\'s product', function () {
    $product = Product::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test(ListProducts::class)
        ->call('toggleActive', $product->id)
        ->assertNotFound();

    expect($product->fresh()->is_active)->toBeTrue();
});
