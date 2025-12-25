<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\Tenant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::factory();
        $event = Event::factory()->state([
            'tenant_id' => $tenant,
        ]);

        return [
            'tenant_id' => $tenant,
            'event_id' => $event,
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 500),
            'quantity_available' => fake()->numberBetween(0, 500),
            'quantity_sold' => 0,
            'sale_starts_at' => now()->subDay(),
            'sale_ends_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
