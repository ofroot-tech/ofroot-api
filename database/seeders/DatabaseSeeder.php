<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Example existing seed: create a test user via factory (safe in dev)
        if (!App::environment('production')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Conditionally invoke dev data seeder
        if (App::environment('production')) {
            $this->command?->info('DatabaseSeeder: Skipping TenantAndLeadSeeder in production.');
            return;
        }

        if (env('APP_SEED_ALLOWED') !== 'true') {
            $this->command?->info('DatabaseSeeder: Skipping TenantAndLeadSeeder (APP_SEED_ALLOWED!=true).');
            return;
        }

        $this->call(TenantAndLeadSeeder::class);
    }
}
