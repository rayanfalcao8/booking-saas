<?php

namespace App\Core\Tenancy\Concerns;

use App\Core\Tenancy\Scopes\BusinessScope;
use App\Core\Tenancy\TenantManager;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope(new BusinessScope());

        static::creating(function ($model) {
            if (empty($model->business_id) && TenantManager::id()) {
                $model->business_id = TenantManager::id();
            }
        });
    }
}
