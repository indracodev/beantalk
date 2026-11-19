<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Routing\Middleware\ThrottleRequests;

abstract class TestCase extends BaseTestCase
{
    // SQLite in-memory: migrasi sekali per proses, tiap test dibungkus transaksi lalu di-rollback.
    use RefreshDatabase;

    /** Ambang profiling (ms): test lebih lambat dicetak sebagai peringatan. */
    protected float $slowThresholdMs = 200.0;

    private float $startedAt = 0.0;

    protected function setUp(): void
    {
        $this->startedAt = microtime(true);
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    protected function tearDown(): void
    {
        $ms = round((microtime(true) - $this->startedAt) * 1000, 2);
        if ($ms > $this->slowThresholdMs) {
            fwrite(STDERR, sprintf("\n  [SLOW TEST: %s ms] %s::%s()", $ms, static::class, $this->name()));
        }

        parent::tearDown();
    }
}
