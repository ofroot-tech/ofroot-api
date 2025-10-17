<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = env('ADMIN_API_SECRET');
        if (!$secret) {
            return response()->json(['message' => 'Admin not configured'], 503);
        }
        $provided = $request->header('X-Admin-Secret');
        if (!hash_equals($secret, (string) $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
