<?php

namespace App\Domain\Booking\Actions;

use App\Core\Tenancy\TenantManager;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use App\Notifications\BusinessBookingCreated;
use App\Notifications\CustomerBookingConfirmed;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateBookingAction
{
    public function run(array $data): Booking
    {
        $booking = DB::transaction(function () use ($data) {
            $service = Service::query()->findOrFail($data['service_id']);
            $staff = Staff::query()->findOrFail($data['staff_id']);

            $this->assertTenantIntegrity($service, $staff);

            $duration = (int) $service->duration_min + (int) $service->buffer_min;

            $timezone = $this->resolveTimezone();
            $start = $this->parseStartTime($data['start_time'], $timezone);
            $end = $start->copy()->addMinutes($duration);

            $collision = Booking::query()
                ->where('staff_id', $data['staff_id'])
                ->where('date', $data['date'])
                ->where('status', 'confirmed')
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end->format('H:i:s'))
                        ->where('end_time', '>', $start->format('H:i:s'));
                })
                ->exists();

            if ($collision) {
                throw ValidationException::withMessages([
                    'start_time' => 'Ce créneau n’est plus disponible.',
                ]);
            }

            return Booking::query()->create([
                'service_id' => $data['service_id'],
                'staff_id' => $staff->id,
                'date' => $data['date'],
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
            ]);
        });

        $booking->loadMissing(['service', 'staff', 'business']);
        $this->sendCreatedNotifications($booking);

        return $booking;
    }

    private function assertTenantIntegrity(Service $service, Staff $staff): void
    {
        $tenantId = TenantManager::id();

        if (! $tenantId) {
            throw ValidationException::withMessages([
                'business' => 'Le tenant actif est introuvable.',
            ]);
        }

        if ((int) $service->business_id !== $tenantId) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est invalide pour ce business.',
            ]);
        }

        if ((int) $staff->business_id !== $tenantId) {
            throw ValidationException::withMessages([
                'staff_id' => 'L’employé sélectionné est invalide pour ce business.',
            ]);
        }
    }

    private function sendCreatedNotifications(Booking $booking): void
    {
        $businessEmail = $booking->business?->email;

        if (is_string($businessEmail) && $businessEmail !== '') {
            Notification::route('mail', $businessEmail)
                ->notify(new BusinessBookingCreated($booking));
        }

        if (is_string($booking->customer_email) && $booking->customer_email !== '') {
            Notification::route('mail', $booking->customer_email)
                ->notify(new CustomerBookingConfirmed($booking));
        }
    }

    private function resolveTimezone(): string
    {
        $timezone = (string) TenantManager::timezone();

        try {
            new DateTimeZone($timezone);
        } catch (Throwable) {
            return (string) config('app.timezone', 'UTC');
        }

        return $timezone;
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
        $formats = ['H:i:s', 'H:i'];

        foreach ($formats as $format) {
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
