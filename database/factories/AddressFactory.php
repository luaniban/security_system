<?php

namespace Database\Factories;

use App\Enums\BrazilianState;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Casa', 'Trabalho', 'Apartamento']),
            'recipient_name' => fake()->name(),
            'zip_code' => fake()->numerify('########'),
            'street' => 'Rua '.fake()->lastName(),
            'number' => (string) fake()->numberBetween(1, 2000),
            'complement' => null,
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => BrazilianState::SP,
        ];
    }
}
