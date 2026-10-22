(function () {
    'use strict';

    let currentStep_form = 0;
    const steps_form = document.querySelectorAll(".form-step");
    const stepper = document.querySelectorAll(".step");
    const nextBtn = document.querySelector(".btn-next");
    const submitBtn = document.querySelector(".btn-submit");
    const OTP_SEND_URL = window.location.origin + '/otp/send';
    const OTP_VERIFY_URL = window.location.origin + '/otp/verify';
    let emailOtpVerified = false;
    let otpAlreadySentEmail = null;
    const actionsDiv = document.querySelector(".actions");

    /* ══════════════════════════════════════
       STEP PERSISTENCE — survive page reloads
       (sessionStorage: cleared when the tab/browser closes,
        but keeps the step across F5 / accidental reloads)
    ══════════════════════════════════════ */
    const STEP_STORAGE_KEY = 'kpmpcats_register_step';
    const OTP_VERIFIED_KEY = 'kpmpcats_otp_verified_email'; // ADD

    function saveOtpVerified(email) {                         // ADD
        try { sessionStorage.setItem(OTP_VERIFIED_KEY, email); } catch (e) { }
    }

    function restoreOtpVerified() {                            // ADD
        try {
            const savedEmail = sessionStorage.getItem(OTP_VERIFIED_KEY);
            if (savedEmail) {
                emailOtpVerified = true;
                otpAlreadySentEmail = savedEmail;
            }
        } catch (e) { }
    }

    function clearOtpVerified() {                               // ADD
        try { sessionStorage.removeItem(OTP_VERIFIED_KEY); } catch (e) { }
    }

    function saveCurrentStep() {
        try {
            sessionStorage.setItem(STEP_STORAGE_KEY, String(currentStep_form));
        } catch (e) {
            // sessionStorage unavailable (e.g. private mode) — fail silently
        }
    }

    function restoreCurrentStep() {
        try {
            const saved = sessionStorage.getItem(STEP_STORAGE_KEY);
            if (saved === null) return;
            const savedIndex = parseInt(saved, 10);
            if (!isNaN(savedIndex) && savedIndex >= 0 && savedIndex < steps_form.length) {
                currentStep_form = savedIndex;
            }
        } catch (e) {
            // sessionStorage unavailable — just start at step 0
        }
    }

    function clearSavedStep() {
        try {
            sessionStorage.removeItem(STEP_STORAGE_KEY);
        } catch (e) { /* no-op */ }
    }

    /* ══════════════════════════════════════
       CLEAR EVERYTHING — used when leaving to Login
       (or anywhere else the form data should not persist)
    ══════════════════════════════════════ */
    function clearAllRegistrationData() {
        try {
            const keysToRemove = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key && key.startsWith('kpmpcats')) {
                    keysToRemove.push(key);
                }
            }
            keysToRemove.forEach(k => sessionStorage.removeItem(k));
        } catch (e) { /* no-op */ }
    }
    window.clearAllRegistrationData = clearAllRegistrationData;

    restoreCurrentStep();
    restoreOtpVerified(); // ADD — call this right after restoreCurrentStep()

    /* ══════════════════════════════════════
       ONE-TIME: inject spinner CSS
    ══════════════════════════════════════ */
    (function injectSpinnerStyles() {
        if (document.getElementById('otp-loading-styles')) return;
        const style = document.createElement('style');
        style.id = 'otp-loading-styles';
        style.textContent = `
            .btn-spinner {
                display: inline-block;
                width: 14px;
                height: 14px;
                border: 2px solid rgba(255, 255, 255, 0.4);
                border-top-color: #fff;
                border-radius: 50%;
                animation: otp-spin 0.7s linear infinite;
                margin-right: 8px;
                vertical-align: middle;
            }
            .btn-spinner.dark {
                border: 2px solid rgba(0, 0, 0, 0.2);
                border-top-color: #333;
            }
            @keyframes otp-spin {
                to { transform: rotate(360deg); }
            }
            .btn-next.is-loading,
            #otp-resend-btn.is-loading {
                opacity: 0.85;
                cursor: not-allowed;
                pointer-events: none;
            }
        `;
        document.head.appendChild(style);
    })();

    /* ══════════════════════════════════════
       Button loading-state helpers
    ══════════════════════════════════════ */
    function setNextButtonLoading(isLoading, text) {
        if (!nextBtn) return;
        if (isLoading) {
            if (!nextBtn.dataset.originalHtml) {
                nextBtn.dataset.originalHtml = nextBtn.innerHTML;
            }
            nextBtn.disabled = true;
            nextBtn.classList.add('is-loading');
            nextBtn.innerHTML = `<span class="btn-spinner"></span><span class="btn-loading-text">${text || 'Sending code…'}</span>`;
        } else {
            nextBtn.disabled = false;
            nextBtn.classList.remove('is-loading');
            if (nextBtn.dataset.originalHtml) {
                nextBtn.innerHTML = nextBtn.dataset.originalHtml;
            }
        }
    }

    function setResendButtonLoading(btn, isLoading) {
        if (!btn) return;
        if (isLoading) {
            if (!btn.dataset.originalHtml) {
                btn.dataset.originalHtml = btn.innerHTML;
            }
            btn.disabled = true;
            btn.classList.add('is-loading');
            btn.innerHTML = `<span class="btn-spinner dark"></span><span>Sending…</span>`;
        } else {
            btn.disabled = false;
            btn.classList.remove('is-loading');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
    }

    function updateSteps() {
        saveCurrentStep();

        steps_form.forEach((step, index) => {
            step.classList.toggle("active", index === currentStep_form);
        });

        // ── Update "Step X of 4" badge in every form-step ──
        const totalSteps = steps_form.length;
        steps_form.forEach((step, index) => {
            const badge = step.querySelector('.header-track span, .header-badge span');
            if (badge) {
                badge.textContent = `Step ${index + 1} of ${totalSteps}`;
            }
        });

        stepper.forEach((step, index) => {
            step.classList.remove("active", "completed");
            const circle = step.querySelector(".circle");

            if (index === currentStep_form) {
                step.classList.add("active");
                circle.innerHTML = '0' + (index + 1);
            } else if (index < currentStep_form) {
                step.classList.add("completed");
                circle.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>`;
            } else {
                circle.innerHTML = '0' + (index + 1);
            }
        });

        // ── Previous button: hidden on step 1, visible on steps 2+ ──
        const prevBtn = document.querySelector(".btn-prev");
        if (prevBtn) {
            prevBtn.style.display = currentStep_form === 0 ? "none" : "flex";
        }

        if (actionsDiv) {
            actionsDiv.style.justifyContent = currentStep_form === 0 ? "flex-end" : "space-between";
        }

        // ── Next / Submit toggle ──
        if (currentStep_form === steps_form.length - 1) {
            nextBtn.style.display = "none";
            submitBtn.style.display = "inline-block";
            setTimeout(initSignaturePad, 80);
        } else {
            nextBtn.style.display = "inline-block";
            submitBtn.style.display = "none";
        }

        // ✅ ADD THIS — reapply saved values (incl. password) when switching steps
        if (typeof window.restoreFormFields === 'function') {
            window.restoreFormFields();
        }
    }

    function goToNextStep() {
        if (currentStep_form < steps_form.length - 1) {
            currentStep_form++;
            updateSteps();
            document.querySelector('.form-box')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function updateNextButtonState() {
        if (!document.getElementById('next-btn-disabled-styles')) {
            const style = document.createElement('style');
            style.id = 'next-btn-disabled-styles';
            style.textContent = `
            .btn-next.btn-disabled {
                opacity: 0.5 !important;
                cursor: not-allowed !important;
                pointer-events: none !important;
            }
        `;
            document.head.appendChild(style);
        }

        if (currentStep_form !== 0) {
            nextBtn.disabled = false;
            nextBtn.classList.remove('btn-disabled');
            return;
        }

        const agree1 = document.getElementById('checkboxDefault1');
        const agree2 = document.getElementById('checkboxDefault2');
        const termsInput = document.getElementById('termsAcceptedInput');

        const allChecked = agree1?.checked && agree2?.checked && termsInput?.value === '1';

        nextBtn.disabled = !allChecked;
        nextBtn.classList.toggle('btn-disabled', !allChecked);
    }

    document.addEventListener('change', function (e) {
        if (e.target.id === 'checkboxDefault1' || e.target.id === 'checkboxDefault2') {
            updateNextButtonState();
        }
    });

    async function sendOtpAndVerify(email) {

        // ✅ Already verified this email — skip entirely
        if (emailOtpVerified && otpAlreadySentEmail === email) {
            return true;
        }

        const overlay = document.getElementById('otp-modal-overlay');
        const confirmBtn = document.getElementById('otp-confirm-btn');
        const resendBtn = document.getElementById('otp-resend-btn');
        const cancelBtn = document.getElementById('otp-cancel-btn');
        const input = document.getElementById('otp-input');
        const error = document.getElementById('otp-error');
        const emailDisp = document.getElementById('otp-email-display');

        if (!overlay || !confirmBtn || !cancelBtn || !input) {
            console.error('OTP modal elements not found in DOM.');
            alert('OTP verification UI is missing. Please contact support.');
            return false;
        }

        if (otpAlreadySentEmail !== email) {
            // 🔄 Show loading state on Next Step button while the OTP is sent
            setNextButtonLoading(true, 'Sending code…');

            try {
                const sendRes = await fetch(OTP_SEND_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({ email })
                });

                if (!sendRes.ok) {
                    let msg = 'Failed to send verification code. Please try again or contact support.';
                    let invalidRecipient = false;
                    try {
                        const errData = await sendRes.json();
                        if (errData.errors && errData.errors.email && errData.errors.email[0]) {
                            msg = errData.errors.email[0];
                        } else if (errData.message) {
                            msg = errData.message;
                        }
                        invalidRecipient = !!errData.invalid_recipient;
                    } catch (e) { /* not JSON, use default msg */ }

                    // console.error('OTP send failed — status:', sendRes.status, msg);
                    setNextButtonLoading(false);

                    const emailInputEl = document.getElementById('email');
                    const existingErr = document.getElementById('email-send-error');
                    if (existingErr) existingErr.remove();

                    if (emailInputEl) {
                        const errorMsg = document.createElement('small');
                        errorMsg.id = 'email-send-error';
                        errorMsg.style.cssText = 'color: #dc2626; font-size: 12.5px; margin-top: 4px; display: block;';
                        errorMsg.textContent = msg;
                        emailInputEl.classList.add('is-invalid');
                        emailInputEl.insertAdjacentElement('afterend', errorMsg);
                        emailInputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else {
                        alert(msg);
                    }

                    return false;
                }

                otpAlreadySentEmail = email;
            } catch (err) {
                console.error('Network error sending OTP:', err);
                alert('Network error while sending verification code. Please check your connection.');
                setNextButtonLoading(false);
                return false;
            }

            // ✅ Send finished — restore button before showing the modal
            setNextButtonLoading(false);
        }

        return new Promise((resolve) => {
            if (emailDisp) emailDisp.textContent = email;
            input.value = '';
            if (error) error.textContent = '';
            overlay.style.display = 'flex';

            // ── Confirm ──────────────────────────────────────────────────────
            // Clone and replace to remove any stale onclick listeners
            const freshConfirm = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(freshConfirm, confirmBtn);

            freshConfirm.addEventListener('click', async function () {
                const code = input.value.trim();
                if (!code) {
                    if (error) error.textContent = 'Please enter the code.';
                    return;
                }

                freshConfirm.disabled = true;
                freshConfirm.textContent = 'Verifying…';

                try {
                    const res = await fetch(OTP_VERIFY_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                || document.querySelector('input[name="_token"]')?.value
                        },
                        body: JSON.stringify({ otp: code, email })
                    });
                    const data = await res.json();

                    if (data.valid) {
                        emailOtpVerified = true;
                        saveOtpVerified(email);
                        overlay.style.display = 'none';
                        freshConfirm.disabled = false;
                        freshConfirm.textContent = 'Confirm';
                        resolve(true);
                    } else {
                        if (error) error.textContent = data.message || 'Invalid code.';
                        freshConfirm.disabled = false;
                        freshConfirm.textContent = 'Confirm';
                    }
                } catch (err) {
                    if (error) error.textContent = 'Verification failed. Please try again.';
                    freshConfirm.disabled = false;
                    freshConfirm.textContent = 'Confirm';
                }
            });

            // ── Resend ───────────────────────────────────────────────────────
            const currentResendBtn = document.getElementById('otp-resend-btn');
            if (currentResendBtn) {
                currentResendBtn.addEventListener('click', async function () {
                    if (error) { error.style.color = ''; error.textContent = ''; }
                    input.value = '';

                    // 🔄 Show loading state on the Resend button itself
                    setResendButtonLoading(currentResendBtn, true);

                    try {
                        const resendRes = await fetch(OTP_SEND_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    || document.querySelector('input[name="_token"]')?.value
                            },
                            body: JSON.stringify({ email })
                        });

                        if (!resendRes.ok) {
                            if (error) error.textContent = 'Failed to resend. Server error (' + resendRes.status + ').';
                            return;
                        }

                        otpAlreadySentEmail = email;
                        if (error) {
                            error.style.color = '#16a34a';
                            error.textContent = 'A new code has been sent!';
                            setTimeout(() => { error.style.color = ''; error.textContent = ''; }, 3000);
                        }
                    } catch (e) {
                        if (error) error.textContent = 'Failed to resend. Please try again.';
                    } finally {
                        setResendButtonLoading(currentResendBtn, false);
                    }
                });
            }

            // ── Cancel ───────────────────────────────────────────────────────
            const currentCancelBtn = document.getElementById('otp-cancel-btn');
            currentCancelBtn.addEventListener('click', function () {
                overlay.style.display = 'none';
                resolve(false);
            });
        });
    }

    // Call on page load to set initial state
    updateSteps();
    updateNextButtonState();

    // ── Helper: compute age from a date string ──────────────────────────────
    function computeAge(dateString) {
        if (!dateString) return null;
        const birth = new Date(dateString);
        const today = new Date();
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        return age;
    }

    async function nextStep() {
        // 🔒 Hard safety guard — never allow advancing past Step 1 unless terms are accepted
        if (currentStep_form === 0) {
            const step0 = steps_form[0];
            const agree1 = step0.querySelector('#checkboxDefault1');
            const agree2 = step0.querySelector('#checkboxDefault2');
            const termsInputCheck = step0.querySelector('#termsAcceptedInput');
            const blocked = !(agree1?.checked && agree2?.checked && termsInputCheck?.value === '1');
            if (blocked) {
                updateNextButtonState();
                const termsError = document.getElementById('terms-error');
                if (termsError) {
                    termsError.style.display = 'block';
                    termsError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
        }

        const currentFormStep = steps_form[currentStep_form];
        const requiredFields = currentFormStep.querySelectorAll("input[required], select[required]");

        for (let field of requiredFields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return;
            }
        }

        // ✅ TERMS & CONDITIONS MODAL VALIDATION — Step 1 only
        if (currentStep_form === 0) {
            const termsInput = currentFormStep.querySelector('#termsAcceptedInput');
            const termsError = document.getElementById('terms-error');
            if (termsInput && termsInput.value !== '1') {
                if (termsError) {
                    termsError.style.display = 'block';
                    termsError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            } else if (termsError) {
                termsError.style.display = 'none';
            }
        }

        // ✅ PERSONAL DOB VALIDATION — must be 18+
        const dobInput = currentFormStep.querySelector('#date_birth');
        if (dobInput && dobInput.value) {
            const dobError = document.getElementById('dob_error');
            const age = computeAge(dobInput.value);
            if (age < 18) {
                if (dobError) dobError.textContent = 'You must be at least 18 years old to register.';
                dobInput.classList.add('is-invalid');
                dobInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            } else {
                if (dobError) dobError.textContent = '';
                dobInput.classList.remove('is-invalid');
            }
        }

        // ✅ SPOUSE DOB VALIDATION
        const spouseDobInput = currentFormStep.querySelector('#spouse_date_birth');
        if (spouseDobInput && spouseDobInput.value) {
            const spouseDobError = document.getElementById('spouse_dob_error');
            const spouseAge = computeAge(spouseDobInput.value);
            if (spouseAge < 18) {
                if (spouseDobError) spouseDobError.textContent = 'Spouse must be at least 18 years old.';
                spouseDobInput.classList.add('is-invalid');
                spouseDobInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            } else {
                if (spouseDobError) spouseDobError.textContent = '';
                spouseDobInput.classList.remove('is-invalid');
            }
        }

        // ✅ PASSWORD MATCH VALIDATION
        const passwordInput = currentFormStep.querySelector('#login-password');
        const confirmInput = currentFormStep.querySelector('#login-password-2');
        if (passwordInput && confirmInput) {
            if (passwordInput.value !== confirmInput.value) {
                confirmInput.classList.add('is-invalid');
                confirmInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            } else {
                confirmInput.classList.remove('is-invalid');
            }
        }

        // ✅ PASSWORD STRENGTH VALIDATION
        const strengthBars = currentFormStep.querySelector('#strength-bars');
        if (passwordInput && strengthBars) {
            const score = parseInt(strengthBars.dataset.score || '0', 10);
            const existingStrengthError = currentFormStep.querySelector('#password-strength-error');
            if (existingStrengthError) existingStrengthError.remove();

            if (score < 4) {
                const errorMsg = document.createElement('small');
                errorMsg.id = 'password-strength-error';
                errorMsg.style.cssText = 'color: #dc2626; font-size: 12.5px; margin-top: 0px; display: block;';
                errorMsg.textContent = 'Password is too weak. Please use a Strong or Very Strong password.';
                passwordInput.classList.add('is-invalid');
                const strengthLabel = currentFormStep.querySelector('#strength-label');
                (strengthLabel || passwordInput).insertAdjacentElement('afterend', errorMsg);
                passwordInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            } else {
                passwordInput.classList.remove('is-invalid');
            }
        }

        // ✅ CELLPHONE NUMBER VALIDATION — must be 11 digits starting with 09
        const contactInput = currentFormStep.querySelector('#contact_no');
        if (contactInput && contactInput.value) {
            const contactError = document.getElementById('contact_no_error');
            const isValidPhone = /^09\d{9}$/.test(contactInput.value);
            if (!isValidPhone) {
                if (contactError) contactError.textContent = 'Enter a valid 11-digit mobile number (e.g. 09171234567).';
                contactInput.classList.add('is-invalid');
                contactInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            } else {
                if (contactError) contactError.textContent = '';
                contactInput.classList.remove('is-invalid');
            }
        }

        const emailInput = currentFormStep.querySelector('#email');

        // ✅ EMAIL + OTP VERIFICATION — then check if email already exists
        if (emailInput && emailInput.value) {
            const existingEmailError = currentFormStep.querySelector('#email-exists-error');
            if (existingEmailError) existingEmailError.remove();

            const otpVerified = await sendOtpAndVerify(emailInput.value);
            if (!otpVerified) return;

            try {
                const response = await fetch('/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({ email: emailInput.value })
                });

                const data = await response.json();

                if (data.exists) {
                    const errorMsg = document.createElement('small');
                    errorMsg.id = 'email-exists-error';
                    errorMsg.style.cssText = 'color: #dc2626; font-size: 12.5px; margin-top: 4px; display: block;';
                    errorMsg.textContent = 'This email is already registered. Please use a different email.';
                    emailInput.classList.add('is-invalid');
                    emailInput.insertAdjacentElement('afterend', errorMsg);
                    emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                } else {
                    emailInput.classList.remove('is-invalid');
                }
            } catch (err) {
                console.error('Email check failed:', err);
            }

            goToNextStep();
            return;
        }

        // ✅ SIGNATURE VALIDATION (only reached on non-email steps)
        const signatureInput = currentFormStep.querySelector('#signature');
        const signatureError = document.getElementById('signature-error');
        if (signatureInput !== null && signatureInput.value === '') {
            if (signatureError) {
                signatureError.style.display = 'block';
                signatureError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        } else if (signatureError) {
            signatureError.style.display = 'none';
        }

        goToNextStep();
    }

    function prevStep() {
        if (currentStep_form > 0) {
            currentStep_form--;
            updateSteps();
            document.querySelector('.form-box')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // ✅ Block form submit if no signature
    document.querySelector('form')?.addEventListener('submit', function (e) {
        const signatureInput = document.getElementById('signature');
        const signatureError = document.getElementById('signature-error');
        if (signatureInput && signatureInput.value === '') {
            e.preventDefault();
            if (signatureError) {
                signatureError.style.display = 'block';
                signatureError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }

        // ✅ Form is actually going through — clear the saved step
        // so the next visit to this page starts fresh at Step 1.
        clearSavedStep();
    });

    /* ══════════════════════════════════════
       LIVE INPUT ERROR CLEARING
    ══════════════════════════════════════ */
    document.addEventListener('input', function (e) {

        if (e.target.id === 'login-password') {
            const score = parseInt(document.getElementById('strength-bars')?.dataset.score || '0', 10);
            const strengthErr = document.getElementById('password-strength-error');
            if (score >= 4 || e.target.value.length === 0) {
                e.target.classList.remove('is-invalid');
                if (strengthErr) strengthErr.remove();
            }
            const pw = document.getElementById('login-password');
            const cpw = document.getElementById('login-password-2');
            if (pw && cpw && pw.value === cpw.value) {
                cpw.classList.remove('is-invalid');
            }
        }

        if (e.target.id === 'login-password-2') {
            const pw = document.getElementById('login-password');
            const cpw = document.getElementById('login-password-2');
            if (pw && cpw && pw.value === cpw.value) {
                cpw.classList.remove('is-invalid');
            }
        }

        if (e.target.id === 'spouse_date_birth') {
            const spouseDobError = document.getElementById('spouse_dob_error');
            const age = computeAge(e.target.value);
            if (!e.target.value || age >= 18) {
                if (spouseDobError) spouseDobError.textContent = '';
                e.target.classList.remove('is-invalid');
            }
        }

        if (e.target.id === 'date_birth') {
            const dobError = document.getElementById('dob_error');
            const age = computeAge(e.target.value);
            if (!e.target.value || age >= 18) {
                if (dobError) dobError.textContent = '';
                e.target.classList.remove('is-invalid');
            }
        }

        if (e.target.id === 'contact_no') {
            const contactError = document.getElementById('contact_no_error');
            if (/^09\d{9}$/.test(e.target.value)) {
                if (contactError) contactError.textContent = '';
                e.target.classList.remove('is-invalid');
            }
        }

        // if (e.target.id === 'email') {
        //     emailOtpVerified = false;
        //     otpAlreadySentEmail = null;
        //     clearOtpVerified(); 
        //     const emailVal = e.target.value;
        //     const existsErr = document.getElementById('email-exists-error');
        //     let gmailErr = document.getElementById('email-gmail-error');

        //     if (existsErr) existsErr.remove();
        //     e.target.classList.remove('is-invalid');

        //     if (emailVal && !emailVal.toLowerCase().endsWith('@gmail.com')) {
        //         if (!gmailErr) {
        //             gmailErr = document.createElement('small');
        //             gmailErr.id = 'email-gmail-error';
        //             gmailErr.style.cssText = 'color: #dc2626; font-size: 12.5px; margin-top: 4px; display: block;';
        //             gmailErr.textContent = 'Only Gmail addresses are allowed (e.g. example@gmail.com).';
        //             e.target.insertAdjacentElement('afterend', gmailErr);
        //         }
        //         e.target.classList.add('is-invalid');
        //     } else {
        //         if (gmailErr) gmailErr.remove();
        //         e.target.classList.remove('is-invalid');
        //     }
        // }
        if (e.target.id === 'email') {
            emailOtpVerified = false;
            otpAlreadySentEmail = null;
            clearOtpVerified();
            const existsErr = document.getElementById('email-exists-error');
            const sendErr = document.getElementById('email-send-error');   // ADD
            if (existsErr) existsErr.remove();
            if (sendErr) sendErr.remove();                                  // ADD
            e.target.classList.remove('is-invalid');
        }
    });

    /* ══════════════════════════════════════
       SIGNATURE PAD — using SignaturePad library
    ══════════════════════════════════════ */
    let signaturePadMain = null;
    let signaturePadModal = null;

    function initSignaturePad() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas || canvas._sigInitialized) return;
        canvas._sigInitialized = true;

        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight || 160;

        signaturePadMain = new SignaturePad(canvas, {
            minWidth: 1,
            maxWidth: 3,
            penColor: '#1a1a1a',
        });

        signaturePadMain.addEventListener('endStroke', () => {
            document.getElementById('signature').value = signaturePadMain.toDataURL();
            const err = document.getElementById('signature-error');
            if (err) err.style.display = 'none';
        });

        document.getElementById('clear')?.addEventListener('click', () => {
            signaturePadMain.clear();
            document.getElementById('signature').value = '';
        });
    }


    function openSignatureModal() {
        const overlay = document.getElementById('signature-modal-overlay');
        overlay.style.display = 'flex';

        setTimeout(() => {
            const canvas = document.getElementById('signature-pad-modal');
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight || 300;

            signaturePadModal = new SignaturePad(canvas, {
                minWidth: 1.5,
                maxWidth: 3.5,
                penColor: '#1a1a1a',
            });

            const existing = document.getElementById('signature').value;
            if (existing && existing.startsWith('data:')) {
                signaturePadModal.fromDataURL(existing);
            }
        }, 80);
    }

    function clearModalSignature() {
        if (signaturePadModal) signaturePadModal.clear();
    }

    function saveModalSignature() {
        if (!signaturePadModal || signaturePadModal.isEmpty()) {
            alert('Please draw your signature first.');
            return;
        }

        const dataUrl = signaturePadModal.toDataURL();
        if (signaturePadMain) signaturePadMain.fromDataURL(dataUrl);

        document.getElementById('signature').value = dataUrl;

        const err = document.getElementById('signature-error');
        if (err) err.style.display = 'none';

        document.getElementById('signature-modal-overlay').style.display = 'none';
    }

    document.getElementById('signature-modal-overlay')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });

    /* ══════════════════════════════════════
       BFCACHE SAFETY NET
       If the browser restores this page from its back/forward
       cache (e.g. user hits the Back button from /login-page),
       force a real reload so stale field values/steps never
       show up visually for the next person using this device.
    ══════════════════════════════════════ */
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            window.location.reload();
        }
    });

    // ✅ Expose functions called via HTML onclick="..."
    window.nextStep = nextStep;
    window.prevStep = prevStep;
    window.openSignatureModal = openSignatureModal;
    window.clearModalSignature = clearModalSignature;
    window.saveModalSignature = saveModalSignature;
    window.updateNextButtonState = updateNextButtonState;

})();