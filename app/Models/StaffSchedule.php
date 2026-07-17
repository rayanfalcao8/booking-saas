<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use App\Core\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (StaffSchedule $schedule): void {
            $schedule->guardBusinessIntegrity();
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    private function guardBusinessIntegrity(): void
    {
        if (empty($this->business_id) && TenantManager::id()) {
            $this->business_id = TenantManager::id();
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

        if ((string) $this->end_time <= (string) $this->start_time) {
            throw ValidationException::withMessages([
                'end_time' => 'La fin doit être après le début.',
            ]);
        }

        $hasOverlap = self::withoutGlobalScopes()
            ->where('business_id', $this->business_id)
            ->where('staff_id', $this->staff_id)
            ->where('day_of_week', $this->day_of_week)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->where('start_time', '<', $this->end_time)
            ->where('end_time', '>', $this->start_time)
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_time' => 'Cet horaire chevauche une plage existante pour ce prestataire.',
            ]);
        }
    }
}
