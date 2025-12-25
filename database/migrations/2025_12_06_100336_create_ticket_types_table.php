<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Multi-Tenancy & Event Link)
            // Note: You must use foreignId() and constrained() together for simplicity and safety.
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete(); // If tenant is deleted, delete ticket types

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete(); // If event is deleted, delete ticket types

            // Inventory Details
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);

            // Inventory Counters
            $table->unsignedInteger('quantity_available');
            $table->unsignedInteger('quantity_sold')->default(0);

            // Sales Window
            $table->dateTime('sale_starts_at')->nullable();
            $table->dateTime('sale_ends_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for performance
            $table->index('tenant_id');
            $table->index('event_id');
            $table->index(['event_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
