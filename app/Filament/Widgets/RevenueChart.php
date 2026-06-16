<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Chiffre d\'affaires (6 derniers mois)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);

            $labels[] = $month->translatedFormat('M Y');

            $values[] = (float) Booking::whereIn('status', ['confirmed', 'active', 'returning', 'completed'])
                ->whereYear('start_date', $month->year)
                ->whereMonth('start_date', $month->month)
                ->sum('total_price');
        }

        return [
            'datasets' => [
                [
                    'label' => 'CA (DA)',
                    'data' => $values,
                    'borderColor' => '#006233',
                    'backgroundColor' => 'rgba(0, 98, 51, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
