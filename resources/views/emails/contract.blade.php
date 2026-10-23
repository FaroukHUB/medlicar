<p>Bonjour {{ $booking->customer?->full_name ?? '' }},</p>

<p>Veuillez trouver ci-joint votre contrat de location <strong>{{ $booking->reference }}</strong>
pour le véhicule <strong>{{ $booking->vehicle?->full_name }}</strong>,
du {{ $booking->start_date?->format('d/m/Y') }} au {{ $booking->end_date?->format('d/m/Y') }}.</p>

<p>Pour toute question, n'hésitez pas à nous contacter.</p>

<p>Cordialement,<br>{{ $agency->name }}
@if($agency->phone)<br>{{ $agency->phone }}@endif</p>
