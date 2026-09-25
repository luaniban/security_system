<?php

namespace App\Enums;

enum BrazilianState: string
{
    case AC = 'AC';
    case AL = 'AL';
    case AP = 'AP';
    case AM = 'AM';
    case BA = 'BA';
    case CE = 'CE';
    case DF = 'DF';
    case ES = 'ES';
    case GO = 'GO';
    case MA = 'MA';
    case MT = 'MT';
    case MS = 'MS';
    case MG = 'MG';
    case PA = 'PA';
    case PB = 'PB';
    case PR = 'PR';
    case PE = 'PE';
    case PI = 'PI';
    case RJ = 'RJ';
    case RN = 'RN';
    case RS = 'RS';
    case RO = 'RO';
    case RR = 'RR';
    case SC = 'SC';
    case SP = 'SP';
    case SE = 'SE';
    case TO = 'TO';

    /**
     * Delivery lead time, in business days, when shipping to this state.
     */
    public function deliveryDays(): int
    {
        return match ($this) {
            self::SP, self::RJ, self::MG, self::ES, self::PR, self::SC, self::RS => 5,
            self::DF, self::GO, self::MT, self::MS => 7,
            self::AC, self::AP, self::AM, self::PA, self::RO, self::RR, self::TO => 12,
            default => 9,
        };
    }
}
