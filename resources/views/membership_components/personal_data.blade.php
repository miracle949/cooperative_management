<style>
    .reminder {
        margin-top: 0.3rem;
    }

    .reminder span {
        font-size: 14px;
        color: #808080;
    }

    /* ── Password Strength Indicator ── */
    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #888;
        background: none;
        border: none;
        padding: 0;
        line-height: 1;
    }

    .strength-bar-wrap {
        margin-top: 8px;
        display: flex;
        gap: 4px;
    }

    .strength-bar-wrap .bar {
        flex: 1;
        height: 5px;
        border-radius: 99px;
        background: #e5e7eb;
        transition: background 0.3s ease;
    }

    .strength-label {
        margin-top: 5px;
        font-size: 12px;
        font-weight: 600;
        min-height: 16px;
        transition: color 0.3s ease;
    }

    /* strength levels */
    .strength-0 .bar {
        background: #e5e7eb;
    }

    .strength-1 .bar:nth-child(1) {
        background: #ef4444;
    }

    .strength-1 .bar:nth-child(n+2) {
        background: #e5e7eb;
    }

    .strength-2 .bar:nth-child(-n+2) {
        background: #f97316;
    }

    .strength-2 .bar:nth-child(n+3) {
        background: #e5e7eb;
    }

    .strength-3 .bar:nth-child(-n+3) {
        background: #eab308;
    }

    .strength-3 .bar:nth-child(n+4) {
        background: #e5e7eb;
    }

    .strength-4 .bar:nth-child(-n+4) {
        background: #22c55e;
    }

    .strength-4 .bar:nth-child(5) {
        background: #e5e7eb;
    }

    .strength-5 .bar {
        background: #16a34a;
    }

    /* strength label colors */
    .label-0 {
        color: transparent;
    }

    .label-1 {
        color: #ef4444;
    }

    .label-2 {
        color: #f97316;
    }

    .label-3 {
        color: #eab308;
    }

    .label-4 {
        color: #22c55e;
    }

    .label-5 {
        color: #16a34a;
    }

    /* password match hint */
    .match-hint {
        margin-top: 5px;
        font-size: 12px;
        font-weight: 600;
        min-height: 16px;
    }

    /* ── OTP Modal — always covers full viewport ── */
    #otp-modal-overlay {
        display: none;
        position: fixed !important;
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, 0.5) !important;
        z-index: 999999 !important;
        align-items: center;
        justify-content: center;
        /* Reset any inherited transforms */
        transform: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<div class="form-step">

    <div class="form-step-sub-parents">
        <div class="form-step-header">
            <div class="form-step-nav">
                {{-- <div class="header-badge">
                    <span>Step 1 of 4</span>
                </div> --}}
                <h2>Personal <b>Data</b></h2>
                <p>Please fill out all required fields marked with *</p>
            </div>
        </div>

        <div class="logo-image">
            <div class="tw:bg-white tw:flex tw:justify-center tw:items-center tw:flex-col picture"
                style="border-radius: 10px;">
                <img src="" alt="" class="" id="inputImage">
                <p class="fw-semibold" id="text" style="font-size: 13.5px;">Photo</p>
                <p class="tw:text-[#808080]" id="text2" style="font-size: 13.5px;">Click here!</p>
                <input type="file" name="profile_picture" id="inputBox" class="form-control">
            </div>
        </div>
    </div>

    <!-- <div class="line-header"></div> -->

    <div class="form-sub-parents">
        <div class="form-step-parents">
            <div class="step-personal">
                <div class="row">
                    <div class="col-lg-4 col-md-4 mt-4">
                        <label>Firstname *</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                    </div>
                    <div class="col-lg-4 col-md-4 mt-4">
                        <label>Middlename <span style="font-size: 14px; color: #808080;">(Optional)</span></label>
                        <input type="text" name="middle_name" id="middle_name" class="form-control">
                    </div>
                    <div class="col-lg-4 col-md-4 mt-4">
                        <label>Lastname *</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-12 mt-4">
                        <label>Cellphone Number *</label>
                        <input type="tel" name="contact_no" id="contact_no" class="form-control"
                            placeholder="09XXXXXXXXX" maxlength="11" required>
                        <small id="contact_no_error" class="text-danger"></small>
                    </div>
                    <div class="col-lg-4 col-md-12 mt-4">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" id="date_birth" class="form-control" required>
                        <small id="dob_error" class="text-danger"></small>
                    </div>
                    <div class="col-lg-4 mt-4">
                        <label>Civil Status *</label>
                        <select name="civil_status" id="civil_status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div class="col-lg-6 mt-4">
                        <label>Place of Birth *</label>
                        <input type="text" name="place_of_birth" id="place_birth" class="form-control" required>
                    </div>
                    <div class="col-lg-6 mt-4">
                        <label>Sex</label>
                        <select name="sex" class="form-select" id="sex" required>
                            <option value="">Choose sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <!-- <div class="col-lg-6 mt-4">
                        <label>Sex</label>
                        <select name="sex" class="form-select" id="sex">
                            <option value="">Choose sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-lg-6 mt-4">
                        <label>Citizenship</label>
                        <select name="citizenship" id="citizenship" class="form-select">
                            <option value="">Select citizenship</option>
                        </select>
                    </div>
                    <div class="col-lg-6 mt-4">
                        <label>Skills/Expertise <span style="font-size: 14px; color: #808080;">(Optional)</span></label>
                        <input type="text" name="skills_expertise" class="form-control" id="skills_expertise">
                    </div> -->
                </div>
            </div>
        </div>

        <!-- <div class="line-body"></div> -->

        <div class="form-step-body">
            <div class="row">

                {{-- ── Citizenship — standard <select>, options populated by JS ── --}}
                    <!-- <div class="col-lg-6 mt-4">
                        <label>Citizenship</label>
                        <select name="citizenship" id="citizenship" class="form-select" required>
                            <option value="">Select citizenship</option>
                            {{-- JS will inject all options below --}}
                        </select>
                    </div> -->
                    <!-- <div class="col-lg-6 mt-4 autocomplete-wrapper">
                        <label>Citizenship</label>
                        <input type="text" name="citizenship" id="citizenship" class="form-control"
                            placeholder="Type to search citizenship..." autocomplete="off" required>
                        <input type="hidden" id="citizenship_value" name="citizenship_confirmed">
                        <div class="autocomplete-list" id="citizenship-list"></div>
                    </div> -->
                    <div class="col-lg-6 mt-4">
                        <label>Citizenship</label>
                        <div class="autocomplete-wrapper">
                            <input type="text" name="citizenship" id="citizenship" class="form-control"
                                placeholder="Type to search citizenship..." autocomplete="off" required>
                            <input type="hidden" id="citizenship_value" name="citizenship_confirmed">
                            <div class="autocomplete-list" id="citizenship-list"></div>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-4">
                        <label>Skills/Expertise <span style="font-size: 14px; color: #808080;">(Optional)</span></label>
                        <input type="text" name="skills_expertise" class="form-control" id="skills_expertise">
                        <!-- <div class="reminder">
                        <span>(Optional)</span>
                    </div> -->
                    </div>

                    <!-- <div class="col-lg-6 col-md-12 mt-4">
                        <label>Username *</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>

                    <div class="col-lg-6 mt-4">
                        <label class="text-start">Membership category?</label>
                        <select name="membership_category" id="select_type" class="form-select" required>
                            <option value="" disabled selected>Select category</option>
                            <option value="Operator">Operator - (Manages transport operations.)</option>
                            <option value="Driver">Driver - (Drives vehicles and transports passengers)</option>
                            <option value="Dispatcher">Dispatcher - (Assigns trips and coordinates drivers.)</option>
                            <option value="Driver-Operator">Driver-Operator - (Drives and manages their own operations.)
                            </option>
                            <option value="Allied Workers">Allied Workers - (Supports operations (e.g., mechanic, staff,
                                helper).)</option>
                            <option value="Transport Entrepreneur">Transport Entrepreneur - (Owns or manages multiple
                                transport
                                units.)</option>
                            <option value="Investor Associate">Investor Associate - (Provides capital but not involved
                                in daily
                                operations.)</option>
                        </select>
                    </div> -->

            </div>
        </div>
    </div>

    <div class="form-step-body">

        <div class="row">
            <div class="col-lg-4 mt-4">
                <label>Email *</label>
                {{-- <input type="email" name="email" id="email" class="form-control"
                    pattern="^[a-zA-Z0-9._%+\-]+@gmail\.com$"
                    title="Only Gmail addresses are allowed (e.g. example@gmail.com)"> --}}
                <input type="email" name="email" id="email" class="form-control">
                <div class="reminder">
                    <span>Enter existing email address</span>
                </div>
            </div>

            {{-- ── Password with strength indicator ── --}}
            <div class="col-lg-4 mt-4">
                <label>Password *</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="login-password" class="form-control"
                        style="padding-right: 40px;" autocomplete="new-password" required>
                    <span id="toggle-login-password"
                        style="position: absolute; right: 12px; top: 55%; transform: translateY(-50%); cursor: pointer; color: #888;">
                        <i class="fa fa-eye" id="eye-login"></i>
                    </span>
                </div>
                <div class="strength-bar-wrap strength-0" id="strength-bars">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <div class="strength-label label-0" id="strength-label"></div>
            </div>

            <div class="col-lg-4 mt-4">
                <label>Confirm Password *</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="login-password-2" class="form-control"
                        style="padding-right: 40px;" autocomplete="new-password" required>
                    <span id="toggle-login-password-2"
                        style="position: absolute; right: 12px; top: 55%; transform: translateY(-50%); cursor: pointer; color: #888;">
                        <i class="fa fa-eye" id="eye-login-2"></i>
                    </span>
                </div>
                <div class="match-hint" id="match-hint"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12 mt-4">
                <label>Username *</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="col-lg-6 mt-4">
                <label class="text-start">Membership category?</label>
                <select name="membership_category" id="select_type" class="form-select" required>
                    <option value="" disabled selected>Select category</option>
                    <option value="Operator">Operator - (Manages transport operations.)</option>
                    <option value="Driver">Driver - (Drives vehicles and transports passengers)</option>
                    <option value="Dispatcher">Dispatcher - (Assigns trips and coordinates drivers.)</option>
                    <option value="Driver-Operator">Driver-Operator - (Drives and manages their own operations.)
                    </option>
                    <option value="Allied Workers">Allied Workers - (Supports operations (e.g., mechanic, staff,
                        helper).)</option>
                    <option value="Transport Entrepreneur">Transport Entrepreneur - (Owns or manages multiple
                        transport
                        units.)</option>
                    <option value="Investor Associate">Investor Associate - (Provides capital but not involved in
                        daily
                        operations.)</option>
                </select>
            </div>
        </div>
    </div>
