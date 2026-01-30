<?php

namespace App\Filament\App\Resources\StaffScheduleResource\Pages;

use App\Filament\App\Resources\StaffScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffSchedule extends CreateRecord
{
    protected static string $resource = StaffScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['end_time'] <= $data['start_time']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_time' => 'La fin doit être après le début.',
            ]);
        }

        return $data;
    }
}
