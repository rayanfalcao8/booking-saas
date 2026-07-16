<?php

namespace App\Providers;

use App\Models\Business;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-availability', function (Request $request): Limit {
            return Limit::perMinute(120)
                ->by('availability:'.$this->publicRequestKey($request));
        });

        RateLimiter::for('public-bookings', function (Request $request): Limit {
            return Limit::perMinute(10)
                ->by('bookings:'.$this->publicRequestKey($request));
        });
    }

    private function publicRequestKey(Request $request): string
    {
        $business = $request->route('business');
        $businessKey = $business instanceof Business
            ? (string) $business->getKey()
            : (string) $business;

        return $businessKey.':'.$request->ip();
    }
}
