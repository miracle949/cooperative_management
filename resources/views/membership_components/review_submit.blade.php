<style>
    .reminder {
        margin-top: 0.5rem;
    }

    .reminder span {
        font-size: 14px;
        color: #808080;
    }

    .review-vehicle-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 2px;
        padding: 6px 0 15px;
        /* border-bottom: 1px solid #f3f4f6; */
        border-bottom: 1px solid var(--line);
    }

    /* .review-vehicle-row:last-child {
        border-bottom: none;
    } */

    .review-vehicle-label {
        font-weight: 600;
        min-width: 100px;
        color: #374151;
    }

    .review-vehicle-qty {
        color: #6b7280;
        font-size: 13px;
    }

    .review-plate-badge {
        /* background: #f0fdf4; */
        background-color: var(--teal);
        /* border: 1px solid #bbf7d0; */
        border: 1px solid var(--line);
        /* color: #166534; */
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 99px;
        letter-spacing: 0.5px;
    }
</style>

<div class="form-step">
    <div class="row">
        <div class="col-lg-12">
            <div class="form-step-header">
                <div class="form-step-nav">
                    <!-- <div class="header-track">
                        <span>Step 1 of 4</span>
                    </div> -->
                    <h2 class="fw-semibold">Review & <b>Submit</b></h2>
                    <p>Please review your information before submitting. You can go back to previous steps to make
                        changes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="form-step-body form-step-body-2">

        {{-- ══════════════════════════════════════ --}}
        {{-- SECTION 1: PERSONAL DATA --}}
        {{-- ══════════════════════════════════════ --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card-body-box">
                    <h5 class="fw-semibold">Personal Data</h5>

                    <div class="review-parent">
                        <div class="review-first">
                            <div class="review mt-3">
                                <p>Firstname:</p>
                                <p id="firstname_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Middlename:</p>
                                <p id="middlename_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Lastname:</p>
                                <p id="lastname_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Date of Birth:</p>
                                <p id="date_birth_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Place of Birth:</p>
                                <p id="place_birth_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Email:</p>
                                <p id="email_display"></p>
                            </div>
                        </div>

                        <div class="review-second">
                            <div class="review mt-3">
                                <p>Username:</p>
                                <p id="username_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Sex:</p>
                                <p id="sex_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Citizenship:</p>
                                <p id="citizenship_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Membership Category:</p>
                                <p id="membership_type_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Skills/Expertise:</p>
                                <p id="skills_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Cellphone Number:</p>
                                <p id="contact_no_display"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════ --}}
        {{-- SECTION 2: FAMILY BACKGROUND --}}
        {{-- ══════════════════════════════════════ --}}
        <div class="row mt-4">
            <div class="col-lg-12 mt-4">
                <div class="card-body-box">
                    <h5 class="fw-semibold">Family Background</h5>

                    <div class="review-parent">
                        <div class="review-first">
                            <div class="review mt-3">
                                <p>Spouse Name:</p>
                                <p id="spouse_name_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Spouse Date of Birth:</p>
                                <p id="spouse_date_birth_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Spouse Place of Birth:</p>
                                <p id="spouse_place_birth_display"></p>
                            </div>
                        </div>

                        <div class="review-second">
                            <div class="review mt-3">
                                <p>Son:</p>
                                <p id="son_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Daughter:</p>
                                <p id="daughter_display"></p>
                            </div>
                            <div class="review mt-3">
                                <p>Other Specification:</p>
                                <p id="other_spec_display"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════ --}}
        {{-- SECTION 3: VEHICLE INFORMATION --}}
        {{-- ══════════════════════════════════════ --}}
        <div class="row mt-4">
            <div class="col-lg-12 mt-4">
                <div class="card-body-box">
                    <h5 class="fw-semibold">Vehicle Information</h5>
                    <div id="vehicles_review_display" class="mt-3">
                        <span style="font-size: 15.5px;">No vehicles entered.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════ --}}
        {{-- SIGNATURE --}}
        {{-- ══════════════════════════════════════ --}}
        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card-body-box">
                    <label class="mt-2"><b>I HEREBY CERTIFY</b> that the foregoing statements are <b>True and
                            Correct:</b></label>
                    <label class="mt-4">Applicant's Signature</label>
                    <small id="signature-error" style="color:#dc2626; font-size:12.5px; display:none; margin-top:4px;">
                        Please provide your signature before submitting.
                    </small>
                    <div class="row">
                        <div class="col-lg-12 mt-2">
                            <div class="card-box-signature"
                                style="border: 1.5px solid #ddd; border-radius: 12px; padding: 1rem; background: #fff;">
                                <canvas id="signature-pad"
                                    style="border: 1px solid #e0e0e0; border-radius: 10px; width: 100%; height: 160px; display: block; touch-action: none;"></canvas>
                                <div class="mt-2 text-end">
                                    <button type="button" id="clear"
                                        style="background: #f5f5f5; border: 1.5px solid #ddd; border-radius: 8px; padding: 5px 16px; font-size: 13px; cursor: pointer; color: #555;">
                                        <i class="fa fa-trash" style="font-size: 11px;"></i> Clear
                                    </button>
                                </div>
                                <input type="hidden" name="signature" id="signature" required>
                            </div>
                        </div>
                    </div>

                    {{-- Fullscreen signature modal --}}
                    <div id="signature-modal-overlay"
                        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;">
                        <div
                            style="background:#fff; border-radius:16px; width:95vw; max-width:900px; padding:1.5rem; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                                <h5 style="margin:0; font-weight:700; color:#1a4a3a;">Applicant's Signature</h5>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" id="btn-clear-modal-signature"
                                        style="background:#f5f5f5; border:1.5px solid #ddd; border-radius:8px; padding:6px 16px; font-size:13px; cursor:pointer; color:#555;">
                                        <i class="fa fa-trash" style="font-size:11px;"></i> Clear
                                    </button>
                                    <button type="button" id="btn-save-modal-signature"
                                        style="background:#1a4a3a; color:#fff; border:none; border-radius:8px; padding:6px 20px; font-size:13px; font-weight:600; cursor:pointer;">
                                        <i class="fa fa-check"></i> Save Signature
                                    </button>
                                </div>
                            </div>
                            <canvas id="signature-pad-modal"
                                style="border:2px solid #1a4a3a; border-radius:10px; width:100%; height:300px; display:block; touch-action:none; background:#fafafa;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card border-0"
                    style="">
                    <p class="m-0" style="font-size: 14.5px; color: var(--gold);"><b>Note:</b> By submitting this
                        application, you authorize
                        the cooperative to verify the information provided and to conduct necessary background checks as
                        required by law.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    document.getElementById('btn-clear-modal-signature')?.addEventListener('click', function () {
        if (typeof clearModalSignature === 'function') clearModalSignature();
    });
    document.getElementById('btn-save-modal-signature')?.addEventListener('click', function () {
        if (typeof saveModalSignature === 'function') saveModalSignature();
    });
</script>
</div>