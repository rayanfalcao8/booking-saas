<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use Illuminate\Contracts\View\View;

class PublicBookingConfirmationController extends Controller
{
    public function __invoke(Business $business, Booking $booking, string $token): View
    {
        if ((int) $booking->business_id != (int) $business->id) {
            abort(404);
        }

        if (! $booking->isCancellationTokenValid($token)) {
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
