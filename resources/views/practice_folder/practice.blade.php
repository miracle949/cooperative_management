@if(!$canApplyLoan)
    <div class="sc-parent">
        <div class="sc-requirement-alert">
            <div class="alert-icon-wrap"><i class="fa fa-ban"></i></div>
            <div class="alert-body">
                <h5>Share Capital Requirement</h5>
                <p>
                    You need at least <strong>10 shares</strong> of Share Capital before you can
                    apply
                    for a
                    loan.
                    You currently have <strong>{{ $currentShares }} share(s)</strong> —
                    you need <strong>{{ 10 - $currentShares }} more</strong> to be eligible.
                </p>
            </div>
        </div>
    </div>
@endif

<div class="card-parent-box">
    <div class="card-box">
        <div class="card-sub-header">
            <div class="tw:w-[46px] tw:h-[46px] card-icon" style="border-radius: 10px">
                <i class="fa-solid fa-file"></i>
            </div>
            <div class="d-flex justify-content-between align-items-center w-100">
                <div class="card-header">
                    <h3>Loan Applications</h3>
                    <p style="margin: 0px;" class="tw:text-[#808080]">Fill out the form below to
                        apply
                        for a
                        loan</p>
                </div>
                <div class="alert-reminder">
                    <button style="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fa-solid fa-question"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-step">
            <div class="step">
                <div class="step-icon">
                    <span>1</span>
                </div>
                <div class="step-text">
                    <span>Step 1</span>
                    <p>Loan details</p>
                </div>
            </div>

            <div class="step">
                <div class="step-icon">
                    <span>2</span>
                </div>
                <div class="step-text">
                    <span>Step 2</span>
                    <p>Charges & Breakdown</p>
                </div>
            </div>

            <div class="step">
                <div class="step-icon">
                    <span>3</span>
                </div>
                <div class="step-text">
                    <span>Step 3</span>
                    <p>Review & Submit</p>
                </div>
            </div>
        </div>

        <div class="card-sub-body">
            <form id="loan-form" action="{{ route("lendingProgram") }}" method="post"
                class="{{ !$canApplyLoan ? 'form-disabled' : '' }}" enctype="multipart/form-data" {{ !$canApplyLoan ? 'onsubmit=return false' : '' }}>
                @csrf
                <div class="row form-parent">

                    {{-- ── Loan Type ── --}}
                    <div class="col-lg-6 col-md-12 mt-4">
                        <label>Loan Type *</label>
                        <select name="lending_type" class="form-select mt-2" id="lending_type"
                            onchange="updateTermOptions()" required>
                            <option value="">Select Loan type</option>
                            <option value="Personal Loan">Personal Loan</option>
                            <option value="Emergency Loan">Emergency Loan</option>
                            <option value="Business Loan">Business Loan</option>
                            <option value="Education Loan">Education Loan</option>
                        </select>
                    </div>

                    {{-- ── Loan Amount ── --}}
                    <div class="col-lg-6 col-md-12 mt-md-4 mt-sm-4 loan-input">
                        <label style="display:flex; align-items:center; gap:8px;">
                            Loan Amount (₱) *
                            <span
                                style="background: var(--gold-dim);border:1px solid #ffe082;color: var(--gold);font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;white-space:nowrap;">
                                Max: ₱{{ number_format($remainingLoanable, 2) }}
                            </span>
                        </label>
                        <input type="number" name="lending_amount" id="lending_amount_input"
                            oninput="let val=parseFloat(this.value);if(val>25000){this.value=25000}else if(val<1)this.value='';recalculate();checkLoanLimit(this);"
                            placeholder="Enter amount (max ₱{{ number_format($remainingLoanable, 2) }})"
                            class="form-control mt-2" min="1" max="{{ $remainingLoanable }}" {{ $hasFullyLoaned ? 'disabled' : '' }}
                            onkeydown="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-')event.preventDefault();"
                            required>
                        <div id="loan-limit-warning"
                            style="display:none;margin-top:6px;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:8px;padding:6px 10px;font-size:12px;color:#dc2626;font-weight:500;">
                            <i class="fa fa-circle-exclamation" style="margin-right:5px;"></i>
                            You can only borrow up to
                            <strong>₱{{ number_format($remainingLoanable, 2) }}</strong> more.
                        </div>
                    </div>

                    {{-- ── Loan Term ── --}}
                    <div class="col-lg-6 col-md-12 mt-4">
                        <label>Loan Term *</label>

                        {{-- Non-business: 6 months only --}}
                        <select name="lending_type_term_nonbusiness" id="lending_type_term_nonbusiness"
                            class="form-select mt-2" onchange="recalculate()">
                            <option value="">Select lending term</option>
                            <option value="6 months">6 months</option>
                        </select>

                        {{-- Business: 6 or 12 months --}}
                        <select name="lending_type_term_business" id="lending_type_term_business"
                            class="form-select mt-2" style="display:none;" onchange="recalculate()">
                            <option value="">Select lending term</option>
                            <option value="6 months">6 months</option>
                            <option value="12 months">12 months</option>
                        </select>

                        {{-- Hidden field carries the actual submitted term value --}}
                        <input type="hidden" name="lending_type_term" id="lending_type_term">
                    </div>

                    {{-- ── Monthly Income ── --}}
                    <div class="col-lg-6 col-md-12 mt-4">
                        <label>Enter Monthly Income (₱) *</label>
                        <input type="number" name="monthly_income" placeholder="Enter monthly income"
                            class="form-control mt-2"
                            oninput="if(this.value.length > 6) this.value = this.value.slice(0,6);"
                            onkeydown="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-')event.preventDefault();"
                            required>
                    </div>

                    {{-- ── Purpose of Loan ── --}}
                    <div class="col-12 mt-4">
                        <label>Purpose of Loan *</label>
                        <select name="purpose_loan" id="purpose_loan_select" class="form-select mt-2"
                            onchange="handlePurposeChange(this)">
                            <option value="" disabled selected>Select purpose</option>
                            <option value="Medical Expenses">Medical Expenses</option>
                            <option value="Education">Education</option>
                            <option value="Business Capital">Business Capital</option>
                            <option value="Emergency Needs">Emergency Needs</option>
                            <option value="Home Improvement">Home Improvement</option>
                            <option value="Debt Consolidation">Debt Consolidation</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Daily Expenses">Daily Expenses</option>
                            <option value="Travel">Travel</option>
                            <option value="Others">Others</option>
                        </select>

                        <div id="others-textarea-wrapper" style="display: block; margin-top: 10px;">
                            <textarea name="purpose_loan_others" id="purpose_loan_textarea"
                                class="form-control p-3 tw:w-[100%]" placeholder="Describe the purpose of your loan..."
                                style="font-size:14.5px;" required></textarea>
                        </div>
                    </div>

                    {{-- ── Supporting Documents ── --}}
                    <div id="docs-wrapper" style="display:none;">
                        <div class="row" style="padding:0 0px;">
                            <div class="col-lg-12" style="padding:0 12px;">
                                <div class="line"></div>
                            </div>
                        </div>

                        <h4 class="docs-heading">Supporting Documents</h4>

                        <!-- Personal Lending -->
                        <div id="docs-personal" style="display:none;">
                            <div class="upload-grid">
                                <div class="upload-card" id="card-vid-personal">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Valid ID</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-vid-personal"></span>
                                    <input type="file" name="personal_valid_id" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-vid-personal','name-vid-personal')">
                                </div>
                                <div class="upload-card" id="card-poi-personal">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Proof of Income</span>
                                    <span class="upload-sub">Payslip / COE · PDF or image</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-poi-personal"></span>
                                    <input type="file" name="personal_proof_of_income" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-poi-personal','name-poi-personal')">
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Lending -->
                        <div id="docs-emergency" style="display:none;">
                            <div class="upload-grid">
                                <div class="upload-card" id="card-vid-emergency">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Valid ID</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-vid-emergency"></span>
                                    <input type="file" name="emergency_valid_id" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-vid-emergency','name-vid-emergency')">
                                </div>
                                <div class="upload-card" id="card-poi-emergency">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Proof of Income</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-poi-emergency"></span>
                                    <input type="file" name="emergency_proof_of_income" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-poi-emergency','name-poi-emergency')">
                                </div>
                                <div class="upload-card" id="card-poe-emergency">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Proof of Emergency</span>
                                    <span class="upload-sub">Medical Cert, Hospital Bill, Police
                                        Report</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-poe-emergency"></span>
                                    <input type="file" name="proof_of_emergency" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-poe-emergency','name-poe-emergency')">
                                </div>
                            </div>
                        </div>

                        <!-- Business Lending -->
                        <div id="docs-business" style="display:none;">
                            <div class="upload-grid">
                                <div class="upload-card" id="card-vid-business">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Valid ID</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-vid-business"></span>
                                    <input type="file" name="business_valid_id" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-vid-business','name-vid-business')">
                                </div>
                                <div class="upload-card" id="card-poi-business">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Proof of Income</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-poi-business"></span>
                                    <input type="file" name="business_proof_of_income" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-poi-business','name-poi-business')">
                                </div>
                                <div class="upload-card" id="card-bp-business">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Business Permit / DTI
                                        Registration</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-bp-business"></span>
                                    <input type="file" name="business_permit" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-bp-business','name-bp-business')">
                                </div>
                                <div class="upload-card" id="card-fs-business">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Business Financial
                                        Statement</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-optional">Optional</span>
                                    <span class="upload-filename" id="name-fs-business"></span>
                                    <input type="file" name="financial_statement" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-fs-business','name-fs-business')">
                                </div>
                            </div>
                        </div>

                        <!-- Education Lending -->
                        <div id="docs-education" style="display:none;">
                            <div class="upload-grid">
                                <div class="upload-card" id="card-sid-education">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">School ID</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-sid-education"></span>
                                    <input type="file" name="school_id" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-sid-education','name-sid-education')">
                                </div>
                                <div class="upload-card" id="card-cor-education">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">COR (Certificate of
                                        Registration)</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-cor-education"></span>
                                    <input type="file" name="cor" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-cor-education','name-cor-education')">
                                </div>
                                <div class="upload-card" id="card-vid-education">
                                    <div class="upload-icon">
                                        <svg class="upload-arrow" viewBox="0 0 24 24">
                                            <path d="M12 16v-8M8 12l4-4 4 4" />
                                            <rect x="3" y="3" width="18" height="18" rx="3" />
                                        </svg>
                                        <svg class="upload-check" viewBox="0 0 24 24" fill="none" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                    <span class="upload-label">Valid ID</span>
                                    <span class="upload-sub">PDF, JPG or PNG · max 5MB</span>
                                    <span class="upload-badge badge-required">Required</span>
                                    <span class="upload-filename" id="name-vid-education"></span>
                                    <input type="file" name="education_valid_id" accept=".jpg,.jpeg,.png,.pdf"
                                        onchange="onFileSelected(this,'card-vid-education','name-vid-education')">
                                </div>
                            </div>
                        </div>

                        {{-- ── Loanable Amount Status Banner ── --}}
                        @if($hasFullyLoaned)
                            <div
                                style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:1rem 1.25rem;margin-top:1.25rem;display:flex;align-items:flex-start;gap:12px;">
                                <div
                                    style="width:36px;height:36px;flex-shrink:0;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa fa-ban" style="color:#dc2626;font-size:15px;"></i>
                                </div>
                                <div>
                                    <p style="margin:0 0 3px;font-size:13.5px;font-weight:700;color:#1a1a1a;">
                                        Loan
                                        Limit Reached</p>
                                    <p style="margin:0;font-size:12.5px;color:#dc2626;line-height:1.5;">
                                        You have an active loan totalling
                                        <strong>₱{{ number_format($totalActiveLoan, 2) }}</strong>
                                        — the maximum allowed is <strong>₱25,000.00</strong>.
                                        You must repay your existing loan before applying again.
                                    </p>
                                </div>
                            </div>
                        @elseif($totalActiveLoan > 0)
                            <div
                                style="background:#fff8e1;border:1.5px solid #ffe082;border-radius:12px;padding:1rem 1.25rem;margin-top:1.25rem;display:flex;align-items:flex-start;gap:12px;">
                                <div
                                    style="width:36px;height:36px;flex-shrink:0;background:#fff3cd;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa fa-circle-info" style="color:#e6a817;font-size:15px;"></i>
                                </div>
                                <div style="width:100%;">
                                    <p style="margin:0 0 6px;font-size:13.5px;font-weight:700;color:#1a1a1a;">
                                        Remaining Loanable Amount</p>
                                    <p style="margin:0 0 10px;font-size:12.5px;color:#856404;line-height:1.5;">
                                        You currently have an active loan of
                                        <strong>₱{{ number_format($totalActiveLoan, 2) }}</strong>.
                                        You
                                        may
                                        still
                                        borrow up to:
                                    </p>
                                    <div
                                        style="background:#f5f5f5;border-radius:20px;height:10px;overflow:hidden;margin-bottom:6px;">
                                        <div
                                            style="height:10px;border-radius:20px;background:linear-gradient(90deg,#e6a817,#f59e0b);width:{{ min(100, ($totalActiveLoan / 25000) * 100) }}%;transition:width 0.4s;">
                                        </div>
                                    </div>
                                    <div
                                        style="display:flex;justify-content:space-between;font-size:11.5px;color:#999;margin-bottom:8px;">
                                        <span>Used: ₱{{ number_format($totalActiveLoan, 2) }}</span>
                                        <span>Limit: ₱25,000.00</span>
                                    </div>
                                    <div
                                        style="background:#fff;border:1.5px solid #ffe082;border-radius:8px;padding:8px 14px;display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#1a4a3a;">
                                        <i class="fa fa-coins"></i>
                                        Available to borrow:
                                        ₱{{ number_format($remainingLoanable, 2) }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:0.75rem 1rem;margin-top:1.25rem;display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#166534;">
                                <i class="fa fa-circle-info" style="margin-top:2px;flex-shrink:0;"></i>
                                <span>You may borrow up to <strong>₱25,000.00</strong>. Applications
                                    exceeding
                                    this
                                    limit will not be processed.</span>
                            </div>
                        @endif

                    </div>

                    <div class="row" style="padding:0 0 0 12px;">
                        <div class="col-lg-12" style="padding:0 0 0 12px;">
                            <div class="line"></div>
                        </div>
                    </div>

                    <input type="hidden" name="monthly_payment" id="hidden-monthly">
                    <input type="hidden" name="total_payment" id="hidden-total">
                    <input type="hidden" name="total_interest" id="hidden-interest">

                    <div class="col-12 mt-5 mt-md-4">
                        <button type="button" class="tw:w-[100%] btn" onclick="showConfirmation()">Submit
                            Application</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-calculate-parent">
        <div class="card-calculate">
            <div class="calculate-header">
                <h3>Loan Invoice</h3>
                <p>Auto Computes as you fill the form</p>
            </div>
            <div class="calculate-body">
                <div class="estimate">
                    <p>Estimated Monthly Payment</p>
                    <div class="payment">
                        <h4 id="calc-monthly">₱-</h4>
                    </div>
                    <p id="calc-term-sub">Fill in amount & term to compute.</p>
                </div>
                <div class="parent-amount">
                    <div class="card-amount">
                        <div class="text-amount">
                            <p>Loan Type</p>
                        </div>
                        <div class="amount">
                            <p id="calc-type">-</p>
                        </div>
                    </div>
                    <div class="card-amount">
                        <div class="text-amount">
                            <p>Loan Amount</p>
                        </div>
                        <div class="amount">
                            <p id="calc-amount">-</p>
                        </div>
                    </div>
                    <div class="card-amount">
                        <div class="text-amount">
                            <p>Interest Rate (monthly)</p>
                        </div>
                        <div class="amount">
                            <p id="calc-rate">-</p>
                        </div>
                    </div>
                    <div class="card-amount">
                        <div class="text-amount">
                            <p>Loan Term</p>
                        </div>
                        <div class="amount">
                            <p id="calc-term">-</p>
                        </div>
                    </div>
                    <hr>
                    <div class="card-amount total-amount">
                        <div class="text-amount">
                            <p>Total Payment</p>
                        </div>
                        <div class="amount">
                            <p id="calc-total">-</p>
                        </div>
                    </div>
                    <div class="card-amount">
                        <div class="text-amount">
                            <p>Total Interest</p>
                        </div>
                        <div class="amount">
                            <p id="calc-interest">-</p>
                        </div>
                    </div>
                    <div class="reminders">
                        <p>💡 Rates are indicative. Final terms subject to credit evaluation and
                            cooperative
                            approval.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>