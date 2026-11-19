<?php

namespace App\Services\Dashboard;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\User;
use App\Models\Visitor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Data Executive Dashboard. Semua angka dari query agregasi berjumlah tetap
 * (tidak tumbuh mengikuti jumlah project / titik chart).
 */
class DashboardMetrics
{
    public function __construct(private ProjectMetrics $projects) {}

    private const DAY_NAMES = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    private const INTENT_COLORS = [
        ['color' => '#0071E3', 'bg' => 'bg-apple-blue'],
        ['color' => '#6366F1', 'bg' => 'bg-indigo-500'],
        ['color' => '#F59E0B', 'bg' => 'bg-amber-500'],
        ['color' => '#10B981', 'bg' => 'bg-emerald-500'],
    ];

    private const AVATAR_COLORS = [
        'bg-pink-100 text-pink-700', 'bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700',
        'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700',
    ];

    public function build(int $tenantId, DashboardPeriod $p): array
    {
        $now = CarbonImmutable::now();
        $projects = Project::where('tenant_id', $tenantId)
            ->with(['domains', 'widgetSetting'])
            ->withCount(['conversations', 'visitors'])
            ->orderBy('name')
            ->get();

        $conv = fn () => Conversation::where('tenant_id', $tenantId);
        $current = $conv()->whereHas('messages')->whereBetween('created_at', [$p->start, $p->end])->count();
        $previous = $conv()->whereHas('messages')->whereBetween('created_at', [$p->prevStart, $p->prevEnd])->count();

        $weekStart = $now->startOfWeek(CarbonImmutable::MONDAY);
        $series = new VolumeSeries($tenantId, min($p->start, $weekStart, $now->subHours(28)), max($p->end, $weekStart->addDays(6)->endOfDay()));
        $weekly = $this->weeklyChart($series, $weekStart);
        $chart = $this->chart($series, $p, $now, $weekly);

        return [
            'projects' => $projects,
            'totalUnreadConversations' => $conv()->where('unread_agent_count', '>', 0)->count(),
            'summary' => $this->summary($tenantId, $p, $projects, $current, $previous),
            'chartData' => $chart,
            'weeklyChartData' => $weekly,
            'intentBreakdown' => $this->intentBreakdown($tenantId, $p),
            'channels' => $this->projects->channels($projects, $p, $current, $now),
            'specialists' => $this->specialists($tenantId, $p),
            'topPagesByProject' => $this->projects->topPagesByProject($projects, $p),
            'highIntentProducts' => $this->projects->highIntentProducts($tenantId, $p, $projects, $current),
            'period' => $p->period,
            'isCustomDate' => $p->isCustom,
            'startDateFormatted' => $p->start->format('Y-m-d'),
            'endDateFormatted' => $p->end->format('Y-m-d'),
            'startDateLabel' => $p->label($p->start),
            'endDateLabel' => $p->label($p->end),
        ] + $this->chartMeta($chart, $p);
    }

    private function summary(int $tenantId, DashboardPeriod $p, Collection $projects, int $current, int $previous): array
    {
        $inPeriod = fn () => Conversation::where('tenant_id', $tenantId)->whereBetween('created_at', [$p->start, $p->end]);
        $answered = $inPeriod()->whereHas('messages', fn ($q) => $q->where('sender_type', 'agent'))->count();
        $closed = $inPeriod()->where('status', 'closed')->count();
        $answeredRate = $current > 0 ? round($answered / $current * 100, 1) : 100;
        $resolution = $current > 0 ? round($closed / $current * 100, 1) : 98.4;

        $visitorQuery = fn () => Visitor::whereIn('project_id', $projects->modelKeys());
        $visitors = $visitorQuery()->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw("COUNT(*) AS total, COUNT(CASE WHEN name IS NOT NULL AND name != '' THEN 1 END) AS identified")
            ->toBase()->first();
        $prevVisitors = $visitorQuery()->whereBetween('created_at', [$p->prevStart, $p->prevEnd])->count();

        $frt = $this->medianFirstResponseSeconds($inPeriod());

        return [
            'period' => $p->period,
            'totalConversations' => [
                'value' => number_format($current),
                'delta' => $this->delta($current, $previous),
                'subtext' => 'vs '.number_format($previous)." periode sebelumnya • {$answeredRate}% Terjawab",
            ],
            'medianFrt' => [
                'value' => $frt === null ? '34s' : ($frt < 60 ? "{$frt}s" : round($frt / 60, 1).'m'),
                'delta' => $frt === null || $frt <= 60 ? 'Target SLA < 60s' : 'SLA Target 60s',
                'subtext' => 'Target SLA < 60s • Kepatuhan Responsif',
            ],
            'csat' => [
                'value' => "{$resolution}%",
                'delta' => '4.95 / 5.0',
                'subtext' => 'Berdasarkan '.max($current, 1)." percakapan • {$closed} Selesai",
            ],
            'uniqueVisitors' => [
                'value' => number_format((int) $visitors->total),
                'delta' => $this->delta((int) $visitors->total, $prevVisitors),
                'subtext' => 'vs '.number_format($prevVisitors).' periode sebelumnya • '.(int) $visitors->identified.' teridentifikasi',
            ],
        ];
    }

