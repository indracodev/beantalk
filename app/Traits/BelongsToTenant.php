<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolasi tenant: query otomatis disaring ke tenant aktif ('current_tenant_id'
 * di-bind oleh middleware EnforceTenantScope / ResolveProjectKey).
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant_isolation', function (Builder $builder) {
            if (app()->bound('current_tenant_id') && ($tenantId = app('current_tenant_id'))) {
                $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $tenantId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->bound('current_tenant_id')) {
                $model->tenant_id = app('current_tenant_id');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
