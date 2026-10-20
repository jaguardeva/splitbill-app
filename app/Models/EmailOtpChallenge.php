<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\EmailOtpChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $purpose
 * @property string $code_hash
 * @property CarbonInterface $expires_at
 * @property CarbonInterface|null $consumed_at
 * @property int $attempts
 * @property int $max_attempts
 * @property CarbonInterface $last_sent_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
#[Fillable([
    'user_id',
    'purpose',
    'code_hash',
    'expires_at',
    'consumed_at',
    'attempts',
    'max_attempts',
    'last_sent_at',
])]
class EmailOtpChallenge extends Model
{
    /** @use HasFactory<EmailOtpChallengeFactory> */
    use HasFactory, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    /**
     * Get the user for this challenge.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only active, unconsumed challenges.
     *
     * @param  Builder<EmailOtpChallenge>  $query
     * @return Builder<EmailOtpChallenge>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('consumed_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Determine if challenge is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if challenge is consumed.
     */
    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    /**
     * Determine if challenge has exceeded maximum allowed attempts.
     */
    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }
}
