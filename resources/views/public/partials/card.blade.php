@php
    $transmissions = ['automatic' => 'Automatique', 'manual' => 'Manuelle'];
    $wa = $agency->whatsapp ? preg_replace('/\D/', '', $agency->whatsapp) : null;
    $advantages = $vehicle->advantages->where('is_active', true);
@endphp
<div class="group relative flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-gray-100 shadow-sm transition hover:shadow-xl">
    {{-- Ruban promo --}}
    @if($vehicle->is_on_promo)
        <div class="absolute right-0 top-5 z-10 rounded-l-lg bg-dz-red px-4 py-1.5 text-sm font-bold text-white shadow-md">
            {{ $vehicle->promo_label ?: 'PROMO' }}
        </div>
    @endif

    {{-- Image + badge catégorie --}}
    <a href="{{ route('public.vehicle', $vehicle->slug) }}" class="relative block aspect-[16/11] bg-gray-50">
        @if($vehicle->category)
            <span class="absolute left-4 top-4 z-10 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm backdrop-blur">
                {{ $vehicle->category->name }}
            </span>
        @endif
        @if($vehicle->image)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($vehicle->image) }}" alt="{{ $vehicle->full_name }}"
                 class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-6xl">🚗</div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-extrabold text-gray-900">{{ $vehicle->full_name }}</h3>

        {{-- Caractéristiques --}}
        <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-gray-600">
            @if($vehicle->transmission)
                <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 ring-1 ring-gray-100">⚙️ {{ $transmissions[$vehicle->transmission] ?? ucfirst($vehicle->transmission) }}</span>
            @endif
            @if($vehicle->has_ac)
                <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 ring-1 ring-gray-100">❄️ Climatisation</span>
            @endif
            @if($vehicle->seats)
                <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 ring-1 ring-gray-100">👥 {{ $vehicle->seats }} places</span>
            @endif
            @if($vehicle->fuel_type)
                <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 ring-1 ring-gray-100">⛽ {{ ucfirst($vehicle->fuel_type) }}</span>
            @endif
        </div>

        {{-- Prix --}}
        @php $eurDay = $agency->toEur($vehicle->price_per_day); @endphp
        <div class="mt-4 rounded-2xl bg-gray-50 px-4 py-3">
            <span class="text-2xl font-extrabold text-dz-green">{{ number_format($vehicle->price_per_day, 0, ',', ' ') }} DA</span>
            @if($eurDay)<span class="text-base font-semibold text-gray-400">/ {{ $eurDay }} €</span>@endif
            <span class="text-sm text-gray-500">/ jour</span>
            @if($vehicle->price_per_week || $vehicle->price_per_month)
                <div class="mt-1 text-xs text-gray-400">
                    @if($vehicle->price_per_week)
                        Semaine : {{ number_format($vehicle->price_per_week, 0, ',', ' ') }} DA @if($agency->toEur($vehicle->price_per_week))/ {{ $agency->toEur($vehicle->price_per_week) }} €@endif
                    @endif
                    @if($vehicle->price_per_month)
                        · Mois : {{ number_format($vehicle->price_per_month, 0, ',', ' ') }} DA @if($agency->toEur($vehicle->price_per_month))/ {{ $agency->toEur($vehicle->price_per_month) }} €@endif
                    @endif
                </div>
            @endif
        </div>

        {{-- Avantages --}}
        @if($advantages->isNotEmpty())
            <ul class="mt-4 space-y-1.5">
                @foreach($advantages as $adv)
                    <li class="flex items-center gap-2 text-sm text-gray-700">
                        <svg class="h-4 w-4 flex-none text-dz-green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ $adv->name }}
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Boutons --}}
        <div class="mt-5 flex gap-2 pt-1">
            <a href="{{ route('public.vehicle', $vehicle->slug) }}"
               class="flex-1 rounded-xl bg-dz-red py-3 text-center text-sm font-bold text-white transition hover:bg-red-700">
                Réserver
            </a>
            @if($wa)
                <a href="https://wa.me/{{ $wa }}?text={{ urlencode('Bonjour, je suis intéressé par la ' . $vehicle->full_name) }}" target="_blank"
                   class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Zm5.8 14.1c-.24.68-1.42 1.3-1.95 1.35-.5.05-.97.24-2.36-.34-2.62-1.03-4.32-3.68-4.45-3.85-.13-.17-1.08-1.43-1.08-2.73s.68-1.94.92-2.2c.24-.27.53-.34.7-.34.18 0 .35 0 .5.01.16.01.38-.06.59.45.24.58.78 1.93.85 2.07.07.14.12.3.02.48-.1.17-.15.27-.29.43-.14.16-.3.36-.43.48-.14.14-.29.29-.12.57.17.27.74 1.22 1.59 1.98 1.09.98 2.01 1.28 2.29 1.43.27.14.43.12.59-.07.16-.19.68-.79.86-1.07.18-.27.36-.22.59-.13.24.09 1.52.72 1.78.85.27.13.44.2.5.31.06.11.06.62-.18 1.3Z"/></svg>
                    WhatsApp
                </a>
            @endif
        </div>
    </div>
</div>
