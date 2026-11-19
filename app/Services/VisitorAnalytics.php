<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\Visitor;
use App\Support\Sql;
use Illuminate\Support\Collection;

/**
 * Statistik halaman Pengunjung via agregasi SQL (GROUP BY), bukan memuat seluruh
 * baris visitor ke memori seperti versi lama.
 */
class VisitorAnalytics
{
    public const CHART_RANGES = ['7d' => 7, '14d' => 14, '30d' => 30];

    /** @param  Collection<int, Project>  $projects */
    public function summary(Collection $projects): object
    {
        return Visitor::whereIn('project_id', $projects->modelKeys())
            ->selectRaw('COUNT(*) AS total_visitors,
                COUNT(CASE WHEN last_seen_at >= ? THEN 1 END) AS online_visitors,
                COUNT(CASE WHEN last_seen_at >= ? OR created_at >= ? THEN 1 END) AS today_visitors,
                COUNT(CASE WHEN name IS NOT NULL OR email IS NOT NULL OR contact_id IS NOT NULL THEN 1 END) AS identified_visitors',
                [now()->subMinutes(5), today(), today()])
            ->toBase()
            ->first();
    }

    public function chattingCount(Collection $projects): int
    {
        return Visitor::whereIn('project_id', $projects->modelKeys())->has('conversations')->count();
    }

    /** @param  Collection<int, Project>  $projects */
    public function traffic(Collection $projects, ?string $range, int $totalVisitors, int $chatting): array
    {
        $range = isset(self::CHART_RANGES[$range]) ? $range : '14d';
        $days = self::CHART_RANGES[$range];
        $ids = $projects->modelKeys();
        $visitors = Visitor::whereIn('project_id', $ids);

        $trend = $this->trend($ids, $days);
        $peak = collect($trend)->sortByDesc('visitors')->first();
        $periodVisitors = array_sum(array_column($trend, 'visitors'));

        [$devices, $os, $browsers] = $this->userAgentBreakdown($ids);

        $perProject = (clone $visitors)->selectRaw('project_id, COUNT(*) AS total')->groupBy('project_id')->pluck('total', 'project_id');
        $channelShare = $projects->map(fn (Project $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'visitors' => (int) ($perProject[$p->id] ?? 0),
            'percentage' => $this->pct($perProject[$p->id] ?? 0, $totalVisitors),
        ])->sortByDesc('visitors')->values()->all();

        return [
            'chartRange' => $range,
            'daysCount' => $days,
            'chartTrendData' => $trend,
            'maxDailyVisitors' => $peak['visitors'] ?? 0,
            'peakDateLabel' => ($peak['visitors'] ?? 0) > 0 ? "{$peak['label']} ({$peak['visitors']} pengunjung)" : '-',
            'totalPeriodVisitors' => $periodVisitors,
            'totalPeriodChats' => array_sum(array_column($trend, 'chats')),
            'avgDailyVisitors' => round($periodVisitors / $days, 1),
            'chatConversionRate' => $this->pct($chatting, $totalVisitors),
            'deviceCounts' => $devices,
            'deviceTotal' => array_sum($devices),
            'osCounts' => array_slice($os, 0, 4, true),
            'browserCounts' => array_slice($browsers, 0, 4, true),
            'channelShare' => $channelShare,
            'topPages' => $this->topPages($ids, $totalVisitors),
            'timeOfDay' => $this->timeOfDay($ids),
        ];
    }

    /** Pengunjung per hari = dibuat ATAU terakhir terlihat pada hari itu (dihitung sekali). */
    private function trend(array $projectIds, int $days): array
    {
        $start = today()->subDays($days - 1);
        $base = fn () => Visitor::whereIn('project_id', $projectIds);
        $created = Sql::date('created_at');
        $seen = Sql::date('last_seen_at');

        $byCreated = $base()->where('created_at', '>=', $start)->selectRaw("{$created} AS d, COUNT(*) AS c")->groupBy('d')->pluck('c', 'd');
        $bySeen = $base()->where('last_seen_at', '>=', $start)->selectRaw("{$seen} AS d, COUNT(*) AS c")->groupBy('d')->pluck('c', 'd');
        $both = $base()->where('created_at', '>=', $start)->whereRaw("{$created} = {$seen}")->selectRaw("{$created} AS d, COUNT(*) AS c")->groupBy('d')->pluck('c', 'd');
        $chats = Conversation::whereIn('project_id', $projectIds)->where('created_at', '>=', $start)
            ->selectRaw("{$created} AS d, COUNT(*) AS c")->groupBy('d')->pluck('c', 'd');

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $key = $day->format('Y-m-d');
            $trend[] = [
                'date' => $key,
                'label' => $day->format('d M'),
                'visitors' => (int) ($byCreated[$key] ?? 0) + (int) ($bySeen[$key] ?? 0) - (int) ($both[$key] ?? 0),
                'chats' => (int) ($chats[$key] ?? 0),
            ];
        }

        return $trend;
    }

    /** Device/OS/browser dari user agent unik (biasanya jauh lebih sedikit dari jumlah visitor). */
    private function userAgentBreakdown(array $projectIds): array
    {
        $devices = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
        $os = $browsers = [];

        Visitor::whereIn('project_id', $projectIds)
            ->selectRaw('user_agent, COUNT(*) AS total')
            ->groupBy('user_agent')
            ->toBase()
            ->get()
            ->each(function ($row) use (&$devices, &$os, &$browsers) {
                $probe = new Visitor(['user_agent' => $row->user_agent]); // pakai ulang parser accessor model
                $devices[$probe->device_type] += $row->total;
                $os[$probe->os_name] = ($os[$probe->os_name] ?? 0) + $row->total;
                $browsers[$probe->browser_name] = ($browsers[$probe->browser_name] ?? 0) + $row->total;
            });

        arsort($os);
        arsort($browsers);

        return [$devices, $os, $browsers];
    }

    private function timeOfDay(array $projectIds): array
    {
        $hours = Visitor::whereIn('project_id', $projectIds)
            ->where(fn ($q) => $q->whereNotNull('last_seen_at')->orWhereNotNull('created_at'))
            ->selectRaw(Sql::hour('COALESCE(last_seen_at, created_at)').' AS h, COUNT(*) AS c')
            ->groupBy('h')
            ->pluck('c', 'h');

        $buckets = ['morning' => 0, 'afternoon' => 0, 'evening' => 0, 'night' => 0];
        foreach ($hours as $h => $count) {
            $bucket = match (true) {
                $h >= 6 && $h < 12 => 'morning',
                $h >= 12 && $h < 18 => 'afternoon',
                $h >= 18 => 'evening',
                default => 'night',
            };
            $buckets[$bucket] += $count;
        }

        return $buckets;
    }

    private function topPages(array $projectIds, int $totalVisitors): Collection
    {
        return Conversation::whereIn('project_id', $projectIds)
            ->whereNotNull('page_url')->where('page_url', '!=', '')
            ->selectRaw('page_url, page_title, COUNT(*) AS count')
            ->groupBy('page_url', 'page_title')
            ->orderByDesc('count')
            ->take(5)
            ->toBase()
            ->get()
            ->map(fn ($p) => [
                'url' => $p->page_url,
                'title' => $p->page_title ?: (parse_url($p->page_url, PHP_URL_PATH) ?: '/'),
                'count' => $p->count,
                'percentage' => $this->pct($p->count, $totalVisitors),
            ]);
    }

    private function pct(int|float $part, int|float $total): float
    {
        return $total > 0 ? round($part / $total * 100, 1) : 0;
    }
}
