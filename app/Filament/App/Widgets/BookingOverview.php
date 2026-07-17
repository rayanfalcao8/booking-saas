<?php

namespace App\Filament\App\Widgets;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                "Rendez-vous aujourd'hui",
                Booking::query()->whereDate('date', today())->count()
            )
                ->description('Tous les statuts')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make(
                'Rendez-vous à venir',
                Booking::query()
                    ->whereDate('date', '>=', today())
                    ->whereNotIn('status', ['canceled', 'completed'])
                    ->count()
            )
                ->description('À préparer')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Services actifs', Service::query()->where('is_active', true)->count())
                ->description(Staff::query()->where('is_active', true)->count().' collaborateurs actifs')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('gray'),
        ];
    }
}
