<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        // 1. Global Scope: Auto-filter all queries by active tenant_id
        static::addGlobalScope('tenant_isolation', function (Builder $builder) {
            if (app()->has('current_tenant_id') && ($tenantId = app('current_tenant_id'))) {
                $table = $builder->getModel()->getTable();
                $builder->where($table . '.tenant_id', $tenantId);
            }
        });

        // 2. Auto-assign tenant_id on model creation if not manually specified
        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->has('current_tenant_id')) {
                $model->tenant_id = app('current_tenant_id');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
