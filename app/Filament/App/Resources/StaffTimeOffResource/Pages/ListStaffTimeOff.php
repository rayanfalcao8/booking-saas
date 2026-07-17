<?php

namespace App\Filament\App\Resources\StaffTimeOffResource\Pages;

use App\Filament\App\Resources\StaffTimeOffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffTimeOff extends ListRecords
{
    protected static string $resource = StaffTimeOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nouvelle absence'),
        ];
    }
}
