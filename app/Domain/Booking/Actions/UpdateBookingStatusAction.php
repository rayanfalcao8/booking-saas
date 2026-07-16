<?php

namespace App\Domain\Booking\Actions;

use App\Core\Tenancy\TenantManager;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBookingStatusAction
{
    public function run(Booking $booking, string $targetStatus): Booking
    {
        return DB::transaction(function () use ($booking, $targetStatus): Booking {
            $lockedBooking = Booking::withoutGlobalScopes()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $tenantId = TenantManager::id();

            if ($tenantId && (int) $lockedBooking->business_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'business' => 'La réservation sélectionnée est invalide pour ce business.',
                ]);
            }

            $currentStatus = (string) $lockedBooking->status;

            if ($currentStatus === $targetStatus) {
                return $lockedBooking;
            }

            if ($currentStatus === 'canceled') {
                throw ValidationException::withMessages([
                    'status' => 'Une réservation annulée ne peut plus changer de statut.',
                ]);
            }

            if ($currentStatus === 'completed') {
                throw ValidationException::withMessages([
                    'status' => 'Une réservation terminée ne peut plus changer de statut.',
                ]);
            }

            if (! in_array($targetStatus, $this->allowedTargetsFrom($currentStatus), true)) {
                throw ValidationException::withMessages([
                    'status' => 'Transition de statut non autorisée.',
                ]);
            }

            if ($targetStatus === 'no_show' && ! $this->hasBookingStarted($lockedBooking)) {
                throw ValidationException::withMessages([
                    'status' => 'Le statut no-show est autorisé uniquement après l’heure prévue.',
                ]);
            }

            if ($targetStatus === 'completed' && ! $this->hasBookingEnded($lockedBooking)) {
                throw ValidationException::withMessages([
                    'status' => 'Le statut terminé est autorisé uniquement après la fin prévue.',
                ]);
            }

            $payload = ['status' => $targetStatus];

            if ($targetStatus === 'canceled') {
                $payload['canceled_at'] = now();
            }

            $lockedBooking->forceFill($payload)->save();

            return $lockedBooking->refresh();
        }, 3);
    }

    private function allowedTargetsFrom(string $currentStatus): array
    {
        return match ($currentStatus) {
            'confirmed' => ['canceled', 'completed', 'no_show'],
            'no_show' => ['confirmed', 'canceled'],
            default => [],
        };
    }

    private function hasBookingStarted(Booking $booking): bool
    {
        return $this->scheduledStartAt($booking)->lte(Carbon::now(TenantManager::timezone()));
    }

    private function hasBookingEnded(Booking $booking): bool
    {
        return $this->scheduledEndAt($booking)->lte(Carbon::now(TenantManager::timezone()));
    }

    private function scheduledStartAt(Booking $booking): Carbon
    {
        $timezone = TenantManager::timezone();
        $date = (string) $booking->date;
        $startTime = substr((string) $booking->start_time, 0, 5);

        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$startTime}", $timezone);
    }

    private function scheduledEndAt(Booking $booking): Carbon
    {
        $timezone = TenantManager::timezone();
        $date = (string) $booking->date;
        $endTime = substr((string) $booking->end_time, 0, 5);

        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$endTime}", $timezone);
    }
}
