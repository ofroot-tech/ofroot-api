<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureAdmin middleware
 *
 * Purpose: allow only privileged accounts to access admin-only endpoints.
 * Strategy: compare the authenticated user's email against an allowlist
 * provided via the ADMIN_EMAILS environment variable (comma-separated).
 *
 * This avoids schema changes while providing an explicit, auditable control.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $allowlist = collect(explode(',', (string) env('ADMIN_EMAILS', '')))
            ->map(fn ($e) => strtolower(trim($e)))
            ->filter();

        $email = strtolower((string) $user->email);
        if (!$allowlist->contains($email)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
