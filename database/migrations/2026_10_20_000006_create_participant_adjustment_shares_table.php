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
        Schema::create('participant_adjustment_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('calculation_id')->constrained('calculations')->cascadeOnDelete();
            $table->uuid('adjustment_id');
            $table->uuid('participant_id');
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->timestamps();

            $table->unique(['adjustment_id', 'participant_id'], 'p_adj_shares_adj_part_unique');
            $table->index(['calculation_id', 'participant_id'], 'p_adj_shares_calc_part_idx');

            $table->foreign(['calculation_id', 'adjustment_id'], 'p_adj_shares_calc_adj_fk')
                ->references(['calculation_id', 'id'])
                ->on('calculation_adjustments')
                ->cascadeOnDelete();

            $table->foreign(['calculation_id', 'participant_id'], 'p_adj_shares_calc_part_fk')
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
        Schema::dropIfExists('participant_adjustment_shares');
    }
};
