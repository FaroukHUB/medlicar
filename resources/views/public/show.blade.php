@extends('public.layout')
@section('title', $vehicle->full_name.' — '.$agency->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <a href="{{ route('public.home') }}" class="text-sm text-gray-500 hover:underline">← Retour à la flotte</a>

    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Visuel + specs --}}
        <div>
            <div class="aspect-[16/10] bg-gray-100 rounded-2xl overflow-hidden flex items-center justify-center">
                @if($vehicle->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($vehicle->image) }}" alt="{{ $vehicle->full_name }}" class="h-full w-full object-cover">
                @else
                    <span class="text-7xl">🚗</span>
                @endif
            </div>

            @if(!empty($vehicle->gallery))
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach(array_slice($vehicle->gallery, 0, 4) as $img)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img) }}" class="aspect-square object-cover rounded-lg">
                    @endforeach
                </div>
            @endif

            <h1 class="mt-5 text-2xl font-extrabold text-gray-900">{{ $vehicle->full_name }}</h1>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                @php $specs = array_filter([
                    'Transmission' => $vehicle->transmission ? ucfirst($vehicle->transmission) : null,
                    'Carburant' => $vehicle->fuel_type ? ucfirst($vehicle->fuel_type) : null,
                    'Places' => $vehicle->seats,
                    'Portes' => $vehicle->doors,
                    'Climatisation' => $vehicle->has_ac ? 'Oui' : null,
                    'Année' => $vehicle->year,
                ]); @endphp
                @foreach($specs as $label => $value)
                    <div class="bg-white rounded-xl ring-1 ring-gray-100 px-3 py-2">
                        <div class="text-gray-400 text-xs">{{ $label }}</div>
                        <div class="font-medium">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            @if($vehicle->description)
                <p class="mt-4 text-gray-600 leading-relaxed">{{ $vehicle->description }}</p>
            @endif
        </div>

        {{-- Bloc réservation --}}
        <div>
            <div class="bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm p-5 sticky top-4">
                <div class="flex items-end justify-between border-b pb-4">
                    <div>
                        <span class="text-2xl font-bold text-dz-green">{{ number_format($vehicle->price_per_day, 0, ',', ' ') }} DA</span>
                        <span class="text-gray-500">/ jour</span>
                    </div>
                    @if($vehicle->deposit_amount)
                        <div class="text-right text-xs text-gray-500">Caution<br><span class="font-medium text-gray-700">{{ number_format($vehicle->deposit_amount, 0, ',', ' ') }} DA</span></div>
                    @endif
                </div>

                @if($errors->any())
                    <div class="mt-4 bg-red-50 text-dz-red text-sm rounded-lg p-3">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('public.reserve') }}" class="mt-4 space-y-3" id="reserve-form">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    <input type="hidden" name="start_date" id="start_date" value="{{ old('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ old('end_date') }}">

                    <div>
                        <label class="block text-sm font-medium mb-1">Vos dates</label>
                        <input type="text" id="daterange" placeholder="Sélectionnez la période"
                               class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2 focus:ring-dz-green" readonly>
                        <p class="text-xs text-gray-400 mt-1">Les dates indisponibles sont grisées.</p>
                    </div>

                    <div id="price-box" class="hidden bg-gray-50 rounded-lg p-3 text-sm">
                        <div class="flex justify-between"><span id="price-detail"></span><span id="price-total" class="font-bold text-dz-green"></span></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Prénom" required class="rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2">
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Nom" required class="rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2">
                    </div>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Téléphone" required class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (optionnel)" class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2">
                    <textarea name="message" placeholder="Message (optionnel)" rows="2" class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2">{{ old('message') }}</textarea>

                    <button type="submit" class="w-full bg-dz-red hover:bg-red-700 text-white font-semibold py-3 rounded-xl transition">
                        Demander la réservation
                    </button>
                    <p class="text-xs text-gray-400 text-center">Votre demande sera confirmée par l'agence.</p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const pricePerDay = {{ (float) $vehicle->price_per_day }};
    const bookedRanges = @json($bookedRanges);

    const startEl = document.getElementById('start_date');
    const endEl = document.getElementById('end_date');
    const priceBox = document.getElementById('price-box');
    const priceDetail = document.getElementById('price-detail');
    const priceTotal = document.getElementById('price-total');

    function fmt(n){ return new Intl.NumberFormat('fr-FR').format(n) + ' DA'; }

    flatpickr('#daterange', {
        mode: 'range',
        locale: 'fr',
        minDate: 'today',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        disable: bookedRanges,
        defaultDate: (startEl.value && endEl.value) ? [startEl.value, endEl.value] : null,
        onClose: function (selectedDates) {
            if (selectedDates.length === 2) {
                const s = selectedDates[0], e = selectedDates[1];
                startEl.value = s.getFullYear()+'-'+String(s.getMonth()+1).padStart(2,'0')+'-'+String(s.getDate()).padStart(2,'0');
                endEl.value = e.getFullYear()+'-'+String(e.getMonth()+1).padStart(2,'0')+'-'+String(e.getDate()).padStart(2,'0');
                const days = Math.max(1, Math.round((e - s) / 86400000));
                const total = days * pricePerDay;
                priceDetail.textContent = fmt(pricePerDay) + ' × ' + days + ' jour(s)';
                priceTotal.textContent = fmt(total);
                priceBox.classList.remove('hidden');
            }
        }
    });
</script>
@endpush
