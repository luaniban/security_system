<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 20000);

        return [
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'status' => OrderStatus::Paid,
            'subtotal_cents' => $subtotal,
            'shipping_cents' => 1500,
            'total_cents' => $subtotal + 1500,
            'shipping_address' => [
                'recipient_name' => fake()->name(),
                'zip_code' => '01001-000',
                'street' => 'Praça da Sé',
                'number' => '1',
                'complement' => null,
                'district' => 'Sé',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            'card_brand' => 'visa',
            'card_last_four' => '1111',
            'estimated_delivery_date' => now()->addWeek(),
            'paid_at' => now(),
        ];
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
            'shipped_at' => now(),
        ]);
    }
}
