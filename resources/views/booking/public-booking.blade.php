<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver - {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-50 text-stone-950">
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
@endphp

<main class="mx-auto flex min-h-screen max-w-6xl flex-col px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-3xl">
        <div class="rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_80px_rgba(28,25,23,0.08)]">
            <div class="border-b border-stone-200 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.18),_transparent_52%),linear-gradient(135deg,_#292524,_#0c0a09)] px-5 py-8 text-white sm:px-8">
                <p class="text-sm font-medium uppercase tracking-[0.24em] text-amber-200">Reservix</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Réserver chez {{ $business->name }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-300 sm:text-base">
                    Choisissez un service, une date, puis un créneau disponible. La réservation prend moins d’une minute.
                </p>

                <ol class="mt-6 grid gap-3 text-sm text-stone-200 sm:grid-cols-5">
                    <li class="rounded-full border border-white/15 bg-white/8 px-2 py-2 items-center justify-center cursor-pointer">1. Service</li>
                    <li class="rounded-full border border-white/15 bg-white/8 px-2 py-2 items-center justify-center cursor-pointer">2. Date</li>
                    <li class="rounded-full border border-white/15 bg-white/8 px-2 py-2 items-center justify-center cursor-pointer">3. Créneau</li>
                    <li class="rounded-full border border-white/15 bg-white/8 px-2 py-2 items-center justify-center cursor-pointer">4. Coordonnées</li>
                    <li class="rounded-full border border-white/15 bg-white/8 px-2 py-2 items-center justify-center cursor-pointer">5. Confirmation</li>
                </ol>
            </div>

            <div class="grid gap-8 px-5 py-6 sm:px-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.9fr)]">
                <section class="space-y-6">
                    <div id="message" class="hidden rounded-2xl border px-4 py-3 text-sm font-medium"></div>

                    <section class="space-y-4 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Étape 1</p>
                                <h2 class="mt-1 text-lg font-semibold text-stone-900">Choisissez votre service</h2>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">Obligatoire</span>
                        </div>

                        <div class="field">
                            <label class="mb-2 block text-sm font-medium text-stone-700" for="service_id">Service</label>
                            <select
                                id="service_id"
                                class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                            >
                                <option value="">Sélectionnez un service</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">
                                        {{ $service->name }} • {{ $service->duration_min + $service->buffer_min }} min
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Étape 2</p>
                                <h2 class="mt-1 text-lg font-semibold text-stone-900">Choisissez une date</h2>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-900">Rapide</span>
                        </div>

                        <div class="field">
                            <label class="mb-2 block text-sm font-medium text-stone-700" for="date">Date</label>
                            <input
                                id="date"
                                type="date"
                                class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                            >
                        </div>
                    </section>

                    <section class="space-y-4 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Étape 3</p>
                                <h2 class="mt-1 text-lg font-semibold text-stone-900">Choisissez un créneau</h2>
                            </div>
                            <button
                                id="refresh-slots"
                                type="button"
                                class="rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700 transition hover:border-stone-400 hover:bg-white"
                            >
                                Actualiser
                            </button>
                        </div>

                        <div class="rounded-2xl border border-dashed border-stone-300 bg-white/80 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium text-stone-700">Disponibilités</p>
                                <div id="slots-loading" class="hidden items-center gap-2 text-xs font-medium text-stone-500">
                                    <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-amber-500"></span>
                                    Chargement des créneaux…
                                </div>
                            </div>

                            <div id="slot-empty-state" class="mt-3 rounded-2xl bg-stone-100 px-4 py-4 text-sm text-stone-600">
                                Sélectionnez d’abord un service et une date.
                            </div>

                            <div id="slot-list" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
                        </div>
                    </section>

                    <section class="space-y-4 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Étape 4</p>
                                <h2 class="mt-1 text-lg font-semibold text-stone-900">Vos informations</h2>
                            </div>
                            <span class="rounded-full bg-stone-200 px-3 py-1 text-xs font-semibold text-stone-700">Nom requis</span>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-stone-700" for="customer_name">Nom</label>
                                <input
                                    id="customer_name"
                                    type="text"
                                    placeholder="Votre nom"
                                    class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-stone-700" for="customer_phone">Téléphone</label>
                                <input
                                    id="customer_phone"
                                    type="text"
                                    placeholder="06 00 00 00 00"
                                    class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-stone-700" for="customer_email">Email</label>
                            <input
                                id="customer_email"
                                type="email"
                                placeholder="vous@exemple.com"
                                class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-stone-700" for="notes">Notes pour le rendez-vous</label>
                            <textarea
                                id="notes"
                                rows="3"
                                placeholder="Optionnel"
                                class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 outline-none ring-0 transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                            ></textarea>
                        </div>
                    </section>
                </section>

                <aside class="space-y-4">
                    <section class="sticky top-6 rounded-3xl border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_16px_60px_rgba(28,25,23,0.18)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-300">Étape 5</p>
                        <h2 class="mt-2 text-2xl font-semibold">Confirmer la réservation</h2>
                        <p class="mt-2 text-sm leading-6 text-stone-300">
                            Vérifiez votre sélection. Une confirmation s’affichera juste après la réservation.
                        </p>

                        <div class="mt-6 space-y-3 rounded-3xl border border-white/10 bg-white/5 p-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Business</p>
                                <p class="mt-1 text-base font-medium text-white">{{ $business->name }}</p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Service</p>
                                <p id="summary-service" class="mt-1 text-base font-medium text-white">À choisir</p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Date</p>
                                <p id="summary-date" class="mt-1 text-base font-medium text-white">À choisir</p>
                            </div>

                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-stone-400">Créneau</p>
                                <p id="summary-slot" class="mt-1 text-base font-medium text-white">Aucun créneau sélectionné</p>
                            </div>
                        </div>

                        <button
                            id="confirm-booking"
                            type="button"
                            class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-5 py-3 text-base font-semibold text-stone-950 transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:bg-stone-700 disabled:text-stone-400"
                            disabled
                        >
                            Confirmer la réservation
                        </button>

                        <p class="mt-3 text-xs leading-5 text-stone-400">
                            En confirmant, vous recevrez les détails du rendez-vous et un lien d’annulation si disponible.
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</main>

<script>
    const availabilityUrl = @json($availabilityUrlTemplate);
    const bookingUrl = @json($bookingUrlTemplate);
    const services = @json($servicePayload);
    const staffMembers = @json($staffPayload);

    const state = {
        selectedSlot: null,
        isLoadingSlots: false,
        isSubmitting: false,
    };

    const serviceSelect = document.getElementById('service_id');
    const dateInput = document.getElementById('date');
    const customerNameInput = document.getElementById('customer_name');
    const customerPhoneInput = document.getElementById('customer_phone');
    const customerEmailInput = document.getElementById('customer_email');
    const notesInput = document.getElementById('notes');
    const refreshSlotsButton = document.getElementById('refresh-slots');
    const confirmBookingButton = document.getElementById('confirm-booking');
    const slotList = document.getElementById('slot-list');
    const slotEmptyState = document.getElementById('slot-empty-state');
    const slotsLoading = document.getElementById('slots-loading');
    const messageBox = document.getElementById('message');
    const summaryService = document.getElementById('summary-service');
    const summaryDate = document.getElementById('summary-date');
    const summarySlot = document.getElementById('summary-slot');

    const dateFormatter = new Intl.DateTimeFormat('fr-CA', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

    dateInput.min = new Date().toISOString().split('T')[0];

    function setMessage(type, content) {
        const baseClass = 'rounded-2xl border px-4 py-3 text-sm font-medium';
        const variants = {
            success: `${baseClass} block border-emerald-200 bg-emerald-50 text-emerald-800`,
            error: `${baseClass} block border-red-200 bg-red-50 text-red-800`,
            info: `${baseClass} block border-stone-200 bg-stone-100 text-stone-700`,
        };

        messageBox.className = variants[type] ?? variants.info;
        messageBox.textContent = content;
        messageBox.classList.remove('hidden');
    }

    function clearMessage() {
        messageBox.textContent = '';
        messageBox.className = 'hidden rounded-2xl border px-4 py-3 text-sm font-medium';
    }

    function formatSelectedDate(date) {
        if (!date) {
            return 'À choisir';
        }

        return dateFormatter.format(new Date(`${date}T00:00:00`));
    }

    function updateSummary() {
        const selectedService = services.find((service) => String(service.id) === serviceSelect.value);

        summaryService.textContent = selectedService
            ? `${selectedService.name} • ${selectedService.duration} min`
            : 'À choisir';

        summaryDate.textContent = formatSelectedDate(dateInput.value);
        summarySlot.textContent = state.selectedSlot
            ? `${state.selectedSlot.time} • ${state.selectedSlot.staffName}`
            : 'Aucun créneau sélectionné';
    }

    function updateSubmitState() {
        const hasRequiredValues = serviceSelect.value !== ''
            && dateInput.value !== ''
            && state.selectedSlot !== null
            && customerNameInput.value.trim() !== '';

        confirmBookingButton.disabled = state.isSubmitting || state.isLoadingSlots || !hasRequiredValues;
        confirmBookingButton.textContent = state.isSubmitting
            ? 'Réservation en cours…'
            : 'Confirmer la réservation';
    }

    function resetSlotSelection() {
        state.selectedSlot = null;
        document.querySelectorAll('[data-slot-button]').forEach((button) => {
            button.classList.remove('border-amber-500', 'bg-amber-400', 'text-stone-950', 'shadow-sm');
            button.classList.add('border-stone-200', 'bg-white', 'text-stone-800');
        });
        updateSummary();
        updateSubmitState();
    }

    function setSlotsLoadingState(isLoading) {
        state.isLoadingSlots = isLoading;
        slotsLoading.classList.toggle('hidden', !isLoading);
        slotsLoading.classList.toggle('flex', isLoading);
        refreshSlotsButton.disabled = isLoading;
        refreshSlotsButton.classList.toggle('opacity-50', isLoading);
        updateSubmitState();
    }

    function showEmptyState(content) {
        slotEmptyState.textContent = content;
        slotEmptyState.classList.remove('hidden');
        slotList.innerHTML = '';
    }

    function createSlotButton(slot) {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.slotButton = 'true';
        button.className = 'rounded-2xl border border-stone-200 bg-white px-3 py-3 text-left text-sm text-stone-800 transition hover:border-amber-300 hover:bg-amber-50';
        button.innerHTML = `
            <span class="block text-base font-semibold">${slot.time}</span>
            <span class="mt-1 block text-xs text-stone-500">${slot.staffName}</span>
        `;

        button.addEventListener('click', () => {
            document.querySelectorAll('[data-slot-button]').forEach((item) => {
                item.classList.remove('border-amber-500', 'bg-amber-400', 'text-stone-950', 'shadow-sm');
                item.classList.add('border-stone-200', 'bg-white', 'text-stone-800');
            });

            button.classList.remove('border-stone-200', 'bg-white', 'text-stone-800');
            button.classList.add('border-amber-500', 'bg-amber-400', 'text-stone-950', 'shadow-sm');

            state.selectedSlot = slot;
            clearMessage();
            updateSummary();
            updateSubmitState();
        });

        return button;
    }

    async function fetchAvailabilityForStaff(staffMember, serviceId, date) {
        const url = new URL(availabilityUrl, window.location.origin);
        url.searchParams.set('service_id', serviceId);
        url.searchParams.set('staff_id', staffMember.id);
        url.searchParams.set('date', date);

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;

            throw new Error(firstError || payload?.message || 'Impossible de charger les disponibilités.');
        }

        return Array.isArray(payload.slots)
            ? payload.slots.map((slot) => ({
                time: slot,
                staffId: staffMember.id,
                staffName: staffMember.name,
              }))
            : [];
    }

    async function loadSlots() {
        clearMessage();
        resetSlotSelection();

        const serviceId = serviceSelect.value;
        const date = dateInput.value;

        if (!serviceId || !date) {
            showEmptyState('Sélectionnez un service et une date pour voir les créneaux.');
            return;
        }

        setSlotsLoadingState(true);

        try {
            const groupedSlots = await Promise.all(
                staffMembers.map((staffMember) => fetchAvailabilityForStaff(staffMember, serviceId, date))
            );

            const slots = groupedSlots
                .flat()
                .sort((first, second) => {
                    if (first.time === second.time) {
                        return first.staffName.localeCompare(second.staffName);
                    }

                    return first.time.localeCompare(second.time);
                });

            slotList.innerHTML = '';

            if (slots.length === 0) {
                showEmptyState('Aucun créneau disponible pour cette date. Essayez une autre journée.');
                return;
            }

            slotEmptyState.classList.add('hidden');
            slots.forEach((slot) => {
                slotList.appendChild(createSlotButton(slot));
            });

            setMessage('info', `${slots.length} créneau(x) disponible(s). Sélectionnez celui qui vous convient.`);
        } catch (error) {
            showEmptyState('Impossible de charger les créneaux pour le moment.');
            setMessage('error', error.message || 'Impossible de charger les créneaux.');
        } finally {
            setSlotsLoadingState(false);
        }
    }

    async function confirmBooking() {
        clearMessage();

        if (!state.selectedSlot) {
            setMessage('error', 'Veuillez choisir un créneau avant de confirmer.');
            return;
        }

        state.isSubmitting = true;
        updateSubmitState();

        try {
            const response = await fetch(bookingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    service_id: serviceSelect.value,
                    staff_id: state.selectedSlot.staffId,
                    date: dateInput.value,
                    start_time: state.selectedSlot.time,
                    customer_name: customerNameInput.value.trim(),
                    customer_email: customerEmailInput.value.trim() || null,
                    customer_phone: customerPhoneInput.value.trim() || null,
                    notes: notesInput.value.trim() || null,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;
                setMessage('error', firstError || payload?.message || 'La réservation a échoué.');
                return;
            }

            setMessage('success', 'Réservation confirmée. Redirection en cours…');

            if (typeof payload.confirmation_url === 'string' && payload.confirmation_url !== '') {
                window.location.href = payload.confirmation_url;
            }
        } catch (error) {
            setMessage('error', 'Une erreur réseau est survenue. Veuillez réessayer.');
        } finally {
            state.isSubmitting = false;
            updateSubmitState();
        }
    }

    serviceSelect.addEventListener('change', () => {
        updateSummary();
        loadSlots();
    });

    dateInput.addEventListener('change', () => {
        updateSummary();
        loadSlots();
    });

    customerNameInput.addEventListener('input', updateSubmitState);
    refreshSlotsButton.addEventListener('click', loadSlots);
    confirmBookingButton.addEventListener('click', confirmBooking);

    updateSummary();
    updateSubmitState();
    showEmptyState('Sélectionnez un service et une date pour voir les créneaux.');
</script>
</body>
</html>
