<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - {{ $business->name }}</title>
    <style>
        :root {
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
        }

        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-weight: 600;
            font-size: 14px;
        }

        .field input,
        .field select,
        .field textarea,
        .field button {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            background: #fff;
        }

        .actions {
            margin-top: 12px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .primary {
            background: #0f172a;
            color: #fff;
            border: 0;
            cursor: pointer;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin: 18px 0 12px;
            flex-wrap: wrap;
        }

        .tab-btn {
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 6px 12px;
            background: #fff;
            cursor: pointer;
            font-size: 13px;
        }

        .tab-btn.active {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .view {
            display: none;
        }

        .view.active {
            display: block;
        }

        .agenda-day {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 10px;
        }

        .hours,
        .slots {
            display: grid;
            grid-template-rows: repeat(13, 44px);
            gap: 6px;
        }

        .hour {
            font-size: 12px;
            color: #64748b;
            text-align: right;
            padding-top: 10px;
        }

        .slot-row {
            border: 1px dashed #dbeafe;
            border-radius: 8px;
            padding: 4px;
            background: #f8fbff;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .week-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(130px, 1fr));
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .week-col {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px;
            min-height: 160px;
            background: #f8fbff;
        }

        .week-col h4 {
            margin: 0 0 8px;
            font-size: 13px;
        }

        .provider-grid {
            display: grid;
            gap: 10px;
        }

        .provider-row {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
        }

        .provider-row h4 {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .slot-btn {
            border: 1px solid #93c5fd;
            border-radius: 999px;
            background: #eff6ff;
            color: #1e40af;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
        }

        .slot-btn.selected {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .message {
            margin-top: 14px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            display: none;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            display: block;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            display: block;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .agenda-day {
                grid-template-columns: 1fr;
            }

            .hours {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Réserver chez {{ $business->name }}</h1>
    <p>Tu peux choisir la vue: journée, semaine, ou disponibilités par prestataire.</p>

    <div class="card">
        <div class="grid">
            <div class="field">
                <label for="service_id">Service</label>
                <select id="service_id">
                    <option value="">Choisir</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration_min + $service->buffer_min }} min)</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="staff_id">Prestataire (optionnel pour vue prestataires)</label>
                <select id="staff_id">
                    <option value="">Tous les prestataires</option>
                    @foreach($staffMembers as $staffMember)
                        <option value="{{ $staffMember->id }}">{{ $staffMember->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="date">Date</label>
                <input id="date" type="date">
            </div>

            <div class="field">
                <label for="view_mode">Vue</label>
                <select id="view_mode">
                    <option value="day">Agenda jour</option>
                    <option value="week">Agenda semaine</option>
                    <option value="providers">Par prestataire</option>
                </select>
            </div>
        </div>

        <div class="actions">
            <button id="load-slots" class="primary">Voir les disponibilités</button>
            <span id="selected-slot">Aucun créneau sélectionné</span>
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-view="day" type="button">Jour</button>
            <button class="tab-btn" data-view="week" type="button">Semaine</button>
            <button class="tab-btn" data-view="providers" type="button">Prestataires</button>
        </div>

        <div id="view-day" class="view active">
            <div class="agenda-day">
                <div class="hours" id="hours"></div>
                <div class="slots" id="slots"></div>
            </div>
        </div>

        <div id="view-week" class="view">
            <div id="week-grid" class="week-grid"></div>
        </div>

        <div id="view-providers" class="view">
            <div id="provider-grid" class="provider-grid"></div>
        </div>

        <hr style="margin: 18px 0; border: 0; border-top: 1px solid #e2e8f0;">

        <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="field">
                <label for="customer_name">Nom</label>
                <input id="customer_name" type="text" placeholder="Ton nom">
            </div>
            <div class="field">
                <label for="customer_email">Email</label>
                <input id="customer_email" type="email" placeholder="ton@email.com">
            </div>
            <div class="field">
                <label for="customer_phone">Téléphone</label>
                <input id="customer_phone" type="text" placeholder="06 ...">
            </div>
        </div>

        <div class="field">
            <label for="notes">Notes (optionnel)</label>
            <textarea id="notes" rows="3" placeholder="Infos complémentaires"></textarea>
        </div>

        <div class="actions">
            <button id="confirm-booking" class="primary">Confirmer la réservation</button>
        </div>

        <div id="message" class="message"></div>
    </div>
</div>

<script>
    const availabilityUrl = @json($availabilityUrlTemplate);
    const bookingUrl = @json($bookingUrlTemplate);
    const staffMembers = @json($staffMembers->map(fn ($staffMember) => ['id' => $staffMember->id, 'name' => $staffMember->name])->values());

    const loadSlotsButton = document.getElementById('load-slots');
    const confirmBookingButton = document.getElementById('confirm-booking');
    const selectedSlotLabel = document.getElementById('selected-slot');
    const messageBox = document.getElementById('message');
    const slotsContainer = document.getElementById('slots');
    const hoursContainer = document.getElementById('hours');
    const weekGrid = document.getElementById('week-grid');
    const providerGrid = document.getElementById('provider-grid');
    const viewModeSelect = document.getElementById('view_mode');
    const dateInput = document.getElementById('date');
    const staffSelect = document.getElementById('staff_id');
    const tabButtons = document.querySelectorAll('.tab-btn');

    let selectedSlot = null;

    function setMessage(type, content) {
        messageBox.className = `message ${type}`;
        messageBox.textContent = content;
    }

    function clearMessage() {
        messageBox.className = 'message';
        messageBox.textContent = '';
    }

    function setActiveView(mode) {
        document.querySelectorAll('.view').forEach((view) => {
            view.classList.remove('active');
        });

        tabButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.view === mode);
        });

        document.getElementById(`view-${mode}`).classList.add('active');
        viewModeSelect.value = mode;
    }

    function drawDayRows() {
        hoursContainer.innerHTML = '';
        slotsContainer.innerHTML = '';

        for (let hour = 8; hour <= 20; hour += 1) {
            const hourLabel = document.createElement('div');
            hourLabel.className = 'hour';
            hourLabel.textContent = `${String(hour).padStart(2, '0')}:00`;
            hoursContainer.appendChild(hourLabel);

            const slotRow = document.createElement('div');
            slotRow.className = 'slot-row';
            slotRow.dataset.hour = String(hour).padStart(2, '0');
            slotsContainer.appendChild(slotRow);
        }
    }

    function readSelection() {
        return {
            service_id: document.getElementById('service_id').value,
            staff_id: staffSelect.value,
            date: dateInput.value,
            view_mode: viewModeSelect.value,
        };
    }

    function selectSlot(slot, date, staffId) {
        selectedSlot = slot;
        selectedSlotLabel.textContent = `Créneau: ${date} ${slot}`;

        dateInput.value = date;

        if (staffId) {
            staffSelect.value = String(staffId);
        }

        document.querySelectorAll('.slot-btn').forEach((item) => {
            item.classList.remove('selected');
        });
    }

    function buildSlotButton(slot, date, staffId) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'slot-btn';
        button.textContent = slot;

        button.addEventListener('click', () => {
            selectSlot(slot, date, staffId);
            button.classList.add('selected');
        });

        return button;
    }

    async function fetchAvailability(serviceId, staffId, date) {
        const url = new URL(availabilityUrl, window.location.origin);
        url.searchParams.set('service_id', serviceId);
        url.searchParams.set('staff_id', staffId);
        url.searchParams.set('date', date);

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok || !Array.isArray(payload.slots)) {
            return [];
        }

        return payload.slots;
    }

    function addDays(baseDate, amount) {
        const date = new Date(baseDate + 'T00:00:00');
        date.setDate(date.getDate() + amount);
        return date.toISOString().slice(0, 10);
    }

    async function renderDayView(serviceId, staffId, date) {
        drawDayRows();

        const slots = await fetchAvailability(serviceId, staffId, date);

        slots.forEach((slot) => {
            const [hour] = slot.split(':');
            const row = slotsContainer.querySelector(`[data-hour="${hour}"]`);

            if (row) {
                row.appendChild(buildSlotButton(slot, date, staffId));
            }
        });

        if (slots.length === 0) {
            setMessage('error', 'Aucune disponibilité sur cette date.');
            return;
        }

        setMessage('success', `${slots.length} créneau(x) trouvé(s) sur la journée.`);
    }

    async function renderWeekView(serviceId, staffId, date) {
        weekGrid.innerHTML = '';

        const days = [0, 1, 2, 3, 4, 5, 6].map((offset) => addDays(date, offset));

        for (const day of days) {
            const col = document.createElement('div');
            col.className = 'week-col';

            const title = document.createElement('h4');
            title.textContent = day;
            col.appendChild(title);

            const slots = await fetchAvailability(serviceId, staffId, day);

            if (slots.length === 0) {
                const empty = document.createElement('p');
                empty.style.fontSize = '12px';
                empty.textContent = 'Aucun créneau';
                col.appendChild(empty);
            } else {
                slots.forEach((slot) => {
                    col.appendChild(buildSlotButton(slot, day, staffId));
                });
            }

            weekGrid.appendChild(col);
        }

        setMessage('success', 'Vue semaine chargée.');
    }

    async function renderProviderView(serviceId, date) {
        providerGrid.innerHTML = '';

        for (const staffMember of staffMembers) {
            const row = document.createElement('div');
            row.className = 'provider-row';

            const title = document.createElement('h4');
            title.textContent = staffMember.name;
            row.appendChild(title);

            const slots = await fetchAvailability(serviceId, staffMember.id, date);

            if (slots.length === 0) {
                const empty = document.createElement('p');
                empty.style.fontSize = '12px';
                empty.textContent = 'Aucune disponibilité';
                row.appendChild(empty);
            } else {
                slots.forEach((slot) => {
                    row.appendChild(buildSlotButton(slot, date, staffMember.id));
                });
            }

            providerGrid.appendChild(row);
        }

        setMessage('success', 'Disponibilités par prestataire chargées.');
    }

    async function loadSlots() {
        clearMessage();
        selectedSlot = null;
        selectedSlotLabel.textContent = 'Aucun créneau sélectionné';

        const { service_id, staff_id, date, view_mode } = readSelection();

        if (!service_id || !date) {
            setMessage('error', 'Service et date sont requis.');
            return;
        }

        if ((view_mode === 'day' || view_mode === 'week') && !staff_id) {
            setMessage('error', 'Choisis un prestataire pour les vues jour/semaine.');
            return;
        }

        if (view_mode === 'day') {
            await renderDayView(service_id, staff_id, date);
            return;
        }

        if (view_mode === 'week') {
            await renderWeekView(service_id, staff_id, date);
            return;
        }

        await renderProviderView(service_id, date);
    }

    async function confirmBooking() {
        clearMessage();

        const { service_id, staff_id, date } = readSelection();
        const customerName = document.getElementById('customer_name').value;
        const customerEmail = document.getElementById('customer_email').value;
        const customerPhone = document.getElementById('customer_phone').value;
        const notes = document.getElementById('notes').value;

        if (!service_id || !staff_id || !date || !selectedSlot || !customerName) {
            setMessage('error', 'Service, prestataire, date, créneau et nom sont requis.');
            return;
        }

        const response = await fetch(bookingUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                service_id,
                staff_id,
                date,
                start_time: selectedSlot,
                customer_name: customerName,
                customer_email: customerEmail || null,
                customer_phone: customerPhone || null,
                notes: notes || null,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : null;
            setMessage('error', firstError || payload?.message || 'La réservation a échoué.');
            return;
        }

        setMessage('success', `Réservation confirmée (#${payload.id}).`);

        if (typeof payload.confirmation_url === 'string' && payload.confirmation_url !== '') {
            window.location.href = payload.confirmation_url;
        }
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setActiveView(button.dataset.view);
        });
    });

    viewModeSelect.addEventListener('change', (event) => {
        setActiveView(event.target.value);
    });

    drawDayRows();
    loadSlotsButton.addEventListener('click', loadSlots);
    confirmBookingButton.addEventListener('click', confirmBooking);
</script>
</body>
</html>
