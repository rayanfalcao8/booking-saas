<x-mail::message>
# Réservation confirmée

Bonjour {{ $booking->customer_name }},

Votre réservation chez **{{ $businessName }}** est confirmée.

<x-mail::panel>
Service : {{ $serviceName }}  
Prestataire : {{ $staffName }}  
Date : {{ $bookingDate }}  
Heure : {{ $booking->start_time }} - {{ $booking->end_time }}
</x-mail::panel>

<x-mail::button :url="$confirmationUrl">
Voir ma réservation
</x-mail::button>

@if ($cancelUrl)
Vous pouvez annuler votre réservation avant l’heure prévue avec le lien ci-dessous.

<x-mail::button :url="$cancelUrl" color="error">
Annuler ma réservation
</x-mail::button>
@endif

Merci pour votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
