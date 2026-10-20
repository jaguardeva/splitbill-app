<?php

namespace Database\Factories;

use App\Models\EmailOtpChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailOtpChallenge>
 */
class EmailOtpChallengeFactory extends Factory
{
    protected $model = EmailOtpChallenge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'purpose' => 'email_verification',
            'code_hash' => bcrypt('123456'),
            'expires_at' => now()->addMinutes(10),
            'consumed_at' => null,
            'attempts' => 0,
            'max_attempts' => 5,
            'last_sent_at' => now(),
        ];
    }
}
