<?php

namespace App\Domain\Booking\Actions;

use App\Models\Booking;
use App\Models\Service;
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
                'staff_id' => $data['staff_id'],
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
        $timezone = (string) \App\Core\Tenancy\TenantManager::timezone();

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
