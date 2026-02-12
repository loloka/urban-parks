<?php

namespace App\Filament\Widgets;

use App\Models\Park;
use App\Models\Activation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Всего парков', Park::count())
                ->description('Активных: ' . Park::active()->count())
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('success'),

            Stat::make('Всего активаций', Activation::count())
                ->description('За последние 30 дней: ' . Activation::where('activation_date', '>=', now()->subDays(30))->count())
                ->descriptionIcon('heroicon-m-signal')
                ->color('primary'),

            Stat::make('Уникальных активаторов', Activation::distinct('callsign')->count('callsign'))
                ->description('Всего позывных')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Городов', Park::distinct()->count('city'))
                ->description('Регионов: ' . Park::distinct()->count('region'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }
}
