<?php

use Illuminate\Support\Facades\Route;
use App\Core\Tenancy\Middleware\InitializeTenant;
use App\Core\Tenancy\TenantManager;
use App\Models\Business;

Route::middleware([InitializeTenant::class])->group(function () {

    Route::get('/b/{business:slug}/ping', function (Business $business) {
        return response()->json([
            'business_id' => TenantManager::id(),
            'business_slug' => TenantManager::get()?->slug,
            'business_name' => TenantManager::get()?->name,
        ]);
    });

});

Route::get('/', function () {
    return view('welcome');
});
