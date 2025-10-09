<?php

/**
 * =============================================================================
 * LeadStoreEndpointTest — A Narrative Spec for POST /api/leads
 * =============================================================================
 * Why this file exists
 * --------------------
 * An inbound lead is the first handshake between a user and our system. This
 * test suite serves as executable documentation for that handshake: what we
 * accept, what we refuse, and how we persist.
 *
 * What we verify (contract)
 * -------------------------
 * 1) Happy path: with minimal required fields (zip, service, phone), we accept
 *    the payload and persist a Lead with status=new by default.
 * 2) Input hygiene: malformed or missing fields are rejected with 422 and
 *    useful error structure.
 * 3) Tenancy: when a real tenant_id is provided, we attach the Lead to that
 *    tenant.
 *
 * Style note
 * ----------
 * each test is divided into Arrange / Act / Assert with brief, purposeful
 * commentary that explains not just the “what”, but the “why”.
 * =============================================================================
 */

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Tenant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadStoreEndpointTest extends TestCase
{
    /**
     * The canonical ingress for lead submissions.
     */
    private const ENDPOINT = '/api/leads';

    // ---------------------------------------------------------------------
    // 1) Happy path — minimal, valid payload is accepted and persisted
    // ---------------------------------------------------------------------
    #[Test]
    public function it_creates_a_lead_with_minimal_required_fields(): void
    {
        // Arrange: The smallest useful request we can honor.
        $payload = [
            'zip' => '90210',           // where the service is needed
            'service' => 'plumbing',    // what help is requested
            'phone' => '555-1111',      // how we call back
        ];

        // Act: Submit the request as JSON.
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert: We created a Lead, defaulting status to "new".
        $response->assertCreated()
            ->assertJsonPath('data.zip', '90210')
            ->assertJsonPath('data.service', 'plumbing')
            ->assertJsonPath('data.phone', '555-1111')
            ->assertJsonPath('data.status', 'new'); // DB default

        // Persistence check guards against accidental controller changes.
        $this->assertDatabaseHas('leads', [
            'zip' => '90210',
            'service' => 'plumbing',
            'phone' => '555-1111',
            'status' => 'new',
        ]);
    }

    // ---------------------------------------------------------------------
    // 2) Input hygiene — errors are specific and shape is predictable
    // ---------------------------------------------------------------------
    #[Test]
    public function it_validates_and_rejects_bad_input(): void
    {
        // Arrange: Violations of length, presence, and format.
        $invalid = [
            'zip' => str_repeat('9', 11), // too long (max:10)
            'service' => '',               // required string
            'phone' => '',                 // required string
            'email' => 'not-an-email',     // must be RFC-compliant
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $invalid);

        // Assert: HTTP 422 with field-wise error map.
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['phone', 'service', 'email', 'zip'],
            ]);
    }

    // ---------------------------------------------------------------------
    // 3) Tenancy — optional linkage to a real tenant
    // ---------------------------------------------------------------------
    #[Test]
    public function it_can_attach_to_a_real_tenant(): void
    {
        // Arrange: A legitimate tenant to own the incoming lead.
        $tenant = Tenant::factory()->create();
        $payload = [
            'tenant_id' => $tenant->id,
            'zip' => '10001',
            'service' => 'hvac',
            'phone' => '555-2222',
            'email' => 'lead@example.com',
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert: We created and linked to that tenant.
        $response->assertCreated();
        $lead = Lead::first();
        $this->assertNotNull($lead, 'Lead should be persisted.');
        $this->assertSame($tenant->id, $lead->tenant_id, 'Lead should reference the provided tenant.');
    }
}
