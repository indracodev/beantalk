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

        if (config('database.default') === 'sqlite' && !static::$migrated) {
            \Illuminate\Support\Facades\Artisan::call('migrate');
            static::$migrated = true;
        }

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }
}
