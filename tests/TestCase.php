<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    // Pulitzer/Knuth note: RefreshDatabase ensures each test runs with a fresh schema
    // against the in-memory SQLite defined in phpunit.xml. This makes tests
    // deterministic and removes cross-test coupling.
}
