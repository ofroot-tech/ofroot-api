<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * =============================================================================
 * CreatesApplication
 * =============================================================================
 * Purpose
 * -------
 * PHPUnit and Pest need a way to bootstrap a full Laravel application instance
 * for each test run. This trait provides the canonical createApplication()
 * method that Laravel's TestCase expects.
 *
 * Why this exists
 * ---------------
 * Keeping this bootstrap in a trait decouples test scaffolding from
 * implementation details and allows multiple base test cases to share the same
 * application factory, should you introduce specialized bases later.
 * =============================================================================
 */
trait CreatesApplication
{
    /**
     * Bootstrap the application for testing.
     */
    public function createApplication()
    {
        // Load the framework and the app factory
        $app = require __DIR__ . '/../bootstrap/app.php';

        // Fully boot the console kernel, which in turn boots the service
        // providers, config, and database layer for our tests.
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
