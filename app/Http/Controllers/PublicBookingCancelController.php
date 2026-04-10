<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use Illuminate\Contracts\View\View;

class PublicBookingCancelController extends Controller
{
    public function __invoke(Business $business, Booking $booking, string $token): View
    {
        $isValidToken = $booking->cancellation_token !== null
            && hash_equals((string) $booking->cancellation_token, $token);

        if (! $isValidToken) {
            return view('booking.cancel-result', [
                'status' => 'error',
                'message' => 'Lien d’annulation invalide.',
            ]);
        }

        if ($booking->status !== 'canceled') {
            $booking->forceFill([
                'status' => 'canceled',
                'canceled_at' => now(),
            ])->save();
        }

        return view('booking.cancel-result', [
            'status' => 'success',
            'message' => 'Votre réservation a été annulée.',
        ]);
    }
}
