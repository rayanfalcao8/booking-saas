<?php

namespace App\Core\Tenancy\Scopes;

use App\Core\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = TenantManager::id();

        if (!$tenantId) {
            return;
        }

        $builder->where($model->getTable() . '.business_id', $tenantId);
    }
}

