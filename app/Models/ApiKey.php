<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'project_id', 'public_key', 'secret_hash', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected static function booted(): void
    {
        static::saved(fn (self $key) => static::forgetProjectCache($key->project_id));
        static::deleted(fn (self $key) => static::forgetProjectCache($key->project_id));
    }

    /**
     * Resolusi key → project (+widgetSetting, domains) dari cache. Dipanggil di setiap
     * request widget (polling ±24/menit per pengunjung), jadi 4 query → 0 saat cache hangat.
     */
    public static function resolveActive(string $publicKey): ?self
    {
        return Cache::remember(static::cacheKey($publicKey), 300, fn () => static::withoutGlobalScopes()
            ->with(['project' => fn ($q) => $q->withoutGlobalScopes()->with(['widgetSetting', 'domains'])])
            ->where('public_key', $publicKey)
            ->where('is_active', true)
            ->first());
    }

    /** Dipanggil saat project/domain/widget/key berubah. */
    public static function forgetProjectCache(?int $projectId): void
    {
        if (! $projectId) {
            return;
        }
        static::withoutGlobalScopes()->where('project_id', $projectId)->pluck('public_key')
            ->each(fn ($pk) => Cache::forget(static::cacheKey($pk)));
    }

    private static function cacheKey(string $publicKey): string
    {
        return 'project_key:'.hash('sha256', $publicKey);
    }

    /**
     * Public key unik & tak tertebak: pk_live_{domain≤10}_{16 acak}.
     * (Sebelumnya mt_rand 4 digit → mudah tabrakan dengan unique index.)
     */
    public static function generatePublicKey(string $seed): string
    {
        $slug = substr(preg_replace('/[^a-z0-9]/', '', strtolower($seed)), 0, 10) ?: 'site';

        return 'pk_live_'.$slug.'_'.Str::lower(Str::random(16));
    }
}
