<?php

namespace App\Domain\Booking\Actions;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CreateBookingAction
{
    public function run(array $data): Booking
    {
        return DB::transaction(function () use ($data) {

            $service = Service::query()->findOrFail($data['service_id']);
            $duration = (int) $service->duration_min + (int) $service->buffer_min;

            $start = Carbon::createFromFormat('H:i', $data['start_time']);
            $end = $start->copy()->addMinutes($duration);

            // Check collision confirmed bookings
            $collision = Booking::query()
                ->where('staff_id', $data['staff_id'])
                ->where('date', $data['date'])
                ->where('status', 'confirmed')
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end->format('H:i:s'))
                      ->where('end_time',   '>', $start->format('H:i:s'));
                })
                ->exists();

            if ($collision) {
                throw ValidationException::withMessages([
                    'start_time' => 'Ce créneau n’est plus disponible.',
                ]);
            }

            return Booking::create([
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
    }
}
