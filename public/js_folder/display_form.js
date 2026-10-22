const first_name = document.getElementById("first_name");
const middle_name = document.getElementById("middle_name");
const last_name = document.getElementById("last_name");
const username = document.getElementById("username");
const date_birth = document.getElementById("date_birth");
const place_birth = document.getElementById("place_birth");
const email = document.getElementById("email");
const member_type = document.getElementById("select_type");
const sex = document.getElementById("sex");
const citizenship = document.getElementById("citizenship");
const skills_expertise = document.getElementById("skills_expertise");
// const tin_no = document.getElementById("tin_no");

const spouse_name = document.getElementById("spouse_name");
const spouse_date_birth = document.getElementById("spouse_date_birth");
const spouse_place_birth = document.getElementById("spouse_place_birth");

const number_son = document.getElementById("number_son");
const number_daughter = document.getElementById("number_daughter");
const other_spec = document.getElementById("other_spec");
const contact_no = document.getElementById("contact_no");

// const uv = document.getElementById("uv");
// const taxi = document.getElementById("taxi");
// const bus = document.getElementById("bus");
// const tricycle = document.getElementById("tricycle");
// const mini_bus = document.getElementById("mini_bus");
// const jeep = document.getElementById("jeep");
// const multi_cab = document.getElementById("multi_cab");
// const other_info_specify = document.getElementById("other_info_specify");


// personal details

// let currentStep = 0;
// const steps = document.querySelectorAll('.step');
// const formSteps = document.querySelectorAll('.form-step');

// function updateStepper() {
//     steps.forEach((step, index) => {
//         step.classList.remove('active', 'completed');
//         if (index === currentStep) {
//             step.classList.add('active');
//         } else if (index < currentStep) {
//             step.classList.add('completed');
//         }
//     });
// }

// function nextStep() {
//     if (currentStep < steps.length - 1) {
//         formSteps[currentStep].classList.remove('active');
//         currentStep++;
//         formSteps[currentStep].classList.add('active');
//         updateStepper();
//     }
// }

// function prevStep() {
//     if (currentStep > 0) {
//         formSteps[currentStep].classList.remove('active');
//         currentStep--;
//         formSteps[currentStep].classList.add('active');
//         updateStepper();
//     }
// }

first_name.addEventListener("input", () => {
    document.getElementById("firstname_display").textContent = first_name.value;
});

middle_name.addEventListener("input", () => {
    document.getElementById("middlename_display").textContent = middle_name.value;
});

last_name.addEventListener("input", () => {
    document.getElementById("lastname_display").textContent = last_name.value;
});

username.addEventListener("input", () => {
    document.getElementById("username_display").textContent = username.value;
});

date_birth.addEventListener("input", () => {

    const ConvertDate = new Date(date_birth.value);

    let readableDate = ConvertDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    document.getElementById("date_birth_display").textContent = readableDate;
});

contact_no.addEventListener("input", () => {
    document.getElementById("contact_no_display").textContent = contact_no.value;
});

place_birth.addEventListener("input", () => {
    document.getElementById("place_birth_display").textContent = place_birth.value;
});

email.addEventListener("input", () => {
    document.getElementById("email_display").textContent = email.value;
});

member_type.addEventListener("input", () => {
    document.getElementById("membership_type_display").textContent = member_type.value;
});

sex.addEventListener("input", () => {
    document.getElementById("sex_display").textContent = sex.value;
});


citizenship.addEventListener("change", () => {
    document.getElementById("citizenship_display").textContent = citizenship.value;
});

skills_expertise.addEventListener("input", () => {
    document.getElementById("skills_display").textContent = skills_expertise.value;
});

// Spouse

spouse_name.addEventListener("input", () => {
    document.getElementById("spouse_name_display").textContent = spouse_name.value;
});

spouse_date_birth.addEventListener("input", () => {

    const ConvertDate = new Date(spouse_date_birth.value);

    let readableDate = ConvertDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    document.getElementById("spouse_date_birth_display").textContent = readableDate;
});

spouse_place_birth.addEventListener("input", () => {
    document.getElementById("spouse_place_birth_display").textContent = spouse_place_birth.value;
});

number_son.addEventListener("input", () => {
    document.getElementById("son_display").textContent = number_son.value;
});

number_daughter.addEventListener("input", () => {
    document.getElementById("daughter_display").textContent = number_daughter.value;
});

other_spec.addEventListener("input", () => {
    document.getElementById("other_spec_display").textContent = other_spec.value;
});

