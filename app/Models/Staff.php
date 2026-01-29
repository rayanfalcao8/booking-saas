<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'name',
        'email',
        'is_active',
    ];
}
