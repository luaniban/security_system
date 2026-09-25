<?php

namespace App\Enums;

enum CardBrand: string
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';
    case Amex = 'amex';
    case Elo = 'elo';
    case Other = 'other';

    /**
     * Detect the card brand from its (digits only) number.
     */
    public static function detect(string $number): self
    {
        return match (true) {
            (bool) preg_match('/^(4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/', $number) => self::Elo,
            str_starts_with($number, '4') => self::Visa,
            (bool) preg_match('/^(5[1-5]|2[2-7])/', $number) => self::Mastercard,
            (bool) preg_match('/^3[47]/', $number) => self::Amex,
            default => self::Other,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Visa => 'Visa',
            self::Mastercard => 'Mastercard',
            self::Amex => 'American Express',
            self::Elo => 'Elo',
            self::Other => 'Cartão',
        };
    }
}
