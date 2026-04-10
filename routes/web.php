<?php

use App\Core\Tenancy\Middleware\InitializeTenant;
use App\Core\Tenancy\TenantManager;
use App\Http\Controllers\PublicBookingCancelController;
use App\Http\Controllers\PublicBookingConfirmationController;
use App\Http\Controllers\PublicBookingPageController;
use App\Models\Business;
use Illuminate\Support\Facades\Route;

Route::middleware([InitializeTenant::class])->group(function () {

    Route::get('/b/{business:slug}/ping', function (Business $business) {
        return response()->json([
            'business_id' => TenantManager::id(),
            'business_slug' => TenantManager::get()?->slug,
            'business_name' => TenantManager::get()?->name,
        ]);
    });

    Route::get('/b/{business:slug}/book', PublicBookingPageController::class)
        ->name('public.booking.page');

    Route::get('/b/{business:slug}/book/{booking}/cancel/{token}', PublicBookingCancelController::class)
        ->name('public.booking.cancel');

    Route::get('/b/{business:slug}/book/{booking}/confirmation/{token}', PublicBookingConfirmationController::class)
        ->name('public.booking.confirmation');

});

Route::get('/', function () {
    return view('welcome');
});
