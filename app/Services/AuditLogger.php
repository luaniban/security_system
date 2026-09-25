<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Security event trail (OWASP A09 - Security Logging and Monitoring).
 * Never pass secrets (passwords, card numbers, tokens) in the context.
 */
class AuditLogger
{
    public function __construct(private Request $request) {}

    /**
     * @param  array<string, scalar|null>  $context
     */
    public function log(string $event, ?User $user = null, array $context = []): void
    {
        AuditLog::create([
            'user_id' => $user?->getKey(),
            'event' => $event,
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit((string) $this->request->userAgent(), 250, ''),
            'context' => $context ?: null,
        ]);
    }
}
