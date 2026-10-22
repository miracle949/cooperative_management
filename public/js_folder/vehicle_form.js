const VEHICLES = [
    { label: "UV's", qtyName: 'total_uv', plateName: 'uv_plate_no' },
    { label: 'TAXI', qtyName: 'total_taxi', plateName: 'taxi_plate_no' },
    { label: 'BUS', qtyName: 'total_bus', plateName: 'bus_plate_no' },
    { label: 'MINI BUS', qtyName: 'total_mini_bus', plateName: 'mini_bus_plate_no' },
    { label: 'JEEP', qtyName: 'total_jeep', plateName: 'jeep_plate_no' },
    { label: 'MULTI-CAB', qtyName: 'total_multi_cab', plateName: 'multi_cab_plate_no' },
    { label: 'TRICYCLE', qtyName: 'total_tricycle', plateName: 'tricycle_plate_no' },
];

const state = {};
VEHICLES.forEach(v => state[v.qtyName] = { qty: 0, plates: [] });

/* ============================================================
   PERSISTENCE — keep vehicle qty + plate numbers across reloads
   ============================================================ */
const VEHICLE_STORAGE_KEY = 'kpmpcats_vehicle_state';

function saveVehicleState() {
    try {
        sessionStorage.setItem(VEHICLE_STORAGE_KEY, JSON.stringify(state));
    } catch (e) { /* storage unavailable — fail silently */ }
}

