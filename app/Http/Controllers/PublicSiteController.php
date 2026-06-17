<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryLocation;
use App\Models\FaqItem;
use App\Models\Feature;
use App\Models\HeroSlide;
use App\Models\Option;
use App\Models\Review;
use App\Models\Stat;
use App\Services\PricingService;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\PayPalService;
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

    /** Accueil : hero slider + sections (Notre sélection + par catégorie). */
    public function index()
    {
        $vehicles = Vehicle::with(['brand', 'category', 'advantages'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Sections par catégorie (uniquement celles qui ont des véhicules).
        $sections = [];
        foreach (Category::orderBy('name')->get() as $category) {
            $list = $vehicles->where('category_id', $category->id)->values();
            if ($list->isNotEmpty()) {
                $sections[] = ['category' => $category, 'vehicles' => $list];
            }
        }

        $agency = Agency::current();

        return view('public.index', [
            'agency' => $agency,
            'heroSlides' => HeroSlide::where('is_active', true)->orderBy('sort_order')->get(),
            'featured' => $vehicles->where('is_featured', true)->values(),
            'sections' => $sections,
            'features' => $agency->section_why ? Feature::where('is_active', true)->orderBy('sort_order')->get() : collect(),
            'stats' => $agency->section_stats ? Stat::where('is_active', true)->orderBy('sort_order')->get() : collect(),
            'faqItems' => $agency->section_faq ? FaqItem::where('is_active', true)->orderBy('sort_order')->get() : collect(),
            'reviews' => $agency->section_reviews
                ? Review::with('customer')->where('is_published', true)->latest()->take(6)->get()
                : collect(),
        ]);
    }

    /** Fiche véhicule avec sélecteur de dates et options. */
    public function show(string $slug)
    {
        $vehicle = Vehicle::with(['brand', 'category', 'advantages'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('public.show', [
            'agency' => Agency::current(),
            'vehicle' => $vehicle,
            'options' => Option::where('is_active', true)->orderBy('sort_order')->get(),
            'deliveryLocations' => DeliveryLocation::where('is_active', true)->orderBy('sort_order')->get(),
            'bookedRanges' => $this->bookedRanges($vehicle),
        ]);
    }

    /** Page de réservation dédiée (tunnel complet). */
    public function book(string $slug)
    {
        $vehicle = Vehicle::with(['brand', 'category'])
            ->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('public.book', [
            'agency' => Agency::current(),
            'vehicle' => $vehicle,
            'options' => Option::where('is_active', true)->orderBy('sort_order')->get(),
            'deliveryLocations' => DeliveryLocation::where('is_active', true)->orderBy('sort_order')->get(),
            'bookedRanges' => $this->bookedRanges($vehicle),
        ]);
    }

    /** Calcul de prix en direct (AJAX) — applique tarification dynamique, options, livraison, protection, retour. */
    public function calculatePrice(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'options' => ['nullable', 'array'],
            'pickup_location_id' => ['nullable', 'integer'],
            'return_location_id' => ['nullable', 'integer'],
            'protection_plan' => ['nullable', 'in:basic,complete'],
            'return_fuel' => ['nullable', 'boolean'],
            'return_wash' => ['nullable', 'boolean'],
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $start = Carbon::parse($data['start_date'] . ' ' . ($data['start_time'] ?? '09:00'));
        $end = Carbon::parse($data['end_date'] . ' ' . ($data['end_time'] ?? '09:00'));
        if ($end->lte($start)) {
            return response()->json(['error' => 'Dates invalides'], 422);
        }

        $pickup = ! empty($data['pickup_location_id']) ? DeliveryLocation::find($data['pickup_location_id']) : null;
        $return = ! empty($data['return_location_id']) ? DeliveryLocation::find($data['return_location_id']) : null;

        $q = $this->computeQuote(
            $vehicle, $start, $end, $data['options'] ?? [], $pickup, $return,
            $data['protection_plan'] ?? 'basic', (bool) ($data['return_fuel'] ?? false), (bool) ($data['return_wash'] ?? false)
        );
        $q['available'] = $vehicle->isAvailableBetween($start, $end);

        return response()->json($q);
    }

    /** Page sécurisée d'envoi des documents par le client (permis, CNI). */
    public function documents(string $token)
    {
        $booking = Booking::with('customer', 'vehicle')->where('client_token', $token)->firstOrFail();

        return view('public.documents', ['agency' => Agency::current(), 'booking' => $booking]);
    }

    /** Réception des documents client. */
    public function storeDocuments(Request $request, string $token)
    {
        $booking = Booking::with('customer')->where('client_token', $token)->firstOrFail();

        $data = $request->validate([
            'license_front' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'license_back' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $customer = $booking->customer;
        foreach (['license_front', 'license_back', 'id_document'] as $field) {
            if ($request->hasFile($field)) {
                $customer->{$field} = $request->file($field)->store('customers', 'public');
            }
        }
        $customer->save();

        return back()->with('docs_success', true);
    }

    /**
     * Devis complet : prix dynamique + options + livraison + protection + frais de retour.
     */
    private function computeQuote(Vehicle $vehicle, Carbon $start, Carbon $end, array $optionIds, ?DeliveryLocation $pickup, ?DeliveryLocation $return, string $protectionPlan, bool $returnFuel, bool $returnWash): array
    {
        $pricing = (new PricingService)->quote($vehicle, $start, $end);
        $days = $pricing['days'];

        $optionsTotal = 0;
        if (! empty($optionIds)) {
            foreach (Option::whereIn('id', $optionIds)->where('is_active', true)->get() as $opt) {
                $optionsTotal += $opt->price_type === 'per_day' ? (float) $opt->price * $days : (float) $opt->price;
            }
        }

        $deliveryFee = ($pickup?->fee() ?? 0) + ($return && $return->id !== $pickup?->id ? $return->fee() : 0);

        $agency = Agency::current();
        $protectionFee = ($agency->protection_enabled && $protectionPlan === 'complete')
            ? round($pricing['subtotal'] * (int) $agency->protection_percent / 100) : 0;

        $returnFees = ($returnFuel ? (float) $vehicle->fuel_return_fee : 0)
            + ($returnWash ? (float) $vehicle->wash_return_fee : 0);

        $total = $pricing['subtotal'] + $optionsTotal + $deliveryFee + $protectionFee + $returnFees;

        return [
            'days' => $days,
            'base_price' => $pricing['base_price'],
            'season_surcharge' => $pricing['season_surcharge'],
            'duration_discount' => $pricing['duration_discount'],
            'subtotal' => $pricing['subtotal'],
            'options_total' => round($optionsTotal, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'protection_fee' => round($protectionFee, 2),
            'return_fees' => round($returnFees, 2),
            'total' => round($total, 2),
            'rules' => $pricing['rules'],
        ];
    }

    /** Page publique des conditions de location. */
    public function terms()
    {
        return view('public.terms', ['agency' => Agency::current()]);
    }

    /** JSON des plages indisponibles (réservations + entretiens) pour le calendrier. */
    public function availability(Vehicle $vehicle)
    {
        return response()->json($this->bookedRanges($vehicle));
    }

    /** Enregistre une demande de réservation depuis le site public. */
    public function store(Request $request)
    {
        $agency = Agency::current();

        $data = Validator::make($request->all(), [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'pickup_location_id' => ['nullable', 'exists:delivery_locations,id'],
            'return_location_id' => ['nullable', 'exists:delivery_locations,id'],
            'options' => ['nullable', 'array'],
            'options.*' => ['integer', 'exists:options,id'],
            'protection_plan' => ['nullable', 'in:basic,complete'],
            'return_fuel' => ['nullable', 'boolean'],
            'return_wash' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => [$agency->require_terms ? 'accepted' : 'nullable'],
        ], [
            'accept_terms.accepted' => 'Vous devez accepter les conditions de location.',
        ])->validate();

        $vehicle = Vehicle::where('is_active', true)->findOrFail($data['vehicle_id']);
        $start = Carbon::parse($data['start_date'] . ' ' . ($data['start_time'] ?? '09:00'));
        $end = Carbon::parse($data['end_date'] . ' ' . ($data['end_time'] ?? '09:00'));

        if ($end->lte($start)) {
            return back()->withInput()->withErrors(['end_date' => 'La date/heure de retour doit être après le départ.']);
        }

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

        $optionIds = $data['options'] ?? [];
        $pickup = ! empty($data['pickup_location_id'])
            ? DeliveryLocation::where('is_active', true)->find($data['pickup_location_id']) : null;
        $return = ! empty($data['return_location_id'])
            ? DeliveryLocation::where('is_active', true)->find($data['return_location_id']) : null;
        $protectionPlan = $data['protection_plan'] ?? 'basic';

        $q = $this->computeQuote(
            $vehicle, $start, $end, $optionIds, $pickup, $return,
            $protectionPlan, (bool) ($data['return_fuel'] ?? false), (bool) ($data['return_wash'] ?? false)
        );

        $advancePct = (float) ($agency->default_advance_percent ?? 0);

        $booking = Booking::create([
            'reference' => Booking::generateReference(),
            'client_token' => \Illuminate\Support\Str::random(48),
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $q['days'],
            'base_price' => $q['base_price'],
            'season_surcharge' => $q['season_surcharge'],
            'duration_discount' => $q['duration_discount'],
            'selected_options' => array_map('intval', $optionIds),
            'options_total' => $q['options_total'],
            'protection_plan' => $protectionPlan,
            'protection_fee' => $q['protection_fee'],
            'extra_fees' => $q['return_fees'],
            'delivery_fee' => $q['delivery_fee'],
            'pickup_location_id' => $pickup?->id,
            'return_location_id' => $return?->id,
            'pickup_location' => $pickup?->name,
            'return_location' => $return?->name,
            'discount_amount' => 0,
            'total_price' => $q['total'],
            'deposit_amount' => (float) $vehicle->deposit_amount,
            'advance_amount' => round($q['total'] * $advancePct / 100),
            'advance_status' => 'pending',
            'payment_status' => 'pending',
            'deposit_status' => 'pending',
            'amount_remaining' => $q['total'],
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
        $agency = Agency::current();
        $booking = Booking::with('vehicle')->where('reference', $reference)->firstOrFail();
        $paypal = new PayPalService($agency);

        $canPay = $paypal->isConfigured()
            && $booking->advance_status !== 'paid'
            && (float) $booking->advance_amount > 0;

        return view('public.confirmation', [
            'agency' => $agency,
            'booking' => $booking,
            'canPay' => $canPay,
            'payAmount' => $canPay ? $paypal->convertFromDa((float) $booking->advance_amount) : null,
            'payCurrency' => $paypal->currency(),
        ]);
    }

    /** Lance le paiement de l'acompte via PayPal : crée la commande et redirige vers PayPal. */
    public function payNow(string $reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        $paypal = new PayPalService(Agency::current());

        if (! $paypal->isConfigured()) {
            return redirect()->route('public.confirmation', $reference)
                ->with('pay_error', 'Le paiement en ligne n\'est pas disponible pour le moment.');
        }

        if ($booking->advance_status === 'paid' || (float) $booking->advance_amount <= 0) {
            return redirect()->route('public.confirmation', $reference);
        }

        $amount = $paypal->convertFromDa((float) $booking->advance_amount);

        $order = $paypal->createOrder(
            $amount,
            $booking->reference,
            route('public.paypal.return', $reference),
            route('public.paypal.cancel', $reference),
        );

        if (! $order || empty($order['approve_url'])) {
            return redirect()->route('public.confirmation', $reference)
                ->with('pay_error', 'Impossible de contacter PayPal. Réessayez plus tard.');
        }

        $booking->update(['paypal_order_id' => $order['id']]);

        return redirect()->away($order['approve_url']);
    }

    /** Retour de PayPal après approbation : capture le paiement et confirme la réservation. */
    public function paypalReturn(Request $request, string $reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        $orderId = $request->query('token');
        $paypal = new PayPalService(Agency::current());

        if (! $orderId || $orderId !== $booking->paypal_order_id) {
            return redirect()->route('public.confirmation', $reference)
                ->with('pay_error', 'Paiement non reconnu.');
        }

        if ($booking->advance_status === 'paid') {
            return redirect()->route('public.confirmation', $reference)->with('pay_success', true);
        }

        if (! $paypal->captureOrder($orderId)) {
            return redirect()->route('public.confirmation', $reference)
                ->with('pay_error', 'Le paiement n\'a pas pu être finalisé.');
        }

        $paid = (float) $booking->amount_paid + (float) $booking->advance_amount;
        $remaining = max(0, (float) $booking->total_price - $paid);

        $booking->update([
            'advance_status' => 'paid',
            'advance_paid_at' => now(),
            'advance_payment_method' => 'paypal',
            'advance_expires_at' => null,
            'amount_paid' => $paid,
            'amount_remaining' => $remaining,
            'payment_status' => $remaining <= 0 ? 'paid' : 'partial',
            'status' => 'confirmed',
        ]);

        $this->notifyAdvancePaid($booking);

        return redirect()->route('public.confirmation', $reference)->with('pay_success', true);
    }

    /** Annulation du paiement PayPal. */
    public function paypalCancel(string $reference)
    {
        return redirect()->route('public.confirmation', $reference)
            ->with('pay_error', 'Paiement annulé. Votre demande reste en attente.');
    }

    /** Prévient l'agence qu'un acompte a été réglé en ligne. */
    private function notifyAdvancePaid(Booking $booking): void
    {
        try {
            $notif = Notification::make()
                ->title('Acompte payé en ligne')
                ->body("{$booking->reference} — acompte de " . number_format($booking->advance_amount, 0, ',', ' ') . ' DA réglé par PayPal. Réservation confirmée.')
                ->success()
                ->icon('heroicon-o-banknotes')
                ->toDatabase();

            User::all()->each(fn (User $user) => $user->notifyNow($notif));
        } catch (\Throwable $e) {
            Log::warning('Notification acompte échouée: ' . $e->getMessage());
        }
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

        // Les bornes de fin (end_date) sont exclusives ; on disable jusqu'à end_date - 1 jour.
        foreach ($vehicle->bookings()->whereIn('status', self::BLOCKING_STATUSES)->get() as $b) {
            $ranges[] = [
                'from' => $b->start_date?->toDateString(),
                'to' => $b->end_date?->copy()->subDay()->toDateString(),
            ];
        }

        foreach ($vehicle->availabilities()->get() as $a) {
            $ranges[] = [
                'from' => $a->start_date?->toDateString(),
                'to' => $a->end_date?->copy()->subDay()->toDateString(),
            ];
        }

        return $ranges;
    }
}
