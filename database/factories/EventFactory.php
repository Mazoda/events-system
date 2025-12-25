<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'location' => fake()->address(),
            'capacity' => fake()->numberBetween(0, 1000),
            'is_published' => true,
            'status' => 'scheduled',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
        ];
    }
}
