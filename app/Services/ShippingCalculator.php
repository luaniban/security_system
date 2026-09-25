<?php

namespace App\Services;

use App\Enums\BrazilianState;
use Illuminate\Support\Carbon;

class ShippingCalculator
{
    public const FREE_SHIPPING_THRESHOLD_CENTS = 30000;

    private const BASE_CENTS = 1500;

    private const PER_ITEM_CENTS = 250;

    /**
     * Shipping price for a single seller's package, computed server-side only.
     */
    public function priceFor(BrazilianState $destination, int $itemCount, int $subtotalCents): int
    {
        if ($subtotalCents >= self::FREE_SHIPPING_THRESHOLD_CENTS) {
            return 0;
        }

        $distanceFactor = intdiv($destination->deliveryDays(), 4);

        return self::BASE_CENTS + ($itemCount * self::PER_ITEM_CENTS) + ($distanceFactor * 500);
    }

    public function estimatedDeliveryDate(BrazilianState $destination): Carbon
    {
        return now()->addWeekdays($destination->deliveryDays());
    }
}
