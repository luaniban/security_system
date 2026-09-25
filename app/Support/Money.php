<?php

namespace App\Support;

class Money
{
    public const PRICE_PATTERN = '/^\d{1,6}([.,]\d{1,2})?$/';

    /**
     * Format an integer amount of cents as Brazilian Real.
     */
    public static function format(int $cents): string
    {
        return 'R$ '.number_format($cents / 100, 2, ',', '.');
    }

    /**
     * Parse a "1234,56" / "1234.56" string into integer cents (no float math).
     */
    public static function toCents(string $value): ?int
    {
        $value = trim($value);

        if (! preg_match(self::PRICE_PATTERN, $value)) {
            return null;
        }

        [$reais, $centavos] = array_pad(preg_split('/[.,]/', $value), 2, '0');

        return ((int) $reais * 100) + (int) str_pad($centavos, 2, '0');
    }

    /**
     * Convert cents back into the "1234,56" format used by form inputs.
     */
    public static function toInput(int $cents): string
    {
        return intdiv($cents, 100).','.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
