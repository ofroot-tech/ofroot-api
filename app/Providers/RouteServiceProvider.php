<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Remove global tenant binding and middleware for public APIs/tests
        // Keep bindings minimal; add route-level middleware when needed.

        $this->routes(function () {
            Route::prefix('api')
                ->middleware(['api'])
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        // Optional API throttling
    }
}
