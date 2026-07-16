<?php

use App\Core\Tenancy\Middleware\InitializeTenant;
use App\Http\Controllers\Api\PublicBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware([InitializeTenant::class])->group(function () {
    Route::get('/b/{business:slug}/availability', [PublicBookingController::class, 'availability'])
        ->middleware('throttle:public-availability')
        ->name('api.public.availability');

    Route::post('/b/{business:slug}/book', [PublicBookingController::class, 'book'])
        ->middleware('throttle:public-bookings')
        ->name('api.public.book');

    Route::post('/b/{business:slug}/book/{booking}/cancel', [PublicBookingController::class, 'cancel'])
        ->middleware('throttle:public-bookings')
        ->name('api.public.book.cancel');
});