</div>

</div>{{-- END .form-step --}}


{{-- ═══════════════════════════════════════════════════
OTP MODAL
Rendered here but immediately moved to

<body> via JS
    so position:fixed always covers the full viewport,
    regardless of any transform/overflow on parent elements.

    NOTE: All confirm/cancel/resend/send logic for this modal
    lives ONLY in card_form.js (sendOtpAndVerify()). Do NOT add
    a second set of click handlers here — that previously caused
    two independent /otp/send + /otp/verify flows to run at once,
    which is what broke verification.
    ════════════════════════════════════════════════════ --}}
    <div id="otp-modal-overlay">
        <div
            style="background:#fff; border-radius:12px; padding:2rem; width:100%; max-width:400px; border: 0.5px solid rgba(0,0,0,0.1);">

            {{-- Icon --}}
            <div
                style="width:44px; height:44px; border-radius:50%; background: var(--teal); display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="M2 8l10 7 10-7" />
                </svg>
            </div>

            <h5 style="font-size:18px; font-weight:500; margin:0 0 6px;">Verify your email</h5>
            <p style="font-size:13px; color:#6b7280; margin:0 0 1.5rem; line-height:1.5;">
                We sent a 6-digit code to <strong style="color:#111;" id="otp-email-display"></strong>. Enter it below
                to continue.
            </p>

            <input type="text" id="otp-input" maxlength="6" class="form-control text-center fw-bold"
                style="font-size:1.6rem; letter-spacing:0.6rem; height:56px; border:1.5px solid #d1d5db;"
                placeholder="— — — — — —">
            <small id="otp-error" class="text-danger d-block mt-2" style="min-height:18px;"></small>

            <div class="d-flex gap-2 mt-3">
                <button id="otp-confirm-btn" type="button"
                    style="flex:1; height:40px; background: var(--teal); color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                    Confirm
                </button>
                <button id="otp-cancel-btn" type="button"
                    style="height:40px; flex:1; padding:0 16px; background:transparent; color:var(--teal); border:0.5px solid var(--teal); border-radius:8px; font-size:14px; cursor:pointer;">
                    Cancel
                </button>
            </div>

            <hr style="margin:1.25rem 0; border-color:#f3f4f6;">
            <p style="font-size:12px; color:#9ca3af; text-align:center; margin:0;">
                Didn't receive the code?
                {{-- id renamed to match card_form.js (setResendButtonLoading / currentResendBtn) --}}
                <button type="button" id="otp-resend-btn"
                    style="background:none; border:none; padding:0; color:#111; font-weight:500; cursor:pointer; text-decoration:underline; font-size:12px;">
                    Send again
                </button>
            </p>
        </div>
    </div>


    <script nonce="{{ csp_nonce() }}">
        // ═══════════════════════════════════════════════════
        //  TELEPORT MODAL TO <body>
        //  Moves the overlay out of any stacking-context trap
        //  (transform / overflow / isolation on ancestor elements)
        //  so position:fixed works relative to the true viewport.
        // ═══════════════════════════════════════════════════
        (function teleportOtpModal() {
            const modal = document.getElementById('otp-modal-overlay');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        })();

        // NOTE: showOtpModal/hideOtpModal and all confirm/cancel/resend
        // click handlers used to be defined here as a second, competing
        // implementation. They have been removed — card_form.js's
        // sendOtpAndVerify() function is now the single source of truth
        // for showing/hiding the modal and sending/verifying the OTP.
    </script>


    <script nonce="{{ csp_nonce() }}">
        // ═══════════════════════════════════════════════════
        //  PASSWORD TOGGLE
        // ═══════════════════════════════════════════════════
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }


        // ═══════════════════════════════════════════════════
        //  PASSWORD STRENGTH
        // ═══════════════════════════════════════════════════
        function checkPasswordStrength(value) {
            const bars = document.getElementById('strength-bars');
            const label = document.getElementById('strength-label');
            if (!bars || !label) return;

            let score = 0;
            if (value.length >= 8) score++;
            if (/[a-z]/.test(value)) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            const levelMap = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
            bars.className = `strength-bar-wrap ${value.length === 0 ? 'strength-0' : 'strength-' + score}`;
            label.className = `strength-label ${value.length === 0 ? 'label-0' : 'label-' + score}`;
            label.textContent = value.length === 0 ? '' : (levelMap[score] || '');
            bars.dataset.score = value.length === 0 ? '0' : String(score);
        }


        // ═══════════════════════════════════════════════════
        //  PASSWORD MATCH
        // ═══════════════════════════════════════════════════
        function checkPasswordMatch() {
            const pw = document.getElementById('login-password');
            const confirm = document.getElementById('login-password-2');
            const hint = document.getElementById('match-hint');
            if (!pw || !confirm || !hint) return;

            const cfVal = confirm.value;
            if (cfVal.length === 0) { hint.textContent = ''; hint.style.color = ''; return; }

            if (pw.value === cfVal) {
                hint.textContent = 'Passwords match';
                hint.style.color = '#16a34a';
            } else {
                hint.textContent = 'Passwords do not match';
                hint.style.color = '#ef4444';
            }
        }


        // ═══════════════════════════════════════════════════
        //  CITIZENSHIP — searchable autocomplete input
        // ═══════════════════════════════════════════════════
        (function citizenshipAutocomplete() {
            const citizenships = [
                'Afghan', 'Albanian', 'Algerian', 'American', 'Andorran', 'Angolan', 'Antiguan', 'Argentine',
                'Armenian', 'Australian', 'Austrian', 'Azerbaijani', 'Bahamian', 'Bahraini', 'Bangladeshi',
                'Barbadian', 'Belarusian', 'Belgian', 'Belizean', 'Beninese', 'Bhutanese', 'Bolivian',
                'Bosnian', 'Botswanan', 'Brazilian', 'Bruneian', 'Bulgarian', 'Burkinabé', 'Burundian',
                'Cambodian', 'Cameroonian', 'Canadian', 'Cape Verdean', 'Central African', 'Chadian',
                'Chilean', 'Chinese', 'Colombian', 'Comorian', 'Congolese', 'Costa Rican', 'Croatian',
                'Cuban', 'Cypriot', 'Czech', 'Danish', 'Djiboutian', 'Dominican', 'Dutch', 'East Timorese',
                'Ecuadorian', 'Egyptian', 'Emirati', 'Equatorial Guinean', 'Eritrean', 'Estonian',
                'Eswatini', 'Ethiopian', 'Fijian', 'Filipino', 'Finnish', 'French', 'Gabonese', 'Gambian', 'Georgian',
                'German', 'Ghanaian', 'Greek', 'Grenadian', 'Guatemalan', 'Guinean', 'Guinea-Bissauan',
                'Guyanese', 'Haitian', 'Honduran', 'Hungarian', 'I-Kiribati', 'Icelandic', 'Indian',
                'Indonesian', 'Iranian', 'Iraqi', 'Irish', 'Israeli', 'Italian', 'Ivorian', 'Jamaican',
                'Japanese', 'Jordanian', 'Kazakhstani', 'Kenyan', 'Kittitian', 'Korean (North)',
                'Korean (South)', 'Kosovar', 'Kuwaiti', 'Kyrgyzstani', 'Laotian', 'Latvian', 'Lebanese',
                'Lesothan', 'Liberian', 'Libyan', 'Liechtensteiner', 'Lithuanian', 'Luxembourgish',
                'Macedonian', 'Malagasy', 'Malawian', 'Malaysian', 'Maldivian', 'Malian', 'Maltese',
                'Marshallese', 'Mauritanian', 'Mauritian', 'Mexican', 'Micronesian', 'Moldovan',
                'Monégasque', 'Mongolian', 'Montenegrin', 'Moroccan', 'Mozambican', 'Myanmarese',
                'Namibian', 'Nauruan', 'Nepalese', 'New Zealander', 'Nicaraguan', 'Nigerien', 'Nigerian',
                'Norwegian', 'Omani', 'Pakistani', 'Palauan', 'Palestinian', 'Panamanian', 'Papua New Guinean',
                'Paraguayan', 'Peruvian', 'Polish', 'Portuguese', 'Qatari', 'Romanian',
                'Russian', 'Rwandan', 'Saint Lucian', 'Salvadoran', 'Samoan', 'San Marinese', 'São Toméan',
                'Saudi', 'Senegalese', 'Serbian', 'Seychellois', 'Sierra Leonean', 'Singaporean', 'Slovak',
                'Slovenian', 'Solomon Islander', 'Somali', 'South African', 'South Sudanese', 'Spanish',
                'Sri Lankan', 'Sudanese', 'Surinamese', 'Swedish', 'Swiss', 'Syrian', 'Taiwanese', 'Tajik',
                'Tanzanian', 'Thai', 'Togolese', 'Tongan', 'Trinidadian', 'Tunisian', 'Turkish', 'Turkmen',
                'Tuvaluan', 'Ugandan', 'Ukrainian', 'Uruguayan', 'Uzbekistani', 'Vanuatuan', 'Venezuelan',
                'Vietnamese', 'Vincentian', 'Yemeni', 'Zambian', 'Zimbabwean'
            ];

            const input = document.getElementById('citizenship');
            const hidden = document.getElementById('citizenship_value');
            const list = document.getElementById('citizenship-list');
            if (!input || !list) return;

            let activeIndex = -1;

            function highlight(text, query) {
                if (!query) return text;
                const idx = text.toLowerCase().indexOf(query.toLowerCase());
                if (idx === -1) return text;
                return (
                    text.slice(0, idx) +
                    '<mark>' + text.slice(idx, idx + query.length) + '</mark>' +
                    text.slice(idx + query.length)
                );
            }

            function renderList(query) {
                const q = query.trim().toLowerCase();
                const matches = q === ''
                    ? citizenships
                    : citizenships.filter(c => c.toLowerCase().includes(q));

                list.innerHTML = '';
                activeIndex = -1;

                if (matches.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'autocomplete-empty';
                    empty.textContent = 'No matching citizenship found';
                    list.appendChild(empty);
                } else {
                    matches.forEach(function (c) {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.innerHTML = highlight(c, query);
                        item.dataset.value = c;
                        item.addEventListener('click', function () {
                            selectValue(c);
                        });
                        list.appendChild(item);
                    });
                }

                list.style.display = 'block';
            }

            function selectValue(value) {
                input.value = value;
                if (hidden) hidden.value = value;
                list.style.display = 'none';
                input.classList.remove('is-invalid');

                // ✅ ADD THIS — persist immediately since no native event fires here
                if (typeof window.persistFormField === 'function') {
                    window.persistFormField('citizenship', value);
                }
                const disp = document.getElementById('citizenship_display');
                if (disp) disp.textContent = value;
            }

            function closeList() {
                list.style.display = 'none';
            }

            input.addEventListener('focus', function () {
                renderList(input.value);
            });

            input.addEventListener('input', function () {
                if (hidden) hidden.value = '';
                renderList(input.value);
            });

            input.addEventListener('keydown', function (e) {
                const items = list.querySelectorAll('.autocomplete-item');
                if (list.style.display !== 'block' || items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActive(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0) {
                        selectValue(items[activeIndex].dataset.value);
                    }
                } else if (e.key === 'Escape') {
                    closeList();
                }
            });

            function updateActive(items) {
                items.forEach((it, i) => it.classList.toggle('active', i === activeIndex));
                if (activeIndex >= 0) {
                    items[activeIndex].scrollIntoView({ block: 'nearest' });
                }
            }

            // Validate on blur — must match an exact option from the list
            input.addEventListener('blur', function () {
                setTimeout(function () {
                    closeList();
                    const match = citizenships.find(
                        c => c.toLowerCase() === input.value.trim().toLowerCase()
                    );
                    if (match) {
                        input.value = match;
                        if (hidden) hidden.value = match;
                        input.classList.remove('is-invalid');

                        // ✅ ADD THIS
                        if (typeof window.persistFormField === 'function') {
                            window.persistFormField('citizenship', match);
                        }
                        const disp = document.getElementById('citizenship_display');
                        if (disp) disp.textContent = match;
                    } else if (input.value.trim() !== '') {
                        input.classList.add('is-invalid');
                        if (hidden) hidden.value = '';
                    }
                }, 150);
            });

            // Close list when clicking outside
            document.addEventListener('click', function (e) {
                if (!input.contains(e.target) && !list.contains(e.target)) {
                    closeList();
                }
            });
        })();
    </script>

    <script nonce="{{ csp_nonce() }}">
        function toggleLoginPassword() {
            const input = document.getElementById('login-password');
            const icon = document.getElementById('eye-login');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function toggleLoginPassword2() {
            const input = document.getElementById('login-password-2');
            const icon = document.getElementById('eye-login-2'); // was 'eye-login' — the bug
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    <script nonce="{{ csp_nonce() }}">
        // Reattach listeners that used to be inline onclick/oninput attributes
        document.getElementById('toggle-login-password')?.addEventListener('click', toggleLoginPassword);
        document.getElementById('toggle-login-password-2')?.addEventListener('click', toggleLoginPassword2);

        function lettersOnlyTitleCase(e) {
            e.target.value = e.target.value.replace(/[^A-Za-z ]/g, '').replace(/\b\w/g, c => c.toUpperCase());
        }
        document.getElementById('first_name')?.addEventListener('input', lettersOnlyTitleCase);
        document.getElementById('middle_name')?.addEventListener('input', lettersOnlyTitleCase);
        document.getElementById('last_name')?.addEventListener('input', lettersOnlyTitleCase);

        document.getElementById('contact_no')?.addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        const loginPw = document.getElementById('login-password');
        const loginPw2 = document.getElementById('login-password-2');
        loginPw?.addEventListener('input', function () {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
        loginPw2?.addEventListener('input', checkPasswordMatch);
    </script>