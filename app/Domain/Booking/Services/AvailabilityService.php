<?php

namespace App\Domain\Booking\Services;

use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Models\Booking;
use App\Models\Service;
use App\Models\StaffSchedule;
use Carbon\Carbon;

class AvailabilityService
{
    public function slots(AvailabilityQuery $q): array
    {
        $service = Service::query()->findOrFail($q->serviceId);
        $duration = (int) $service->duration_min + (int) $service->buffer_min;

        $tz = \App\Core\Tenancy\TenantManager::timezone();
        $date = Carbon::createFromFormat('Y-m-d', $q->date, $tz);

        $dow = (int) $date->dayOfWeek;
        $schedules = StaffSchedule::query()
            ->where('staff_id', $q->staffId)
            ->where('day_of_week', $dow)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $existing = Booking::query()
            ->where('staff_id', $q->staffId)
            ->where('date', $q->date)
            ->where('status', 'confirmed')
            ->get(['start_time', 'end_time']);

        $busy = $existing->map(fn ($b) => [
            'start' => Carbon::createFromFormat('H:i:s', $b->start_time, $tz),
            'end' => Carbon::createFromFormat('H:i:s', $b->end_time, $tz),
        ])->all();

        $slots = [];

        foreach ($schedules as $sch) {
            $start = Carbon::createFromFormat('H:i:s', $sch->start_time, $tz);
            $end = Carbon::createFromFormat('H:i:s', $sch->end_time, $tz);

            $cursor = $start->copy();

            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if (! $this->overlapsBusy($slotStart, $slotEnd, $busy)) {
                    $slots[] = $slotStart->format('H:i');
                }

                $cursor->addMinutes($q->stepMin);
            }
        }

        $slots = array_values(array_unique($slots));
        sort($slots);

        return $slots;
    }

    private function overlapsBusy(Carbon $slotStart, Carbon $slotEnd, array $busy): bool
    {
        foreach ($busy as $b) {
            if ($slotStart->lt($b['end']) && $slotEnd->gt($b['start'])) {
                return true;
            }
        }

        return false;
    }
}
