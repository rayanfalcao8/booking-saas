<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'name',
        'duration_min',
        'buffer_min',
        'is_active',
    ];
    
     protected $casts = [
        'is_active' => 'boolean',
    ];
}
