<?php

namespace App\Filament\Resources\VehicleResource\Widgets;

use App\Models\Vehicle;
use Filament\Widgets\Widget;

class VehicleCalendarWidget extends Widget
{
    protected static string $view = 'filament.resources.vehicle.calendar-widget';

    protected int|string|array $columnSpan = 'full';

    /** Injecté automatiquement par la page d'édition de la ressource. */
    public ?Vehicle $record = null;

    /** Réservations + blocages de CE véhicule pour FullCalendar. */
    public function getEvents(): array
    {
        if (! $this->record) {
            return [];
        }

        $events = [];

        $statusColors = [
            'pending' => '#f59e0b',
            'confirmed' => '#006233',
            'active' => '#16a34a',
            'returning' => '#f59e0b',
            'completed' => '#6b7280',
            'dispute' => '#D21034',
        ];

        $bookings = $this->record->bookings()
            ->with('customer')
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->get();

        foreach ($bookings as $b) {
            $events[] = [
                'title' => ($b->reference ?? '') . ' — ' . ($b->customer?->full_name ?? 'Client'),
                'start' => $b->start_date?->toIso8601String(),
                'end' => $b->end_date?->toIso8601String(),
                'color' => $statusColors[$b->status] ?? '#6b7280',
            ];
        }

        foreach ($this->record->availabilities()->whereNull('booking_id')->get() as $a) {
            $events[] = [
                'title' => '🔧 ' . ($a->reason ?: 'Indisponible'),
                'start' => $a->start_date?->toDateString(),
                'end' => $a->end_date?->copy()->addDay()->toDateString(),
                'color' => '#9ca3af',
                'display' => 'block',
            ];
        }

        return $events;
    }
}
