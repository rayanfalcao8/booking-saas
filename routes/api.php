<?php

use App\Core\Tenancy\Middleware\InitializeTenant;
use App\Http\Controllers\Api\PublicBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware([InitializeTenant::class])->group(function () {
    Route::get('/b/{business:slug}/availability', [PublicBookingController::class, 'availability'])
        ->name('api.public.availability');

    Route::post('/b/{business:slug}/book', [PublicBookingController::class, 'book'])
        ->name('api.public.book');
});
