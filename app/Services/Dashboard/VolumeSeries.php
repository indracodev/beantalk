<?php

namespace App\Services\Dashboard;

use App\Models\Conversation;
use App\Support\Sql;
use Carbon\CarbonImmutable;

/**
 * Deret waktu inbound (percakapan baru berpesan) vs resolved (ditutup) per tenant.
 * Satu query GROUP BY jam per metrik untuk seluruh rentang, lalu dijumlah per bucket di PHP
 * (versi lama: 2 query per titik chart).
 */
final class VolumeSeries
{
    /** @var array<string, int> kunci "Y-m-d H" */
    private array $inbound;

    /** @var array<string, int> */
    private array $resolved;

    public function __construct(int $tenantId, CarbonImmutable $from, CarbonImmutable $to)
    {
        $base = fn () => Conversation::where('tenant_id', $tenantId);
        $this->inbound = $this->hourly($base()->whereHas('messages'), 'created_at', $from, $to);
        $this->resolved = $this->hourly($base()->where('status', 'closed'), 'updated_at', $from, $to);
    }

    /** @return array{inbound: int, resolved: int} */
    public function between(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return ['inbound' => $this->sum($this->inbound, $start, $end), 'resolved' => $this->sum($this->resolved, $start, $end)];
    }

    private function hourly($query, string $column, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $date = Sql::date($column);
        $hour = Sql::hour($column);

        return $query->whereBetween($column, [$from, $to])
            ->selectRaw("{$date} AS d, {$hour} AS h, COUNT(*) AS c")
            ->groupBy('d', 'h')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($r) => [sprintf('%s %02d', $r->d, $r->h) => (int) $r->c])
            ->all();
    }

    private function sum(array $hourly, CarbonImmutable $start, CarbonImmutable $end): int
    {
        $from = $start->format('Y-m-d H');
        $to = $end->format('Y-m-d H');
        $total = 0;
        foreach ($hourly as $key => $count) {
            if ($key >= $from && $key <= $to) {
                $total += $count;
            }
        }

        return $total;
    }
}
