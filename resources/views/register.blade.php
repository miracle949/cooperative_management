<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Apply for Membership</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/register.css?=44">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid">

        <div class="sidebar">
            <div class="sidebar-nav">
                <div class="nav-tag">Member Application</div>

                <h2>Join the <b>Cooperative</b></h2>

                <p>Complete the steps below to become a registered KPMPCATS member.</p>
            </div>

            <div class="stepper">
                <div class="step active">
                    <div class="step-left">
                        <div class="circle">01</div>
                        {{-- <div class="connector"></div> --}}
                    </div>
                    <div class="label">
                        <p class="title">Terms &amp; Agreement</p>
                        <p class="sub">Read and accept cooperative terms</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-left">
                        <div class="circle">02</div>
                        {{-- <div class="connector"></div> --}}
                    </div>
                    <div class="label">
                        <p class="title">Personal Data</p>
                        <p class="sub">Your name, contact &amp; address</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-left">
                        <div class="circle">03</div>
                        {{-- <div class="connector"></div> --}}
                    </div>
                    <div class="label">
                        <p class="title">Other Information</p>
                        <p class="sub">Membership type &amp; employment</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-left">
                        <div class="circle">04</div>
                        {{-- <div class="connector"></div> --}}
                    </div>
                    <div class="label">
                        <p class="title">Review &amp; Submit</p>
                        <p class="sub">Confirm and finalize application</p>
                    </div>
                </div>
            </div>

            <hr style="margin-top: 5rem; color: var(--teal);">

            <div class="form-acc">
                <label>Already a member? <a href="{{ route("LoginPage") }}" id="signin-link">Sign in here</a></label>
                <label>Need help? <button>Contact Support</button></label>
            </div>
        </div>

        <div class="rightbar">

            <nav>
                <div class="nav-logo">
                    {{-- <img src="../images/logo2.png" alt=""> --}}
                    <div class="nav-text">
                        {{-- <h2 class="fw-bold">Membership Application Form</h2> --}}
                        <div class="nav-side">
                            <img src="images/logo2.png" alt="">
                            <h2>KPMPCATS</h2>
                        </div>

                        <a href="{{ route('LoginPage') }}">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                        {{-- <p>Ready to become part of something special? We can't wait to welcome you.</p> --}}
                    </div>
                </div>
            </nav>

            <div class="main">
                <form action="{{ route("registration") }}" id="form" method="post" class="needs-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf

                    {{-- <div class="tw:flex tw:justify-center tw:items-center line">
                        <hr class="tw:w-[20%] border-2">
                    </div> --}}

                    <div class="choose-type">
                        <div class="choose">
                            <div class="back">
                                <a href="{{ route('LoginPage') }}"><i class="fa fa-arrow-left"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="form-box">

                        <!-- Form -->
                        <div class="card-box">

                            <!-- Step 1 -->
                            <div class="form-step active">

                                <div class="form-step-nav">
                                    {{-- <div class="header-track">
                                        <span>Step 1 of 4</span>
                                    </div> --}}

                                    <a href="{{ route("LoginPage") }}">
                                        <i class="fa fa-chevron-left"></i>
                                        Back to Sign in
                                    </a>

                                    <h2>Terms & <b>Agreement</b></h2>

                                    <p>Please read the following terms carefully before proceeding.</p>
                                </div>

                                <div class="line-agreement"></div>

                                <div class="form-step-header">
                                    <h3>Cooperative Membership Terms</h3>
                                    <p>KPMPCATS — Authorized Share Capital Agreement</p>
                                </div>
                                <div class="form-step-body">
                                    <div class="row">
                                        <div class="col-lg-12">

                                            <label class="lh-lg mt-3">The Undersigned hereby subscribed and agreed to
                                                take
                                                100
                                                share of the KPMPCATS authorized Share Capital with a par value of One
                                                Hundred
                                                Pesos
                                                (Php100.00) per share amounting to Ten Thousand (P10,000.00) Pesos
                                                payable
                                                that
                                                was
                                                scheduled within two (2) years, and agrees to pay atleast 25% as initial
                                                payment
                                                of
                                                subscription within a Membership Fee of Two Thousand (P2,000.00)
                                                Pesos.</label>

                                            <label class="lh-lg mt-4">The undersigned agrees further to pay Fifty
                                                Thousand
                                                (P50,000.00) Pesos (For Tourist Unit) as entrance fee for my vehicle and
                                                Mobilization Fee of Three Thousand (P3,000.00) of the Transport /
                                                Vehicle
                                                Operation
                                                of KPMPCATS.</label>

                                            <label class="lh-lg mt-4">The undersigned further pledge to undertake
                                                Regular
                                                Savings
                                                and / or Contributions to the Caplital Build-Up of the Cooperative or to
                                                its
                                                Programs and Services.</label>

                                            {{-- ══════════════════════════════════════
                                            NEW: Clickable text that opens the
                                            full Terms & Conditions modal
                                            ══════════════════════════════════════ --}}
                                            <button type="button" class="terms-link-box" id="openTermsModalBtn"
                                                data-bs-toggle="modal" data-bs-target="#termsModal">
                                                <span class="terms-link-icon">
                                                    <i class="fa fa-file-contract"></i>
                                                </span>
                                                <span class="terms-link-text">
                                                    <span class="terms-link-title" id="termsLinkTitle">Read the
                                                        full Cooperative Terms &amp; Conditions</span>
                                                    <span class="terms-link-sub" id="termsLinkSub">Tap to open
                                                        — required before you can continue</span>
                                                </span>
                                                <span class="terms-link-chevron">
                                                    <i class="fa fa-chevron-right"></i>
                                                </span>
                                            </button>

                                            <label class="mt-4">Declaration and Agreement</label>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" name="agree1"
                                                    id="checkboxDefault1" required>
                                                <label class="form-check-label" for="checkboxDefault1">
                                                    I hereby certify that the foregoing statements are true and correct
                                                    to
                                                    the
                                                    best
                                                    of my knowledge. I understand that any false information may result
                                                    in
                                                    the
                                                    cancellation of my membership.
                                                </label>
                                            </div>

                                            <div class="form-check mt-3">
                                                <input class="form-check-input" type="checkbox" name="agree2"
                                                    id="checkboxDefault2" required>
                                                <label class="form-check-label" for="checkboxDefault2">
                                                    I agree to abide by the Constitution and By-Laws of the Cooperative
                                                    and
                                                    to
                                                    accept the rights, responsibilities, and obligations of membership.
                                                    I
                                                    understand
                                                    that my application is subject to approval by the Board of
                                                    Directors.
                                                </label>
                                            </div>

                                            {{-- ══════════════════════════════════════
                                            NEW: 3rd checkbox — set automatically
                                            once the member agrees inside the modal.
                                            Stays disabled/unchecked until then.
                                            ══════════════════════════════════════ --}}
                                            <div class="form-check mt-3 p-0" id="checkboxDefaultForm">
                                                <label class="form-check-label">
                                                    I have read and agree to the full
                                                    <a href="#" class="terms-inline-link" data-bs-toggle="modal"
                                                        data-bs-target="#termsModal">Terms and Conditions</a>
                                                    of the Cooperative.
                                                </label>
                                            </div>
                                            <input type="hidden" id="termsAcceptedInput" name="agree3" value="0">
                                            <small id="terms-error"
                                                style="color:#dc2626; font-size:12.5px; margin-top:6px; display:none;">
                                                Please open and agree to the Terms and Conditions to continue.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            @include('membership_components.personal_data')

                            <!-- Step 3 -->
                            @include('membership_components.other_information')

                            {{-- Step 4 --}}
                            @include('membership_components.review_submit')

                            <!-- <div class="line-agreement"></div> -->

                            <!-- Buttons -->
                            <div class="actions">
                                <button type="button" class="btn-prev" id="btnPrev" style="display:none;">
                                    <i class="fa fa-chevron-left"></i>
                                    <span>Previous</span>
                                </button>
                                <button type="button" class="btn-next" id="btnNext">
                                    <span>Next Step</span>
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                                <button type="submit" class="btn-submit" style="display:none;">Submit</button>
                            </div>

                        </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
    TERMS & CONDITIONS MODAL
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true" aria-labelledby="termsModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg terms-modal-dialog">
            <div class="modal-content terms-modal-content">

                <div class="terms-modal-header">
                    <div class="terms-modal-header-left">
                        <div class="terms-modal-icon">
                            <i class="fa fa-scale-balanced"></i>
                        </div>
                        <div>
                            <h5 class="terms-modal-title" id="termsModalLabel">Cooperative Terms &amp; Conditions</h5>
                            <p class="terms-modal-subtitle">KPMPCATS — Membership Agreement</p>
                        </div>
                    </div>
                    <button type="button" class="terms-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-xmark"></i>
                    </button>
                </div>

                <div class="terms-modal-body" id="termsModalBody">

                    <div class="terms-section">
                        <div class="terms-section-num">1</div>
                        <div class="terms-section-text">
                            <h4>Membership Eligibility</h4>
                            <p>
                                Membership in the Kabankalan Public Market and Public Utility Cooperative
                                Allied Transport Service (KPMPCATS) is open to any natural person who is at
                                least eighteen (18) years of age, of legal capacity, a resident within the
                                Cooperative's area of operation, and who is willing to abide by the
                                Cooperative's Articles of Cooperation, By-Laws, policies, and this Terms and
                                Conditions of Membership. Membership may be denied, suspended, or revoked by
                                the Board of Directors for cause, in accordance with the Cooperative's
                                By-Laws and the Philippine Cooperative Code (Republic Act No. 9520, as
                                amended).
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">2</div>
                        <div class="terms-section-text">
                            <h4>Share Capital Subscription</h4>
                            <p>
                                The Undersigned hereby subscribes and agrees to take up One Hundred (100)
                                shares of the KPMPCATS authorized Share Capital at a par value of One
                                Hundred Pesos (Php100.00) per share, amounting to a total subscription of
                                Ten Thousand Pesos (P10,000.00), payable within a scheduled period of two
                                (2) years from the date of approval of membership. The Undersigned further
                                agrees to pay at least twenty-five percent (25%) of the subscribed capital
                                as an initial payment, together with a Membership Fee of Two Thousand Pesos
                                (P2,000.00), upon filing of this application.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">3</div>
                        <div class="terms-section-text">
                            <h4>Entrance and Mobilization Fees</h4>
                            <p>
                                For members applying to operate a Tourist Unit or any vehicle under the
                                Cooperative's transport operations, the Undersigned agrees to pay an
                                Entrance Fee of Fifty Thousand Pesos (P50,000.00) per vehicle, and a
                                Mobilization Fee of Three Thousand Pesos (P3,000.00) covering the Transport
                                / Vehicle Operation of KPMPCATS. These fees are non-transferable and shall
                                be forfeited in favor of the Cooperative should the applicant withdraw the
                                application after approval, except as otherwise provided by the Board of
                                Directors.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">4</div>
                        <div class="terms-section-text">
                            <h4>Savings and Capital Build-Up</h4>
                            <p>
                                The Undersigned further pledges to undertake Regular Savings and/or
                                Contributions toward the Capital Build-Up of the Cooperative, or to its
                                duly approved Programs and Services, in such amount and frequency as may be
                                determined by the Board of Directors from time to time. Savings deposited
                                with the Cooperative shall earn interest or dividends, if any, in
                                accordance with the Cooperative's policies and applicable resolutions of
                                the General Assembly.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">5</div>
                        <div class="terms-section-text">
                            <h4>Rights of a Member</h4>
                            <p>
                                Upon approval of this application and full compliance with the membership
                                requirements, the member shall be entitled to: (a) vote and be voted upon
                                in General Assembly meetings; (b) avail of the products, services, loans,
                                and programs offered by the Cooperative; (c) receive patronage refunds and
                                interest on share capital as may be declared by the Board; (d) inspect
                                books and records of the Cooperative in accordance with law and the
                                By-Laws; and (e) such other rights granted under the Cooperative Code and
                                the Cooperative's governing documents.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">6</div>
                        <div class="terms-section-text">
                            <h4>Responsibilities and Obligations</h4>
                            <p>
                                Every member is expected to: (a) attend and participate in General Assembly
                                meetings and trainings; (b) settle share capital, fees, loans, and other
                                obligations on time; (c) act with honesty, good faith, and in the best
                                interest of the Cooperative in all dealings; (d) comply with the
                                Cooperative's Constitution and By-Laws, policies, and resolutions; and (e)
                                report any false, inaccurate, or misleading information provided in this
                                application, which may result in denial, suspension, or cancellation of
                                membership.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">7</div>
                        <div class="terms-section-text">
                            <h4>Data Privacy</h4>
                            <p>
                                Personal information collected through this application — including but
                                not limited to name, contact details, address, identification documents,
                                and signature — shall be collected, processed, and stored by KPMPCATS
                                solely for purposes of membership processing, cooperative operations,
                                communication, and compliance with legal and regulatory requirements, in
                                accordance with the Data Privacy Act of 2012 (Republic Act No. 10173) and
                                its Implementing Rules and Regulations. The Cooperative shall not sell,
                                rent, or disclose personal information to third parties except as required
                                by law, upon the member's consent, or as necessary to carry out cooperative
                                functions.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">8</div>
                        <div class="terms-section-text">
                            <h4>Termination or Cancellation of Membership</h4>
                            <p>
                                Membership may be terminated by voluntary withdrawal, death (for natural
                                persons), expulsion for cause, or dissolution of the Cooperative, subject
                                to the procedures set forth in the By-Laws. Any false statement made in
                                this application may result in the immediate cancellation of membership and
                                forfeiture of privileges, without prejudice to the return of paid-up share
                                capital in accordance with cooperative policy and applicable law.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section">
                        <div class="terms-section-num">9</div>
                        <div class="terms-section-text">
                            <h4>Amendments</h4>
                            <p>
                                KPMPCATS reserves the right to amend, revise, or update these Terms and
                                Conditions, its By-Laws, and related policies from time to time, subject to
                                the approval of the General Assembly or the Board of Directors as required.
                                Members will be notified of material changes through the Cooperative's
                                official communication channels.
                            </p>
                        </div>
                    </div>

                    <div class="terms-section terms-section-last">
                        <div class="terms-section-num">10</div>
                        <div class="terms-section-text">
                            <h4>Governing Law</h4>
                            <p>
                                This Agreement shall be governed by and construed in accordance with the
                                Philippine Cooperative Code of 2008 (Republic Act No. 9520, as amended),
                                the Cooperative's Articles of Cooperation and By-Laws, and other applicable
                                laws, rules, and regulations of the Republic of the Philippines.
                            </p>
                        </div>
                    </div>

                    <div class="terms-modal-check">
                        <input type="checkbox" id="termsModalCheckbox"
                            class="form-check-input terms-modal-checkbox-input" required>
                        <label for="termsModalCheckbox">
                            I have read and understood the foregoing Terms and Conditions of KPMPCATS.
                        </label>
                    </div>

                </div>
                {{-- ── end .terms-modal-body ── --}}

                <div class="terms-modal-footer">
                    <div class="terms-modal-actions">
                        <button type="button" class="terms-modal-btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" class="terms-modal-btn-primary" id="termsModalAgreeBtn" disabled>
                            <i class="fa fa-check"></i>
                            <span>I Agree &amp; Continue</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ── Toggle password visibility ─────────────────────────────────────── --}}
    <script nonce="{{ csp_nonce() }}">
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

    {{-- ── Membership type toggle ──────────────────────────────────────────── --}}
    <script nonce="{{ csp_nonce() }}">
        const select_type = document.getElementById("select_type");

        select_type.addEventListener("change", function () {
            const driver_operator = document.querySelector(".driver-operator");
            if (this.value === "Driver" || this.value === "Allied Workers" || this.value === "Investor Associate") {
                driver_operator.style.display = "none";
            } else {
                driver_operator.style.display = "block";
            }
        });
    </script>

    {{-- ══════════════════════════════════════════════════════════════════
    TERMS & CONDITIONS MODAL — behaviour
    1. "I Agree & Continue" stays disabled until the modal checkbox
    is checked. This part works regardless of whether a matching
    checkbox exists back on the page (checkboxDefault3 is optional —
    it may be commented out, as it currently is).
    2. Clicking it closes the modal, and — only if a page-side
    checkbox with id="checkboxDefault3" exists — checks + enables
    it and marks the link box as "accepted".
    3. Re-opening the modal later keeps the checkbox state so the
    member isn't forced to re-tick it every time.
    ══════════════════════════════════════════════════════════════════ --}}
    <script nonce="{{ csp_nonce() }}">
        (function () {
            const modalCheckbox = document.getElementById('termsModalCheckbox');
            const agreeBtn = document.getElementById('termsModalAgreeBtn');
            // const pageCheckbox = document.getElementById('checkboxDefault3'); // optional — may not exist
            const linkBox = document.getElementById('openTermsModalBtn'); // optional — may be hidden
            const linkTitle = document.getElementById('termsLinkTitle');
            const linkSub = document.getElementById('termsLinkSub');
            const termsModalEl = document.getElementById('termsModal');
            // The visible "Close" button already has data-bs-dismiss="modal".
            // We reuse it (instead of the bootstrap.Modal JS object) so that
            // Bootstrap's OWN event-delegated handler does the closing —
            // this is what actually removes the .modal-backdrop and the
            // body's "modal-open" class.
            const dismissBtn = termsModalEl?.querySelector('[data-bs-dismiss="modal"]');

            // Only the checkbox + Agree button are required for the modal
            // itself to function. Everything else (the page checkbox, the
            // link box) is optional and guarded individually below, so
            // removing/hiding them elsewhere in the page never breaks the
            // modal's own checkbox → button behaviour.
            if (!modalCheckbox || !agreeBtn) return;

            // (function restoreTermsAccepted() {
            //     try {
            //         if (sessionStorage.getItem('kpmpcats_terms_accepted') === '1') {
            //             const hiddenInput = document.getElementById('termsAcceptedInput');
            //             if (hiddenInput) hiddenInput.value = '1';
            //         }
            //     } catch (e) { }
            // })();

            function markAccepted() {
                const hiddenInput = document.getElementById('termsAcceptedInput');
                if (hiddenInput) hiddenInput.value = '1';

                linkBox?.classList.add('terms-link-accepted');
                if (linkTitle) linkTitle.textContent = 'Cooperative Terms & Conditions';
                if (linkSub) {
                    linkSub.innerHTML = '<i class="fa fa-circle-check"></i> Accepted — tap to review again';
                }

                const termsErr = document.getElementById('terms-error');
                if (termsErr) termsErr.style.display = 'none';

                if (typeof window.updateNextButtonState === 'function') {
                    window.updateNextButtonState();
                }
            }

            // Safety net: if a backdrop or the body lock is ever left behind
            // for any reason, clear it so the page never gets stuck again.
            function forceCleanupAnyStuckBackdrop() {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }

            modalCheckbox.addEventListener('change', function () {
                agreeBtn.disabled = !this.checked;
            });

            agreeBtn.addEventListener('click', function () {
                if (!modalCheckbox.checked) return;
                markAccepted();

                if (dismissBtn) {
                    dismissBtn.click();
                } else if (termsModalEl) {
                    termsModalEl.classList.remove('show');
                    termsModalEl.style.display = 'none';
                }

                // Bootstrap's fade-out transition is ~300ms — double check
                // after it finishes that nothing was left behind.
                setTimeout(forceCleanupAnyStuckBackdrop, 350);
            });

            // If the member unchecks the 3rd checkbox manually on the page,
            // require them to reopen the modal and re-confirm.
            // (Only wired up if that checkbox actually exists on the page.)
            // pageCheckbox?.addEventListener('change', function () {
            //     if (!this.checked) {
            //         linkBox?.classList.remove('terms-link-accepted');
            //         if (linkTitle) linkTitle.textContent = 'Read the full Cooperative Terms & Conditions';
            //         if (linkSub) linkSub.textContent = 'Tap to open — required before you can continue';
            //     }
            // });

            // Prevent the inline "Terms and Conditions" <a href="#"> link
            // from jumping the page to the top when it opens the modal.
            document.querySelectorAll('.terms-inline-link[data-bs-toggle="modal"]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                });
            });
        })();
    </script>

    {{-- ── JS files ────────────────────────────────────────────────────────── --}}
    <script src="js_folder/otherinfo_form.js"></script>
    <script src="js_folder/vehicle_form.js"></script>
    <script src="js_folder/display_form.js?=1"></script>
    <script src="js_folder/card_form.js?=1"></script>
    <script src="js_folder/picture_display.js"></script>
    <script src="js_folder/signature_pad.umd.min.js"></script>

    {{-- ── Animate card-box on load ────────────────────────────────────────── --}}
    <script nonce="{{ csp_nonce() }}">
        window.addEventListener('load', function () {
            const cardBox = document.querySelector('.card-box');
            cardBox.style.animation = 'none';
            cardBox.offsetHeight;
            cardBox.style.animation = '';
        });
    </script>

    {{-- ── Clear saved registration data when leaving to the Login page ───── --}}
    {{-- This makes sure the form always starts fresh (Step 1, blank fields) --}}
    {{-- the next time anyone opens /register-page on this browser/tab. --}}
    <script nonce="{{ csp_nonce() }}">
        document.getElementById('signin-link')?.addEventListener('click', function () {
            if (typeof window.clearAllRegistrationData === 'function') {
                window.clearAllRegistrationData();
            }
        });
    </script>

    {{-- ── Success modal (shown after successful registration) ────────────── --}}
    @if (session("success"))
        <button id="triggerModal" data-bs-toggle="modal" data-bs-target="#successModal" style="display:none;"></button>

        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 16px;">

                    <div class="modal-header d-flex flex-column align-items-center text-center py-4"
                        style="background: #ffffff; border-bottom: 1px solid var(--line);">
                        <div class="d-flex justify-content-center align-items-center mb-3"
                            style="width: 72px; height: 72px; border-radius: 50%; background-color: var(--teal);">
                            <i class="fa fa-check" style="font-size: 32px; color: #ffffff;"></i>
                        </div>
                        <h4 class="modal-title fw-bold mb-1" style="color: #1a1a1a;">Registration Successful!</h4>
                        <p class="mb-0" style="opacity: 0.85; font-size: 0.9rem; color: var(--muted)">Your application has
                            been
                            submitted</p>
                    </div>

                    <div class="modal-body text-center px-4 py-4">
                        <p class="fw-semibold mb-2" style="color: var(--teal); font-size: 1rem;">
                            {{ session("success") }}
                        </p>
                        <hr style="border-color: #e9ecef;">
                        <div class="d-flex align-items-start gap-3 text-start mt-3 p-3 rounded-3"
                            style="background: #EDF0F5; border: 1px solid var(--border);">
                            <i class="fa fa-envelope mt-1" style="color: var(--teal); font-size: 1.1rem;"></i>
                            <p class="mb-0" style="color: var(--teal); font-size: 0.9rem; line-height: 1.6;">
                                You will receive an <strong>email notification</strong> once your application
                                has been reviewed and approved by the Board of Directors. Please check your Gmail inbox.
                            </p>
                        </div>
                        <div class="d-flex align-items-start gap-3 text-start mt-3 p-3 rounded-3"
                            style="background: var(--gold-pale); border: 1px solid #F5D9A8;">
                            <i class="fa fa-clock mt-1" style="color: var(--gold); font-size: 1.1rem;"></i>
                            <p class="mb-0" style="color: #6b4f18; font-size: 0.9rem; line-height: 1.6;">
                                Processing may take a few business days. Please be patient while we review your membership
                                application.
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn px-5 py-2 fw-semibold text-white" data-bs-dismiss="modal"
                            style="background: var(--teal); border-radius: 50px; border: none; font-size: 1rem; letter-spacing: 0.5px;">
                            OK, Got it!
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <script nonce="{{ csp_nonce() }}">
            window.addEventListener('load', function () {
                document.getElementById('triggerModal').click();
                document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
                    window.location.href = "{{ route('LoginPage') }}";
                });
            });
        </script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        document.getElementById('btnPrev')?.addEventListener('click', prevStep);
        document.getElementById('btnNext')?.addEventListener('click', nextStep);
    </script>

</body>

</html>