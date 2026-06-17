@extends('public.layout')
@section('title', $agency->name.' — Location de voitures')

@section('content')
{{-- ============ HERO SLIDER ============ --}}
@if($heroSlides->isNotEmpty())
<section class="relative" x-data="{ active: 0, count: {{ $heroSlides->count() }} }"
         x-init="if (count > 1) setInterval(() => active = (active + 1) % count, 6000)">
    <div class="relative h-[58vh] min-h-[360px] max-h-[560px] overflow-hidden">
        @foreach($heroSlides as $i => $slide)
            <div x-show="active === {{ $i }}" x-transition.opacity.duration.700ms
                 class="absolute inset-0 bg-cover bg-center"
                 style="background-image: linear-gradient(to right, rgba(0,0,0,.6), rgba(0,0,0,.2)){{ $slide->image ? ', url(' . \Illuminate\Support\Facades\Storage::url($slide->image) . ')' : '' }}; {{ $slide->image ? '' : 'background:#006233;' }}">
                <div class="mx-auto flex h-full max-w-6xl flex-col justify-center px-6">
                    <h1 class="max-w-2xl text-3xl font-extrabold leading-tight text-white sm:text-5xl">{{ $slide->title }}</h1>
                    @if($slide->subtitle)
                        <p class="mt-4 max-w-xl text-lg text-white/90">{{ $slide->subtitle }}</p>
                    @endif
                    @if($slide->button_text)
                        <div class="mt-7">
                            <a href="{{ $slide->button_url ?: '#vehicules' }}"
                               class="inline-block rounded-full bg-dz-red px-7 py-3.5 text-base font-bold text-white shadow-lg transition hover:bg-red-700">
                                {{ $slide->button_text }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @if($heroSlides->count() > 1)
        <div class="absolute bottom-5 left-1/2 flex -translate-x-1/2 gap-2">
            @foreach($heroSlides as $i => $slide)
                <button @click="active = {{ $i }}" :class="active === {{ $i }} ? 'w-7 bg-white' : 'w-2.5 bg-white/50'"
                        class="h-2.5 rounded-full transition-all"></button>
            @endforeach
        </div>
    @endif
</section>
@else
    <section class="bg-dz-green text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 text-center">
            <h1 class="text-3xl font-extrabold sm:text-4xl">Louez votre voiture en quelques clics</h1>
            <p class="mt-3 text-white/85">Choisissez un véhicule, sélectionnez vos dates, réservez en ligne.</p>
        </div>
    </section>
@endif

<div id="vehicules"></div>

{{-- ============ NOTRE SÉLECTION ============ --}}
@if($featured->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-12">
    <div class="mb-7 flex items-center gap-3">
        <span class="text-2xl">⭐</span>
        <h2 class="text-2xl font-extrabold text-gray-900">Notre sélection</h2>
    </div>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($featured as $vehicle)
            @include('public.partials.card', ['vehicle' => $vehicle, 'agency' => $agency])
        @endforeach
    </div>
</section>
@endif

{{-- ============ SECTIONS PAR CATÉGORIE ============ --}}
@foreach($sections as $section)
<section class="mx-auto max-w-6xl px-4 py-12 {{ $loop->even ? 'bg-gray-50' : '' }}">
    <div class="mb-7 flex items-end justify-between">
        <h2 class="text-2xl font-extrabold text-gray-900">{{ $section['category']->name }}</h2>
        <span class="text-sm text-gray-400">{{ $section['vehicles']->count() }} véhicule(s)</span>
    </div>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($section['vehicles'] as $vehicle)
            @include('public.partials.card', ['vehicle' => $vehicle, 'agency' => $agency])
        @endforeach
    </div>
</section>
@endforeach

@if($featured->isEmpty() && empty($sections))
    <div class="mx-auto max-w-6xl px-4 py-20 text-center text-gray-500">Aucun véhicule disponible pour le moment.</div>
@endif
@endsection
