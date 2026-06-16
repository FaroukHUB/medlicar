<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = now();

        $caMois = Booking::whereIn('status', ['confirmed', 'active', 'returning', 'completed'])
            ->whereYear('start_date', $now->year)
            ->whereMonth('start_date', $now->month)
            ->sum('total_price');

        $enCours = Booking::where('status', 'active')->count();

        $aVenir = Booking::whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('start_date', [$now, (clone $now)->addDays(7)])
            ->count();

        $disponibles = Vehicle::where('status', 'available')->where('is_active', true)->count();
        $loues = Vehicle::where('status', 'rented')->count();
        $totalVehicules = Vehicle::where('is_active', true)->count();

        return [
            Stat::make('CA du mois', number_format($caMois, 0, ',', ' ') . ' DA')
                ->description('Réservations confirmées ce mois')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Locations en cours', $enCours)
                ->description('Véhicules actuellement loués')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning'),

            Stat::make('À venir (7 jours)', $aVenir)
                ->description('Départs prévus cette semaine')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Véhicules disponibles', $disponibles . ' / ' . $totalVehicules)
                ->description('Prêts à la location')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
