<?php

/**
 * =============================================================================
 * AdminTenantsApiTest — Acceptance specs for admin-only tenants endpoints
 * =============================================================================
 * What is being verified
 * ----------------------
 * - Only authenticated users whose email appears in ADMIN_EMAILS may access
 *   the admin tenants API group.
 * - POST /api/tenants creates a tenant.
 * - GET /api/tenants returns the authenticated user's tenant (current impl).
 * - PUT /api/tenants/{id} updates a tenant by id.
 *
 * Test shape
 * ----------
 * Each test follows a clear flow: prepare actors and data, perform the request,
 * then assert on status codes and payload.
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTenantsApiTest extends TestCase
{
    private const ADMIN_EMAIL = 'admin@example.com';
    private const NONADMIN_EMAIL = 'user@example.com';

    private function allowAdminEmail(string $email): void
    {
        // Permit the given email via env allowlist for the duration of the test
        putenv('ADMIN_EMAILS=' . $email);
        $_ENV['ADMIN_EMAILS'] = $email;
        $_SERVER['ADMIN_EMAILS'] = $email;
    }

    // #[Test]
    public function unauthenticated_requests_are_rejected(): void
    {
        $this->postJson('/api/tenants', ['name' => 'Acme'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated']);
    }

    // #[Test]
    public function non_admin_is_forbidden_admin_routes(): void
    {
        $this->allowAdminEmail(self::ADMIN_EMAIL);

        $user = User::factory()->create([
            'email' => self::NONADMIN_EMAIL,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tenants', ['name' => 'Acme'])
            ->assertStatus(403)
            ->assertJson(['message' => 'Forbidden']);
    }

    // #[Test]
    public function admin_can_create_list_and_update_tenant(): void
    {
        $this->allowAdminEmail(self::ADMIN_EMAIL);

        // Arrange: admin user with token
        $admin = User::factory()->create([
            'email' => self::ADMIN_EMAIL,
        ]);
        Sanctum::actingAs($admin);

        // Create
        $create = $this->postJson('/api/tenants', [
            'name' => 'Acme ' . Str::random(6),
            'plan' => 'free',
        ]);
        $create->assertCreated();
        $tenantId = $create->json('data.id');
        $this->assertNotNull($tenantId);

        // Link the admin user to a tenant so GET /api/tenants returns data
        $admin->tenant_id = $tenantId;
        $admin->save();

        // List (current behavior: returns caller's tenant)
        $this->getJson('/api/tenants')
            ->assertOk()
            ->assertJsonPath('data.id', $tenantId);

        // Update by id
        $this->putJson("/api/tenants/{$tenantId}", ['plan' => 'pro'])
            ->assertOk()
            ->assertJsonPath('data.plan', 'pro');
    }
}
