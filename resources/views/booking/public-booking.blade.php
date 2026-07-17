@php
    $servicePayload = $services->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'duration' => (int) $service->duration_min + (int) $service->buffer_min,
    ])->values();

    $staffPayload = $staffMembers->map(fn ($staffMember) => [
        'id' => $staffMember->id,
        'name' => $staffMember->name,
    ])->values();

    $bookingConfig = [
        'availabilityUrl' => $availabilityUrlTemplate,
        'bookingUrl' => $bookingUrlTemplate,
        'minDate' => $bookingMinDate,
        'maxDate' => $bookingMaxDate,
        'services' => $servicePayload,
        'staffMembers' => $staffPayload,
    ];
@endphp

<x-public.layout
    :title="'Réserver chez '.$business->name.' — Reservix'"
    :description="'Choisissez un service et un créneau disponible chez '.$business->name.'.'"
    body-class="booking-page"
>
    <header class="booking-topbar">
        <div class="booking-topbar__inner">
            <x-public.brand :href="url('/')" />
            <p class="booking-provider">
                <span>Page de réservation de</span>
                <strong>{{ $business->name }}</strong>
            </p>
        </div>
    </header>

    <main id="booking-app" class="booking-main" data-booking-app>
        <div class="booking-intro">
            <p class="booking-intro__back">Réservation en ligne</p>
            <h1>Prendre rendez-vous chez {{ $business->name }}</h1>
            <p>Choisissez ce dont vous avez besoin. Nous afficherons uniquement les horaires réellement disponibles.</p>
        </div>

        <nav class="booking-progress" aria-label="Progression de la réservation">
            <button class="booking-progress__item" type="button" data-progress-step="1" data-state="active" data-short-label="Service">1. Service</button>
            <button class="booking-progress__item" type="button" data-progress-step="2" data-state="upcoming" data-short-label="Date">2. Date</button>
            <button class="booking-progress__item" type="button" data-progress-step="3" data-state="upcoming" data-short-label="Heure">3. Créneau</button>
            <button class="booking-progress__item" type="button" data-progress-step="4" data-state="upcoming" data-short-label="Infos">4. Coordonnées</button>
        </nav>

        <div class="booking-layout">
            <div>
                <div id="message" class="booking-message" role="status" aria-live="polite" hidden></div>

                <form id="booking-form" class="booking-flow" novalidate>
                    <input id="service_id" name="service_id" type="hidden">

                    <section class="booking-step" data-step="1" data-state="active">
                        <div class="booking-step__header">
                            <span class="booking-step__number" aria-hidden="true">1</span>
                            <div>
                                <h2 class="booking-step__title">Quel service souhaitez-vous ?</h2>
                                <p id="step-summary-1" class="booking-step__summary">Choisissez une option pour continuer</p>
                            </div>
                            <button class="booking-step__edit" type="button" data-edit-step="1">Modifier</button>
                        </div>

                        <div class="booking-step__body">
                            <div class="booking-choice-list">
                                @forelse ($services as $service)
                                    <button
                                        class="booking-choice"
                                        type="button"
                                        data-service-choice
                                        data-service-id="{{ $service->id }}"
                                        aria-pressed="false"
                                    >
                                        <span class="booking-choice__name">{{ $service->name }}</span>
                                        <span class="booking-choice__meta">{{ $service->duration_min + $service->buffer_min }} min</span>
                                    </button>
                                @empty
                                    <div class="booking-slot-empty">Aucun service ne peut être réservé en ligne pour le moment.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <section class="booking-step" data-step="2" data-state="upcoming">
                        <div class="booking-step__header">
                            <span class="booking-step__number" aria-hidden="true">2</span>
                            <div>
                                <h2 class="booking-step__title">Quel jour vous convient ?</h2>
                                <p id="step-summary-2" class="booking-step__summary">La date du rendez-vous</p>
                            </div>
                            <button class="booking-step__edit" type="button" data-edit-step="2">Modifier</button>
                        </div>

                        <div class="booking-step__body">
                            <div id="quick-date-list" class="booking-date-list" aria-label="Prochaines dates"></div>
                            <div class="booking-date-custom">
                                <label for="date">Ou choisir une autre date</label>
                                <input id="date" name="date" type="date">
                            </div>
                        </div>
                    </section>

                    <section class="booking-step" data-step="3" data-state="upcoming">
                        <div class="booking-step__header">
                            <span class="booking-step__number" aria-hidden="true">3</span>
                            <div>
                                <h2 class="booking-step__title">À quelle heure ?</h2>
                                <p id="step-summary-3" class="booking-step__summary">Les créneaux disponibles</p>
                            </div>
                            <button class="booking-step__edit" type="button" data-edit-step="3">Modifier</button>
                        </div>

                        <div class="booking-step__body">
                            <div class="booking-slots-toolbar">
                                <p id="slots-loading" class="booking-slots-status" hidden>Recherche des disponibilités…</p>
                                <span id="slots-ready-label" class="text-sm text-slate-500">Sélectionnez une date.</span>
                                <button id="refresh-slots" class="booking-refresh" type="button">Actualiser</button>
                            </div>

                            <div id="slot-empty-state" class="booking-slot-empty">Sélectionnez d’abord un service et une date.</div>
                            <div id="slot-list"></div>
                        </div>
                    </section>

                    <section class="booking-step" data-step="4" data-state="upcoming">
                        <div class="booking-step__header">
                            <span class="booking-step__number" aria-hidden="true">4</span>
                            <div>
                                <h2 class="booking-step__title">À qui réservons-nous ce créneau ?</h2>
                                <p id="step-summary-4" class="booking-step__summary">Vos coordonnées de confirmation</p>
                            </div>
                            <button class="booking-step__edit" type="button" data-edit-step="4">Modifier</button>
                        </div>

                        <div class="booking-step__body">
                            <div class="booking-fields booking-fields--two">
                                <div class="booking-field">
                                    <label for="customer_name">Nom complet</label>
                                    <input id="customer_name" name="customer_name" type="text" autocomplete="name" placeholder="Votre nom" required>
                                </div>
                                <div class="booking-field">
                                    <label for="customer_phone">Téléphone <span>facultatif</span></label>
                                    <input id="customer_phone" name="customer_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+1 418 555 0123">
                                </div>
                            </div>

                            <div class="booking-fields mt-4">
                                <div class="booking-field">
                                    <label for="customer_email">Adresse email</label>
                                    <input id="customer_email" name="customer_email" type="email" autocomplete="email" placeholder="vous@exemple.com" required>
                                </div>
                                <div class="booking-field">
                                    <label for="notes">Note pour l’établissement <span>facultatif</span></label>
                                    <textarea id="notes" name="notes" rows="3" placeholder="Une précision utile avant le rendez-vous"></textarea>
                                </div>
                            </div>

                            <button id="confirm-booking" class="booking-submit" type="submit" disabled>Confirmer la réservation</button>
                            <p class="booking-consent">En confirmant, vous acceptez de recevoir les informations liées à ce rendez-vous par email et, si fourni, par SMS.</p>
                        </div>
                    </section>
                </form>
            </div>

            <aside class="booking-summary" aria-label="Récapitulatif">
                <h2>Votre rendez-vous</h2>
                <p class="booking-summary__business">{{ $business->name }}</p>
                <dl>
                    <div><dt>Service</dt><dd id="summary-service">À choisir</dd></div>
                    <div><dt>Date</dt><dd id="summary-date">À choisir</dd></div>
                    <div><dt>Heure</dt><dd id="summary-slot">À choisir</dd></div>
                </dl>
                <p class="booking-summary__note">
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 9V7a5 5 0 0 1 10 0v2M4 9h12v8H4V9Z"/></svg>
                    Le créneau n’est bloqué qu’après votre confirmation.
                </p>
            </aside>
        </div>
    </main>

    <script id="booking-config" type="application/json">@json($bookingConfig)</script>
</x-public.layout>
