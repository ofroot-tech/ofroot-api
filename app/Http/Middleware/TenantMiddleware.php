<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // e.g., demo.ofroot.app

        // Attempt to find the tenant by domain
        $tenant = Tenant::where('domain', $host)->first();

        if (!$tenant) {
            // Optionally, fallback to default tenant
            abort(404, 'Tenant not found');
        }

        // Share the tenant globally for the request
        app()->instance(Tenant::class, $tenant);

        // Or store in request for easy access: $request->tenant
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
