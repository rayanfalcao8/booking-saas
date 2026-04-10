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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'canceled_at' => 'datetime',
        ];
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
