@extends('public.layout')
@section('title', 'Demande envoyée — '.$agency->name)

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    <div class="text-6xl">✅</div>
    <h1 class="mt-4 text-2xl font-extrabold text-gray-900">Demande envoyée !</h1>
    <p class="mt-2 text-gray-600">Merci, votre demande de réservation a bien été reçue. L'agence va vous contacter pour la confirmer.</p>

    <div class="mt-6 bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm p-6 text-left">
        <div class="flex justify-between py-2 border-b">
            <span class="text-gray-500">Référence</span>
            <span class="font-bold text-dz-green">{{ $booking->reference }}</span>
        </div>
        <div class="flex justify-between py-2 border-b">
            <span class="text-gray-500">Véhicule</span>
            <span class="font-medium">{{ $booking->vehicle?->full_name }}</span>
        </div>
        <div class="flex justify-between py-2 border-b">
            <span class="text-gray-500">Période</span>
            <span class="font-medium">{{ $booking->start_date?->format('d/m/Y') }} → {{ $booking->end_date?->format('d/m/Y') }}</span>
        </div>
        <div class="flex justify-between py-2 border-b">
            <span class="text-gray-500">Durée</span>
            <span class="font-medium">{{ $booking->total_days }} jour(s)</span>
        </div>
        <div class="flex justify-between py-2">
            <span class="text-gray-500">Total estimé</span>
            <span class="font-bold">{{ number_format($booking->total_price, 0, ',', ' ') }} DA</span>
        </div>
    </div>

    @if($agency->whatsapp)
        <a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}?text={{ urlencode('Bonjour, ma demande de réservation '.$booking->reference) }}"
           class="inline-block mt-6 bg-dz-green text-white font-semibold px-5 py-3 rounded-xl hover:bg-dz-greendark">
            Contacter l'agence sur WhatsApp
        </a>
    @endif
    <div class="mt-4"><a href="{{ route('public.home') }}" class="text-sm text-gray-500 hover:underline">← Retour à l'accueil</a></div>
</div>
@endsection
