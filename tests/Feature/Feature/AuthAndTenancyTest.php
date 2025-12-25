<?php

use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows super admin to manage tenants', function () {
    $super = User::factory()->create([
        'tenant_id' => null,
        'role' => 'super_admin',
    ]);

    Sanctum::actingAs($super);

    $res = $this->postJson('/api/super/tenants', [
        'name' => 'New Tenant',
        'slug' => 'new-tenant',
        'logo_url' => '',
        'is_active' => true,
    ]);

    $res->assertStatus(201);
    $this->assertDatabaseHas('tenants', ['slug' => 'new-tenant']);
});

it('prevents tenant admin from accessing super routes', function () {
    $tenant = Tenant::factory()->create(['is_active' => true]);
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'admin',
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/super/tenants')->assertStatus(403);
});

it('tenant admin cannot access other tenant events', function () {
    $tenantA = Tenant::factory()->create(['is_active' => true]);
    $tenantB = Tenant::factory()->create(['is_active' => true]);

    $adminA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role' => 'admin',
    ]);

    $eventB = Event::factory()->create([
        'tenant_id' => $tenantB->id,
    ]);

    Sanctum::actingAs($adminA);

    // Route model binding will be tenant-scoped -> should be 404.
    $this->getJson("/api/admin/events/{$eventB->id}")->assertStatus(404);
});
