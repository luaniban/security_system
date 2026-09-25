<?php

namespace App\Support;

class Luhn
{
    /**
     * Validate a numeric string using the Luhn (mod 10) checksum.
     */
    public static function isValid(string $number): bool
    {
        if (! preg_match('/^\d{13,19}$/', $number)) {
            return false;
        }

        $sum = 0;
        $shouldDouble = false;

        for ($index = strlen($number) - 1; $index >= 0; $index--) {
            $digit = (int) $number[$index];

            if ($shouldDouble) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $shouldDouble = ! $shouldDouble;
        }

        return $sum % 10 === 0;
    }
}
