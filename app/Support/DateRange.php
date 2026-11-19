<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/** Preset filter tanggal yang dipakai halaman Logs & Visitors (today | 7d | 30d). */
class DateRange
{
    public static function since(?string $preset): ?CarbonImmutable
    {
        return match ($preset) {
            'today' => CarbonImmutable::today(),
            '7d' => CarbonImmutable::today()->subDays(7),
            '30d' => CarbonImmutable::today()->subDays(30),
            default => null,
        };
    }
}
