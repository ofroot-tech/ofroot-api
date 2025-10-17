<?php

/**
 * =============================================================================
 * TenantAndLeadSeederTest
 * =============================================================================
 * Intention
 * ---------
 * We test that the safety rails around seeding behave exactly as promised:
 *  1) In production, the seeder must refuse to run.
 *  2) In non-production, the seeder runs only when the explicit flag
 *     APP_SEED_ALLOWED=true is provided.
 *  3) When it does run, it creates Tenants and Leads with the expected linkage.
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use Database\Seeders\TenantAndLeadSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantAndLeadSeederTest extends TestCase
{
    #[Test]
    public function seeder_skips_in_production(): void
    {
        // Arrange: Override app environment to production
        // We must set this BEFORE the application resolves the environment
        $this->app['env'] = 'production';
        config()->set('app.env', 'production');
        
        // Also set environment variables for APP_SEED_ALLOWED
        putenv('APP_SEED_ALLOWED=true');
        $_ENV['APP_SEED_ALLOWED'] = 'true';
        $_SERVER['APP_SEED_ALLOWED'] = 'true';

        // Act: run the seeder
        $this->seed(TenantAndLeadSeeder::class);

        // Assert: nothing created
        $this->assertSame(0, Tenant::count(), 'No tenants should be created in production.');
        $this->assertSame(0, Lead::count(), 'No leads should be created in production.');

        // Cleanup
        $this->app['env'] = 'testing';
        config()->set('app.env', 'testing');
        putenv('APP_SEED_ALLOWED');
        unset($_ENV['APP_SEED_ALLOWED'], $_SERVER['APP_SEED_ALLOWED']);
    }

    #[Test]
    public function seeder_skips_when_flag_is_not_true(): void
    {
        // Arrange: non-production with flag disabled
        config()->set('app.env', 'testing');
        putenv('APP_SEED_ALLOWED=false');
        $_ENV['APP_SEED_ALLOWED'] = 'false';
        $_SERVER['APP_SEED_ALLOWED'] = 'false';

        // Act
        $this->seed(TenantAndLeadSeeder::class);

        // Assert
        $this->assertSame(0, Tenant::count(), 'No tenants should be created when APP_SEED_ALLOWED is not true.');
        $this->assertSame(0, Lead::count(), 'No leads should be created when APP_SEED_ALLOWED is not true.');

        // Cleanup
        putenv('APP_SEED_ALLOWED');
        unset($_ENV['APP_SEED_ALLOWED'], $_SERVER['APP_SEED_ALLOWED']);
    }

    #[Test]
    public function seeder_runs_with_flag_in_non_production(): void
    {
        // Arrange: non-production and allow flag
        putenv('APP_ENV=testing');
        putenv('APP_SEED_ALLOWED=true');
        putenv('SEED_TENANTS=2');
        putenv('SEED_LEADS_PER_TENANT=3');

        // Also set superglobals so env() picks up values in all scenarios
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_SEED_ALLOWED'] = 'true';
        $_ENV['SEED_TENANTS'] = '2';
        $_ENV['SEED_LEADS_PER_TENANT'] = '3';
        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['APP_SEED_ALLOWED'] = 'true';
        $_SERVER['SEED_TENANTS'] = '2';
        $_SERVER['SEED_LEADS_PER_TENANT'] = '3';

        // Ensure config reflects env in-process
        config()->set('app.env', 'testing');

        // Act
        $this->seed(\Database\Seeders\TenantAndLeadSeeder::class);

        // Assert: counts match N x M
        $this->assertSame(2, \App\Models\Tenant::count(), 'Expected 2 tenants to be created.');
        $this->assertSame(6, \App\Models\Lead::count(), 'Expected 3 leads per tenant x 2 tenants = 6 leads.');

        // Verify linkage: every lead has a tenant_id that exists
        $this->assertTrue(
            \App\Models\Lead::query()->whereNull('tenant_id')->doesntExist(),
            'All leads created by seeder should be attached to a tenant.'
        );

        $this->assertTrue(
            \App\Models\Lead::query()->pluck('tenant_id')->unique()->every(fn ($id) => Tenant::query()->whereKey($id)->exists()),
            'Every lead tenant_id should point to a real tenant.'
        );

        // Cleanup
        putenv('APP_ENV');
        putenv('APP_SEED_ALLOWED');
        putenv('SEED_TENANTS');
        putenv('SEED_LEADS_PER_TENANT');
        unset($_ENV['APP_ENV'], $_ENV['APP_SEED_ALLOWED'], $_ENV['SEED_TENANTS'], $_ENV['SEED_LEADS_PER_TENANT']);
        unset($_SERVER['APP_ENV'], $_SERVER['APP_SEED_ALLOWED'], $_SERVER['SEED_TENANTS'], $_SERVER['SEED_LEADS_PER_TENANT']);
    }
}
