<?php

namespace Database\Factories;

use App\Models\Calculation;
use App\Models\CalculationAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalculationAdjustment>
 */
class CalculationAdjustmentFactory extends Factory
{
    protected $model = CalculationAdjustment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calculation_id' => Calculation::factory(),
            'type' => 'tax',
            'label' => 'PPN 10%',
            'value_type' => 'percentage',
            'value_minor' => null,
            'percentage_rate' => 10.0000,
            'base_type' => 'after_discount',
            'is_enabled' => true,
            'position' => 0,
            'amount_minor' => 10000,
        ];
    }
}
