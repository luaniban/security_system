<?php

use App\Exceptions\TamperedRequestException;
use App\Http\Middleware\SecurityHeaders;
use App\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;

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

        // OWASP A09: a denied authorization is a security event. Laravel ignores it by default;
        // record it in the audit trail instead (and keep it out of the error log).
        $exceptions->stopIgnoring(AuthorizationException::class);
        $exceptions->report(function (AuthorizationException $exception): false {
            app(AuditLogger::class)->log('security.access_denied', auth()->user(), [
                'method' => request()->method(),
                'path' => '/'.ltrim(request()->path(), '/'),
            ]);

            return false;
        });

        // Livewire swallows tampered locked properties in production without logging them.
        $exceptions->map(
            CannotUpdateLockedPropertyException::class,
            fn (CannotUpdateLockedPropertyException $exception) => new TamperedRequestException((string) $exception->property, $exception),
        );
    })->create();