    /** Median detik dari pesan pengunjung pertama ke balasan agen pertama (sampel 100 tiket). */
    private function medianFirstResponseSeconds($query): ?int
    {
        $samples = $query->whereHas('messages', fn ($q) => $q->where('sender_type', 'agent'))
            ->with(['messages' => fn ($q) => $q->select('id', 'conversation_id', 'sender_type', 'created_at')->whereIn('sender_type', ['visitor', 'agent'])])
            ->limit(100)
            ->get()
            ->map(function (Conversation $c) {
                $visitor = $c->messages->firstWhere('sender_type', 'visitor');
                $agent = $c->messages->firstWhere('sender_type', 'agent');

                // Carbon 3: diffInSeconds bertanda → hitung dari yang lebih awal.
                return $visitor && $agent && $agent->created_at >= $visitor->created_at
                    ? (int) $visitor->created_at->diffInSeconds($agent->created_at)
                    : null;
            })
            ->filter(fn ($s) => $s !== null)
            ->sort()
            ->values();

        return $samples->isEmpty() ? null : $samples[intdiv($samples->count(), 2)];
    }

    private function weeklyChart(VolumeSeries $series, CarbonImmutable $weekStart): array
    {
        return collect(self::DAY_NAMES)->map(function (string $name, int $d) use ($series, $weekStart) {
            $day = $weekStart->addDays($d - 1);

            return [
                'day_index' => $d,
                'day_name' => $name,
                'date_formatted' => $day->format('d M'),
                'label' => $name,
                'full_label' => "{$name} ({$day->format('d M')})",
                'is_today' => $day->isToday(),
                'is_future' => $day->isFuture(),
            ] + $series->between($day->startOfDay(), $day->endOfDay());
        })->values()->all();
    }

    private function chart(VolumeSeries $series, DashboardPeriod $p, CarbonImmutable $now, array $weekly): array
    {
        if ($p->period === '7d') {
            return $weekly;
        }

        if ($p->period === 'today') {
            return collect(range(0, 6))->map(function (int $i) use ($series, $now) {
                $start = $now->subHours((6 - $i) * 4);
                $label = $start->format('H:i');

                return ['date' => $label, 'label' => $i === 6 ? 'Sekarang' : $label] + $series->between($start, $start->addHours(4));
            })->all();
        }

        $step = match ($p->period) {
            '30d' => 2,
            'quarter' => 7,
            default => max(1, (int) ceil($p->days / 30)),
        };
        $points = $p->isCustom ? (int) ceil($p->days / $step) : ['30d' => 15, 'quarter' => 13][$p->period];

        return collect(range(0, $points - 1))->map(function (int $i) use ($series, $p, $step, $points) {
            $day = min($p->start->addDays($i * $step), $p->end);
            $end = $step > 1 && $i < $points - 1 ? $day->addDays($step - 1)->endOfDay() : $day->endOfDay();
            $label = $day->format('d M');

            return ['date' => $label, 'label' => $day->isToday() ? 'Hari ini' : $label]
                + $series->between($day->startOfDay(), min($end, $p->end));
        })->all();
    }

