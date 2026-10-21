<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Role $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Check if the user has an Admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * Check if the user has a regular User role.
     */
    public function isUser(): bool
    {
        return $this->role === Role::User;
    }

    /**
     * Determine if the user has a specific role.
     */
    public function hasRole(Role|string $role): bool
    {
        $roleValue = $role instanceof Role ? $role->value : $role;

        return $this->role->value === $roleValue;
    }

    /**
     * Get the calculations owned by this user.
     *
     * @return HasMany<Calculation, $this>
     */
    public function calculations(): HasMany
    {
        return $this->hasMany(Calculation::class);
    }

    /**
     * Get the email OTP challenges issued for this user.
     *
     * @return HasMany<EmailOtpChallenge, $this>
     */
    public function emailOtpChallenges(): HasMany
    {
        return $this->hasMany(EmailOtpChallenge::class);
    }
}
