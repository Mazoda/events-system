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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Multi-Tenancy Link
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Event Details
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('location', 255);
            $table->unsignedInteger('capacity')->default(0);

            // Status & Time
            $table->boolean('is_published')->default(false);
            $table->enum('status', ['draft', 'scheduled', 'cancelled', 'completed'])->default('draft');

            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
