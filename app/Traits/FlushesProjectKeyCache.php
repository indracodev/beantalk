<?php

namespace App\Traits;

use App\Models\ApiKey;

/** Hapus cache resolusi project key (ApiKey::resolveActive) bila data project berubah. */
trait FlushesProjectKeyCache
{
    public static function bootFlushesProjectKeyCache(): void
    {
        $flush = fn ($model) => ApiKey::forgetProjectCache($model->project_id ?? $model->getKey());

        static::saved($flush);
        static::deleted($flush);
    }
}
