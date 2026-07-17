const app = document.querySelector('[data-booking-app]');
const configNode = document.getElementById('booking-config');

if (app && configNode) {
    const config = JSON.parse(configNode.textContent);
    const services = Array.isArray(config.services) ? config.services : [];
    const staffMembers = Array.isArray(config.staffMembers) ? config.staffMembers : [];

    const state = {
        currentStep: 1,
        selectedSlot: null,
        isLoadingSlots: false,
        isSubmitting: false,
        slotsRequestId: 0,
    };

    const elements = {
        form: document.getElementById('booking-form'),
        serviceInput: document.getElementById('service_id'),
        dateInput: document.getElementById('date'),
        customerName: document.getElementById('customer_name'),
        customerPhone: document.getElementById('customer_phone'),
        customerEmail: document.getElementById('customer_email'),
        notes: document.getElementById('notes'),
        message: document.getElementById('message'),
        quickDates: document.getElementById('quick-date-list'),
        slotList: document.getElementById('slot-list'),
        slotEmpty: document.getElementById('slot-empty-state'),
        slotsLoading: document.getElementById('slots-loading'),
        slotsReadyLabel: document.getElementById('slots-ready-label'),
        refreshSlots: document.getElementById('refresh-slots'),
        submit: document.getElementById('confirm-booking'),
        summaryService: document.getElementById('summary-service'),
        summaryDate: document.getElementById('summary-date'),
        summarySlot: document.getElementById('summary-slot'),
        stepSummaryService: document.getElementById('step-summary-1'),
        stepSummaryDate: document.getElementById('step-summary-2'),
        stepSummarySlot: document.getElementById('step-summary-3'),
        stepSummaryContact: document.getElementById('step-summary-4'),
    };

    const longDateFormatter = new Intl.DateTimeFormat('fr-CA', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    const dayFormatter = new Intl.DateTimeFormat('fr-CA', { weekday: 'short' });

    function parseDate(isoDate) {
        return new Date(`${isoDate}T12:00:00`);
    }

    function toIsoDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatDate(isoDate) {
        if (!isoDate) {
            return 'À choisir';
        }

        return longDateFormatter.format(parseDate(isoDate));
    }

    function setMessage(type, content) {
        elements.message.dataset.type = type;
        elements.message.textContent = content;
        elements.message.hidden = false;
    }

    function clearMessage() {
        elements.message.textContent = '';
        elements.message.hidden = true;
        delete elements.message.dataset.type;
    }

    function scrollToStep(step) {
        if (window.matchMedia('(max-width: 899px)').matches) {
            document.querySelector(`[data-step="${step}"]`)?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }
    }

    function openStep(step, shouldScroll = true) {
        state.currentStep = step;

        document.querySelectorAll('[data-step]').forEach((section) => {
            const sectionStep = Number(section.dataset.step);
            section.dataset.state = sectionStep === step
                ? 'active'
                : (sectionStep < step ? 'complete' : 'upcoming');
        });

        document.querySelectorAll('[data-progress-step]').forEach((button) => {
            const buttonStep = Number(button.dataset.progressStep);
            button.dataset.state = buttonStep === step
                ? 'active'
                : (buttonStep < step ? 'complete' : 'upcoming');
            button.disabled = buttonStep > step;
            button.setAttribute('aria-current', buttonStep === step ? 'step' : 'false');
        });

        if (shouldScroll) {
            scrollToStep(step);
        }
    }

    function selectedService() {
        return services.find((service) => String(service.id) === elements.serviceInput.value) ?? null;
    }

    function updateSummary() {
        const service = selectedService();
        const serviceLabel = service ? `${service.name} · ${service.duration} min` : 'À choisir';
        const dateLabel = formatDate(elements.dateInput.value);
        const slotLabel = state.selectedSlot
            ? `${state.selectedSlot.time} · ${state.selectedSlot.staffName}`
            : 'À choisir';

        elements.summaryService.textContent = serviceLabel;
        elements.summaryDate.textContent = dateLabel;
        elements.summarySlot.textContent = slotLabel;
        elements.stepSummaryService.textContent = serviceLabel;
        elements.stepSummaryDate.textContent = elements.dateInput.value ? dateLabel : 'La date du rendez-vous';
        elements.stepSummarySlot.textContent = state.selectedSlot ? slotLabel : 'Les créneaux disponibles';
        elements.stepSummaryContact.textContent = elements.customerName.value.trim()
            ? elements.customerName.value.trim()
            : 'Vos coordonnées de confirmation';
    }

    function updateSubmitState() {
        const canSubmit = elements.serviceInput.value !== ''
            && elements.dateInput.value !== ''
            && state.selectedSlot !== null
            && elements.customerName.value.trim() !== ''
            && elements.customerEmail.value.trim() !== '';

        elements.submit.disabled = state.isSubmitting || state.isLoadingSlots || !canSubmit;
        elements.submit.textContent = state.isSubmitting
            ? 'Confirmation en cours…'
            : 'Confirmer la réservation';
    }

    function resetSlotSelection() {
        state.selectedSlot = null;
        elements.slotList.querySelectorAll('[data-slot-button]').forEach((button) => {
            button.setAttribute('aria-pressed', 'false');
        });
        updateSummary();
        updateSubmitState();
    }

    function renderQuickDates() {
        elements.quickDates.replaceChildren();

        const firstDate = parseDate(config.minDate);
        const maximumDate = parseDate(config.maxDate);

        for (let offset = 0; offset < 8; offset += 1) {
            const date = new Date(firstDate);
            date.setDate(firstDate.getDate() + offset);

            if (date > maximumDate) {
                break;
            }

            const isoDate = toIsoDate(date);
            const button = document.createElement('button');
            const day = document.createElement('span');
            const number = document.createElement('strong');

            button.type = 'button';
            button.className = 'booking-date-choice';
            button.dataset.quickDate = isoDate;
            button.setAttribute('aria-pressed', String(elements.dateInput.value === isoDate));
            button.setAttribute('aria-label', formatDate(isoDate));
            day.textContent = dayFormatter.format(date).replace('.', '');
            number.textContent = String(date.getDate());
            button.append(day, number);
            button.addEventListener('click', () => selectDate(isoDate));
            elements.quickDates.appendChild(button);
        }
    }

    function renderSelectedDate() {
        document.querySelectorAll('[data-quick-date]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.quickDate === elements.dateInput.value));
        });
    }

    function setSlotsLoading(isLoading) {
        state.isLoadingSlots = isLoading;
        elements.slotsLoading.hidden = !isLoading;
        elements.slotsReadyLabel.hidden = isLoading;
        elements.refreshSlots.disabled = isLoading;
        updateSubmitState();
    }

    function showSlotEmpty(content) {
        elements.slotEmpty.textContent = content;
        elements.slotEmpty.hidden = false;
        elements.slotList.hidden = true;
        elements.slotList.replaceChildren();
    }

    function slotPeriod(time) {
        const hour = Number(time.split(':')[0]);

        if (hour < 12) {
            return 'Matin';
        }

        if (hour < 17) {
            return 'Après-midi';
        }

        return 'Soir';
    }

    function renderSlots(slots) {
        const groups = new Map();

        slots.forEach((slot) => {
            const period = slotPeriod(slot.time);
            const periodSlots = groups.get(period) ?? [];
            periodSlots.push(slot);
            groups.set(period, periodSlots);
        });

        elements.slotList.replaceChildren();
        elements.slotList.hidden = false;
        elements.slotEmpty.hidden = true;

        groups.forEach((periodSlots, period) => {
            const group = document.createElement('section');
            const heading = document.createElement('h3');
            const grid = document.createElement('div');

            group.className = 'booking-slot-group';
            heading.textContent = period;
            grid.className = 'booking-slot-grid';

            periodSlots.forEach((slot) => {
                const button = document.createElement('button');
                const time = document.createElement('strong');
                const staffName = document.createElement('span');

                button.type = 'button';
                button.className = 'booking-slot';
                button.dataset.slotButton = 'true';
                button.setAttribute('aria-pressed', 'false');
                time.textContent = slot.time;
                staffName.textContent = slot.staffName;
                button.append(time, staffName);

                button.addEventListener('click', () => {
                    elements.slotList.querySelectorAll('[data-slot-button]').forEach((slotButton) => {
                        slotButton.setAttribute('aria-pressed', 'false');
                    });
                    button.setAttribute('aria-pressed', 'true');
                    state.selectedSlot = slot;
                    clearMessage();
                    updateSummary();
                    updateSubmitState();
                    openStep(4);
                    elements.customerName.focus({ preventScroll: true });
                });

                grid.appendChild(button);
            });

            group.append(heading, grid);
            elements.slotList.appendChild(group);
        });
    }

    async function fetchAvailabilityForStaff(staffMember, serviceId, date) {
        const url = new URL(config.availabilityUrl, window.location.origin);
        url.searchParams.set('service_id', serviceId);
        url.searchParams.set('staff_id', staffMember.id);
        url.searchParams.set('date', date);

        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
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
        const requestId = ++state.slotsRequestId;

        clearMessage();
        resetSlotSelection();

        const serviceId = elements.serviceInput.value;
        const date = elements.dateInput.value;

        if (!serviceId || !date) {
            showSlotEmpty('Sélectionnez un service et une date pour voir les créneaux.');
            return;
        }

        if (staffMembers.length === 0) {
            showSlotEmpty('Aucune disponibilité ne peut être proposée pour le moment.');
            return;
        }

        setSlotsLoading(true);

        try {
            const results = await Promise.all(
                staffMembers.map((staffMember) => fetchAvailabilityForStaff(staffMember, serviceId, date)),
            );

            if (requestId !== state.slotsRequestId) {
                return;
            }

            const slots = results.flat().sort((first, second) => {
                if (first.time === second.time) {
                    return first.staffName.localeCompare(second.staffName);
                }

                return first.time.localeCompare(second.time);
            });

            if (slots.length === 0) {
                showSlotEmpty('Aucun créneau ce jour-là. Essayez une autre date.');
                elements.slotsReadyLabel.textContent = 'Aucun créneau disponible';
                return;
            }

            renderSlots(slots);
            elements.slotsReadyLabel.textContent = `${slots.length} créneau${slots.length > 1 ? 'x' : ''} disponible${slots.length > 1 ? 's' : ''}`;
        } catch (error) {
            if (requestId !== state.slotsRequestId) {
                return;
            }

            showSlotEmpty('Nous ne parvenons pas à charger les créneaux. Réessayez dans un instant.');
            setMessage('error', error.message || 'Impossible de charger les disponibilités.');
        } finally {
            if (requestId === state.slotsRequestId) {
                setSlotsLoading(false);
            }
        }
    }

    function selectDate(isoDate) {
        elements.dateInput.value = isoDate;
        resetSlotSelection();
        renderSelectedDate();
        updateSummary();
        openStep(3);
        loadSlots();
    }

    async function submitBooking() {
        clearMessage();

        if (!elements.form.reportValidity() || !state.selectedSlot) {
            setMessage('error', 'Complétez les informations requises avant de confirmer.');
            return;
        }

        state.isSubmitting = true;
        updateSubmitState();

        try {
            const response = await fetch(config.bookingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    service_id: elements.serviceInput.value,
                    staff_id: state.selectedSlot.staffId,
                    date: elements.dateInput.value,
                    start_time: state.selectedSlot.time,
                    customer_name: elements.customerName.value.trim(),
                    customer_email: elements.customerEmail.value.trim(),
                    customer_phone: elements.customerPhone.value.replace(/[^+\d]/g, '') || null,
                    notes: elements.notes.value.trim() || null,
                }),
            });
            const payload = await response.json();

            if (!response.ok) {
                const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;
                setMessage('error', firstError || payload?.message || 'La réservation n’a pas pu être confirmée.');
                return;
            }

            setMessage('success', 'Réservation confirmée. Ouverture de votre récapitulatif…');

            if (typeof payload.confirmation_url === 'string' && payload.confirmation_url !== '') {
                window.location.assign(payload.confirmation_url);
            }
        } catch (error) {
            setMessage('error', 'Une erreur réseau est survenue. Vérifiez votre connexion puis réessayez.');
        } finally {
            state.isSubmitting = false;
            updateSubmitState();
        }
    }

    elements.dateInput.min = config.minDate;
    elements.dateInput.max = config.maxDate;

    document.querySelectorAll('[data-service-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const serviceChanged = elements.serviceInput.value !== button.dataset.serviceId;
            elements.serviceInput.value = button.dataset.serviceId;
            document.querySelectorAll('[data-service-choice]').forEach((choice) => {
                choice.setAttribute('aria-pressed', String(choice === button));
            });

            if (serviceChanged) {
                state.slotsRequestId += 1;
                setSlotsLoading(false);
                resetSlotSelection();
            }

            clearMessage();
            updateSummary();
            openStep(2);
        });
    });

    document.querySelectorAll('[data-edit-step]').forEach((button) => {
        button.addEventListener('click', () => openStep(Number(button.dataset.editStep)));
    });

    document.querySelectorAll('[data-progress-step]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.state === 'complete') {
                openStep(Number(button.dataset.progressStep));
            }
        });
    });

    elements.dateInput.addEventListener('change', () => {
        if (elements.dateInput.value) {
            selectDate(elements.dateInput.value);
        }
    });
    elements.refreshSlots.addEventListener('click', loadSlots);
    elements.customerName.addEventListener('input', () => {
        updateSummary();
        updateSubmitState();
    });
    elements.customerEmail.addEventListener('input', updateSubmitState);
    elements.form.addEventListener('submit', (event) => {
        event.preventDefault();
        submitBooking();
    });

    renderQuickDates();
    updateSummary();
    updateSubmitState();
    openStep(1, false);
}
