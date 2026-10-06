<?php

use App\Models\User;

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

test('authenticated pages are not cached', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response
        ->assertOk();

    expect($response->headers->get('Cache-Control'))->toContain('no-store')->toContain('private');
});
