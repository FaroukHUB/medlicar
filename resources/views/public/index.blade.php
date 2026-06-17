@extends('public.layout')

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
                               class="inline-block rounded-full bg-brand-secondary px-7 py-3.5 text-base font-bold text-white shadow-lg transition hover:bg-brand-secondary-dark">
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
    <section class="bg-brand-primary text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 text-center">
            <h1 class="text-3xl font-extrabold sm:text-4xl">Louez votre voiture en quelques clics</h1>
            <p class="mt-3 text-white/85">Choisissez un véhicule, sélectionnez vos dates, réservez en ligne.</p>
        </div>
    </section>
@endif

{{-- ============ POURQUOI NOUS CHOISIR ============ --}}
@if($agency->section_why && $features->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-14">
    <h2 class="mb-10 text-center text-3xl font-extrabold text-gray-900">{{ $agency->why_title ?: 'Pourquoi nous choisir' }}</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($features as $feature)
            <div class="rounded-2xl bg-white p-6 text-center ring-1 ring-gray-100 shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-primary/10 text-brand-primary">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </div>
                <h3 class="font-bold text-gray-900">{{ $feature->title }}</h3>
                @if($feature->description)<p class="mt-2 text-sm text-gray-500">{{ $feature->description }}</p>@endif
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ============ STATISTIQUES ============ --}}
@if($agency->section_stats && $stats->isNotEmpty())
<section class="bg-brand-primary py-14 text-white">
    <div class="mx-auto max-w-6xl px-4">
        @if($agency->stats_title)<h2 class="mb-10 text-center text-3xl font-extrabold">{{ $agency->stats_title }}</h2>@endif
        <div class="grid grid-cols-2 gap-6 lg:grid-cols-4">
            @foreach($stats as $stat)
                <div class="text-center">
                    @if($stat->icon)<div class="text-3xl">{{ $stat->icon }}</div>@endif
                    <div class="text-4xl font-extrabold">{{ $stat->value }}</div>
                    <div class="mt-1 text-white/80">{{ $stat->label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ VÉHICULES ============ --}}
@if($agency->section_vehicles)
<div id="vehicules"></div>

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
@endif

{{-- ============ AVIS CLIENTS ============ --}}
@if($agency->section_reviews && $reviews->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 py-14">
    <h2 class="mb-10 text-center text-3xl font-extrabold text-gray-900">{{ $agency->reviews_title ?: 'Avis clients' }}</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($reviews as $review)
            <div class="rounded-2xl bg-white p-6 ring-1 ring-gray-100 shadow-sm">
                <div class="flex gap-0.5 text-amber-400">
                    @for($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                    @endfor
                </div>
                @if($review->comment)<p class="mt-3 text-gray-600">« {{ $review->comment }} »</p>@endif
                <div class="mt-4 font-semibold text-gray-900">{{ $review->customer?->full_name ?? 'Client' }}</div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
@if($agency->section_faq && $faqItems->isNotEmpty())
<section class="bg-gray-50 py-14">
    <div class="mx-auto max-w-3xl px-4">
        <h2 class="mb-10 text-center text-3xl font-extrabold text-gray-900">{{ $agency->faq_title ?: 'Questions fréquentes' }}</h2>
        <div class="space-y-3">
            @foreach($faqItems as $faq)
                <div x-data="{ open: false }" class="rounded-2xl bg-white ring-1 ring-gray-100">
                    <button @click="open = !open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-gray-900">
                        <span>{{ $faq->question }}</span>
                        <span x-text="open ? '−' : '+'" class="text-2xl text-brand-primary"></span>
                    </button>
                    <div x-show="open" x-transition class="px-5 pb-4 text-gray-600">{{ $faq->answer }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ CONTACT ============ --}}
@if($agency->section_contact)
<section class="mx-auto max-w-6xl px-4 py-14">
    <h2 class="mb-10 text-center text-3xl font-extrabold text-gray-900">{{ $agency->contact_title ?: 'Nous contacter' }}</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @if($agency->phone)
            <a href="tel:{{ $agency->phone }}" class="rounded-2xl bg-white p-6 text-center ring-1 ring-gray-100 shadow-sm hover:shadow-md">
                <div class="text-3xl">📞</div><div class="mt-2 text-sm text-gray-400">Téléphone</div>
                <div class="font-semibold text-gray-900">{{ $agency->phone }}</div>
            </a>
        @endif
        @if($agency->whatsapp)
            <a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}" target="_blank" class="rounded-2xl bg-white p-6 text-center ring-1 ring-gray-100 shadow-sm hover:shadow-md">
                <div class="text-3xl">💬</div><div class="mt-2 text-sm text-gray-400">WhatsApp</div>
                <div class="font-semibold text-gray-900">{{ $agency->whatsapp }}</div>
            </a>
        @endif
        @if($agency->email)
            <a href="mailto:{{ $agency->email }}" class="rounded-2xl bg-white p-6 text-center ring-1 ring-gray-100 shadow-sm hover:shadow-md">
                <div class="text-3xl">✉️</div><div class="mt-2 text-sm text-gray-400">Email</div>
                <div class="font-semibold text-gray-900">{{ $agency->email }}</div>
            </a>
        @endif
        @if($agency->address)
            <div class="rounded-2xl bg-white p-6 text-center ring-1 ring-gray-100 shadow-sm">
                <div class="text-3xl">📍</div><div class="mt-2 text-sm text-gray-400">Adresse</div>
                <div class="font-semibold text-gray-900">{{ $agency->address }}{{ $agency->city ? ', '.$agency->city : '' }}</div>
            </div>
        @endif
    </div>
</section>
@endif
@endsection
