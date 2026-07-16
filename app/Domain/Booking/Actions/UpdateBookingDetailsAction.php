<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\BookingValidationService;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingDetailsAction
{
    public function __construct(private BookingValidationService $bookingValidationService) {}

    public function run(Booking $booking, array $data): Booking
    {
        if ((string) $booking->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'status' => 'Seules les réservations confirmées peuvent être modifiées.',
            ]);
        }

        $validated = $this->bookingValidationService->validate($data, $booking);

        return DB::transaction(function () use ($booking, $data, $validated): Booking {
            $booking->forceFill([
                'service_id' => $validated['service']->id,
                'staff_id' => $validated['staff']->id,
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'cancellation_expires_at' => $validated['cancellation_expires_at'],
            ])->save();

            return $booking->refresh()->loadMissing(['service', 'staff', 'business']);
        });
    }
}
