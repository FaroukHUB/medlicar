@extends('public.layout')
@section('title', 'Mes documents — '.$agency->name)

@section('content')
<div class="max-w-xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-extrabold text-gray-900">Vos documents</h1>
    <p class="mt-2 text-gray-600">Réservation <span class="font-semibold text-brand-primary">{{ $booking->reference }}</span> — {{ $booking->vehicle?->full_name }}</p>
    <p class="mt-1 text-sm text-gray-500">Téléversez votre permis et votre pièce d'identité pour accélérer la prise en charge.</p>

    @if(session('docs_success'))
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">✅ Documents reçus, merci !</div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-brand-secondary">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    @php $c = $booking->customer; @endphp
    <form method="POST" action="{{ route('public.documents.store', $booking->client_token) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Permis de conduire — recto @if($c?->license_front)<span class="text-green-600 text-xs">(déjà envoyé ✓)</span>@endif</label>
            <input type="file" name="license_front" accept="image/*,application/pdf" class="w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Permis de conduire — verso @if($c?->license_back)<span class="text-green-600 text-xs">(déjà envoyé ✓)</span>@endif</label>
            <input type="file" name="license_back" accept="image/*,application/pdf" class="w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Pièce d'identité (CNI / passeport) @if($c?->id_document)<span class="text-green-600 text-xs">(déjà envoyé ✓)</span>@endif</label>
            <input type="file" name="id_document" accept="image/*,application/pdf" class="w-full rounded-lg ring-1 ring-gray-200 px-3 py-2">
        </div>
        <button type="submit" class="w-full rounded-xl bg-brand-primary py-3 font-bold text-white hover:bg-brand-primary-dark">Envoyer mes documents</button>
    </form>
</div>
@endsection
