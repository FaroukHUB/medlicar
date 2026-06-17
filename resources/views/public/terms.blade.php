@extends('public.layout')
@section('title', 'Conditions de location — '.$agency->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <a href="{{ route('public.home') }}" class="text-sm text-gray-500 hover:underline">← Retour à l'accueil</a>
    <h1 class="mt-4 text-3xl font-extrabold text-gray-900">Conditions de location</h1>

    @if($agency->terms_pdf)
        <a href="{{ \Illuminate\Support\Facades\Storage::url($agency->terms_pdf) }}" target="_blank"
           class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-primary px-5 py-3 text-white font-semibold hover:bg-brand-primary-dark">
            📄 Télécharger les conditions (PDF)
        </a>
    @endif

    @if($agency->contract_terms)
        <div class="prose mt-6 max-w-none text-gray-700 leading-relaxed">
            {!! $agency->contract_terms !!}
        </div>
    @elseif(! $agency->terms_pdf)
        <p class="mt-6 text-gray-500">Les conditions de location seront communiquées par l'agence.</p>
    @endif
</div>
@endsection
