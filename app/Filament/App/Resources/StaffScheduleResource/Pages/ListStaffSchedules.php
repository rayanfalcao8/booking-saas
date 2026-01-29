<?php

namespace App\Filament\App\Resources\StaffScheduleResource\Pages;

use App\Filament\App\Resources\StaffScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffSchedules extends ListRecords
{
    protected static string $resource = StaffScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
