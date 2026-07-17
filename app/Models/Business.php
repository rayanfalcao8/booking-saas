<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'email',
        'phone',
        'is_booking_enabled',
        'booking_min_notice_minutes',
        'booking_max_advance_days',
        'slot_interval_minutes',
        'cancellation_notice_hours',
    ];

    protected function casts(): array
    {
        return [
            'is_booking_enabled' => 'boolean',
            'booking_min_notice_minutes' => 'integer',
            'booking_max_advance_days' => 'integer',
            'slot_interval_minutes' => 'integer',
            'cancellation_notice_hours' => 'integer',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function staffSchedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class);
    }

    public function staffTimeOff(): HasMany
    {
        return $this->hasMany(StaffTimeOff::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