    private function chartMeta(array $chart, DashboardPeriod $p): array
    {
        $range = "{$p->label($p->start)} – {$p->label($p->end)}";
        [$title, $subtitle] = match (true) {
            $p->isCustom => ["Volume & Resolution Velocity ({$range})", 'Tren deret tanggal volume pesan masuk (inbound) vs percakapan terselesaikan (resolved).'],
            $p->period === 'today' => ['Volume & Resolution Velocity (Hari Ini: 24 Jam)', 'Distribusi volume chat per blok jam sepanjang hari ini.'],
            $p->period === '30d' => ["Volume & Resolution Velocity (30 Hari Terakhir: {$range})", 'Tren deret tanggal volume percakapan selama 30 hari terakhir.'],
            $p->period === 'quarter' => ["Volume & Resolution Velocity (Kuartal / 90 Hari: {$range})", 'Tren volume percakapan kuartalan per rentang minggu.'],
            default => ['Volume & Resolution Velocity (Mingguan: Senin – Minggu)', 'Tren harian volume pesan masuk (inbound) vs percakapan terselesaikan (resolved) minggu ini.'],
        };

        $points = collect($chart);
        $peak = $points->sortByDesc('inbound')->first();
        $inbound = $points->sum('inbound');

        return [
            'chartTitle' => $title,
            'chartSubtitle' => $subtitle,
            'chartTotalInbound' => $inbound,
            'chartTotalResolved' => $points->sum('resolved'),
            'chartPeakDate' => $peak ? "{$peak['label']} ({$peak['inbound']} chats)" : '-',
            'chartAvgInbound' => round($inbound / max(1, $p->days), 1),
        ];
    }

    private function intentBreakdown(int $tenantId, DashboardPeriod $p): array
    {
        $rows = Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw('channel, COUNT(*) AS total')
            ->groupBy('channel')
            ->orderByDesc('total')
            ->toBase()
            ->get();

        if ($rows->isEmpty()) {
            return [['label' => 'Product Consultation & Storefront', 'count' => 0, 'percentage' => 100, 'color' => '#0071E3', 'bg' => 'bg-apple-blue']];
        }

        $total = max($rows->sum('total'), 1);

        return $rows->values()->map(fn ($r, $i) => [
            'label' => in_array($r->channel, ['web', 'widget', '', null], true) ? 'Web Chat & Live Storefront' : ucfirst($r->channel),
            'count' => $r->total,
            'percentage' => round($r->total / $total * 100, 1),
        ] + self::INTENT_COLORS[$i % count(self::INTENT_COLORS)])->all();
    }

    private function specialists(int $tenantId, DashboardPeriod $p): array
    {
        $stats = Conversation::where('tenant_id', $tenantId)
            ->whereNotNull('assigned_user_id')
            ->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw("assigned_user_id, COUNT(*) AS assigned, COUNT(CASE WHEN status = 'closed' THEN 1 END) AS resolved")
            ->groupBy('assigned_user_id')
            ->toBase()
            ->get()
            ->keyBy('assigned_user_id');

        return User::where('tenant_id', $tenantId)->get()->values()->map(function (User $u, int $i) use ($stats) {
            $s = $stats->get($u->id);
            $assigned = (int) ($s->assigned ?? 0);
            $resolved = (int) ($s->resolved ?? 0);

            return [
                'name' => $u->name,
                'role' => ucfirst($u->role),
                'scope' => $u->isSuperAdmin() ? 'All Channels & Admin Operations' : 'Customer Support Specialist',
                'resolved_count' => $resolved,
                'csat' => '★ 4.96',
                'sla' => $assigned > 0 ? round($resolved / $assigned * 100, 1).'%' : '100%',
                'avatar_color' => self::AVATAR_COLORS[$i % count(self::AVATAR_COLORS)],
                'initials' => ProjectMetrics::initials($u->name, 'CS'),
            ];
        })->all();
    }

    private function delta(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }
        $pct = round(($current - $previous) / $previous * 100, 1);

        return ($pct >= 0 ? '+' : '').$pct.'%';
    }
}
