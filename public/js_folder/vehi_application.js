const VEHICLES = [
    { label: "UV's", qtyName: 'total_uv', plateName: 'uv_plate_no', dbKey: 'UV' },
    { label: 'TAXI', qtyName: 'total_taxi', plateName: 'taxi_plate_no', dbKey: 'TAXI' },
    { label: 'BUS', qtyName: 'total_bus', plateName: 'bus_plate_no', dbKey: 'BUS' },
    { label: 'MINI BUS', qtyName: 'total_mini_bus', plateName: 'mini_bus_plate_no', dbKey: 'MINI BUS' },
    { label: 'JEEP', qtyName: 'total_jeep', plateName: 'jeep_plate_no', dbKey: 'JEEP' },
    { label: 'MULTI-CAB', qtyName: 'total_multi_cab', plateName: 'multi_cab_plate_no', dbKey: 'MULTI-CAB' },
    { label: 'TRICYCLE', qtyName: 'total_tricycle', plateName: 'tricycle_plate_no', dbKey: 'TRICYCLE' },
];

const state = {};
VEHICLES.forEach(v => {
    const existingPlates = (typeof EXISTING_VEHICLES !== 'undefined' && EXISTING_VEHICLES[v.dbKey])
        ? EXISTING_VEHICLES[v.dbKey]
        : [];
    state[v.qtyName] = {
        qty: existingPlates.length,
        plates: [...existingPlates]
    };
});

function syncHiddenInputs(vehicle) {
    const form = document.getElementById('form');
    if (!form) return;

    form.querySelectorAll(`.vh-${vehicle.qtyName}`).forEach(el => el.remove());

    const plates = state[vehicle.qtyName].plates;
    if (!plates.length) return;

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

function createPlateRow(vehicle, i) {
    const row = document.createElement('div');
    row.className = 'plate-row';
    row.dataset.index = i;

    const idx = document.createElement('span');
    idx.className = 'plate-index';
    idx.textContent = (i + 1) + '.';

    const input = document.createElement('input');
    const val = state[vehicle.qtyName].plates[i] || '';
    input.className = 'plate-input' + (val ? ' filled' : '');
    input.type = 'text';
    input.name = vehicle.plateName + '[' + i + ']';
    input.maxLength = 12;
    input.placeholder = 'Enter plate number';
    input.value = val;

    input.onkeydown = function (event) {
        if (['+', '=', '?', '.', ',', '(', ')'].includes(event.key)) event.preventDefault();
    };

    input.oninput = function () {
        this.value = this.value.toUpperCase();
        state[vehicle.qtyName].plates[i] = this.value;
        this.classList.toggle('filled', this.value.length > 0);
        syncHiddenInputs(vehicle);
    };

    row.appendChild(idx);
    row.appendChild(input);
    return row;
}

function updatePlates(vehicle, qty, inputEl) {
    const card = document.getElementById('card-' + vehicle.qtyName);
    const container = document.getElementById('plates-' + vehicle.qtyName);

    // Save current DOM values into state FIRST
    Array.from(container.querySelectorAll('.plate-input')).forEach((input, i) => {
        state[vehicle.qtyName].plates[i] = input.value;
    });

    if (qty === 0) {
        inputEl.classList.remove('has-value');
        card.classList.remove('has-data');
        container.innerHTML = '<span class="empty-state">Enter quantity to add plates</span>';
        state[vehicle.qtyName].plates = [];
        state[vehicle.qtyName].qty = 0;
        syncHiddenInputs(vehicle);
        return;
    }

    inputEl.classList.add('has-value');
    card.classList.add('has-data');

    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    const currentRows = Array.from(container.querySelectorAll('.plate-row'));
    const currentCount = currentRows.length;

    if (qty > currentCount) {
        // ADD new rows
        for (let i = currentCount; i < qty; i++) {
            if (state[vehicle.qtyName].plates[i] === undefined) {
                state[vehicle.qtyName].plates[i] = '';
            }
            container.appendChild(createPlateRow(vehicle, i));
        }
    } else if (qty < currentCount) {
        // HIDE rows instead of removing — data stays in state
        for (let i = currentCount - 1; i >= qty; i--) {
            currentRows[i].style.display = 'none';
        }
        // Trim state to qty so hidden plates don't get submitted
        state[vehicle.qtyName].plates = state[vehicle.qtyName].plates.slice(0, qty);
    }

    // Show rows that exist but are hidden (when increasing back)
    Array.from(container.querySelectorAll('.plate-row')).forEach((row, i) => {
        if (i < qty) row.style.display = '';
    });

    state[vehicle.qtyName].qty = qty;
    syncHiddenInputs(vehicle);
}

function buildCards() {
    const grid = document.getElementById('vehiclesGrid');
    grid.innerHTML = '';

    VEHICLES.forEach(vehicle => {
        const existingQty = state[vehicle.qtyName].qty;

        const card = document.createElement('div');
        card.className = 'vehicle-card' + (existingQty > 0 ? ' has-data' : '');
        card.id = 'card-' + vehicle.qtyName;

        card.innerHTML = `
            <div class="card-header">
                <span class="vehicle-type">${vehicle.label}</span>
                <div class="qty-wrapper">
                    <span class="qty-label">#</span>
                    <input
                        class="qty-input ${existingQty > 0 ? 'has-value' : ''}"
                        type="number"
                        name="${vehicle.qtyName}"
                        min="0" max="99"
                        placeholder="0"
                        value="${existingQty > 0 ? existingQty : ''}"
                        onkeydown="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-') event.preventDefault();"
                    />
                </div>
            </div>
            <div class="card-body">
                <div class="plates-container" id="plates-${vehicle.qtyName}">
                    <span class="empty-state">Enter quantity to add plates</span>
                </div>
            </div>
        `;

        grid.appendChild(card);

        if (existingQty > 0) {
            updatePlates(vehicle, existingQty, card.querySelector('.qty-input'));
        }

        card.querySelector('.qty-input').addEventListener('input', function () {
            // Don't do anything if field is empty (user is still typing)
            if (this.value === '' || this.value === null) return;

            const val = parseInt(this.value) || 0;
            const clamped = Math.max(0, Math.min(99, val));
            if (val !== clamped) this.value = clamped;
            updatePlates(vehicle, clamped, this);
        });
    });
}

buildCards();