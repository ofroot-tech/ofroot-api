<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App; // Facade used for environment checks
use App\Models\Tenant;
use App\Models\Lead;

class TenantAndLeadSeeder extends Seeder
{
    public function run(): void
    {
        // The first duty of this seeder is to refuse execution in contexts where
        // sample data would be dangerous. Two explicit gates follow.

        // Gate 1: absolute stop in production
        if (App::environment('production')) {
            $this->command?->info('TenantAndLeadSeeder: Skipped (production environment).');
            return;
        }

        // Gate 2: require an explicit, human-set flag (robust boolean parsing)
        $allowed = filter_var(env('APP_SEED_ALLOWED', false), FILTER_VALIDATE_BOOL);
        if (!$allowed) {
            $this->command?->info('TenantAndLeadSeeder: Skipped (APP_SEED_ALLOWED is not true).');
            return;
        }

        // Adjustable volume knobs for local/dev and CI
        $tenantsToCreate = (int) (env('SEED_TENANTS', 3));
        $leadsPerTenant   = (int) (env('SEED_LEADS_PER_TENANT', 10));

        $this->command?->info("Seeding {$tenantsToCreate} tenants x {$leadsPerTenant} leads each...");

        Tenant::factory()
            ->count($tenantsToCreate)
            ->create()
            ->each(function (Tenant $tenant) use ($leadsPerTenant) {
                Lead::factory()
                    ->count($leadsPerTenant)
                    ->make(['tenant_id' => $tenant->id])
                    ->each(function (Lead $lead) use ($tenant) {
                        // Explicitly attach each lead to its tenant.
                        $lead->tenant_id = $tenant->id;
                        $lead->save();
                    });
            });

        $this->command?->info('TenantAndLeadSeeder: Completed successfully.');
    }
}
