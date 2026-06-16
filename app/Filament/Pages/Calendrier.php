<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Availability;
use App\Models\Booking;
use Filament\Pages\Page;

class Calendrier extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Réservations';
    protected static ?string $navigationLabel = 'Calendrier';
    protected static ?string $title = 'Calendrier des réservations';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.calendrier';

    /** Événements pour FullCalendar : réservations + blocages d'entretien. */
    public function getEvents(): array
    {
        $events = [];

        $statusColors = [
            'pending' => '#f59e0b',
            'confirmed' => '#006233',
            'active' => '#16a34a',
            'returning' => '#f59e0b',
            'completed' => '#6b7280',
            'dispute' => '#D21034',
        ];

        $bookings = Booking::with(['vehicle', 'customer'])
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->get();

        foreach ($bookings as $b) {
            $events[] = [
                'title' => trim(($b->vehicle?->full_name ?? 'Véhicule') . ' — ' . ($b->customer?->full_name ?? 'Client')),
                'start' => $b->start_date?->toIso8601String(),
                'end' => $b->end_date?->toIso8601String(),
                'url' => BookingResource::getUrl('edit', ['record' => $b]),
                'color' => $statusColors[$b->status] ?? '#6b7280',
            ];
        }

        foreach (Availability::with('vehicle')->get() as $a) {
            $events[] = [
                'title' => '🔧 ' . ($a->vehicle?->full_name ?? 'Véhicule') . ' (' . ($a->reason ?: 'indisponible') . ')',
                'start' => $a->start_date?->toDateString(),
                'end' => $a->end_date?->copy()->addDay()->toDateString(),
                'color' => '#9ca3af',
                'display' => 'block',
            ];
        }

        return $events;
    }
}
