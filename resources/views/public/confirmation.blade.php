@extends('public.layout')
@section('title', 'Demande envoyée — '.$agency->name)

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    @if(session('pay_success') || $booking->advance_status === 'paid')
        <div class="text-6xl">🎉</div>
        <h1 class="mt-4 text-2xl font-extrabold text-gray-900">Acompte payé, réservation confirmée !</h1>
        <p class="mt-2 text-gray-600">Merci, votre acompte a bien été reçu. Votre réservation est confirmée.</p>
    @else
        <div class="text-6xl">✅</div>
        <h1 class="mt-4 text-2xl font-extrabold text-gray-900">Demande envoyée !</h1>
        <p class="mt-2 text-gray-600">Merci, votre demande de réservation a bien été reçue. L'agence va vous contacter pour la confirmer.</p>
    @endif

    @if(session('pay_error'))
        <div class="mt-4 bg-red-50 text-brand-secondary text-sm rounded-lg p-3">{{ session('pay_error') }}</div>
    @endif

    <div class="mt-6 bg-white rounded-2xl ring-1 ring-gray-100 shadow-sm p-6 text-left">
        <div class="flex justify-between py-2 border-b">
            <span class="text-gray-500">Référence</span>
            <span class="font-bold text-brand-primary">{{ $booking->reference }}</span>
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
            <span class="font-bold">{{ number_format($booking->total_price, 0, ',', ' ') }} DA{{ $agency->toEur($booking->total_price) ? ' / '.$agency->toEur($booking->total_price).' €' : '' }}</span>
        </div>
    </div>

    @if($canPay)
        <div class="mt-6 bg-white rounded-2xl ring-1 ring-blue-100 shadow-sm p-6">
            <div class="text-sm text-gray-600">Réglez votre acompte en ligne pour confirmer immédiatement votre réservation :</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
                {{ number_format($booking->advance_amount, 0, ',', ' ') }} DA
                <span class="text-base font-normal text-gray-500">≈ {{ number_format($payAmount, 2, ',', ' ') }} {{ $payCurrency }}</span>
            </div>
            <a href="{{ route('public.pay', $booking->reference) }}"
               class="inline-flex items-center gap-2 mt-4 bg-[#0070ba] hover:bg-[#005ea6] text-white font-semibold px-6 py-3 rounded-xl">
                Payer l'acompte avec PayPal
            </a>
            <p class="text-xs text-gray-400 mt-2">Paiement sécurisé via PayPal.</p>
        </div>
    @endif

    @if($booking->client_token)
        <a href="{{ route('public.documents', $booking->client_token) }}"
           class="inline-block mt-6 mr-2 bg-brand-primary text-white font-semibold px-5 py-3 rounded-xl hover:bg-brand-primary-dark">
            📄 Téléverser mes documents
        </a>
    @endif

    @if($agency->whatsapp)
        <a href="https://wa.me/{{ preg_replace('/\D/','',$agency->whatsapp) }}?text={{ urlencode('Bonjour, ma demande de réservation '.$booking->reference) }}"
           class="inline-block mt-6 bg-brand-primary text-white font-semibold px-5 py-3 rounded-xl hover:bg-brand-primary-dark">
            Contacter l'agence sur WhatsApp
        </a>
    @endif
    <div class="mt-4"><a href="{{ route('public.home') }}" class="text-sm text-gray-500 hover:underline">← Retour à l'accueil</a></div>
</div>
@endsection
