<?php

namespace App\Filament\Pages;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Calendrier extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Réservations';
    protected static ?string $navigationLabel = 'Calendrier';
    protected static ?string $title = 'Calendrier de disponibilité';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.calendrier';

    /** Statuts de réservation qui occupent le véhicule. */
    private const BLOCKING_STATUSES = ['pending', 'confirmed', 'active', 'returning'];

    public ?int $vehicleId = null;

    /** Premier jour du mois affiché (format Y-m-d). */
    public string $cursor;

    public function mount(): void
    {
        $this->vehicleId = Vehicle::where('is_active', true)->orderBy('sort_order')->value('id');
        $this->cursor = now()->startOfMonth()->toDateString();
    }

    /** Liste des véhicules pour le sélecteur. */
    public function getVehiclesProperty(): Collection
    {
        return Vehicle::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function getSelectedVehicleProperty(): ?Vehicle
    {
        return $this->vehicleId ? Vehicle::find($this->vehicleId) : null;
    }

    public function previousMonth(): void
    {
        $this->cursor = Carbon::parse($this->cursor)->subMonth()->startOfMonth()->toDateString();
    }

    public function nextMonth(): void
    {
        $this->cursor = Carbon::parse($this->cursor)->addMonth()->startOfMonth()->toDateString();
    }

    public function goToday(): void
    {
        $this->cursor = now()->startOfMonth()->toDateString();
    }

    /** Clic sur un jour : bloque ou débloque (uniquement les jours libres / bloqués manuellement, non passés). */
    public function toggleDay(string $date): void
    {
        if (! $this->vehicleId) {
            return;
        }

        $day = Carbon::parse($date)->startOfDay();
        if ($day->lt(now()->startOfDay())) {
            return; // pas de modification du passé
        }

        $vehicle = Vehicle::find($this->vehicleId);

        // Jour réservé ou en maintenance : non modifiable ici.
        $reserved = $vehicle->bookings()
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('start_date', '<=', $day)
            ->where('end_date', '>', $day)
            ->exists();

        $maintenance = $vehicle->availabilities()
            ->whereNull('booking_id')->where('type', 'maintenance')
            ->where('start_date', '<=', $day)->where('end_date', '>', $day)
            ->exists();

        if ($reserved || $maintenance) {
            return;
        }

        // Blocage manuel existant couvrant ce jour ?
        $blocks = $vehicle->availabilities()
            ->whereNull('booking_id')->where('type', '!=', 'maintenance')
            ->where('start_date', '<=', $day)->where('end_date', '>', $day)
            ->get();

        if ($blocks->isNotEmpty()) {
            $blocks->each->delete(); // débloquer
        } else {
            $vehicle->availabilities()->create([
                'start_date' => $day->toDateString(),
                'end_date' => $day->copy()->addDay()->toDateString(), // borne de fin exclusive
                'type' => 'blocked',
                'reason' => 'Bloqué manuellement',
            ]);
        }
    }

    /** Données du calendrier (semaines + stats) pour la vue. */
    public function getCalendarData(): array
    {
        $monthStart = Carbon::parse($this->cursor)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $today = now()->startOfDay();

        $bookings = collect();
        $blocks = collect();
        $maintenances = collect();

        if ($this->vehicleId) {
            $vehicle = Vehicle::find($this->vehicleId);

            $bookings = $vehicle->bookings()
                ->whereIn('status', self::BLOCKING_STATUSES)
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>', $monthStart)
                ->get(['start_date', 'end_date']);

            $availabilities = $vehicle->availabilities()
                ->whereNull('booking_id')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>', $monthStart)
                ->get(['start_date', 'end_date', 'type']);

            $maintenances = $availabilities->where('type', 'maintenance');
            $blocks = $availabilities->where('type', '!=', 'maintenance');
        }

        $covers = fn (Collection $ranges, Carbon $d) => $ranges->contains(
            fn ($r) => Carbon::parse($r->start_date)->lte($d) && Carbon::parse($r->end_date)->gt($d)
        );

        $stats = ['available' => 0, 'blocked' => 0, 'reserved' => 0];
        $cells = [];

        // Cases vides avant le 1er (semaine commençant lundi).
        $lead = ($monthStart->dayOfWeekIso) - 1; // 0..6
        for ($i = 0; $i < $lead; $i++) {
            $cells[] = null;
        }

        foreach (CarbonPeriod::create($monthStart, $monthEnd) as $d) {
            $isPast = $d->lt($today);
            $isToday = $d->isSameDay($today);

            if ($covers($bookings, $d)) {
                $status = 'reserved';
                $stats['reserved']++;
            } elseif ($covers($maintenances, $d)) {
                $status = 'maintenance';
            } elseif ($covers($blocks, $d)) {
                $status = 'blocked';
                $stats['blocked']++;
            } elseif ($isPast) {
                $status = 'past';
            } else {
                $status = 'available';
                $stats['available']++;
            }

            $cells[] = [
                'date' => $d->toDateString(),
                'day' => $d->day,
                'status' => $status,
                'isToday' => $isToday,
                'clickable' => ! $isPast && in_array($status, ['available', 'blocked'], true),
            ];
        }

        // Découpage en semaines de 7.
        $weeks = array_chunk($cells, 7);

        return [
            'monthLabel' => ucfirst($monthStart->locale('fr')->isoFormat('MMMM YYYY')),
            'weeks' => $weeks,
            'stats' => $stats,
        ];
    }
}
