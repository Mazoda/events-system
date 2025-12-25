<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantA = Tenant::factory()->create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'is_active' => true,
        ]);

        $tenantB = Tenant::factory()->create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'is_active' => true,
        ]);

        User::factory()->create([
            'tenant_id' => null,
            'role' => 'super_admin',
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'admin',
            'name' => 'Tenant A Admin',
            'email' => 'admin@tenant-a.com',
            'password' => Hash::make('password'),
        ]);

        $userA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'user',
            'name' => 'Tenant A User',
            'email' => 'user@tenant-a.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'tenant_id' => $tenantB->id,
            'role' => 'admin',
            'name' => 'Tenant B Admin',
            'email' => 'admin@tenant-b.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'tenant_id' => $tenantB->id,
            'role' => 'user',
            'name' => 'Tenant B User',
            'email' => 'user@tenant-b.com',
            'password' => Hash::make('password'),
        ]);

        $eventA = Event::factory()->create([
            'tenant_id' => $tenantA->id,
            'is_published' => true,
            'status' => 'scheduled',
        ]);

        $ticketTypeA = TicketType::factory()->create([
            'tenant_id' => $tenantA->id,
            'event_id' => $eventA->id,
            'name' => 'General Admission',
            'quantity_available' => 100,
            'quantity_sold' => 0,
            'is_active' => true,
        ]);

        // Optional: create a sample order via API during manual testing.
        // TicketPurchaseService is exercised via automated tests.
        $userA->refresh();
        $ticketTypeA->refresh();
    }
}
