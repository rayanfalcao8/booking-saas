<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Actions\UpdateBookingStatusAction;
use App\Models\Booking;
use App\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class PublicBookingCancelController extends Controller
{
    public function show(Business $business, Booking $booking, string $token): View
    {
        if (! $this->canCancel($business, $booking, $token)) {
            return $this->errorView('Lien d’annulation invalide ou expiré.');
        }

        if ((string) $booking->status === 'canceled') {
            return $this->errorView('Cette réservation est déjà annulée.');
        }

        $booking->loadMissing(['service', 'staff']);

        return view('booking.cancel-result', [
            'status' => 'confirm',
            'message' => 'Vérifiez les détails avant de confirmer l’annulation.',
            'business' => $business,
            'booking' => $booking,
            'cancelAction' => route('public.booking.cancel.perform', [
                'business' => $business->slug,
                'booking' => $booking->id,
                'token' => $token,
            ]),
        ]);
    }

    public function cancel(Business $business, Booking $booking, string $token, UpdateBookingStatusAction $updateBookingStatusAction): View
    {
        if (! $this->canCancel($business, $booking, $token)) {
            return $this->errorView('Lien d’annulation invalide ou expiré.');
        }

        try {
            $updateBookingStatusAction->run($booking, 'canceled');
        } catch (ValidationException $exception) {
            return $this->errorView(
                collect($exception->errors())->flatten()->first() ?? 'La réservation ne peut pas être annulée.'
            );
        }

        return view('booking.cancel-result', [
            'status' => 'success',
            'message' => 'Votre réservation a été annulée.',
            'business' => $business,
            'booking' => $booking->refresh()->loadMissing(['service', 'staff']),
            'cancelAction' => null,
        ]);
    }

    private function canCancel(Business $business, Booking $booking, string $token): bool
    {
        return (int) $booking->business_id === (int) $business->id
            && $booking->isCancellationTokenValid($token);
    }

    private function errorView(string $message): View
    {
        return view('booking.cancel-result', [
            'status' => 'error',
            'message' => $message,
            'business' => null,
            'booking' => null,
            'cancelAction' => null,
        ]);
    }
}
