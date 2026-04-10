<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'service_id',
        'staff_id',
        'date',
        'start_time',
        'end_time',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'cancellation_token',
        'canceled_at',
        'cancellation_expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'canceled_at' => 'datetime',
            'cancellation_expires_at' => 'datetime',
        ];
    }


    public function isCancellationTokenValid(string $token): bool
    {
        if ($this->cancellation_token === null || ! hash_equals((string) $this->cancellation_token, $token)) {
            return false;
        }

        if ($this->cancellation_expires_at !== null && now()->greaterThan($this->cancellation_expires_at)) {
            return false;
        }

        return true;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}
