<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Lightweight metrics endpoint.
     * Returns zero when no data is present; never dummy values.
     */
    public function metrics(Request $request): JsonResponse
    {
        $tenantCount = Tenant::count();
        $userCount = User::count();

        return response()->json([
            'data' => [
                'tenants' => $tenantCount,
                'users' => $userCount,
                // TODO: integrate billing provider; for now return 0 MRR safely
                'mrr' => 0,
                'subscribers' => 0,
            ],
        ], 200);
    }

    /**
     * List users with minimal fields and roles.
     *
     * We include subscription metadata (plan, billing_cycle) to enable
     * richer admin tables and future filtering/export.
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::with(['roles:id,slug,name'])
            ->select('id', 'name', 'email', 'tenant_id', 'plan', 'billing_cycle', 'created_at', 'updated_at')
            ->orderBy('id', 'desc')
            ->paginate(min(max((int) $request->integer('per_page', 25), 1), 100));

        // Shape each item to include roles and a computed top_role
        $users->setCollection(
            $users->getCollection()->transform(function (User $u) {
                $roles = $u->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug])->values();
                $top = $roles->first()['slug'] ?? ($u->is_admin ? 'admin' : 'member');
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'tenant_id' => $u->tenant_id,
                    'roles' => $roles,
                    'top_role' => $top,
                    // Expose subscription metadata for admin UI
                    'plan' => $u->plan,
                    'billing_cycle' => $u->billing_cycle,
                    'created_at' => $u->created_at,
                    'updated_at' => $u->updated_at,
                ];
            })
        );

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage(),
            ],
        ], 200);
    }

    /**
     * List tenants with minimal fields.
     */
    public function tenants(Request $request): JsonResponse
    {
        $tenants = Tenant::select('id', 'name', 'domain', 'plan', 'created_at', 'updated_at')->orderBy('id', 'desc')->paginate(25);
        return response()->json(['data' => $tenants->items(), 'meta' => ['total' => $tenants->total()]], 200);
    }

    /**
     * Placeholder subscribers endpoint returning empty array until billing integration exists.
     */
    public function subscribers(Request $request): JsonResponse
    {
        return response()->json(['data' => [], 'meta' => ['total' => 0]], 200);
    }
}
