<?php

namespace Database\Factories;

use App\Models\Calculation;
use App\Models\CalculationParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalculationParticipant>
 */
class CalculationParticipantFactory extends Factory
{
    protected $model = CalculationParticipant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calculation_id' => Calculation::factory(),
            'name' => fake()->firstName(),
            'position' => 0,
        ];
    }
}
