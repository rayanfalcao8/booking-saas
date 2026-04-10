<?php

namespace App\Http\Controllers\Api;

use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Domain\Booking\Services\AvailabilityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicAvailabilityRequest;
use App\Http\Requests\PublicBookRequest;
use App\Models\Business;
use Illuminate\Http\JsonResponse;

class PublicBookingController extends Controller
{
    public function availability(Business $business, PublicAvailabilityRequest $request, AvailabilityService $availabilityService): JsonResponse
    {
        $data = $request->validated();

        $query = new AvailabilityQuery(
            serviceId: (int) $data['service_id'],
            staffId: (int) $data['staff_id'],
            date: $data['date'],
            stepMin: (int) ($data['step_min'] ?? 15),
        );

        return response()->json([
            'slots' => $availabilityService->slots($query),
        ]);
    }

    public function book(Business $business, PublicBookRequest $request, CreateBookingAction $createBookingAction): JsonResponse
    {
        $booking = $createBookingAction->run($request->validated());

        return response()->json([
            'id' => $booking->id,
            'status' => $booking->status,
        ], 201);
    }
}
