<x-public.layout
    title="Réservation confirmée — Reservix"
    :description="'Votre rendez-vous chez '.$business->name.' est confirmé.'"
    body-class="transaction-page"
>
    <header class="booking-topbar">
        <div class="booking-topbar__inner">
            <x-public.brand :href="url('/')" />
            <p class="booking-provider"><span>Réservation auprès de</span><strong>{{ $business->name }}</strong></p>
        </div>
    </header>

    <main class="transaction-main">
        <section class="transaction-card">
            <div class="transaction-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 12 4 4 8-9"/></svg>
            </div>
            <p class="transaction-kicker">Réservation confirmée</p>
            <h1>C’est réservé, {{ $booking->customer_name }}.</h1>
            <p class="transaction-lead">Votre rendez-vous chez {{ $business->name }} est bien enregistré. Conservez cette page : elle contient tous les détails utiles.</p>

            <dl class="transaction-details">
                <div><dt>Établissement</dt><dd>{{ $business->name }}</dd></div>
                <div><dt>Service</dt><dd>{{ $booking->service?->name ?? '—' }}</dd></div>
                <div><dt>Avec</dt><dd>{{ $booking->staff?->name ?? '—' }}</dd></div>
                <div><dt>Date</dt><dd>{{ $booking->date }}</dd></div>
                <div><dt>Heure</dt><dd>{{ $booking->start_time }} – {{ $booking->end_time }}</dd></div>
                <div><dt>Statut</dt><dd>{{ $booking->status === 'confirmed' ? 'Confirmé' : ucfirst($booking->status) }}</dd></div>
            </dl>

            <div class="transaction-actions">
                <a class="transaction-link" href="{{ route('public.booking.page', ['business' => $business->slug]) }}">Faire une autre réservation</a>
                @if ($booking->status !== 'canceled')
                    <a class="transaction-danger" href="{{ $cancelUrl }}">Annuler cette réservation</a>
                @endif
            </div>
        </section>
    </main>
</x-public.layout>
