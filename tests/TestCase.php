<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function createApplication(): \Illuminate\Contracts\Foundation\Application
    {
        // When installed in vendor/ or packages/ under a Laravel app, go up 4 directory levels
        // from tests/ to reach the Laravel application root.
        $laravelRoot = dirname(__DIR__, 4);
        if (!file_exists($laravelRoot . '/bootstrap/app.php')) {
            // Fallback: use the current working directory (e.g., when running from the host app root)
            $laravelRoot = getcwd();
        }
        $app = require $laravelRoot . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}