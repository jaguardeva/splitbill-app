<?php

namespace App\Models;

use Database\Factories\CalculationAdjustmentFactory;
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
 * @property string $type
 * @property string $label
 * @property string $value_type
 * @property int|null $value_minor
 * @property string|float|null $percentage_rate
 * @property string $base_type
 * @property bool $is_enabled
 * @property int $position
 * @property int $amount_minor
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'calculation_id',
    'type',
    'label',
    'value_type',
    'value_minor',
    'percentage_rate',
    'base_type',
    'is_enabled',
    'position',
    'amount_minor',
])]
class CalculationAdjustment extends Model
{
    /** @use HasFactory<CalculationAdjustmentFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_minor' => 'integer',
            'percentage_rate' => 'decimal:4',
            'is_enabled' => 'boolean',
            'position' => 'integer',
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
     * Get the participant shares for this adjustment.
     *
     * @return HasMany<ParticipantAdjustmentShare, $this>
     */
    public function shares(): HasMany
    {
        return $this->hasMany(ParticipantAdjustmentShare::class, 'adjustment_id');
    }
}
