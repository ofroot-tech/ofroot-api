<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Role; // Assign default role to new users

class AuthController extends Controller
{
    /**
     * Determine whether public registration is currently open.
     * Policy: allow until the first user exists, then close.
     * Optional override: set ALLOW_PUBLIC_REGISTRATION=true to force-open.
     */
    private function registrationOpen(): bool
    {
        if (env('ALLOW_PUBLIC_REGISTRATION') === true || env('ALLOW_PUBLIC_REGISTRATION') === 'true') {
            return true;
        }
        return User::count() === 0;
    }

    /**
     * Lightweight status endpoint for UI gating.
     */
    public function registrationStatus(): JsonResponse
    {
        return response()->json(['open' => $this->registrationOpen()], 200);
    }

    /**
     * Issue a Sanctum personal access token for valid credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ])->validated();

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $deviceName = $data['device_name'] ?? ($request->userAgent() ?: 'web');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $token,
            'data' => $user,
        ], 200);
    }

    /**
     * Register a new user and immediately issue a token.
     *
     * Narrative: In a SaaS context, fresh accounts should receive
     * a safe default role. We attach the 'member' role if it exists,
     * avoiding privilege escalation while enabling basic access.
     */
    public function register(Request $request): JsonResponse
    {
        // Guard: disable registration once an account exists (unless explicitly allowed)
        if (!$this->registrationOpen()) {
            return response()->json([
                'message' => 'Registration is disabled',
            ], 403);
        }

        $data = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            // Optional subscription metadata
            'plan' => ['nullable', 'in:free,pro,business'],
            'billingCycle' => ['nullable', 'in:monthly,yearly'],
            'coupon' => ['nullable', 'string', 'max:50'],
        ])->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via User::$casts
            'plan' => $data['plan'] ?? 'free',
            'billing_cycle' => $data['billingCycle'] ?? 'monthly',
        ]);

        // Assign default role: member (idempotent)
        $member = Role::where('slug', 'member')->first();
        if ($member) {
            $user->roles()->syncWithoutDetaching([$member->id]);
        }

        $token = $user->createToken($request->userAgent() ?: 'web')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully',
            'token' => $token,
            'data' => $user,
        ], 201);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ], 200);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
