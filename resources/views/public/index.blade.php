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
    <h2 class="text-xl font-bold mb-6">Notre flotte</h2>

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
