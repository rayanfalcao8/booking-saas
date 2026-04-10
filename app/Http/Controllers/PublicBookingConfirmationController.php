<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use Illuminate\Contracts\View\View;

class PublicBookingConfirmationController extends Controller
{
    public function __invoke(Business $business, Booking $booking, string $token): View
    {
        $isValidToken = $booking->cancellation_token !== null
            && hash_equals((string) $booking->cancellation_token, $token);

        if (! $isValidToken) {
            abort(404);
        }

        $booking->loadMissing(['service', 'staff']);

        return view('booking.confirmation', [
            'business' => $business,
            'booking' => $booking,
            'cancelUrl' => route('public.booking.cancel', [
                'business' => $business->slug,
                'booking' => $booking->id,
                'token' => $token,
            ]),
        ]);
    }
}
