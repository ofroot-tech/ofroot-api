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

        // Bind 'tenant' parameter to Tenant model automatically
        Route::bind('tenant', function ($value) {
            return Tenant::where('domain', $value)->firstOrFail();
        });

        $this->routes(function () {
            Route::prefix('api')
                ->middleware(['api', 'tenant'])
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
