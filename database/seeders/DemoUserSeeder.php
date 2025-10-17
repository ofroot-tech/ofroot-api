<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!App::environment('production')) {
            $this->command?->info('DemoUserSeeder: Skipped (not production).');
            return;
        }

        // Demo login for production
        User::updateOrCreate(
            ['email' => 'demo@gmail.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('demopassword'),
            ]
        );
    }
}
