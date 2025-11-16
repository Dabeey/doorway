<?php

namespace App\Filament\Widgets;
use App\Models\Property;
use App\Models\Enquiry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            stat::make('Number of Properties',Property::count()),
            stat::make('Number of Enquiries',Enquiry::count()),
            stat::make('Number of Available Properties',Property::where('status', 'available')->count()),
            stat::make('Number of Rented Properties',Property::where('status', 'rented')->count()),
            stat::make('Number of Sold Properties',Property::where('status', 'sold')->count()),

        ];
    }
}
