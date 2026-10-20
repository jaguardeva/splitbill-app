<?php

namespace Database\Factories;

use App\Models\BillItem;
use App\Models\Calculation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillItem>
 */
class BillItemFactory extends Factory
{
    protected $model = BillItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(10000, 100000);
        $quantity = 1.0;

        return [
            'calculation_id' => Calculation::factory(),
            'description' => fake()->word(),
            'quantity' => $quantity,
            'unit_price_minor' => $unitPrice,
            'line_subtotal_minor' => $unitPrice,
            'position' => 0,
        ];
    }
}
