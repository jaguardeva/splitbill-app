<?php

namespace Database\Factories;

use App\Models\Calculation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Calculation>
 */
class CalculationFactory extends Factory
{
    protected $model = Calculation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'currency' => 'IDR',
            'split_mode' => 'itemized',
            'status' => 'completed',
            'subtotal_minor' => 100000,
            'discount_total_minor' => 0,
            'tax_total_minor' => 10000,
            'service_charge_minor' => 5000,
            'tip_total_minor' => 0,
            'grand_total_minor' => 115000,
            'calculation_version' => 1,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
