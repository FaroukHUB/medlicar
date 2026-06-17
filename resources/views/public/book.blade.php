@extends('public.layout')
@section('title', 'Réserver '.$vehicle->full_name.' — '.$agency->name)

@section('content')
@php
    $transmissions = ['automatic' => 'Automatique', 'manual' => 'Manuelle'];
    $hStart = (int) ($agency->operating_hours_start ?? 8);
    $hEnd = (int) ($agency->operating_hours_end ?? 20);
@endphp
<div class="max-w-6xl mx-auto px-4 py-8">
    <a href="{{ route('public.vehicle', $vehicle->slug) }}" class="text-sm text-gray-500 hover:underline">← Retour au véhicule</a>
    <h1 class="mt-3 text-2xl font-extrabold text-gray-900">Réserver — {{ $vehicle->full_name }}</h1>

    @if($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-brand-secondary">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('public.reserve') }}" id="book-form" enctype="multipart/form-data"
          class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
        <input type="hidden" name="start_date" id="start_date" value="{{ old('start_date') }}">
        <input type="hidden" name="end_date" id="end_date" value="{{ old('end_date') }}">

        {{-- Colonne formulaire --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- 1. Dates & heures --}}
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">1. Dates & heures</h2>
                <div id="calendar"></div>
                <p id="range-label" class="text-xs text-gray-500 mt-2">Sélectionnez vos dates (les indisponibles sont grisées).</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Heure de départ</label>
                        <input type="time" name="start_time" id="start_time" min="{{ sprintf('%02d:00',$hStart) }}" max="{{ sprintf('%02d:00',$hEnd) }}" value="{{ old('start_time','09:00') }}" class="js-recalc w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Heure de retour</label>
                        <input type="time" name="end_time" id="end_time" min="{{ sprintf('%02d:00',$hStart) }}" max="{{ sprintf('%02d:00',$hEnd) }}" value="{{ old('end_time','09:00') }}" class="js-recalc w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Horaires d'ouverture : {{ $hStart }}h – {{ $hEnd }}h.</p>
            </section>

            {{-- 2. Lieux --}}
            @if($deliveryLocations->isNotEmpty())
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">2. Prise en charge & retour</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Lieu de prise en charge</label>
                        <select name="pickup_location_id" id="pickup-loc" class="js-recalc w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                            <option value="">— Choisir —</option>
                            @foreach($deliveryLocations as $loc)
                                <option value="{{ $loc->id }}" @selected(old('pickup_location_id')==$loc->id)>{{ $loc->name }} — {{ $loc->is_free ? 'Gratuit' : number_format($loc->fee(),0,',',' ').' DA' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Lieu de retour</label>
                        <select name="return_location_id" id="return-loc" class="js-recalc w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                            <option value="">— Même lieu —</option>
                            @foreach($deliveryLocations as $loc)
                                <option value="{{ $loc->id }}" @selected(old('return_location_id')==$loc->id)>{{ $loc->name }} — {{ $loc->is_free ? 'Gratuit' : number_format($loc->fee(),0,',',' ').' DA' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>
            @endif

            {{-- 3. Options --}}
            @if($options->isNotEmpty())
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">3. Options</h2>
                <div class="space-y-2">
                    @foreach($options as $opt)
                        <label class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2 cursor-pointer">
                            <span><input type="checkbox" name="options[]" value="{{ $opt->id }}" class="js-recalc opt-check mr-2 rounded text-brand-primary" @checked(in_array($opt->id, old('options',[])))>{{ $opt->name }}</span>
                            <span class="text-gray-500">{{ number_format($opt->price,0,',',' ') }} DA{{ $opt->price_type==='per_day'?'/j':'' }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- 4. Protection --}}
            @if($agency->protection_enabled)
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">4. Protection</h2>
                <div class="space-y-3">
                    <label class="block rounded-xl ring-1 ring-gray-200 p-3 cursor-pointer">
                        <span class="flex items-center justify-between font-semibold">
                            <span><input type="radio" name="protection_plan" value="basic" class="js-recalc mr-2" {{ old('protection_plan','basic')==='basic'?'checked':'' }}>Protection basique</span>
                            <span class="text-brand-primary">Inclus</span>
                        </span>
                        @if(!empty($agency->protection_basic_details))
                            <ul class="mt-2 ml-6 list-disc text-xs text-gray-500">
                                @foreach($agency->protection_basic_details as $d)<li>{{ is_array($d) ? ($d['text'] ?? '') : $d }}</li>@endforeach
                            </ul>
                        @endif
                    </label>
                    <label class="block rounded-xl ring-1 ring-gray-200 p-3 cursor-pointer">
                        <span class="flex items-center justify-between font-semibold">
                            <span><input type="radio" name="protection_plan" value="complete" class="js-recalc mr-2" {{ old('protection_plan')==='complete'?'checked':'' }}>Protection complète</span>
                            <span class="text-brand-secondary">+{{ (int) $agency->protection_percent }}%</span>
                        </span>
                        @if(!empty($agency->protection_complete_details))
                            <ul class="mt-2 ml-6 list-disc text-xs text-gray-500">
                                @foreach($agency->protection_complete_details as $d)<li>{{ is_array($d) ? ($d['text'] ?? '') : $d }}</li>@endforeach
                            </ul>
                        @endif
                    </label>
                </div>
            </section>
            @endif

            {{-- 5. Options de retour --}}
            @if((float)$vehicle->fuel_return_fee > 0 || (float)$vehicle->wash_return_fee > 0)
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">5. Retour du véhicule</h2>
                <div class="space-y-2 text-sm">
                    @if((float)$vehicle->fuel_return_fee > 0)
                        <label class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 cursor-pointer">
                            <span><input type="checkbox" name="return_fuel" value="1" class="js-recalc mr-2 rounded" @checked(old('return_fuel'))>Rendre sans faire le plein</span>
                            <span class="text-gray-500">{{ number_format($vehicle->fuel_return_fee,0,',',' ') }} DA</span>
                        </label>
                    @endif
                    @if((float)$vehicle->wash_return_fee > 0)
                        <label class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 cursor-pointer">
                            <span><input type="checkbox" name="return_wash" value="1" class="js-recalc mr-2 rounded" @checked(old('return_wash'))>Rendre sans laver</span>
                            <span class="text-gray-500">{{ number_format($vehicle->wash_return_fee,0,',',' ') }} DA</span>
                        </label>
                    @endif
                </div>
            </section>
            @endif

            {{-- 6. Vos informations --}}
            <section class="rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-3">6. Vos informations</h2>
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Prénom" required class="rounded-lg ring-1 ring-gray-200 px-3 py-2">
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Nom" required class="rounded-lg ring-1 ring-gray-200 px-3 py-2">
                </div>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Téléphone" required class="mt-3 w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (optionnel)" class="mt-3 w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
                <textarea name="message" rows="2" placeholder="Message (optionnel)" class="mt-3 w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">{{ old('message') }}</textarea>
            </section>
        </div>

        {{-- Récapitulatif sticky --}}
        <div class="lg:col-span-1">
            <div class="sticky top-4 rounded-2xl bg-white p-5 ring-1 ring-gray-100 shadow-sm">
                <h2 class="font-bold text-gray-900">Récapitulatif</h2>
                <div id="summary" class="mt-3 space-y-1.5 text-sm text-gray-600">
                    <p class="text-gray-400">Choisissez vos dates pour voir le prix.</p>
                </div>

                @if($agency->require_terms || $agency->terms_pdf || $agency->contract_terms)
                    <div class="mt-4 text-sm">
                        @if($agency->require_terms)
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="accept_terms" value="1" class="mt-0.5 rounded text-brand-primary">
                                <span>J'accepte les <a href="{{ route('public.terms') }}" target="_blank" class="text-brand-primary underline">conditions</a>.</span>
                            </label>
                        @else
                            <a href="{{ route('public.terms') }}" target="_blank" class="text-brand-primary underline">Voir les conditions</a>
                        @endif
                    </div>
                @endif

                <button type="submit" class="mt-4 w-full rounded-xl bg-brand-secondary py-3 font-bold text-white hover:bg-brand-secondary-dark">
                    Confirmer la réservation
                </button>
                <p class="mt-2 text-xs text-gray-400 text-center">Votre demande sera confirmée par l'agence.</p>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const bookedRanges = @json($bookedRanges);
    const eurRate = {{ (float) ($agency->showsEur() ? $agency->eur_rate : 0) }};
    const calcUrl = @js(route('public.calculate'));
    const vehicleId = {{ $vehicle->id }};
    const startEl = document.getElementById('start_date'), endEl = document.getElementById('end_date');
    const rangeLabel = document.getElementById('range-label'), summary = document.getElementById('summary');

    function fmt(n){ return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' DA'; }
    function fmtEur(n){ return eurRate > 0 ? ' / ' + Math.round(n/eurRate) + ' €' : ''; }

    function gather() {
        const fd = new FormData(document.getElementById('book-form'));
        const body = { vehicle_id: vehicleId, start_date: startEl.value, end_date: endEl.value,
            start_time: fd.get('start_time'), end_time: fd.get('end_time'),
            pickup_location_id: fd.get('pickup_location_id'), return_location_id: fd.get('return_location_id'),
            protection_plan: fd.get('protection_plan') || 'basic',
            return_fuel: fd.get('return_fuel') ? 1 : 0, return_wash: fd.get('return_wash') ? 1 : 0,
            options: fd.getAll('options[]') };
        return body;
    }

    async function recalc() {
        if (!startEl.value || !endEl.value) return;
        try {
            const res = await fetch(calcUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify(gather()),
            });
            if (!res.ok) return;
            const q = await res.json();
            let rows = '';
            const line = (l, v) => `<div class="flex justify-between"><span>${l}</span><span>${fmt(v)}</span></div>`;
            rows += line(`Location (${q.days} j)`, q.base_price);
            if (q.season_surcharge && q.season_surcharge !== 0) rows += line('Supplément saison', q.season_surcharge);
            if (q.duration_discount && q.duration_discount !== 0) rows += line(q.duration_discount < 0 ? 'Remise durée' : 'Ajustement', q.duration_discount);
            if (q.options_total > 0) rows += line('Options', q.options_total);
            if (q.protection_fee > 0) rows += line('Protection complète', q.protection_fee);
            if (q.return_fees > 0) rows += line('Frais de retour', q.return_fees);
            if (q.delivery_fee > 0) rows += line('Livraison', q.delivery_fee);
            rows += `<div class="flex justify-between border-t pt-2 mt-2 font-bold text-brand-primary"><span>Total</span><span>${fmt(q.total)}${fmtEur(q.total)}</span></div>`;
            if (!q.available) rows += `<div class="mt-2 text-brand-secondary text-xs">⚠️ Indisponible sur ces dates.</div>`;
            summary.innerHTML = rows;
        } catch (e) {}
    }

    document.querySelectorAll('.js-recalc').forEach(el => el.addEventListener('change', recalc));

    flatpickr('#calendar', {
        mode: 'range', locale: 'fr', inline: true, minDate: 'today', dateFormat: 'Y-m-d', disable: bookedRanges,
        defaultDate: (startEl.value && endEl.value) ? [startEl.value, endEl.value] : null,
        onChange: function (dates) {
            if (dates.length === 2) {
                const f = d => d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
                startEl.value = f(dates[0]); endEl.value = f(dates[1]);
                const days = Math.max(1, Math.round((dates[1]-dates[0])/86400000));
                rangeLabel.textContent = 'Du ' + dates[0].toLocaleDateString('fr-FR') + ' au ' + dates[1].toLocaleDateString('fr-FR') + ' · ' + days + ' j';
                recalc();
            }
        }
    });
</script>
@endpush
