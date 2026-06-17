@php $primary = $agency->colorPrimary(); @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de location — {{ $booking->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.4; color: #1f2937; padding: 28px; }
        .header { display: table; width: 100%; border-bottom: 3px solid {{ $primary }}; padding-bottom: 12px; margin-bottom: 18px; }
        .header .brand { display: table-cell; vertical-align: middle; }
        .header .brand h1 { font-size: 20px; color: {{ $primary }}; }
        .header .brand .sub { font-size: 10px; color: #6b7280; }
        .header .meta { display: table-cell; text-align: right; vertical-align: middle; font-size: 10px; color: #374151; }
        .header .meta .ref { font-size: 13px; font-weight: bold; color: #111827; }
        h2.title { text-align: center; font-size: 14px; margin: 6px 0 16px; text-transform: uppercase; letter-spacing: .5px; }
        .section-title { background: {{ $primary }}; color: #fff; padding: 6px 10px; font-size: 11px; font-weight: bold; margin: 14px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 4px 6px; vertical-align: top; }
        .info td.label { color: #6b7280; width: 28%; }
        .info td.val { font-weight: bold; }
        .cols { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; vertical-align: top; }
        .col:first-child { padding-right: 12px; }
        .money { width: 100%; }
        .money td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        .money td.r { text-align: right; }
        .money tr.total td { border-top: 2px solid {{ $primary }}; border-bottom: none; font-size: 13px; font-weight: bold; color: {{ $primary }}; }
        .terms { font-size: 9.5px; color: #4b5563; border: 1px solid #e5e7eb; padding: 10px; border-radius: 4px; }
        .signatures { display: table; width: 100%; margin-top: 30px; }
        .sig { display: table-cell; width: 50%; text-align: center; padding-top: 40px; }
        .sig .line { border-top: 1px solid #9ca3af; margin: 0 20px; padding-top: 4px; font-size: 10px; color: #6b7280; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .badge { display: inline-block; background: #ecfdf5; color: {{ $primary }}; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' DA';
        $cust = $booking->customer;
        $veh = $booking->vehicle;
    @endphp

    <div class="header">
        <div class="brand">
            <h1>{{ $agency->name }}</h1>
            <div class="sub">
                {{ $agency->address }}{{ $agency->city ? ', ' . $agency->city : '' }}
                @if($agency->phone) · Tél : {{ $agency->phone }} @endif
                @if($agency->email) · {{ $agency->email }} @endif
            </div>
        </div>
        <div class="meta">
            <div class="ref">{{ $booking->reference }}</div>
            <div>Le {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <h2 class="title">Contrat de location de véhicule</h2>

    <div class="cols">
        <div class="col">
            <div class="section-title">Locataire</div>
            <table class="info">
                <tr><td class="label">Nom</td><td class="val">{{ $cust?->full_name ?? '—' }}</td></tr>
                <tr><td class="label">Téléphone</td><td class="val">{{ $cust?->phone ?? '—' }}</td></tr>
                <tr><td class="label">Email</td><td class="val">{{ $cust?->email ?? '—' }}</td></tr>
                <tr><td class="label">CNI / Passeport</td><td class="val">{{ $cust?->id_number ?? '—' }}</td></tr>
                <tr><td class="label">N° de permis</td><td class="val">{{ $cust?->license_number ?? '—' }}</td></tr>
                <tr><td class="label">Adresse</td><td class="val">{{ $cust?->address ?? '—' }}</td></tr>
            </table>
        </div>
        <div class="col">
            <div class="section-title">Véhicule</div>
            <table class="info">
                <tr><td class="label">Véhicule</td><td class="val">{{ $veh?->full_name ?? '—' }}</td></tr>
                <tr><td class="label">Immatriculation</td><td class="val">{{ $veh?->plate ?? '—' }}</td></tr>
                <tr><td class="label">Catégorie</td><td class="val">{{ $veh?->category?->name ?? '—' }}</td></tr>
                <tr><td class="label">Carburant</td><td class="val">{{ $veh?->fuel_type ?? '—' }}</td></tr>
                <tr><td class="label">Boîte</td><td class="val">{{ $veh?->transmission ?? '—' }}</td></tr>
                <tr><td class="label">Km au départ</td><td class="val">{{ $booking->mileage_start ?? $veh?->mileage ?? '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section-title">Période de location</div>
    <table class="info">
        <tr>
            <td class="label">Du</td><td class="val">{{ $booking->start_date?->format('d/m/Y H:i') }}</td>
            <td class="label">Au</td><td class="val">{{ $booking->end_date?->format('d/m/Y H:i') }}</td>
            <td class="label">Durée</td><td class="val">{{ $booking->total_days }} jour(s)</td>
        </tr>
        @if($booking->pickup_location || $booking->return_location)
        <tr>
            <td class="label">Lieu de prise</td><td class="val">{{ $booking->pickup_location ?? '—' }}</td>
            <td class="label">Lieu de retour</td><td class="val" colspan="3">{{ $booking->return_location ?? '—' }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">Détail financier</div>
    <table class="money">
        <tr><td>Prix de base ({{ $booking->total_days }} jour(s))</td><td class="r">{{ $fmt($booking->base_price) }}</td></tr>
        @if((float) $booking->options_total > 0)
        <tr><td>Options / extras</td><td class="r">{{ $fmt($booking->options_total) }}</td></tr>
        @endif
        @if($booking->protection_plan === 'complete' && (float) $booking->protection_fee > 0)
        <tr><td>Protection complète</td><td class="r">{{ $fmt($booking->protection_fee) }}</td></tr>
        @endif
        @if((float) $booking->extra_fees > 0)
        <tr><td>Frais de retour</td><td class="r">{{ $fmt($booking->extra_fees) }}</td></tr>
        @endif
        @if((float) $booking->season_surcharge > 0)
        <tr><td>Supplément saison</td><td class="r">{{ $fmt($booking->season_surcharge) }}</td></tr>
        @endif
        @if((float) $booking->delivery_fee > 0)
        <tr><td>Frais de livraison</td><td class="r">{{ $fmt($booking->delivery_fee) }}</td></tr>
        @endif
        @if((float) $booking->discount_amount > 0)
        <tr><td>Remise</td><td class="r">− {{ $fmt($booking->discount_amount) }}</td></tr>
        @endif
        <tr class="total"><td>Total à payer</td><td class="r">{{ $fmt($booking->total_price) }}</td></tr>
        @if((float) $booking->advance_amount > 0)
        <tr><td>Acompte versé</td><td class="r">{{ $fmt($booking->advance_amount) }}</td></tr>
        <tr><td>Solde restant</td><td class="r">{{ $fmt($booking->total_price - $booking->advance_amount) }}</td></tr>
        @endif
        @if((float) $booking->deposit_amount > 0)
        <tr><td>Caution <span class="badge">restituable</span></td><td class="r">{{ $fmt($booking->deposit_amount) }}</td></tr>
        @endif
    </table>

    @if($agency->contract_terms)
    <div class="section-title">Conditions générales</div>
    <div class="terms">{!! $agency->contract_terms !!}</div>
    @endif

    <div class="signatures">
        <div class="sig"><div class="line">Signature du locataire</div></div>
        <div class="sig"><div class="line">Pour {{ $agency->name }}</div></div>
    </div>

    <div class="footer">
        {{ $agency->name }} — Contrat {{ $booking->reference }} — Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
