<?php

namespace Database\Factories;

use App\Models\BillItem;
use App\Models\Calculation;
use App\Models\CalculationParticipant;
use App\Models\ItemAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemAssignment>
 */
class ItemAssignmentFactory extends Factory
{
    protected $model = ItemAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calculation_id' => Calculation::factory(),
            'bill_item_id' => BillItem::factory(),
            'participant_id' => CalculationParticipant::factory(),
            'share_weight' => 1.0000,
            'allocated_subtotal_minor' => 10000,
        ];
    }
}
