<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calculation_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_id')->constrained('calculations')->cascadeOnDelete();
            $table->string('type', 24); // discount, tax, service_charge, tip
            $table->string('label', 100);
            $table->string('value_type', 16); // fixed, percentage
            $table->unsignedBigInteger('value_minor')->nullable();
            $table->decimal('percentage_rate', 7, 4)->nullable();
            $table->string('base_type', 24)->default('subtotal');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->timestamps();

            $table->index(['calculation_id', 'position']);
            $table->index(['calculation_id', 'type']);
            $table->unique(['calculation_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_adjustments');
    }
};
