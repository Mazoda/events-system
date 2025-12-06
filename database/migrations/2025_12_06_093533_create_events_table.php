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
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description');
            $table->string('location', 255);
            $table->integer('capacity');
            $table->boolean('is_published')->default(false);
            $table->enum('status', ['draft', 'scheduled', 'cancelled', 'completed']);

            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamps();
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
