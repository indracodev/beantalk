<?php

namespace App\Services\Dashboard;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\Visitor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/** Metrik per website/project untuk dashboard: kesehatan kanal, halaman teratas, halaman ber-intent tinggi. */
class ProjectMetrics
{
    public function channels(Collection $projects, DashboardPeriod $p, int $current, CarbonImmutable $now): array
    {
        $ids = $projects->modelKeys();
        $online = Visitor::whereIn('project_id', $ids)->where('last_seen_at', '>=', $now->subMinutes(15))
            ->selectRaw('project_id, COUNT(*) AS c')->groupBy('project_id')->pluck('c', 'project_id');
        $chats = Conversation::whereIn('project_id', $ids)->whereHas('messages')->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw('project_id, COUNT(*) AS c')->groupBy('project_id')->pluck('c', 'project_id');

        return $projects->map(function (Project $proj) use ($online, $chats, $current) {
            $count = (int) ($chats[$proj->id] ?? 0);

            return [
                'id' => $proj->id,
                'name' => $proj->name,
                'domain' => $this->domainOf($proj),
                'active_online' => (int) ($online[$proj->id] ?? 0),
                'chats_7d' => $count,
                'median_frt' => '34s',
                'conversion' => $current > 0 ? min(round($count / $current * 100, 1), 100).'%' : '0%',
                'revenue' => 'Rp '.number_format($count * 95000, 0, ',', '.'),
                'status' => 'Aktif',
                'color' => $proj->widgetSetting?->primary_color ?? '#0071E3',
                'badge' => 'Connected Channel',
            ];
        })->all();
    }

    /** 4 halaman teratas per project dalam satu query GROUP BY (versi lama: 1 query per project). */
    public function topPagesByProject(Collection $projects, DashboardPeriod $p): array
    {
        $pages = Conversation::whereIn('project_id', $projects->modelKeys())
            ->whereNotNull('page_url')->where('page_url', '!=', '')
            ->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw("project_id, page_url, COALESCE(NULLIF(page_title, ''), page_url) AS title, COUNT(*) AS volume")
            ->groupBy('project_id', 'page_url', 'title')
            ->orderByDesc('volume')
            ->toBase()
            ->get()
            ->groupBy('project_id');

        return $projects->map(function (Project $proj) use ($pages) {
            $total = (int) $proj->conversations_count;
            $domain = $this->domainOf($proj);
            $list = ($pages[$proj->id] ?? collect())->take(4)->map(fn ($pg) => [
                'title' => $pg->title,
                'url' => $pg->page_url,
                'path' => parse_url($pg->page_url, PHP_URL_PATH) ?: $pg->page_url,
                'volume' => $pg->volume,
                'share' => ($total > 0 ? round($pg->volume / $total * 100, 1) : 100).'%',
            ])->values()->all();

            return [
                'project_id' => $proj->id,
                'project_name' => $proj->name,
                'domain' => $domain,
                'color' => $proj->widgetSetting?->primary_color ?? '#0071E3',
                'initials' => self::initials($proj->name, 'WS'),
                'total_chats' => $total,
                'pages' => $list ?: [[
                    'title' => "{$proj->name} - Storefront Homepage",
                    'url' => "https://{$domain}",
                    'path' => '/',
                    'volume' => $total,
                    'share' => '100%',
                ]],
            ];
        })->all();
    }

    public function highIntentProducts(int $tenantId, DashboardPeriod $p, Collection $projects, int $current): array
    {
        $pages = Conversation::where('tenant_id', $tenantId)
            ->whereNotNull('page_url')->where('page_url', '!=', '')
            ->whereBetween('created_at', [$p->start, $p->end])
            ->selectRaw('page_url, page_title, COUNT(*) AS volume')
            ->groupBy('page_url', 'page_title')
            ->orderByDesc('volume')
            ->limit(3)
            ->toBase()
            ->get();

        if ($pages->isNotEmpty()) {
            return $pages->map(fn ($pg) => [
                'title' => $pg->page_title ?: 'Storefront Product Page',
                'path' => parse_url($pg->page_url, PHP_URL_PATH) ?: $pg->page_url,
                'meta' => parse_url($pg->page_url, PHP_URL_HOST) ?: 'Live Store',
                'volume' => "{$pg->volume} inquiries",
                'result' => round($pg->volume / max($current, 1) * 100, 1).'% Chat Share',
                'icon' => '☕',
            ])->all();
        }

        return $projects->take(3)->map(fn (Project $proj) => [
            'title' => "{$proj->name} Storefront",
            'path' => "/{$proj->slug}",
            'meta' => $proj->domains->first()?->domain ?? 'Direct Web',
            'volume' => "{$proj->conversations_count} inquiries",
            'result' => 'Active Channel',
            'icon' => '☕',
        ])->values()->all();
    }

    private function domainOf(Project $proj): string
    {
        return $proj->domains->first()?->domain ?? "{$proj->slug}.store";
    }

    public static function initials(string $name, string $fallback): string
    {
        return strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 2)) ?: $fallback;
    }
}
