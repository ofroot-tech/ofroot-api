<?php

/**
 * =============================================================================
 * LeadAssignmentServiceTest — unit spec for assign/unassign semantics
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeadAssignmentService;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadAssignmentServiceTest extends TestCase
{
    #[Test]
    public function admin_can_assign_and_unassign_a_lead(): void
    {
        // Arrange: admin guard
        putenv('ADMIN_EMAILS=admin@example.com');
        $_ENV['ADMIN_EMAILS'] = 'admin@example.com';
        $_SERVER['ADMIN_EMAILS'] = 'admin@example.com';

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Sanctum::actingAs($admin);

        $tenant = Tenant::factory()->create();
        $lead = Lead::factory()->create(['tenant_id' => null]);

        // Act: assign via service
        $svc = new LeadAssignmentService();
        $assigned = $svc->assign($lead, $tenant);

        // Assert
        $this->assertSame($tenant->id, $assigned->tenant_id);

        // Act: unassign
        $unassigned = $svc->unassign($assigned);
        $this->assertNull($unassigned->tenant_id);
    }
}
