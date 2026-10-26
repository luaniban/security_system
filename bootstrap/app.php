<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global and outermost, so error responses (404 from routing or model binding,
        // 503 maintenance, 413 oversized posts) also carry the hardening headers.
        $middleware->prepend(SecurityHeaders::class);

        // Only answer for the APP_URL host: password reset links are built from the
        // Host header, so a forged Host would point them at an attacker's site.
        $middleware->trustHosts();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Never write secrets to logs / error reports.
        $exceptions->dontFlash([
            'current_password', 'password', 'password_confirmation', 'number', 'cvv', 'cpf',
        ]);
    })->create();
