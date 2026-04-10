<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - {{ $business->name }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
        }

        .container {
            max-width: 1100px;
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
            grid-template-columns: repeat(3, 1fr);
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

        .agenda {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 10px;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
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

        .slot-btn {
            border: 1px solid #93c5fd;
            border-radius: 999px;
            background: #eff6ff;
            color: #1e40af;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
        }

        .slot-btn.selected {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .primary {
            background: #0f172a;
            color: #fff;
            cursor: pointer;
            border: 0;
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

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .agenda {
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
    <p>Sélectionne un service, un membre du staff et une date. Ensuite choisis ton créneau dans l'agenda.</p>

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
                <label for="staff_id">Staff</label>
                <select id="staff_id">
                    <option value="">Choisir</option>
                    @foreach($staffMembers as $staffMember)
                        <option value="{{ $staffMember->id }}">{{ $staffMember->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="date">Date</label>
                <input id="date" type="date">
            </div>
        </div>

        <div class="actions">
            <button id="load-slots" class="primary">Voir les disponibilités</button>
            <span id="selected-slot">Aucun créneau sélectionné</span>
        </div>

        <div class="agenda">
            <div class="hours" id="hours"></div>
            <div class="slots" id="slots"></div>
        </div>

        <hr style="margin: 18px 0; border: 0; border-top: 1px solid #e2e8f0;">

        <div class="grid">
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

    const loadSlotsButton = document.getElementById('load-slots');
    const confirmBookingButton = document.getElementById('confirm-booking');
    const selectedSlotLabel = document.getElementById('selected-slot');
    const messageBox = document.getElementById('message');
    const slotsContainer = document.getElementById('slots');
    const hoursContainer = document.getElementById('hours');

    let selectedSlot = null;

    function setMessage(type, content) {
        messageBox.className = `message ${type}`;
        messageBox.textContent = content;
    }

    function clearMessage() {
        messageBox.className = 'message';
        messageBox.textContent = '';
    }

    function drawAgendaRows() {
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
            staff_id: document.getElementById('staff_id').value,
            date: document.getElementById('date').value,
        };
    }

    function attachSlotButton(slot) {
        const [hour] = slot.split(':');
        const row = slotsContainer.querySelector(`[data-hour="${hour}"]`);

        if (!row) {
            return;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'slot-btn';
        button.textContent = slot;

        button.addEventListener('click', () => {
            document.querySelectorAll('.slot-btn').forEach((item) => {
                item.classList.remove('selected');
            });

            button.classList.add('selected');
            selectedSlot = slot;
            selectedSlotLabel.textContent = `Créneau: ${slot}`;
        });

        row.appendChild(button);
    }

    async function loadSlots() {
        clearMessage();
        drawAgendaRows();
        selectedSlot = null;
        selectedSlotLabel.textContent = 'Aucun créneau sélectionné';

        const { service_id, staff_id, date } = readSelection();

        if (!service_id || !staff_id || !date) {
            setMessage('error', 'Sélectionne le service, le staff et la date avant de charger les disponibilités.');
            return;
        }

        const url = new URL(availabilityUrl, window.location.origin);
        url.searchParams.set('service_id', service_id);
        url.searchParams.set('staff_id', staff_id);
        url.searchParams.set('date', date);

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            setMessage('error', 'Impossible de récupérer les disponibilités. Vérifie les données.');
            return;
        }

        if (!Array.isArray(payload.slots) || payload.slots.length === 0) {
            setMessage('error', 'Aucune disponibilité sur cette date.');
            return;
        }

        payload.slots.forEach((slot) => {
            attachSlotButton(slot);
        });

        setMessage('success', `${payload.slots.length} créneau(x) disponible(s).`);
    }

    async function confirmBooking() {
        clearMessage();

        const { service_id, staff_id, date } = readSelection();
        const customerName = document.getElementById('customer_name').value;
        const customerEmail = document.getElementById('customer_email').value;
        const customerPhone = document.getElementById('customer_phone').value;
        const notes = document.getElementById('notes').value;

        if (!service_id || !staff_id || !date || !selectedSlot || !customerName) {
            setMessage('error', 'Service, staff, date, créneau et nom client sont requis.');
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
            const errorText = payload?.message || 'La réservation a échoué.';
            setMessage('error', errorText);
            return;
        }

        setMessage('success', `Réservation confirmée (#${payload.id}). Un email sera envoyé si configuré.`);
        selectedSlot = null;
        selectedSlotLabel.textContent = 'Aucun créneau sélectionné';
        drawAgendaRows();
    }

    drawAgendaRows();
    loadSlotsButton.addEventListener('click', loadSlots);
    confirmBookingButton.addEventListener('click', confirmBooking);
</script>
</body>
</html>
