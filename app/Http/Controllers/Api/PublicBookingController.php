<?php

namespace App\Http\Controllers\Api;

use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\Actions\UpdateBookingStatusAction;
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
            'confirmation_url' => route('public.booking.confirmation', [
                'business' => $business->slug,
                'booking' => $booking->id,
                'token' => $booking->cancellation_token,
            ]),
        ], 201);
    }

    public function cancel(Business $business, Booking $booking, Request $request, UpdateBookingStatusAction $updateBookingStatusAction): JsonResponse
    {
        if ((int) $booking->business_id != (int) $business->id) {
            return response()->json([
                'message' => 'Réservation introuvable.',
            ], 404);
        }

        $token = (string) $request->input('token', '');

        if ($token === '' || ! $booking->isCancellationTokenValid($token)) {
            return response()->json([
                'message' => 'Lien d’annulation invalide ou expiré.',
            ], 422);
        }

        $booking = $updateBookingStatusAction->run($booking, 'canceled');

        return response()->json([
            'id' => $booking->id,
            'status' => $booking->status,
            'message' => 'Réservation annulée avec succès.',
        ]);
    }
}
