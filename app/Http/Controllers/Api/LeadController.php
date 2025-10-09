<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * LeadsController (Store Endpoint)
 *
 * --------------------------------
 * Intent
 *  - Accept lead submissions from public clients (landing pages, forms).
 *  - Validate inputs rigorously; persist in a single, explicit step.
 *  - Remain tenant-agnostic: allow optional tenant_id assignment when known.
 *
 * Design
 *  - Public endpoint (no auth) to ease inbound integrations.
 *  - Validation: name (optional), email (optional), phone (required),
 *    service (required), zip (required), optional source & tenant_id.
 *  - On success, returns 201 with the created lead payload.
 *  - On failure, returns 422 with validation errors.
 */
class LeadController extends Controller
{
    /**
     * Store a newly submitted lead.
     */
    public function store(Request $request): JsonResponse
    {
        // 1) Validate inputs. Keep rules explicit and minimal.
        $validator = Validator::make($request->all(), [
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'name'      => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'string', 'email', 'max:255'],
            'phone'     => ['required', 'string', 'max:50'],
            'service'   => ['required', 'string', 'max:100'],
            'zip'       => ['required', 'string', 'max:10'],
            'source'    => ['nullable', 'string', 'max:255'],
            'status'    => ['nullable', 'string', 'max:50'], // defaults to 'new' at DB layer
            'meta'      => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2) Persist the lead. Rely on $fillable and DB defaults.
        $payload = $validator->validated();

        // Ensure a deterministic default for status across environments.
        if (!array_key_exists('status', $payload) || $payload['status'] === null) {
            $payload['status'] = 'new';
        }

        $lead = Lead::create($payload);

        // 3) Return a precise, friendly response.
        return response()->json([
            'message' => 'Lead created successfully',
            'data'    => $lead,
        ], 201);
    }
}
