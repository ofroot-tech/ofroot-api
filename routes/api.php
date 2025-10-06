<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController;
// Future controllers:
// use App\Http\Controllers\Api\LeadController;
// use App\Http\Controllers\Api\ProjectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and assigned to the "api"
| middleware group. Make something great!
|
*/

// Default route for authenticated users
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| All tenant-related API endpoints are now automatically scoped to the
| authenticated user's tenant. No tenant ID is needed in the URL.
|
*/
Route::middleware('auth:sanctum')->group(function () {
    // Tenant CRUD
    Route::apiResource('tenants', TenantController::class);

    // Future tenant-scoped resources
    // Route::apiResource('leads', LeadController::class);
    // Route::apiResource('projects', ProjectController::class);
});

Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

