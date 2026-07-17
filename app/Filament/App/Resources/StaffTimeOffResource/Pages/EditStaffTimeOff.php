<?php

namespace App\Filament\App\Resources\StaffTimeOffResource\Pages;

use App\Filament\App\Resources\StaffTimeOffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffTimeOff extends EditRecord
{
    protected static string $resource = StaffTimeOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
