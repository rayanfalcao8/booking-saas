<?php

namespace App\Domain\Booking\Services;

use App\Domain\Booking\DTO\AvailabilityQuery;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffSchedule;
use App\Models\StaffTimeOff;
use Carbon\Carbon;

class AvailabilityService
{
    public function slots(AvailabilityQuery $q): array
    {
        $business = Business::query()->findOrFail(\App\Core\Tenancy\TenantManager::id());

        if (! $business->is_booking_enabled) {
            return [];
        }

        $service = Service::query()->findOrFail($q->serviceId);

        if (! $service->is_active) {
            return [];
        }

        $staff = Staff::query()->findOrFail($q->staffId);

        if (! $staff->is_active) {
            return [];
        }

        if (! $staff->services()->whereKey($service->id)->exists()) {
            return [];
        }

        $duration = (int) $service->duration_min + (int) $service->buffer_min;

        $tz = \App\Core\Tenancy\TenantManager::timezone();
        $date = Carbon::createFromFormat('Y-m-d', $q->date, $tz);
        $now = Carbon::now($tz);

        if ($date->copy()->startOfDay()->lt($now->copy()->startOfDay())) {
            return [];
        }

        if ($date->copy()->startOfDay()->gt($now->copy()->addDays($business->booking_max_advance_days)->endOfDay())) {
            return [];
        }

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
            ->where('status', '!=', 'canceled')
            ->get(['start_time', 'end_time']);

        $busy = $existing->map(fn ($b) => [
            'start' => Carbon::createFromFormat('H:i:s', $b->start_time, $tz),
            'end' => Carbon::createFromFormat('H:i:s', $b->end_time, $tz),
        ])->all();

        $timeOff = StaffTimeOff::query()
            ->where('staff_id', $q->staffId)
            ->where('date', $q->date)
            ->get();

        if ($timeOff->contains(fn (StaffTimeOff $absence): bool => $absence->isFullDay())) {
            return [];
        }

        foreach ($timeOff as $absence) {
            $busy[] = [
                'start' => Carbon::createFromFormat('H:i:s', $absence->start_time, $tz),
                'end' => Carbon::createFromFormat('H:i:s', $absence->end_time, $tz),
            ];
        }

        $slots = [];

        foreach ($schedules as $sch) {
            $start = Carbon::createFromFormat('H:i:s', $sch->start_time, $tz);
            $end = Carbon::createFromFormat('H:i:s', $sch->end_time, $tz);

            $cursor = $start->copy();

            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);
                $slotStartsAt = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    sprintf('%s %s', $q->date, $slotStart->format('H:i:s')),
                    $tz,
                );

                $earliestBookingAt = $now->copy()->addMinutes($business->booking_min_notice_minutes);

                if ($slotStartsAt->gte($earliestBookingAt) && ! $this->overlapsBusy($slotStart, $slotEnd, $busy)) {
                    $slots[] = $slotStart->format('H:i');
                }

                $cursor->addMinutes($business->slot_interval_minutes);
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