function loadVehicleState() {
    try {
        const raw = sessionStorage.getItem(VEHICLE_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

function syncHiddenInputs(vehicle) {
    const form = document.getElementById('form');
    if (!form) return;

    form.querySelectorAll(`.vh-${vehicle.qtyName}`).forEach(el => el.remove());

    const qty = state[vehicle.qtyName].qty;
    const plates = state[vehicle.qtyName].plates;

    if (qty <= 0 || !plates.length) return;

    plates.forEach((plateValue, index) => {
        const trimmed = (plateValue || '').trim();
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = vehicle.plateName + '[' + index + ']';
        hidden.value = trimmed;
        hidden.classList.add('vh-' + vehicle.qtyName);
        form.appendChild(hidden);
    });
}

function buildCards() {
    const grid = document.getElementById('vehiclesGrid');
    grid.innerHTML = '';

    VEHICLES.forEach(vehicle => {
        const cardId = 'card-' + vehicle.qtyName;
        const containerId = 'plates-' + vehicle.qtyName;

        const card = document.createElement('div');
        card.className = 'vehicle-card';
        card.id = cardId;

        card.innerHTML = `
            <div class="card-header">
                <span class="vehicle-type">${vehicle.label}</span>
                <div class="qty-wrapper">
                    <span class="qty-label">#</span>
                    <input
                        class="qty-input"
                        type="number"
                        name="${vehicle.qtyName}"
                        min="0" max="99"
                        placeholder="0"
                    />
                </div>
            </div>
            <div class="card-body">
                <div class="plates-container" id="${containerId}">
                    <span class="empty-state">Enter quantity to add plates</span>
                </div>
            </div>
        `;

        grid.appendChild(card);



        // card.querySelector('.qty-input').addEventListener('input', function () {
        //     const val = parseInt(this.value) || 0;
        //     const clamped = Math.max(0, Math.min(99, val));
        //     if (val !== clamped) this.value = clamped;
        //     handleQtyChange(vehicle, clamped, this);
        //     saveVehicleState();
        // });
        const qtyInputEl = card.querySelector('.qty-input');
        qtyInputEl.addEventListener('keydown', function (event) {
            if (event.key === 'e' || event.key === 'E' || event.key === '+' || event.key === '-') {
                event.preventDefault();
            }
        });
        let shrinkTimer = null;
        const SHRINK_DEBOUNCE_MS = 500;

        qtyInputEl.addEventListener('input', function () {
            // Empty transient (digit being replaced) — ignore, blur handles it.
            if (this.value === '') {
                return;
            }

            const val = parseInt(this.value) || 0;
            const clamped = Math.max(0, Math.min(99, val));
            if (val !== clamped) this.value = clamped;

            const currentQty = state[vehicle.qtyName].qty;

            if (clamped >= currentQty) {
                // Growing (or unchanged) — always safe, apply right away.
                // This also covers the "1 -> 11" case once the second digit lands.
                if (shrinkTimer) { clearTimeout(shrinkTimer); shrinkTimer = null; }
                handleQtyChange(vehicle, clamped, this);
                saveVehicleState();
            } else {
                // Shrinking — this deletes plate rows, so it might just be a
                // mid-typing stepping stone (e.g. typing "1" while aiming for
                // "11" when the current qty is 6). Wait for a pause before
                // actually committing the shrink.
                if (shrinkTimer) clearTimeout(shrinkTimer);
                shrinkTimer = setTimeout(() => {
                    handleQtyChange(vehicle, clamped, qtyInputEl);
                    saveVehicleState();
                    shrinkTimer = null;
                }, SHRINK_DEBOUNCE_MS);
            }
        });

        qtyInputEl.addEventListener('blur', function () {
            // Leaving the field finalizes whatever is pending immediately,
            // no need to wait out the debounce any longer.
            if (shrinkTimer) { clearTimeout(shrinkTimer); shrinkTimer = null; }

            if (this.value === '') {
                this.value = 0;
                handleQtyChange(vehicle, 0, this);
                saveVehicleState();
                return;
            }

            const val = parseInt(this.value) || 0;
            const clamped = Math.max(0, Math.min(99, val));
            if (clamped !== state[vehicle.qtyName].qty) {
                handleQtyChange(vehicle, clamped, this);
                saveVehicleState();
            }
        });
    });
}

/* Renders the plate-number rows for a vehicle using state[vehicle.qtyName].plates
   as the source of truth (already set by caller). */
function renderPlateRows(vehicle, qty) {
    const container = document.getElementById('plates-' + vehicle.qtyName);
    container.innerHTML = '';

    if (qty === 0) {
        container.innerHTML = '<span class="empty-state">Enter quantity to add plates</span>';
        return;
    }

    for (let i = 0; i < qty; i++) {
        const row = document.createElement('div');
        row.className = 'plate-row';
        row.style.animationDelay = (i * 25) + 'ms';

        const idx = document.createElement('span');
        idx.className = 'plate-index';
        idx.textContent = (i + 1) + '.';

        const input = document.createElement('input');
        input.className = 'plate-input';
        input.type = 'text';
        input.name = vehicle.plateName + '[' + i + ']';
        input.maxLength = 12;
        input.placeholder = 'Enter plate number';
        input.value = state[vehicle.qtyName].plates[i] || '';
        if (input.value) input.classList.add('filled');

        input.addEventListener('keydown', function (event) {
            if (['+', '=', '?', '.', ',', '(', ')'].includes(event.key)) event.preventDefault();
        });

        input.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
            state[vehicle.qtyName].plates[i] = this.value;
            this.classList.toggle('filled', this.value.length > 0);
            syncHiddenInputs(vehicle);
            saveVehicleState();
            if (typeof updateVehicleReview === 'function') updateVehicleReview();
        });

        row.appendChild(idx);
        row.appendChild(input);
        container.appendChild(row);
    }
}

function handleQtyChange(vehicle, qty, inputEl) {
    const card = document.getElementById('card-' + vehicle.qtyName);
    const container = document.getElementById('plates-' + vehicle.qtyName);

    const existingValues = Array.from(container.querySelectorAll('.plate-input')).map(i => i.value);
    state[vehicle.qtyName].qty = qty;

    if (qty > 0) {
        inputEl.classList.add('has-value');
        card.classList.add('has-data');
    } else {
        inputEl.classList.remove('has-value');
        card.classList.remove('has-data');
    }

    if (qty === 0) {
        state[vehicle.qtyName].plates = [];
        renderPlateRows(vehicle, 0);
        syncHiddenInputs(vehicle);
        if (typeof updateVehicleReview === 'function') updateVehicleReview();
        return;
    }

    state[vehicle.qtyName].plates = Array(qty).fill('').map((_, i) => existingValues[i] || '');
    renderPlateRows(vehicle, qty);
    syncHiddenInputs(vehicle);
    if (typeof updateVehicleReview === 'function') updateVehicleReview();
}

/* Re-hydrate qty inputs + plate rows from sessionStorage on page load */
function restoreVehicleState() {
    const saved = loadVehicleState();
    if (!saved) return;

    VEHICLES.forEach(vehicle => {
        const savedEntry = saved[vehicle.qtyName];
        if (!savedEntry || !savedEntry.qty) return;

        const qty = Math.max(0, Math.min(99, parseInt(savedEntry.qty) || 0));
        if (qty <= 0) return;

        state[vehicle.qtyName].qty = qty;
        state[vehicle.qtyName].plates = Array(qty).fill('').map((_, i) => (savedEntry.plates && savedEntry.plates[i]) || '');

        const card = document.getElementById('card-' + vehicle.qtyName);
        const qtyInput = card.querySelector('.qty-input');
        qtyInput.value = qty;
        qtyInput.classList.add('has-value');
        card.classList.add('has-data');

        renderPlateRows(vehicle, qty);
        syncHiddenInputs(vehicle);
    });

    if (typeof updateVehicleReview === 'function') updateVehicleReview();
}

buildCards();
restoreVehicleState();