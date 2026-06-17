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

            @if($vehicle->advantages->where('is_active', true)->isNotEmpty())
                <ul class="mt-4 space-y-1.5">
                    @foreach($vehicle->advantages->where('is_active', true) as $adv)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <svg class="h-4 w-4 flex-none text-brand-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ $adv->name }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($vehicle->description)
                <p class="mt-4 text-gray-600 leading-relaxed">{{ $vehicle->description }}</p>
            @endif
        </div>

        {{-- Bloc prix + CTA --}}
        <div>
            @php $onPromo = $vehicle->isOnPromoNow(); $promoPrice = $vehicle->promoPrice(); @endphp
            <div class="bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm p-5 sticky top-4">
                @if($onPromo)
                    <span class="inline-block mb-3 rounded-lg bg-brand-secondary px-3 py-1 text-sm font-bold text-white">{{ $vehicle->promoBadge() }}</span>
                @endif
                <div class="flex items-end justify-between border-b pb-4">
                    <div>
                        @if($onPromo)<span class="block text-sm text-gray-400 line-through">{{ number_format($vehicle->price_per_day, 0, ',', ' ') }} DA</span>@endif
                        <span class="text-3xl font-extrabold text-brand-primary">{{ number_format($promoPrice, 0, ',', ' ') }} DA</span>
                        @if($agency->toEur($promoPrice))<span class="text-base font-semibold text-gray-400">/ {{ $agency->toEur($promoPrice) }} €</span>@endif
                        <span class="text-gray-500">/ jour</span>
                    </div>
                    @if($vehicle->deposit_amount)
                        <div class="text-right text-xs text-gray-500">Caution<br><span class="font-medium text-gray-700">{{ number_format($vehicle->deposit_amount, 0, ',', ' ') }} DA{{ $agency->toEur($vehicle->deposit_amount) ? ' / '.$agency->toEur($vehicle->deposit_amount).' €' : '' }}</span></div>
                    @endif
                </div>

                @if($vehicle->price_per_week || $vehicle->price_per_month)
                    <div class="mt-3 text-sm text-gray-500 space-y-1">
                        @if($vehicle->price_per_week)<div class="flex justify-between"><span>Semaine</span><span>{{ number_format($vehicle->price_per_week,0,',',' ') }} DA</span></div>@endif
                        @if($vehicle->price_per_month)<div class="flex justify-between"><span>Mois</span><span>{{ number_format($vehicle->price_per_month,0,',',' ') }} DA</span></div>@endif
                    </div>
                @endif

                <a href="{{ route('public.book', $vehicle->slug) }}"
                   class="mt-5 block w-full rounded-xl bg-brand-secondary py-3.5 text-center font-bold text-white transition hover:bg-brand-secondary-dark">
                    Réserver ce véhicule
                </a>
                @if($agency->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}?text={{ urlencode('Bonjour, je suis intéressé par la '.$vehicle->full_name) }}" target="_blank"
                       class="mt-2 block w-full rounded-xl bg-emerald-600 py-3 text-center font-semibold text-white hover:bg-emerald-700">
                        Demander sur WhatsApp
                    </a>
                @endif
                <p class="mt-3 text-xs text-gray-400 text-center">Réservation en ligne · confirmation par l'agence.</p>
            </div>
        </div>
    </div>
</div>
@endsection
