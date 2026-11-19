<?php

namespace App\Models\Concerns;

use App\Support\DateRange;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/** Filter halaman Pengunjung: search, project, presence, device, browser, OS, rentang tanggal. */
trait FiltersVisitors
{
    /** Pola LIKE per nilai filter; 'exclude' = pola yang tidak boleh muncul. */
    private const UA_FILTERS = [
        'device' => [
            'mobile' => ['include' => ['mobile', 'iphone', 'android']],
            'tablet' => ['include' => ['ipad', 'tablet']],
            'desktop' => ['exclude' => ['mobile', 'iphone', 'android', 'ipad', 'tablet']],
        ],
        'browser' => [
            'Edge' => ['include' => ['Edg']],
            'Chrome' => ['include' => ['Chrome'], 'exclude' => ['Edg']],
            'Safari' => ['include' => ['Safari'], 'exclude' => ['Chrome']],
            'Firefox' => ['include' => ['Firefox']],
            'Opera' => ['include' => ['Opera', 'OPR']],
            'Lainnya' => ['other' => ['Chrome', 'Safari', 'Firefox', 'Edg', 'Opera', 'OPR']],
        ],
        'os' => [
            'Windows' => ['include' => ['Windows']],
            'iOS' => ['include' => ['iPhone', 'iPad', 'iOS']],
            'macOS' => ['include' => ['Macintosh', 'Mac OS']],
            'Android' => ['include' => ['Android']],
            'Linux' => ['include' => ['Linux'], 'exclude' => ['Android']],
            'Lainnya' => ['other' => ['Windows', 'iPhone', 'iPad', 'iOS', 'Macintosh', 'Mac OS', 'Android', 'Linux']],
        ],
    ];

    #[Scope]
    protected function filter(Builder $query, array $f): void
    {
        $search = trim((string) ($f['search'] ?? ''));

        $query->when($search !== '', fn ($q) => $q->where(fn ($s) => $s->whereLike('name', "%{$search}%")
            ->orWhereLike('email', "%{$search}%")->orWhereLike('customer_code', "%{$search}%")
            ->orWhereLike('ip_address', "%{$search}%")->orWhereLike('visitor_uuid', "%{$search}%")))
            ->when($f['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))
            ->when(DateRange::since($f['date_range'] ?? null), fn ($q, $since) => $q->where('last_seen_at', '>=', $since));

        match ($f['status'] ?? null) {
            'online' => $query->where('last_seen_at', '>=', now()->subMinutes(5)),
            'today' => $query->where('last_seen_at', '>=', today()),
            'chatting' => $query->has('conversations'),
            'identified' => $query->where(fn ($q) => $q->whereNotNull('name')->orWhereNotNull('email')->orWhereNotNull('contact_id')),
            'anonymous' => $query->whereNull('name')->whereNull('email')->whereNull('contact_id'),
            default => null,
        };

        foreach (self::UA_FILTERS as $key => $options) {
            if ($rule = $options[$f[$key] ?? ''] ?? null) {
                $this->applyUaRule($query, $rule);
            }
        }
    }

    private function applyUaRule(Builder $query, array $rule): void
    {
        if (isset($rule['other'])) {
            $query->where(fn ($q) => $q->whereNull('user_agent')->orWhere('user_agent', '')
                ->orWhere(fn ($s) => collect($rule['other'])->each(fn ($p) => $s->whereNotLike('user_agent', "%{$p}%"))));

            return;
        }
        if (! empty($rule['include'])) {
            $query->where(fn ($q) => collect($rule['include'])->each(fn ($p) => $q->orWhereLike('user_agent', "%{$p}%")));
        }
        foreach ($rule['exclude'] ?? [] as $p) {
            $query->whereNotLike('user_agent', "%{$p}%");
        }
    }
}
