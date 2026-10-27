<?php

namespace App\Exceptions;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

/**
 * A request tried to change a Livewire property that is locked on the server (e.g. swapping
 * the order or product id). Livewire answers it in production with an empty 419 but does not
 * log it anywhere; this keeps the same answer and records the attempt (OWASP A09).
 */
class TamperedRequestException extends RuntimeException
{
    public function __construct(public readonly string $property, ?Throwable $previous = null)
    {
        parent::__construct("Locked property tampered: [{$property}]", previous: $previous);
    }

    public function report(AuditLogger $audit, Request $request): void
    {
        $audit->log('security.tampering', Auth::user(), [
            'property' => $this->property,
            'path' => '/'.ltrim($request->path(), '/'),
        ]);
    }

    public function render(): Response
    {
        return response('', 419);
    }
}
