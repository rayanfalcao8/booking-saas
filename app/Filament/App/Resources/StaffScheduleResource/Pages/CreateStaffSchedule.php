<?php

namespace App\Filament\App\Resources\StaffScheduleResource\Pages;

use App\Filament\App\Resources\StaffScheduleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateStaffSchedule extends CreateRecord
{
    protected static string $resource = StaffScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => 'La fin doit être après le début.',
            ]);
        }

        return $data;
    }
}
