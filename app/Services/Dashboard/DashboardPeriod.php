<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Throwable;

/** Rentang laporan dashboard (preset today|7d|30d|quarter atau custom start/end) + periode pembanding. */
final class DashboardPeriod
{
    private const PRESETS = ['today' => 1, '7d' => 7, '30d' => 30, 'quarter' => 90];

    public function __construct(
        public readonly string $period,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly CarbonImmutable $prevStart,
        public readonly CarbonImmutable $prevEnd,
        public readonly int $days,
        public readonly bool $isCustom,
    ) {}

    public static function fromRequest(Request $request): self
    {
        if ($request->filled(['start_date', 'end_date'])) {
            try {
                $a = CarbonImmutable::parse($request->input('start_date'))->startOfDay();
                $b = CarbonImmutable::parse($request->input('end_date'))->startOfDay();
                [$start, $endDay] = $a <= $b ? [$a, $b] : [$b, $a];
                // Carbon 3: diffInDays mengembalikan float, jadi dibulatkan eksplisit.
                $days = (int) round($start->diffInDays($endDay)) + 1;

                return new self('custom', $start, $endDay->endOfDay(), $start->subDays($days), $start->subDay()->endOfDay(), $days, true);
            } catch (Throwable) {
                // tanggal tak valid → jatuh ke preset
            }
        }

        $period = array_key_exists($request->input('period'), self::PRESETS) ? $request->input('period') : '7d';
        $days = self::PRESETS[$period];
        $today = CarbonImmutable::today();
        $start = $today->subDays($days - 1);

        return new self($period, $start, $today->endOfDay(), $start->subDays($days), $start->subDay()->endOfDay(), $days, false);
    }

    public function label(CarbonImmutable $date): string
    {
        return $date->format('d M Y');
    }
}
