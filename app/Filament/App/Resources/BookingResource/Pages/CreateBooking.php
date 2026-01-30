<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Filament\App\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
}
