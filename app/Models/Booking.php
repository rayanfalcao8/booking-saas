<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use App\Core\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (Booking $booking): void {
            $booking->guardBusinessIntegrity();
        });
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
        return $this->belongsTo(Business::class);
    }

    private function guardBusinessIntegrity(): void
    {
        if (empty($this->business_id) && TenantManager::id()) {
            $this->business_id = TenantManager::id();
        }

        $service = Service::withoutGlobalScopes()->find($this->service_id);

        if (! $service) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est introuvable.',
            ]);
        }

        if ((int) $service->business_id !== (int) $this->business_id) {
            throw ValidationException::withMessages([
                'service_id' => 'Le service sélectionné est invalide pour ce business.',
            ]);
        }

        $staff = Staff::withoutGlobalScopes()->find($this->staff_id);

        if (! $staff) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est introuvable.',
            ]);
        }

        if ((int) $staff->business_id !== (int) $this->business_id) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est invalide pour ce business.',
            ]);
        }
    }
}
