<?php

namespace Database\Factories;

use App\Models\Calculation;
use App\Models\CalculationAdjustment;
use App\Models\CalculationParticipant;
use App\Models\ParticipantAdjustmentShare;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParticipantAdjustmentShare>
 */
class ParticipantAdjustmentShareFactory extends Factory
{
    protected $model = ParticipantAdjustmentShare::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calculation_id' => Calculation::factory(),
            'adjustment_id' => CalculationAdjustment::factory(),
            'participant_id' => CalculationParticipant::factory(),
            'amount_minor' => 5000,
        ];
    }
}
