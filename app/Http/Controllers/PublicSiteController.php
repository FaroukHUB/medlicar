<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Option;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PublicSiteController extends Controller
{
    /** Statuts qui occupent réellement un véhicule. */
    private const BLOCKING_STATUSES = ['pending', 'confirmed', 'active', 'returning'];

    /** Accueil : catalogue des véhicules disponibles, avec filtres. */
    public function index(Request $request)
    {
        $query = Vehicle::with(['brand', 'category'])
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('price_min'), fn ($q) => $q->where('price_per_day', '>=', $request->integer('price_min')))
            ->when($request->filled('price_max'), fn ($q) => $q->where('price_per_day', '<=', $request->integer('price_max')))
            ->orderBy('sort_order');

        $vehicles = $query->get();

        // Filtre par disponibilité sur une période (optionnel).
        $start = $request->filled('start_date') ? Carbon::parse($request->date('start_date'))->startOfDay() : null;
        $end = $request->filled('end_date') ? Carbon::parse($request->date('end_date'))->startOfDay() : null;
        if ($start && $end && $end->gt($start)) {
            $vehicles = $vehicles->filter(fn (Vehicle $v) => $v->isAvailableBetween($start, $end))->values();
        }

        return view('public.index', [
            'agency' => Agency::current(),
            'vehicles' => $vehicles,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['category_id', 'price_min', 'price_max', 'start_date', 'end_date']),
        ]);
    }

    /** Fiche véhicule avec sélecteur de dates et options. */
    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['brand', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('public.show', [
            'agency' => Agency::current(),
            'vehicle' => $vehicle,
            'options' => Option::where('is_active', true)->orderBy('sort_order')->get(),
            'bookedRanges' => $this->bookedRanges($vehicle),
        ]);
    }

    /** JSON des plages indisponibles (réservations + entretiens) pour le calendrier. */
    public function availability(Vehicle $vehicle)
    {
        return response()->json($this->bookedRanges($vehicle));
    }

    /** Enregistre une demande de réservation depuis le site public. */
    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'options' => ['nullable', 'array'],
            'options.*' => ['integer', 'exists:options,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $vehicle = Vehicle::where('is_active', true)->findOrFail($data['vehicle_id']);
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();

        // Re-vérification serveur anti double-réservation (le calendrier client ne suffit pas).
        if (! $vehicle->isAvailableBetween($start, $end)) {
            return back()->withInput()->withErrors([
                'start_date' => 'Désolé, ce véhicule n\'est plus disponible sur ces dates. Choisissez une autre période.',
            ]);
        }

        // Client existant (par téléphone/email) ou création.
        $customer = Customer::query()
            ->where('phone', $data['phone'])
            ->when(! empty($data['email']), fn ($q) => $q->orWhere('email', $data['email']))
            ->first();

        if (! $customer) {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
            ]);
        }

        $days = max(1, (int) ceil($start->floatDiffInDays($end)));
        $base = $days * (float) $vehicle->price_per_day;

        // Options choisies (par jour ou forfait).
        $optionIds = $data['options'] ?? [];
        $optionsTotal = 0;
        if (! empty($optionIds)) {
            foreach (Option::whereIn('id', $optionIds)->where('is_active', true)->get() as $opt) {
                $optionsTotal += $opt->price_type === 'per_day' ? (float) $opt->price * $days : (float) $opt->price;
            }
        }

        $total = $base + $optionsTotal;
        $agency = Agency::current();
        $advancePct = (float) ($agency->default_advance_percent ?? 0);

        $booking = Booking::create([
            'reference' => Booking::generateReference(),
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $days,
            'base_price' => $base,
            'selected_options' => array_map('intval', $optionIds),
            'options_total' => $optionsTotal,
            'discount_amount' => 0,
            'total_price' => $total,
            'deposit_amount' => (float) $vehicle->deposit_amount,
            'advance_amount' => round($total * $advancePct / 100),
            'advance_status' => 'pending',
            'payment_status' => 'pending',
            'deposit_status' => 'pending',
            'amount_remaining' => $total,
            'status' => 'pending',
            'source' => 'website',
            'advance_expires_at' => now()->addHours((int) ($agency->advance_expiry_hours ?? 48)),
            'internal_notes' => $data['message'] ?? null,
        ]);

        $this->notifyAgency($booking, $agency);

        return redirect()->route('public.confirmation', $booking->reference);
    }

    /** Page de confirmation. */
    public function confirmation(string $reference)
    {
        $booking = Booking::with('vehicle')->where('reference', $reference)->firstOrFail();

        return view('public.confirmation', [
            'agency' => Agency::current(),
            'booking' => $booking,
        ]);
    }

    /** Prévient l'agence d'une nouvelle demande (notification admin + email best-effort). */
    private function notifyAgency(Booking $booking, Agency $agency): void
    {
        $title = 'Nouvelle réservation en ligne';
        $body = "{$booking->reference} — {$booking->vehicle?->full_name} · "
            . "{$booking->start_date?->format('d/m/Y')} → {$booking->end_date?->format('d/m/Y')} · "
            . number_format($booking->total_price, 0, ',', ' ') . ' DA';

        // Notification dans l'admin (cloche Filament). notifyNow = synchrone,
        // indispensable car la queue par défaut est "database" (pas de worker garanti).
        try {
            $databaseNotification = Notification::make()
                ->title($title)
                ->body($body)
                ->success()
                ->icon('heroicon-o-globe-alt')
                ->toDatabase();

            User::all()->each(fn (User $user) => $user->notifyNow($databaseNotification));
        } catch (\Throwable $e) {
            Log::warning('Notification admin échouée: ' . $e->getMessage());
        }

        // Email à l'agence (best-effort : n'interrompt jamais la réservation).
        if (! empty($agency->email)) {
            try {
                Mail::raw($title . "\n\n" . $body, function ($m) use ($agency, $title) {
                    $m->to($agency->email)->subject($title);
                });
            } catch (\Throwable $e) {
                Log::warning('Email agence échoué: ' . $e->getMessage());
            }
        }
    }

    /** Plages [from,to] indisponibles d'un véhicule. */
    private function bookedRanges(Vehicle $vehicle): array
    {
        $ranges = [];

        foreach ($vehicle->bookings()->whereIn('status', self::BLOCKING_STATUSES)->get() as $b) {
            $ranges[] = [
                'from' => $b->start_date?->toDateString(),
                'to' => $b->end_date?->toDateString(),
            ];
        }

        foreach ($vehicle->availabilities()->get() as $a) {
            $ranges[] = [
                'from' => $a->start_date?->toDateString(),
                'to' => $a->end_date?->toDateString(),
            ];
        }

        return $ranges;
    }
}
