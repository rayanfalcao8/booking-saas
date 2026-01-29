<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use BelongsToBusiness;
    
    protected $fillable = [
        'business_id',
        'staff_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];
}
