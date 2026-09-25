<?php

namespace App\Support;

class Cpf
{
    /**
     * Strip everything that is not a digit.
     */
    public static function normalize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * Validate a CPF using its two check digits.
     */
    public static function isValid(string $value): bool
    {
        $cpf = self::normalize($value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mask a CPF so only the middle digits are visible (***.456.789-**).
     */
    public static function mask(string $value): string
    {
        $cpf = self::normalize($value);

        return '***.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-**';
    }

    /**
     * Keyed hash used as a blind index so encrypted CPFs can still be unique.
     */
    public static function blindIndex(string $value): string
    {
        return BlindIndex::make('cpf', self::normalize($value));
    }

    /**
     * Generate a random valid CPF (used by factories and seeders).
     */
    public static function generate(): string
    {
        do {
            $base = '';

            for ($index = 0; $index < 9; $index++) {
                $base .= random_int(0, 9);
            }

            for ($position = 9; $position < 11; $position++) {
                $sum = 0;

                for ($index = 0; $index < $position; $index++) {
                    $sum += (int) $base[$index] * (($position + 1) - $index);
                }

                $base .= ((10 * $sum) % 11) % 10;
            }
        } while (! self::isValid($base));

        return $base;
    }
}
