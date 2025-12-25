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
        Schema::create('attendee_tickets', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Context)
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('ticket_orders') // Specify table name if necessary
                ->cascadeOnDelete(); // If order is refunded/deleted, delete passes

            $table->foreignId('ticket_type_id')
                ->constrained('ticket_types')
                ->restrictOnDelete(); // Don't delete a type if passes exist

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            // Core Scannable Data
            $table->uuid('reference_code')->unique(); // Unique code for QR/Barcodes

            // Check-in Status
            $table->boolean('is_scanned')->default(false);
            $table->dateTime('scanned_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('reference_code'); // Extremely fast lookup for scanning
            $table->index('order_id');
            $table->index('ticket_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendee_tickets');
    }
};
