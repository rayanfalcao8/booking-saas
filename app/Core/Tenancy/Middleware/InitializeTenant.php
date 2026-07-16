<?php

namespace App\Core\Tenancy\Middleware;

use App\Core\Tenancy\TenantManager;
use App\Models\Business;
use Closure;
use Illuminate\Http\Request;

class InitializeTenant
{
    public function handle(Request $request, Closure $next)
    {
        TenantManager::forget();

        try {
            $business = $request->route('business');

            if ($business instanceof Business) {
                TenantManager::set($business);
            } else {
                $user = $request->user();

                if ($user && $user->business_id) {
                    $tenant = Business::query()->find($user->business_id);

                    if ($tenant) {
                        TenantManager::set($tenant);
                    }
                }
            }

            return $next($request);
        } finally {
            TenantManager::forget();
        }
    }
}
