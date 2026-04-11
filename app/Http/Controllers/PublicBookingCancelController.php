<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Actions\UpdateBookingStatusAction;
use App\Models\Booking;
use App\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class PublicBookingCancelController extends Controller
{
    public function __invoke(Business $business, Booking $booking, string $token, UpdateBookingStatusAction $updateBookingStatusAction): View
    {
        if ((int) $booking->business_id != (int) $business->id) {
            return view('booking.cancel-result', [
                'status' => 'error',
                'message' => 'Réservation introuvable.',
            ]);
        }

        if (! $booking->isCancellationTokenValid($token)) {
            return view('booking.cancel-result', [
                'status' => 'error',
                'message' => 'Lien d’annulation invalide ou expiré.',
            ]);
        }

        try {
            $updateBookingStatusAction->run($booking, 'canceled');
        } catch (ValidationException $exception) {
            return view('booking.cancel-result', [
                'status' => 'error',
                'message' => collect($exception->errors())->flatten()->first() ?? 'La réservation ne peut pas être annulée.',
            ]);
        }

        return view('booking.cancel-result', [
            'status' => 'success',
            'message' => 'Votre réservation a été annulée.',
        ]);
    }
}
