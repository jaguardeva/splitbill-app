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
        Schema::create('item_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_id')->constrained('calculations')->cascadeOnDelete();
            $table->uuid('bill_item_id');
            $table->uuid('participant_id');
            $table->decimal('share_weight', 10, 4)->default(1.0000);
            $table->unsignedBigInteger('allocated_subtotal_minor')->default(0);
            $table->timestamps();

            $table->unique(['bill_item_id', 'participant_id']);
            $table->index(['calculation_id', 'participant_id']);
            $table->index(['calculation_id', 'bill_item_id']);

            $table->foreign(['calculation_id', 'bill_item_id'])
                ->references(['calculation_id', 'id'])
                ->on('bill_items')
                ->cascadeOnDelete();

            $table->foreign(['calculation_id', 'participant_id'])
                ->references(['calculation_id', 'id'])
                ->on('calculation_participants')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_assignments');
    }
};
