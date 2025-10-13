<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment('production')) {
            $this->command?->info('UserSeeder: Skipped in production.');
            return;
        }

        // Generate or read a development password from env. Do not commit real secrets.
        $devPassword = env('DEV_SEED_PASSWORD');
        if (!$devPassword) {
            // Random strong fallback for local only; printed so dev can capture it.
            $devPassword = Str::password(16, symbols: true);
            $this->command?->warn('UserSeeder: DEV_SEED_PASSWORD not set; generated a random dev password (shown below).');
            $this->command?->line($devPassword);
        }

        // Default dev login user (idempotent)
        User::updateOrCreate(
            ['email' => 'dimitri.mcdaniel@gmail.com'],
            [
                'name' => 'Dimitri McDaniel',
                'password' => Hash::make($devPassword),
            ]
        );

        // Optional test user (dev only)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make($devPassword),
            ]
        );
    }
}
