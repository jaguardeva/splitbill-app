<?php

namespace App\Models;

use Database\Factories\BillItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $calculation_id
 * @property string $description
 * @property string|float $quantity
 * @property int $unit_price_minor
 * @property int $line_subtotal_minor
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'calculation_id',
    'description',
    'quantity',
    'unit_price_minor',
    'line_subtotal_minor',
    'position',
])]
class BillItem extends Model
{
    /** @use HasFactory<BillItemFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price_minor' => 'integer',
            'line_subtotal_minor' => 'integer',
            'position' => 'integer',
        ];
    }

    /**
     * Get the parent calculation.
     *
     * @return BelongsTo<Calculation, $this>
     */
    public function calculation(): BelongsTo
    {
        return $this->belongsTo(Calculation::class);
    }

    /**
     * Get the assignments for this item.
     *
     * @return HasMany<ItemAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'bill_item_id');
    }

    /**
     * Get the assigned participants through assignments.
     *
     * @return BelongsToMany<CalculationParticipant, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            CalculationParticipant::class,
            'item_assignments',
            'bill_item_id',
            'participant_id'
        )->withPivot(['share_weight', 'allocated_subtotal_minor'])->withTimestamps();
    }
}
