<?php

namespace App\Filament\Widgets;

use App\Models\VisitEvent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $visits7 = VisitEvent::where('type', 'visit')->where('created_at', '>=', now()->subDays(7))->count();
        $visitsTotal = VisitEvent::where('type', 'visit')->count();
        $whatsapp7 = VisitEvent::where('type', 'whatsapp')->where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Visites (7 jours)', $visits7)->description('Pages publiques')->color('primary'),
            Stat::make('Visites (total)', $visitsTotal)->color('gray'),
            Stat::make('Clics WhatsApp (7 jours)', $whatsapp7)->description('Intérêt client')->color('success'),
        ];
    }
}
