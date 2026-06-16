<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicSiteController extends Controller
{
    /** Statuts qui occupent réellement un véhicule. */
    private const BLOCKING_STATUSES = ['pending', 'confirmed', 'active', 'returning'];

    /** Accueil : catalogue des véhicules disponibles. */
    public function index()
    {
        $vehicles = Vehicle::with(['brand', 'category'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.index', [
            'agency' => Agency::current(),
            'vehicles' => $vehicles,
        ]);
    }

    /** Fiche véhicule avec sélecteur de dates. */
    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['brand', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('public.show', [
            'agency' => Agency::current(),
            'vehicle' => $vehicle,
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
            'options_total' => 0,
            'discount_amount' => 0,
            'total_price' => $base,
            'deposit_amount' => (float) $vehicle->deposit_amount,
            'advance_amount' => round($base * $advancePct / 100),
            'advance_status' => 'pending',
            'payment_status' => 'pending',
            'deposit_status' => 'pending',
            'amount_remaining' => $base,
            'status' => 'pending',
            'source' => 'website',
            'advance_expires_at' => now()->addHours((int) ($agency->advance_expiry_hours ?? 48)),
            'internal_notes' => $data['message'] ?? null,
        ]);

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
