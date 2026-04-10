<?php

use App\Core\Tenancy\Middleware\InitializeTenant;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Domain\Booking\Services\AvailabilityService;
use App\Models\Business;
use App\Core\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::middleware([InitializeTenant::class])->group(function () {

    Route::get('/b/{business:slug}/availability', function (Business $business, Request $request, AvailabilityService $svc) {
        $data = $request->validate([
            'service_id' => [
                'required','integer',
                Rule::exists('services', 'id')->where(fn($q) => $q->where('business_id', \App\Core\Tenancy\TenantManager::id())),
            ],
            'staff_id' => [
                'required','integer',
                Rule::exists('staff', 'id')->where(fn($q) => $q->where('business_id', \App\Core\Tenancy\TenantManager::id())),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'step_min' => ['nullable', 'integer', 'min:5', 'max:60'],
        ]);

        $q = new AvailabilityQuery(
            serviceId: (int) $data['service_id'],
            staffId: (int) $data['staff_id'],
            date: $data['date'],
            stepMin: (int) ($data['step_min'] ?? 15),
        );

        return response()->json([
            'slots' => $svc->slots($q),
        ]);
    })->name('api.public.availability');

    Route::post('/b/{business:slug}/book', function (Business $business, Request $request, CreateBookingAction $action) {
        $data = $request->validate([
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('business_id', TenantManager::id())),
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query->where('business_id', TenantManager::id())),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = $action->run($data);

        return response()->json([
            'id' => $booking->id,
            'status' => $booking->status,
        ], 201);
    })->name('api.public.book');

});
