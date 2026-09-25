<?php

namespace App\Rules;

use App\Support\Luhn;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidCardNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Luhn::isValid(preg_replace('/\D/', '', $value) ?? '')) {
            $fail('O número do cartão é inválido.');
        }
    }
}
