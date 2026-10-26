<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hardening headers (OWASP Secure Headers Project).
 *
 * The Content-Security-Policy only allows scripts/styles from our own origin or
 * carrying the per-request nonce, so an injected <script> or inline handler
 * (onerror=, javascript:) is refused by the browser even if escaping failed.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $headers = [
            'Content-Security-Policy' => $this->contentSecurityPolicy(Vite::cspNonce()),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), usb=(), payment=(self)',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        // Pages behind login may contain personal data: never let browsers/proxies cache them.
        // Requests that never reached the session (e.g. an unknown route) have no user.
        if ($request->hasSession() && $request->user()) {
            $headers['Cache-Control'] = 'no-store, private';
            $headers['Pragma'] = 'no-cache';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        $devServer = $this->viteDevServer();

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-{$nonce}'", $devServer],
            'style-src' => ["'self'", "'nonce-{$nonce}'", $devServer],
            'img-src' => ["'self'", 'data:', 'blob:'],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'", $devServer, $devServer ? str_replace('http', 'ws', $devServer) : null],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
        ];

        return collect($directives)
            ->map(fn (array $sources, string $directive): string => trim($directive.' '.implode(' ', array_filter($sources))))
            ->implode('; ');
    }

    /**
     * Allow the Vite dev server only while `npm run dev` is running locally.
     */
    private function viteDevServer(): ?string
    {
        if (! app()->isLocal() || ! Vite::isRunningHot()) {
            return null;
        }

        $url = trim((string) file_get_contents(public_path('hot')));

        return parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).':'.parse_url($url, PHP_URL_PORT);
    }
}
