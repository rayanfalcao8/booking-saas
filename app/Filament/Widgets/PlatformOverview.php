<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Business;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Entreprises', Business::query()->count())
                ->description('Espaces Reservix')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),
            Stat::make('Utilisateurs', User::query()->where('is_super_admin', false)->count())
                ->description('Gestionnaires clients')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Rendez-vous', Booking::withoutGlobalScopes()->count())
                ->description('Tous les espaces')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),
        ];
    }
}
