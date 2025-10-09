<?php

// -----------------------------------------------------------------------------
//  api.php - API Route Definitions for Laravel Application
//  -----------------------------------------------------------------------------
//  This file defines the API endpoints for the application. It leverages
//  Laravel's routing system to organize and secure API routes, particularly
//  those related to tenant management. All routes are grouped and documented
//  for clarity and maintainability, following the style of literate programming
//  as advocated by Donald Knuth.
// -----------------------------------------------------------------------------

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\EnsureAdmin;

// -----------------------------------------------------------------------------
//  Section 1: Default Authenticated User Route
//  -----------------------------------------------------------------------------
//  This route returns the authenticated user's information. It is protected
//  by the 'auth:sanctum' middleware, ensuring only authenticated requests
//  can access it.
// -----------------------------------------------------------------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// -----------------------------------------------------------------------------
//  Section 2: Tenant-Scoped API Routes
//  -----------------------------------------------------------------------------
//  All routes within this group require authentication via Sanctum. The
//  tenant-related endpoints are automatically scoped to the authenticated
//  user's tenant, eliminating the need to specify a tenant ID in the URL.
// -----------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    // Tenant CRUD operations (Create, Read, Update, Delete)
    Route::apiResource('tenants', TenantController::class);

    // Future tenant-scoped resources:
    // Route::apiResource('leads', LeadController::class);
    // Route::apiResource('projects', ProjectController::class);
});

// Public endpoint for inbound lead submissions
Route::post('/leads', [LeadController::class, 'store']);

// Admin-only lead assignment endpoints
Route::middleware(['auth:sanctum', EnsureAdmin::class])->group(function () {
    Route::post('/leads/assign', [LeadController::class, 'assign']);
    Route::post('/leads/unassign', [LeadController::class, 'unassign']);
});

// -----------------------------------------------------------------------------
//  Section 3: Health Check Endpoint
//  -----------------------------------------------------------------------------
//  This route provides a simple health check for the API, returning a JSON
//  response indicating the service status. It is publicly accessible.
// -----------------------------------------------------------------------------
Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

// -----------------------------------------------------------------------------
//  End of api.php
// -----------------------------------------------------------------------------

/*
===============================================================================
Instructions: Activating This Route File in Your Laravel Application
===============================================================================

1. File Location:
   - Ensure this file is saved as 'routes/api.php' in your Laravel project.

2. RouteServiceProvider:
   - By default, Laravel automatically loads 'routes/api.php' via the
     RouteServiceProvider. No additional configuration is required.

3. Middleware:
   - The 'auth:sanctum' middleware is used for authentication. Ensure you have
     Laravel Sanctum installed and configured:
       a. Install Sanctum: composer require laravel/sanctum
       b. Publish Sanctum config: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
       c. Run migrations: php artisan migrate

4. Controllers:
   - Make sure the referenced controllers (e.g., TenantController) exist in
     'app/Http/Controllers/Api/'.

5. Testing:
   - Use tools like Postman or curl to test the endpoints, e.g.:
       GET /api/health
       GET /api/user (requires authentication)
       CRUD on /api/tenants (requires authentication)

6. Start the Application:
   - Run your Laravel application: php artisan serve

===============================================================================
*/

// Public auth endpoints
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Authenticated auth endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Admin-only Tenants API: create/list/update
Route::middleware(['auth:sanctum', EnsureAdmin::class])->group(function () {
    // POST /api/tenants (create)
    Route::post('/tenants', [TenantController::class, 'store']);

    // GET /api/tenants (list)
    Route::get('/tenants', [TenantController::class, 'index']);

    // PUT /api/tenants/{id} (update)
    Route::put('/tenants/{id}', [TenantController::class, 'updateById']);
});
