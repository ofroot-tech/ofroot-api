<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * LeadAssignmentService
 *
 * Purpose: provide a narrow, auditable seam for attaching and detaching leads
 * to tenants. Encapsulates the write path and its invariants.
 *
 * Contract:
 * - assign($lead, $tenant): attaches lead to tenant; returns updated lead.
 * - unassign($lead): detaches lead (sets tenant_id = null); returns updated lead.
 */
class LeadAssignmentService
{
    /** Attach the given lead to the given tenant. */
    public function assign(Lead $lead, Tenant $tenant): Lead
    {
        return DB::transaction(function () use ($lead, $tenant) {
            $lead->tenant_id = $tenant->id;
            $lead->save();
            return $lead->refresh();
        });
    }

    /** Detach the given lead from any tenant. */
    public function unassign(Lead $lead): Lead
    {
        return DB::transaction(function () use ($lead) {
            $lead->tenant_id = null;
            $lead->save();
            return $lead->refresh();
        });
    }
}
