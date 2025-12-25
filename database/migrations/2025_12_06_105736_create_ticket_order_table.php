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
        Schema::create('ticket_orders', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Ownership & Tenancy)
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete(); // Prevent deleting a user with existing orders

            // Financial & Tracking
            $table->decimal('total_price', 10, 2);
            $table->unsignedSmallInteger('quantity_total');
            $table->string('transaction_id', 255)->nullable()->unique();

            // Status & Dates
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->dateTime('purchased_at')->nullable();

            $table->timestamps();

            // Indexes for fast lookups
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_orders');
    }
};
