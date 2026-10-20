<?php

namespace App\Models;

use Database\Factories\CalculationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $title
 * @property string $currency
 * @property string $split_mode
 * @property string $status
 * @property int $subtotal_minor
 * @property int $discount_total_minor
 * @property int $tax_total_minor
 * @property int $service_charge_minor
 * @property int $tip_total_minor
 * @property int $grand_total_minor
 * @property int $calculation_version
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'title',
    'currency',
    'split_mode',
    'status',
    'subtotal_minor',
    'discount_total_minor',
    'tax_total_minor',
    'service_charge_minor',
    'tip_total_minor',
    'grand_total_minor',
    'calculation_version',
    'notes',
])]
class Calculation extends Model
{
    /** @use HasFactory<CalculationFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'discount_total_minor' => 'integer',
            'tax_total_minor' => 'integer',
            'service_charge_minor' => 'integer',
            'tip_total_minor' => 'integer',
            'grand_total_minor' => 'integer',
            'calculation_version' => 'integer',
        ];
    }

    /**
     * Get the owning user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the participants for the calculation.
     *
     * @return HasMany<CalculationParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(CalculationParticipant::class)->orderBy('position');
    }

    /**
     * Get the bill items for the calculation.
     *
     * @return HasMany<BillItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class)->orderBy('position');
    }

    /**
     * Get all item assignments for the calculation.
     *
     * @return HasMany<ItemAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class);
    }

    /**
     * Get the adjustments for the calculation.
     *
     * @return HasMany<CalculationAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(CalculationAdjustment::class)->orderBy('position');
    }

    /**
     * Get all participant adjustment shares for the calculation.
     *
     * @return HasMany<ParticipantAdjustmentShare, $this>
     */
    public function adjustmentShares(): HasMany
    {
        return $this->hasMany(ParticipantAdjustmentShare::class);
    }
}
