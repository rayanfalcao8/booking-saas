<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\Services\BookingValidationService;
use App\Models\Booking;
use App\Models\Staff;
use App\Notifications\BusinessBookingCreated;
use App\Notifications\CustomerBookingConfirmed;
use Illuminate\Notifications\Notification as NotificationMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class CreateBookingAction
{
    public function __construct(private BookingValidationService $bookingValidationService) {}

    public function run(array $data): Booking
    {
        $booking = DB::transaction(function () use ($data): Booking {
            $this->lockStaff((int) ($data['staff_id'] ?? 0));
            $validated = $this->bookingValidationService->validate($data);

            return Booking::query()->create([
                'service_id' => $validated['service']->id,
                'staff_id' => $validated['staff']->id,
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
                'cancellation_token' => Str::random(48),
                'cancellation_expires_at' => $validated['cancellation_expires_at'],
            ]);
        }, 3);

        $booking->loadMissing(['service', 'staff', 'business']);
        $this->sendCreatedNotifications($booking);

        return $booking;
    }

    private function lockStaff(int $staffId): void
    {
        Staff::withoutGlobalScopes()
            ->whereKey($staffId)
            ->lockForUpdate()
            ->first();
    }

    private function sendCreatedNotifications(Booking $booking): void
    {
        $this->sendMailNotification(
            recipientEmail: $booking->business?->email,
            notification: new BusinessBookingCreated($booking),
            audience: 'business',
            booking: $booking,
        );

        $this->sendMailNotification(
            recipientEmail: $booking->customer_email,
            notification: new CustomerBookingConfirmed($booking),
            audience: 'customer',
            booking: $booking,
        );
    }

    private function sendMailNotification(
        ?string $recipientEmail,
        NotificationMessage $notification,
        string $audience,
        Booking $booking,
    ): void {
        if (! is_string($recipientEmail) || $recipientEmail === '') {
            return;
        }

        try {
            Notification::route('mail', $recipientEmail)
                ->notifyNow($notification);
        } catch (Throwable $exception) {
            Log::warning('Booking notification delivery failed.', [
                'booking_id' => $booking->id,
                'audience' => $audience,
                'recipient_email' => $recipientEmail,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
