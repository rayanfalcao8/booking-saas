<?php

namespace App\Models;

use App\Core\Tenancy\Concerns\BelongsToBusiness;
use App\Core\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StaffTimeOff extends Model
{
    use BelongsToBusiness;

    protected $table = 'staff_time_off';

    protected $fillable = [
        'business_id',
        'staff_id',
        'date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected static function booted(): void
    {
        static::saving(function (StaffTimeOff $timeOff): void {
            $timeOff->guardBusinessIntegrity();
            $timeOff->guardTimeRange();
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

    public function isFullDay(): bool
    {
        return $this->start_time === null && $this->end_time === null;
    }

    private function guardBusinessIntegrity(): void
    {
        if (empty($this->business_id) && TenantManager::id()) {
            $this->business_id = TenantManager::id();
        }

        $staff = Staff::withoutGlobalScopes()->find($this->staff_id);

        if (! $staff || (int) $staff->business_id !== (int) $this->business_id) {
            throw ValidationException::withMessages([
                'staff_id' => 'Le prestataire sélectionné est invalide pour ce business.',
            ]);
        }
    }

    private function guardTimeRange(): void
    {
        $hasStart = filled($this->start_time);
        $hasEnd = filled($this->end_time);

        if ($hasStart !== $hasEnd) {
            throw ValidationException::withMessages([
                'start_time' => 'Indiquez une heure de début et de fin, ou laissez les deux champs vides.',
            ]);
        }

        if ($hasStart && (string) $this->end_time <= (string) $this->start_time) {
            throw ValidationException::withMessages([
                'end_time' => 'La fin doit être après le début.',
            ]);
        }
    }
}
