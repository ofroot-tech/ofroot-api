<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Display the currently authenticated user's tenant with related users.
     */
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for this user',
            ], 404);
        }

        $tenant->load('users');

        return response()->json([
            'message' => 'Tenant retrieved successfully',
            'data'    => $tenant,
        ], 200);
    }

    /**
     * Store a newly created tenant in storage.
     * Only accessible by authorized users.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255|unique:tenants,name',
            'domain'   => 'nullable|string|max:255|unique:tenants,domain',
            'plan'     => 'nullable|string|max:255',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tenant = Tenant::create($request->only(['name', 'domain', 'plan', 'settings']));

        return response()->json([
            'message' => 'Tenant created successfully',
            'data'    => $tenant,
        ], 201);
    }

    /**
     * Display the currently authenticated user's tenant.
     */
    public function show(Request $request)
    {
        $tenant = $request->user()->tenant;
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for this user',
            ], 404);
        }

        $tenant->load('users');

        return response()->json([
            'message' => 'Tenant retrieved successfully',
            'data'    => $tenant,
        ], 200);
    }

    /**
     * Update the currently authenticated user's tenant.
     */
    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for this user',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255|unique:tenants,name,' . $tenant->id,
            'domain'   => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'plan'     => 'nullable|string|max:255',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tenant->update($request->only(['name', 'domain', 'plan', 'settings']));

        return response()->json([
            'message' => 'Tenant updated successfully',
            'data'    => $tenant,
        ], 200);
    }

    /**
     * Remove the currently authenticated user's tenant.
     * Only accessible by authorized users.
     */
    public function destroy(Request $request)
    {
        $tenant = $request->user()->tenant;

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found for this user',
            ], 404);
        }

        $tenant->delete();

        return response()->json([
            'message' => 'Tenant deleted successfully',
        ], 200);
    }

    /**
     * Admin: update tenant by id (explicit resource targeting).
     */
    public function updateById(Request $request, int $id)
    {
        $tenant = Tenant::find($id);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255|unique:tenants,name,' . $tenant->id,
            'domain'   => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'plan'     => 'nullable|string|max:255',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tenant->update($request->only(['name', 'domain', 'plan', 'settings']));

        return response()->json([
            'message' => 'Tenant updated successfully',
            'data'    => $tenant,
        ], 200);
    }
}
