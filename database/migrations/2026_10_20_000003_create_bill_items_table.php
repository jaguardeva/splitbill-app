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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_id')->constrained('calculations')->cascadeOnDelete();
            $table->string('description', 180);
            $table->decimal('quantity', 10, 3)->default(1.000);
            $table->unsignedBigInteger('unit_price_minor');
            $table->unsignedBigInteger('line_subtotal_minor')->default(0);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['calculation_id', 'position']);
            $table->unique(['calculation_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
