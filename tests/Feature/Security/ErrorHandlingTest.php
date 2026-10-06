<?php

use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;

function registerFailingRoute(): void
{
    Route::middleware('web')->get('/_test/falha', fn () => throw new RuntimeException('SQLSTATE[HY000] db-password=hunter2'));
}

test('unexpected errors show a generic page and are still reported', function () {
    Exceptions::fake();
    config(['app.debug' => false]);
    registerFailingRoute();

    $this->get('/_test/falha')
        ->assertInternalServerError()
        ->assertSee('Algo deu errado do nosso lado')
        ->assertDontSee('hunter2')
        ->assertDontSee('RuntimeException')
        ->assertDontSee(base_path(), false);

    Exceptions::assertReported(RuntimeException::class);
});

test('json clients get a generic error without exception details or stack trace', function () {
    Exceptions::fake();
    config(['app.debug' => false]);
    registerFailingRoute();

    $this->getJson('/_test/falha')
        ->assertInternalServerError()
        ->assertExactJson(['message' => 'Server Error']);
});

test('unknown pages and records answer with the friendly 404 page', function (string $uri) {
    $this->get($uri)
        ->assertNotFound()
        ->assertSee('Página não encontrada');
})->with([
    'unknown route' => '/wp-admin.php',
    'missing product' => '/produtos/999999',
    'non numeric product id' => '/produtos/1%20OR%201=1',
]);
