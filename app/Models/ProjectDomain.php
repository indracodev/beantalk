<?php

namespace App\Models;

use App\Traits\FlushesProjectKeyCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDomain extends Model
{
    use FlushesProjectKeyCache;

    protected $fillable = ['project_id', 'domain', 'is_verified'];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Apakah host (dari Origin/Referer) cocok dengan domain ini atau subdomainnya. */
    public function matchesHost(string $host): bool
    {
        $host = strtolower(preg_replace('/^www\./i', '', $host));
        $domain = strtolower(preg_replace('/^www\./i', '', $this->domain));

        return $host === $domain || str_ends_with($host, '.'.$domain);
    }
}
