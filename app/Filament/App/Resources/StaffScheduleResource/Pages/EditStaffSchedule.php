<?php

namespace App\Filament\App\Resources\StaffScheduleResource\Pages;

use App\Filament\App\Resources\StaffScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffSchedule extends EditRecord
{
    protected static string $resource = StaffScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
