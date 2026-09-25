<?php

namespace App\Support;

class BlindIndex
{
    /**
     * Deterministic HMAC-SHA256 of a sensitive value, scoped by purpose,
     * allowing equality lookups without storing the plaintext.
     */
    public static function make(string $purpose, string $value): string
    {
        return hash_hmac('sha256', $purpose.'|'.$value, config('app.key'));
    }
}
