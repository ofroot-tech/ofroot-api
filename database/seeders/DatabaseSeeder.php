<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // System roles
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'is_system' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'is_system' => false],
            ['name' => 'Member', 'slug' => 'member', 'is_system' => false],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // Remove non-idempotent factory user creation to avoid unique email conflicts.
        // Always seed default users (non-prod)
        if (!App::environment('production')) {
            $this->call(UserSeeder::class);

            // Assign admin role to allowlisted emails if users exist
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $emails = collect(explode(',', (string) env('ADMIN_EMAILS', '')))
                    ->map(fn($e) => strtolower(trim($e)))
                    ->filter();
                if ($emails->isNotEmpty()) {
                    $users = User::whereIn('email', $emails->all())->get();
                    foreach ($users as $u) {
                        $u->roles()->syncWithoutDetaching([$adminRole->id]);
                    }
                }
            }
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

        // Import filesystem docs into DB (safe, idempotent)
        $this->call(DocsSeeder::class);

        $this->call(TenantAndLeadSeeder::class);
    }
}
