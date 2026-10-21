<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin account
        User::firstOrCreate(
            ['email' => 'admin@splitbill.test'],
            [
                'name' => 'Super Admin',
                'role' => Role::Admin,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        // Standard User account
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'role' => Role::User,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
    }
}
