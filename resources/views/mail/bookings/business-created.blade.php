<x-mail::message>
# Nouvelle réservation

Une nouvelle réservation vient d’être confirmée pour **{{ $businessName }}**.

<x-mail::panel>
Client : {{ $booking->customer_name }}  
Email : {{ $booking->customer_email ?: '-' }}  
Téléphone : {{ $booking->customer_phone ?: '-' }}  
Service : {{ $serviceName }}  
Prestataire : {{ $staffName }}  
Date : {{ $bookingDate }}  
Heure : {{ $booking->start_time }} - {{ $booking->end_time }}
</x-mail::panel>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
