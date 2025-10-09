<?php

/**
 * =============================================================================
 * LeadAssignmentEndpointsTest — acceptance for admin assignment routes
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadAssignmentEndpointsTest extends TestCase
{
    private function allowAdmin(): void
    {
        putenv('ADMIN_EMAILS=admin@example.com');
        $_ENV['ADMIN_EMAILS'] = 'admin@example.com';
        $_SERVER['ADMIN_EMAILS'] = 'admin@example.com';
    }

    #[Test]
    public function admin_can_assign_a_lead_to_tenant(): void
    {
        $this->allowAdmin();
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Sanctum::actingAs($admin);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => null]);

        $res = $this->postJson('/api/leads/assign', [
            'lead_id' => $lead->id,
            'tenant_id' => $tenant->id,
        ]);

        $res->assertOk()->assertJsonPath('data.tenant_id', $tenant->id);
    }

    #[Test]
    public function admin_can_unassign_a_lead(): void
    {
        $this->allowAdmin();
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Sanctum::actingAs($admin);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => $tenant->id]);

        $res = $this->postJson('/api/leads/unassign', [
            'lead_id' => $lead->id,
        ]);

        $res->assertOk()->assertJsonPath('data.tenant_id', null);
    }
}
