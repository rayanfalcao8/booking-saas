<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\BookingValidationService;
use App\Models\Booking;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingDetailsAction
{
    public function __construct(private BookingValidationService $bookingValidationService) {}

    public function run(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data): Booking {
            $lockedBooking = Booking::withoutGlobalScopes()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->lockStaff((int) ($data['staff_id'] ?? 0));

            if ((string) $lockedBooking->status !== 'confirmed') {
                throw ValidationException::withMessages([
                    'status' => 'Seules les réservations confirmées peuvent être modifiées.',
                ]);
            }

            $validated = $this->bookingValidationService->validate($data, $lockedBooking);

            $lockedBooking->forceFill([
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

            return $lockedBooking->refresh()->loadMissing(['service', 'staff', 'business']);
        }, 3);
    }

    private function lockStaff(int $staffId): void
    {
        Staff::withoutGlobalScopes()
            ->whereKey($staffId)
            ->lockForUpdate()
            ->first();
    }
}
