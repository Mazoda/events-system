<?php

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates an order and attendee tickets and decrements inventory', function () {
    $tenant = Tenant::factory()->create(['is_active' => true]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'user',
    ]);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'is_published' => true,
    ]);

    $type = TicketType::factory()->create([
        'tenant_id' => $tenant->id,
        'event_id' => $event->id,
        'price' => 50,
        'quantity_available' => 10,
        'quantity_sold' => 0,
        'is_active' => true,
        'sale_starts_at' => now()->subHour(),
        'sale_ends_at' => now()->addHour(),
    ]);

    Sanctum::actingAs($user);

    $res = $this->postJson('/api/orders', [
        'items' => [
            ['ticket_type_id' => $type->id, 'quantity' => 2],
        ],
    ]);

    $res->assertStatus(201);

    $type->refresh();
    expect($type->quantity_sold)->toBe(2);

    $this->assertDatabaseCount('attendee_tickets', 2);
});

it('rejects purchase when inventory is insufficient', function () {
    $tenant = Tenant::factory()->create(['is_active' => true]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'user',
    ]);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'is_published' => true,
    ]);

    $type = TicketType::factory()->create([
        'tenant_id' => $tenant->id,
        'event_id' => $event->id,
        'price' => 50,
        'quantity_available' => 1,
        'quantity_sold' => 0,
        'is_active' => true,
        'sale_starts_at' => now()->subHour(),
        'sale_ends_at' => now()->addHour(),
    ]);

    Sanctum::actingAs($user);

    $this->postJson('/api/orders', [
        'items' => [
            ['ticket_type_id' => $type->id, 'quantity' => 2],
        ],
    ])->assertStatus(422);
});
