<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $migrated = false;

    /**
     * Waktu mulai eksekusi test (dalam mikrodetik)
     *
     * @var float
     */
    protected $testStartTime = 0.0;

    /**
     * Batas ambang batas waktu uji (ms).
     * Jika sebuah unit test melebihi batas ini, peringatan profiling akan dicetak.
     *
     * @var float
     */
    protected $slowThresholdMs = 200.0;

    protected function setUp(): void
    {
        $this->testStartTime = microtime(true);

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => database_path('testing.sqlite'),
            'hashing.bcrypt.rounds' => 4,
            'cache.default' => 'array',
        ]);
        DB::purge();

        if (!static::$migrated) {
            Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
            static::$migrated = true;
        }

        // Optimasi Performa SQLite untuk Lingkungan Pengujian:
        // synchronous=OFF & journal_mode=MEMORY meminimalkan bottleneck disk I/O di Windows OS.
        try {
            DB::connection('sqlite')->getPdo()->exec(
                'PRAGMA synchronous = OFF; PRAGMA journal_mode = MEMORY; PRAGMA temp_store = MEMORY; PRAGMA cache_size = -64000;'
            );
        } catch (\Throwable $e) {
            // Ignore if in-memory driver doesn't support specific pragmas
        }

        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }

    protected function tearDown(): void
    {
        $durationMs = round((microtime(true) - $this->testStartTime) * 1000, 2);

        // Jika eksekusi unit test melampaui batas threshold, log peringatan untuk analisis optimalisasi
        if ($this->testStartTime > 0 && $durationMs > $this->slowThresholdMs) {
            $testName = method_exists($this, 'name') ? $this->name() : (method_exists($this, 'getName') ? $this->getName() : 'test');
            fwrite(STDERR, sprintf("\n  ⏱️ [SLOW TEST: %s ms] %s::%s()", $durationMs, static::class, $testName));
        }

        parent::tearDown();
    }
}
