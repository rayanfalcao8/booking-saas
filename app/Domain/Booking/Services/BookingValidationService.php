<?php

namespace App\Domain\Booking\Services;

use App\Core\Tenancy\TenantManager;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffTimeOff;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingValidationService
{
    /**
     * @return array{
     *     business_id: int,
     *     service: Service,
     *     staff: Staff,
     *     date: string,
     *     start_time: string,
     *     end_time: string,
     *     cancellation_expires_at: Carbon
     * }
     */
    public function validate(array $data, ?Booking $ignoreBooking = null): array
    {
        $businessId = $this->resolveBusinessId($ignoreBooking);
        $service = Service::withoutGlobalScopes()->find($data['service_id'] ?? null);
        $staff = Staff::withoutGlobalScopes()->find($data['staff_id'] ?? null);

        if (! $service) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est introuvable.',
            ]);
        }

        if (! $staff) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est introuvable.',
            ]);
        }

        $business = Business::query()->find($businessId);

        if (! $business) {
            throw ValidationException::withMessages([
                'business' => 'Le business actif est introuvable.',
            ]);
        }

        $this->assertTenantIntegrity($service, $staff, $businessId);
        $this->assertActiveCatalog($service, $staff);
        $this->assertStaffOffersService($service, $staff);

        $timezone = $this->resolveTimezone($business);
        $date = (string) $data['date'];
        $start = $this->parseStartTime($data['start_time'] ?? null, $timezone);
        $end = $start->copy()->addMinutes((int) $service->duration_min + (int) $service->buffer_min);
        $startsAt = $this->combineDateAndTime($date, $start, $timezone);

        $this->assertBookingWindow($startsAt, $business);

        $this->assertWithinStaffSchedule(
            businessId: $businessId,
            staffId: $staff->id,
            date: $date,
            start: $start,
            end: $end,
            timezone: $timezone,
        );

        $this->assertSlotAvailable(
            businessId: $businessId,
            staffId: $staff->id,
            date: $date,
            start: $start,
            end: $end,
            ignoreBooking: $ignoreBooking,
        );

        $this->assertStaffIsAvailable(
            businessId: $businessId,
            staffId: $staff->id,
            date: $date,
            start: $start,
            end: $end,
        );

        return [
            'business_id' => $businessId,
            'service' => $service,
            'staff' => $staff,
            'date' => $date,
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'cancellation_expires_at' => $startsAt->copy()->subHours($business->cancellation_notice_hours),
        ];
    }

    private function resolveBusinessId(?Booking $ignoreBooking): int
    {
        $tenantBusinessId = TenantManager::id();
        $businessId = $ignoreBooking?->business_id ?? $tenantBusinessId;

        if (! $businessId) {
            throw ValidationException::withMessages([
                'business' => 'Le tenant actif est introuvable.',
            ]);
        }

        if ($ignoreBooking && $tenantBusinessId && (int) $ignoreBooking->business_id !== (int) $tenantBusinessId) {
            throw ValidationException::withMessages([
                'business' => 'La réservation sélectionnée est invalide pour ce business.',
            ]);
        }

        return (int) $businessId;
    }

    private function assertBookingWindow(Carbon $startsAt, Business $business): void
    {
        $now = Carbon::now($startsAt->getTimezone());

        if (! $business->is_booking_enabled) {
            throw ValidationException::withMessages([
                'business' => 'La réservation en ligne est désactivée pour ce business.',
            ]);
        }

        if ($startsAt->lte($now)) {
            throw ValidationException::withMessages([
                'start_time' => 'Le créneau doit être dans le futur.',
            ]);
        }

        if ($startsAt->lt($now->copy()->addMinutes($business->booking_min_notice_minutes))) {
            throw ValidationException::withMessages([
                'start_time' => 'Ce créneau ne respecte pas le délai minimal de réservation.',
            ]);
        }

        if ($startsAt->gt($now->copy()->addDays($business->booking_max_advance_days)->endOfDay())) {
            throw ValidationException::withMessages([
                'date' => 'Cette date dépasse l’horizon de réservation autorisé.',
            ]);
        }
    }

    private function assertTenantIntegrity(Service $service, Staff $staff, int $businessId): void
    {
        if ((int) $service->business_id !== $businessId) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est invalide pour ce business.',
            ]);
        }

        if ((int) $staff->business_id !== $businessId) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est invalide pour ce business.',
            ]);
        }
    }

    private function assertActiveCatalog(Service $service, Staff $staff): void
    {
        if (! $service->is_active) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est indisponible.',
            ]);
        }

        if (! $staff->is_active) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est indisponible.',
            ]);
        }
    }

    private function assertStaffOffersService(Service $service, Staff $staff): void
    {
        if (! $staff->services()->whereKey($service->id)->exists()) {
            throw ValidationException::withMessages([
                'staff_id' => 'Ce prestataire ne propose pas le service sélectionné.',
            ]);
        }
    }

    private function assertWithinStaffSchedule(int $businessId, int $staffId, string $date, Carbon $start, Carbon $end, string $timezone): void
    {
        $dayOfWeek = Carbon::createFromFormat('Y-m-d', $date, $timezone)->dayOfWeek;

        $hasMatchingSchedule = StaffSchedule::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->where('staff_id', $staffId)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'))
            ->exists();

        if (! $hasMatchingSchedule) {
            throw ValidationException::withMessages([
                'start_time' => 'Ce créneau est hors horaires du prestataire.',
            ]);
        }
    }

    private function assertSlotAvailable(int $businessId, int $staffId, string $date, Carbon $start, Carbon $end, ?Booking $ignoreBooking = null): void
    {
        $collision = Booking::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->where('staff_id', $staffId)
            ->where('date', $date)
            ->where('status', '!=', 'canceled')
            ->when($ignoreBooking, fn ($query) => $query->whereKeyNot($ignoreBooking->getKey()))
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end->format('H:i:s'))
                    ->where('end_time', '>', $start->format('H:i:s'));
            })
            ->exists();

        if ($collision) {
            throw ValidationException::withMessages([
                'start_time' => 'Ce créneau n’est plus disponible.',
            ]);
        }
    }

    private function assertStaffIsAvailable(int $businessId, int $staffId, string $date, Carbon $start, Carbon $end): void
    {
        $timeOff = StaffTimeOff::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->where('staff_id', $staffId)
            ->where('date', $date)
            ->where(function ($query) use ($start, $end): void {
                $query->where(function ($fullDay): void {
                    $fullDay->whereNull('start_time')->whereNull('end_time');
                })->orWhere(function ($partial) use ($start, $end): void {
                    $partial->where('start_time', '<', $end->format('H:i:s'))
                        ->where('end_time', '>', $start->format('H:i:s'));
                });
            })
            ->exists();

        if ($timeOff) {
            throw ValidationException::withMessages([
                'start_time' => 'Le prestataire est indisponible pendant ce créneau.',
            ]);
        }
    }

    private function resolveTimezone(Business $business): string
    {
        $timezone = (string) $business->timezone;

        if ($timezone === '') {
            $timezone = (string) config('app.timezone', 'UTC');
        }

        try {
            new DateTimeZone($timezone);
        } catch (Throwable) {
            return (string) config('app.timezone', 'UTC');
        }

        return $timezone;
    }

    private function combineDateAndTime(string $date, Carbon $time, string $timezone): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', sprintf('%s %s', $date, $time->format('H:i:s')), $timezone);
    }

    private function parseStartTime(mixed $rawStartTime, string $timezone): Carbon
    {
        if ($rawStartTime instanceof CarbonInterface) {
            return Carbon::instance($rawStartTime->toDateTimeImmutable())
                ->setTimezone($timezone);
        }

        if ($rawStartTime instanceof DateTimeInterface) {
            return Carbon::instance($rawStartTime)
                ->setTimezone($timezone);
        }

        if (! is_string($rawStartTime)) {
            throw ValidationException::withMessages([
                'start_time' => 'Le format de l’heure de début est invalide.',
            ]);
        }

        $startTime = trim($rawStartTime);

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                $start = Carbon::createFromFormat($format, $startTime, $timezone);

                if ($start !== false) {
                    return $start;
                }
            } catch (Throwable) {
            }
        }

        throw ValidationException::withMessages([
            'start_time' => 'Le format de l’heure de début est invalide.',
        ]);
    }
}
