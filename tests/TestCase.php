<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => database_path('testing.sqlite'),
        ]);
        \Illuminate\Support\Facades\DB::purge();

        if (!static::$migrated) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
            static::$migrated = true;
        }

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }
}
