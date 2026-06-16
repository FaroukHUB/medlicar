@extends('public.layout')
@section('title', $agency->name.' — Location de voitures')

@section('content')
<section class="bg-dz-green text-white">
    <div class="max-w-6xl mx-auto px-4 py-14 text-center">
        <h1 class="text-3xl sm:text-4xl font-extrabold">Louez votre voiture en quelques clics</h1>
        <p class="mt-3 text-white/85">Choisissez un véhicule, sélectionnez vos dates, réservez en ligne.</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-10">
    {{-- Filtres --}}
    <form method="GET" action="{{ route('public.home') }}" class="bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm p-4 mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Catégorie</label>
            <select name="category_id" class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2 text-sm">
                <option value="">Toutes</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? null) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Prix min (DA/j)</label>
            <input type="number" name="price_min" value="{{ $filters['price_min'] ?? '' }}" min="0" class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Prix max (DA/j)</label>
            <input type="number" name="price_max" value="{{ $filters['price_max'] ?? '' }}" min="0" class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Disponible (période)</label>
            <input type="text" id="filter-range" placeholder="Toutes dates" readonly class="w-full rounded-lg border-gray-300 ring-1 ring-gray-200 px-3 py-2 text-sm">
            <input type="hidden" name="start_date" id="f_start" value="{{ $filters['start_date'] ?? '' }}">
            <input type="hidden" name="end_date" id="f_end" value="{{ $filters['end_date'] ?? '' }}">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-dz-green text-white text-sm font-semibold py-2 rounded-lg hover:bg-dz-greendark">Filtrer</button>
            <a href="{{ route('public.home') }}" class="px-3 py-2 text-sm text-gray-500 ring-1 ring-gray-200 rounded-lg hover:bg-gray-50">Réinitialiser</a>
        </div>
    </form>

    <h2 class="text-xl font-bold mb-6">Notre flotte <span class="text-sm font-normal text-gray-400">({{ $vehicles->count() }})</span></h2>

    @if($vehicles->isEmpty())
        <p class="text-gray-500">Aucun véhicule disponible pour le moment.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $vehicle)
                <a href="{{ route('public.vehicle', $vehicle->slug) }}"
                   class="group bg-white rounded-2xl shadow-sm hover:shadow-md ring-1 ring-gray-100 overflow-hidden transition">
                    <div class="aspect-[16/10] bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($vehicle->image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($vehicle->image) }}"
                                 alt="{{ $vehicle->full_name }}" class="h-full w-full object-cover group-hover:scale-105 transition">
                        @else
                            <span class="text-5xl">🚗</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            @if($vehicle->category)<span class="bg-gray-100 px-2 py-0.5 rounded">{{ $vehicle->category->name }}</span>@endif
                            @if($vehicle->transmission)<span>{{ ucfirst($vehicle->transmission) }}</span>@endif
                            @if($vehicle->seats)<span>· {{ $vehicle->seats }} places</span>@endif
                        </div>
                        <h3 class="mt-1 font-semibold text-gray-900">{{ $vehicle->full_name }}</h3>
                        <div class="mt-3 flex items-end justify-between">
                            <div>
                                <span class="text-lg font-bold text-dz-green">{{ number_format($vehicle->price_per_day, 0, ',', ' ') }} DA</span>
                                <span class="text-sm text-gray-500">/ jour</span>
                            </div>
                            <span class="text-sm font-medium text-dz-red group-hover:underline">Réserver →</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
    const fStart = document.getElementById('f_start'), fEnd = document.getElementById('f_end');
    flatpickr('#filter-range', {
        mode: 'range', locale: 'fr', minDate: 'today', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y',
        defaultDate: (fStart.value && fEnd.value) ? [fStart.value, fEnd.value] : null,
        onClose: function (dates) {
            const f = d => d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
            fStart.value = dates[0] ? f(dates[0]) : '';
            fEnd.value = dates[1] ? f(dates[1]) : '';
        }
    });
</script>
@endpush
