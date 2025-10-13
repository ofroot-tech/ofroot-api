<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment('production')) {
            $this->command?->info('UserSeeder: Skipped in production.');
            return;
        }

        // Default dev login user (idempotent)
        User::updateOrCreate(
            ['email' => 'dimitri.mcdaniel@gmail.com'],
            [
                'name' => 'Dimitri McDaniel',
                'password' => Hash::make('True1231d!'),
            ]
        );

        // Optionally keep the existing test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
