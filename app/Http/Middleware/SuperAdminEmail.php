<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SuperAdminEmail — a small gate keyed by ADMIN_EMAILS.
 *
 * Purpose
 *  Ensure only explicitly allowed emails can access privileged admin routes.
 *  Works alongside auth:sanctum so $request->user() is resolved.
 */
class SuperAdminEmail
{
    /**
     * Handle an incoming request.
     *
     * - Reads ADMIN_EMAILS (comma-separated) from env/config
     * - Compares against the authenticated user email (case-insensitive)
     * - Returns 403 when not allowed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $email = is_string($user?->email ?? null) ? strtolower(trim($user->email)) : null;

        $raw = env('ADMIN_EMAILS', '');
        $allow = collect(explode(',', $raw))
            ->map(fn ($s) => strtolower(trim($s)))
            ->filter()
            ->values();

        if (!$email || !$allow->contains($email)) {
            return response()->json([
                'ok' => false,
                'error' => [ 'message' => 'Forbidden' ],
            ], 403);
        }

        return $next($request);
    }
}
