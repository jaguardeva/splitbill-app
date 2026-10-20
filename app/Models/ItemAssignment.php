<?php

namespace App\Models;

use Database\Factories\ItemAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $calculation_id
 * @property string $bill_item_id
 * @property string $participant_id
 * @property string|float $share_weight
 * @property int $allocated_subtotal_minor
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'calculation_id',
    'bill_item_id',
    'participant_id',
    'share_weight',
    'allocated_subtotal_minor',
])]
class ItemAssignment extends Model
{
    /** @use HasFactory<ItemAssignmentFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'share_weight' => 'decimal:4',
            'allocated_subtotal_minor' => 'integer',
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
     * Get the assigned bill item.
     *
     * @return BelongsTo<BillItem, $this>
     */
    public function billItem(): BelongsTo
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }

    /**
     * Get the assigned participant.
     *
     * @return BelongsTo<CalculationParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(CalculationParticipant::class, 'participant_id');
    }
}
