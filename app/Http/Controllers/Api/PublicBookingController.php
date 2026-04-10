<?php

namespace App\Http\Controllers\Api;

use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Domain\Booking\Services\AvailabilityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicAvailabilityRequest;
use App\Http\Requests\PublicBookRequest;
use App\Models\Booking;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function cancel(Business $business, Booking $booking, Request $request): JsonResponse
    {
        $token = (string) $request->input('token', '');

        if ($token === '' || ! hash_equals((string) $booking->cancellation_token, $token)) {
            return response()->json([
                'message' => 'Lien d’annulation invalide.',
            ], 422);
        }

        if ($booking->status === 'canceled') {
            return response()->json([
                'id' => $booking->id,
                'status' => $booking->status,
                'message' => 'La réservation est déjà annulée.',
            ]);
        }

        $booking->forceFill([
            'status' => 'canceled',
            'canceled_at' => now(),
        ])->save();

        return response()->json([
            'id' => $booking->id,
            'status' => $booking->status,
            'message' => 'Réservation annulée avec succès.',
        ]);
    }
}

