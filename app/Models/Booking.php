<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Core\Tenancy\Concerns\BelongsToBusiness;

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
        'notes',
    ];
}
