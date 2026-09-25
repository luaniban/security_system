<?php

namespace App\Services;

use App\Exceptions\PaymentDeclinedException;
use App\Models\PaymentCard;

/**
 * Simulated acquirer. In production this would call a PCI-compliant provider
 * using a token, never forwarding the raw PAN from our own database.
 */
class PaymentGateway
{
    /**
     * @throws PaymentDeclinedException
     */
    public function charge(PaymentCard $card, string $cvv, int $amountCents): void
    {
        if ($card->isExpired()) {
            throw new PaymentDeclinedException('Cartão vencido.');
        }

        if (! preg_match('/^\d{3,4}$/', $cvv)) {
            throw new PaymentDeclinedException('CVV inválido.');
        }

        // Test card that always declines, useful for demonstrating the failure flow.
        if ($card->last_four === '0002') {
            throw new PaymentDeclinedException('Pagamento recusado pela operadora.');
        }

        if ($amountCents <= 0) {
            throw new PaymentDeclinedException('Valor inválido.');
        }
    }
}
