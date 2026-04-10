<?php

namespace App\Domain\Booking\Actions;

use App\Core\Tenancy\TenantManager;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateBookingStatusAction
{
    public function run(Booking $booking, string $targetStatus): Booking
    {
        $currentStatus = (string) $booking->status;

        if ($currentStatus === $targetStatus) {
            return $booking;
        }

        if ($currentStatus === 'canceled') {
            throw ValidationException::withMessages([
                'status' => 'Une réservation annulée ne peut plus changer de statut.',
            ]);
        }

        $allowedTargets = ['canceled', 'no_show'];

        if (! in_array($targetStatus, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'status' => 'Transition de statut non autorisée.',
            ]);
        }

        if ($targetStatus === 'no_show' && ! $this->hasBookingStarted($booking)) {
            throw ValidationException::withMessages([
                'status' => 'Le statut no-show est autorisé uniquement après l’heure prévue.',
            ]);
        }

        $payload = ['status' => $targetStatus];

        if ($targetStatus === 'canceled') {
            $payload['canceled_at'] = now();
        }

        $booking->forceFill($payload)->save();

        return $booking->refresh();
    }

    private function hasBookingStarted(Booking $booking): bool
    {
        $timezone = TenantManager::timezone();
        $date = (string) $booking->date;
        $startTime = substr((string) $booking->start_time, 0, 5);

        $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$startTime}", $timezone);

        return $scheduledAt->lte(Carbon::now($timezone));
    }
}
