<?php

namespace Database\Factories;

use App\Enums\CardBrand;
use App\Models\PaymentCard;
use App\Models\User;
use App\Support\BlindIndex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentCard>
 */
class PaymentCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = '4111111111111111';

        return [
            'user_id' => User::factory(),
            'holder_name' => mb_strtoupper(fake()->name()),
            'number' => $number,
            'fingerprint' => BlindIndex::make('card', $number),
            'brand' => CardBrand::detect($number),
            'last_four' => substr($number, -4),
            'exp_month' => 12,
            'exp_year' => (int) now()->addYears(3)->format('Y'),
        ];
    }

    /**
     * Test card the simulated gateway always declines.
     */
    public function declined(): static
    {
        $number = '4000000000000002';

        return $this->state(fn (array $attributes) => [
            'number' => $number,
            'fingerprint' => BlindIndex::make('card', $number),
            'last_four' => '0002',
        ]);
    }
}
