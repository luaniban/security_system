<?php

use App\Livewire\Account\Addresses;
use App\Livewire\Account\Cards;
use App\Livewire\Account\Profile;
use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Users;
use App\Livewire\Cart\ShowCart;
use App\Livewire\Checkout\PlaceOrder;
use App\Livewire\Dashboard;
use App\Livewire\Orders\ListOrders;
use App\Livewire\Orders\ShowOrder;
use App\Livewire\Seller\ListProducts;
use App\Livewire\Seller\ListSales;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Shop\Catalog;
use App\Livewire\Shop\ShowProduct;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Catalog::class)->name('home');
Route::livewire('produtos/{product}', ShowProduct::class)->whereNumber('product')->name('products.show');
Route::view('privacidade', 'privacy')->name('privacy');

Route::middleware('auth')->group(function () {
    Route::livewire('conta/perfil', Profile::class)->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('painel', Dashboard::class)->name('dashboard');

    Route::livewire('conta/enderecos', Addresses::class)->name('addresses.index');
    Route::livewire('conta/cartoes', Cards::class)->middleware('password.confirm')->name('cards.index');

    Route::livewire('carrinho', ShowCart::class)->name('cart');
    Route::livewire('checkout', PlaceOrder::class)->name('checkout');

    Route::livewire('pedidos', ListOrders::class)->name('orders.index');
    Route::livewire('pedidos/{order}', ShowOrder::class)->name('orders.show');

    Route::prefix('vender')->name('seller.')->group(function () {
        Route::livewire('produtos', ListProducts::class)->name('products.index');
        Route::livewire('produtos/novo', ProductForm::class)->name('products.create');
        Route::livewire('produtos/{product}/editar', ProductForm::class)->whereNumber('product')->name('products.edit');
        Route::livewire('vendas', ListSales::class)->name('sales.index');
    });

    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::livewire('usuarios', Users::class)->name('users');
        Route::livewire('auditoria', AuditLogs::class)->name('audit');
    });
});

require __DIR__.'/auth.php';
