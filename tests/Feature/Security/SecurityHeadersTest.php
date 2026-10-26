<?php

use App\Models\User;
use Illuminate\Http\Request;

test('responses carry hardening headers and a nonce based CSP', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'")
        ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9]+'/")
        ->not->toContain('unsafe-inline')
        ->not->toContain('unsafe-eval');
});

test('browser features and cross-origin access are locked down', function () {
    $this->get('/')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), usb=(), payment=(self)')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
        ->assertHeaderMissing('X-Powered-By');
});

test('HSTS is sent over https', function () {
    $this->get('https://localhost/')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('HSTS is not sent over plain http', function () {
    $this->get('http://localhost/')
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('error pages carry the hardening headers too', function (string $uri) {
    $response = $this->get($uri);

    $response->assertNotFound()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'none'");
})->with([
    'unknown route' => '/pagina-que-nao-existe',
    'missing product' => '/produtos/999999',
]);

test('requests with a forged Host header are refused, so reset links can not point elsewhere', function () {
    $this->app['env'] = 'production';

    $this->get('http://evil.example/forgot-password')->assertBadRequest();
    $this->get('/forgot-password')->assertOk();
})->after(fn () => Request::setTrustedHosts([]));

test('authenticated pages are not cached', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response
        ->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('no-store')->toContain('private');
});
