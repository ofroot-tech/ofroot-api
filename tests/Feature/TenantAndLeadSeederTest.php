<?php

/**
 * =============================================================================
 * TenantAndLeadSeederTest (Pulitzer/Knuth-style commentary)
 * =============================================================================
 * Intention
 * ---------
 * We test that the safety rails around seeding behave exactly as promised:
 *  1) In production, the seeder must refuse to run.
 *  2) In non-production, the seeder runs only when the explicit flag
 *     APP_SEED_ALLOWED=true is provided.
 *  3) When it does run, it creates Tenants and Leads with the expected linkage.
 *
 * Rationale
 * ---------
 * Seeders are a powerful bootstrapping tool. They are also a foot-gun in
 * production. Our guard contract is simple and explicit, so these tests serve
 * as executable documentation. If the contract changes, these tests should be
 * updated accordingly.
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use Database\Seeders\TenantAndLeadSeeder;
use Tests\TestCase;

class TenantAndLeadSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // RefreshDatabase ensures a clean schema and data for each test.
    }

    /** @test */
    public function seeder_skips_in_production(): void
    {
        // Arrange: emulate production and allow flag (the guard should still skip)
        putenv('APP_ENV=production');
        putenv('APP_SEED_ALLOWED=true');

        // Act: run the seeder
        $this->seed(TenantAndLeadSeeder::class);

        // Assert: nothing created
        $this->assertSame(0, Tenant::count(), 'No tenants should be created in production.');
        $this->assertSame(0, Lead::count(), 'No leads should be created in production.');

        // Cleanup
        putenv('APP_ENV');
        putenv('APP_SEED_ALLOWED');
    }

    /** @test */
    public function seeder_skips_when_flag_is_not_true(): void
    {
        // Arrange: non-production with flag disabled
        putenv('APP_ENV=testing');
        putenv('APP_SEED_ALLOWED=false');

        // Act
        $this->seed(TenantAndLeadSeeder::class);

        // Assert
        $this->assertSame(0, Tenant::count(), 'No tenants should be created when APP_SEED_ALLOWED is not true.');
        $this->assertSame(0, Lead::count(), 'No leads should be created when APP_SEED_ALLOWED is not true.');

        // Cleanup
        putenv('APP_ENV');
        putenv('APP_SEED_ALLOWED');
    }

    /** @test */
    public function seeder_runs_with_flag_in_non_production(): void
    {
        // Arrange: non-production and allow flag
        putenv('APP_ENV=testing');
        putenv('APP_SEED_ALLOWED=true');
        putenv('SEED_TENANTS=2');
        putenv('SEED_LEADS_PER_TENANT=3');

        // Act
        $this->seed(TenantAndLeadSeeder::class);

        // Assert: counts match N x M
        $this->assertSame(2, Tenant::count(), 'Expected 2 tenants to be created.');
        $this->assertSame(6, Lead::count(), 'Expected 3 leads per tenant x 2 tenants = 6 leads.');

        // Verify linkage: every lead has a tenant_id that exists
        $this->assertTrue(
            Lead::query()->whereNull('tenant_id')->doesntExist(),
            'All leads created by seeder should be attached to a tenant.'
        );

        $this->assertTrue(
            Lead::query()->pluck('tenant_id')->unique()->every(fn ($id) => Tenant::query()->whereKey($id)->exists()),
            'Every lead tenant_id should point to a real tenant.'
        );

        // Cleanup
        putenv('APP_ENV');
        putenv('APP_SEED_ALLOWED');
        putenv('SEED_TENANTS');
        putenv('SEED_LEADS_PER_TENANT');
    }
}