// ── Vehicle display in review ──────────────────────────────────────────────
function updateVehicleReview() {
    const container = document.getElementById('vehicles_review_display');
    if (!container) return;

    const VEHICLE_LABELS = {
        total_uv: "UV's",
        total_taxi: 'TAXI',
        total_bus: 'BUS',
        total_mini_bus: 'MINI BUS',
        total_jeep: 'JEEP',
        total_multi_cab: 'MULTI-CAB',
        total_tricycle: 'TRICYCLE',
    };

    let html = '';
    let hasAny = false;

    Object.entries(VEHICLE_LABELS).forEach(([qtyName, label]) => {
        const qtyInput = document.querySelector(`input[name="${qtyName}"]`);
        const qty = parseInt(qtyInput?.value) || 0;
        if (qty <= 0) return;

        hasAny = true;
        const plates = (typeof state !== 'undefined' && state[qtyName]?.plates) ? state[qtyName].plates : [];
        const plateList = plates.filter(p => p).map(p => `<span class="review-plate-badge">${p}</span>`).join(' ');

        html += `
            <div class="review-vehicle-row mt-2">
                <div class="review-vehicle-row-text">
                    <span class="review-vehicle-label">${label}:</span>
                    <span class="review-vehicle-qty">${qty} unit${qty > 1 ? 's' : ''}</span>
                </div>
                ${plateList ? `<div class="review-plate-list mt-1">${plateList}</div>` : ''}
            </div>`;
    });

    container.innerHTML = hasAny ? html : '<span style="color:#aaa; font-size:13px;">No vehicles entered.</span>';
}

// Update vehicle review whenever qty inputs change
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty-input') || e.target.classList.contains('plate-input')) {
        updateVehicleReview();
    }
});

/* ============================================================
   FORM PERSISTENCE — keep entered data across page reloads
   ============================================================ */
(function () {
    const STORAGE_KEY = 'kpmpcats_register_formdata';

    function getStore() {
        try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || {}; }
        catch (e) { return {}; }
    }

    function setStore(data) {
        try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data)); }
        catch (e) { }
    }

    function saveField(el) {
        if (!el || !el.id) return;
        // Only exclude binary signature data now — passwords are persisted per request
        if (el.id === 'signature') return;
        const data = getStore();
        data[el.id] = (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value;
        setStore(data);
    }

    // ✅ ADD THIS — lets fields with custom widgets (no real input event) save directly
    window.persistFormField = function (id, value) {
        const data = getStore();
        data[id] = value;
        setStore(data);
    };

    // Save on every input/change anywhere in the form (delegated, no need to touch every field)
    document.addEventListener('input', (e) => saveField(e.target));
    document.addEventListener('change', (e) => saveField(e.target));

    function restoreFields() {
        const data = getStore();
        Object.keys(data).forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;

            // ── Citizenship autocomplete: restore directly, skip its own
            // input listener which wipes the hidden confirmed value ──
            if (id === 'citizenship') {
                el.value = data[id];
                const hidden = document.getElementById('citizenship_value');
                if (hidden) hidden.value = data[id];
                el.classList.remove('is-invalid');
                document.getElementById('citizenship_display')?.replaceChildren(
                    document.createTextNode(data[id])
                );
                return;
            }

            // ── Email: restore directly, skip dispatching input/change —
            // that native event triggers card_form.js's "user edited email"
            // handler, which wipes the saved OTP-verified state on every
            // reload/step-change even though the user didn't actually type anything ──
            if (id === 'email') {
                el.value = data[id];
                document.getElementById('email_display')?.replaceChildren(
                    document.createTextNode(data[id])
                );
                return;
            }

            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = data[id];
            } else {
                el.value = data[id];
            }
            // Password fields still dispatch input here — that's needed for
            // checkPasswordStrength()/checkPasswordMatch() to redraw correctly.
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (typeof updateVehicleReview === 'function') updateVehicleReview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreFields);
    } else {
        restoreFields();
    }

    // ✅ ADD THIS — expose it so card_form.js's updateSteps() can call it
    // every time the step changes (including landing directly on Review)
    window.restoreFormFields = restoreFields;

    // Once the form is actually submitted, clear saved data so the next visit starts fresh
    document.querySelector('form')?.addEventListener('submit', () => {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) { }
    });

    // Once the form is actually submitted, clear saved data so the next visit starts fresh
    document.querySelector('form')?.addEventListener('submit', () => {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) { }
    });
})();