<?php

namespace App\Models;

use Database\Factories\CalculationParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $calculation_id
 * @property string $name
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'calculation_id',
    'name',
    'position',
])]
class CalculationParticipant extends Model
{
    /** @use HasFactory<CalculationParticipantFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * Get the item assignments for this participant.
     *
     * @return HasMany<ItemAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'participant_id');
    }

    /**
     * Get the adjustment shares for this participant.
     *
     * @return HasMany<ParticipantAdjustmentShare, $this>
     */
    public function adjustmentShares(): HasMany
    {
        return $this->hasMany(ParticipantAdjustmentShare::class, 'participant_id');
    }
}
