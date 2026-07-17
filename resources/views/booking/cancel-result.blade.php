@php
    $isSuccess = $status === 'success';
    $isConfirmation = $status === 'confirm';
    $title = $isSuccess ? 'Votre réservation est annulée' : ($isConfirmation ? 'Annuler cette réservation ?' : 'Impossible d’annuler');
@endphp

<x-public.layout
    :title="$title.' — Reservix'"
    description="Gérez l’annulation de votre réservation Reservix."
    body-class="transaction-page"
>
    <header class="booking-topbar">
        <div class="booking-topbar__inner">
            <x-public.brand :href="url('/')" />
            @if ($business)
                <p class="booking-provider"><span>Réservation auprès de</span><strong>{{ $business->name }}</strong></p>
            @endif
        </div>
    </header>

    <main class="transaction-main">
        <section class="transaction-card">
            <div class="transaction-icon {{ $isConfirmation ? 'transaction-icon--warning' : ($isSuccess ? '' : 'transaction-icon--error') }}">
                @if ($isSuccess)
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-9"/></svg>
                @elseif ($isConfirmation)
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
                @else
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 8 8 8M16 8l-8 8"/><circle cx="12" cy="12" r="9"/></svg>
                @endif
            </div>
            <p class="transaction-kicker">{{ $isSuccess ? 'Annulation confirmée' : ($isConfirmation ? 'Confirmation requise' : 'Lien indisponible') }}</p>
            <h1>{{ $title }}</h1>
            <p class="transaction-lead">{{ $message }}</p>

            @if ($booking)
                <dl class="transaction-details">
                    <div><dt>Établissement</dt><dd>{{ $business->name }}</dd></div>
                    <div><dt>Service</dt><dd>{{ $booking->service?->name ?? '—' }}</dd></div>
                    <div><dt>Avec</dt><dd>{{ $booking->staff?->name ?? '—' }}</dd></div>
                    <div><dt>Date et heure</dt><dd>{{ $booking->date }} · {{ $booking->start_time }}</dd></div>
                </dl>
            @endif

            <div class="transaction-actions">
                @if ($isConfirmation && $cancelAction)
                    <form method="POST" action="{{ $cancelAction }}">
                        @csrf
                        <button class="transaction-danger" type="submit">Confirmer l’annulation</button>
                    </form>
                    <a class="transaction-link" href="{{ route('public.booking.confirmation', ['business' => $business->slug, 'booking' => $booking->id, 'token' => request()->route('token')]) }}">Garder mon rendez-vous</a>
                @elseif ($isSuccess && $business)
                    <a class="transaction-link" href="{{ route('public.booking.page', ['business' => $business->slug]) }}">Choisir un autre créneau</a>
                @else
                    <a class="transaction-link" href="{{ url('/') }}">Retour à l’accueil</a>
                @endif
            </div>
        </section>
    </main>
</x-public.layout>
