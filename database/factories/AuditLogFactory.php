<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'event' => 'auth.login',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'context' => null,
        ];
    }
}
