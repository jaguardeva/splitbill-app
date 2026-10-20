<?php

namespace App\Models;

use Database\Factories\ParticipantAdjustmentShareFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $calculation_id
 * @property string $adjustment_id
 * @property string $participant_id
 * @property int $amount_minor
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'calculation_id',
    'adjustment_id',
    'participant_id',
    'amount_minor',
])]
class ParticipantAdjustmentShare extends Model
{
    /** @use HasFactory<ParticipantAdjustmentShareFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
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
     * Get the adjustment for this share.
     *
     * @return BelongsTo<CalculationAdjustment, $this>
     */
    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(CalculationAdjustment::class, 'adjustment_id');
    }

    /**
     * Get the participant for this share.
     *
     * @return BelongsTo<CalculationParticipant, $this>
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(CalculationParticipant::class, 'participant_id');
    }
}
