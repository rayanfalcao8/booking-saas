<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use BelongsToBusiness;

    protected $table = 'feedback';

    protected $fillable = [
        'business_id',
        'user_id',
        'category',
        'rating',
        'message',
        'contact_email',
        'status',
        'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
