<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Application Form</title>
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <link rel="icon" href="images/websitelogo.png" type="image/png">
    <link rel="stylesheet" href="{{ asset('css_folder/application-form.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('font-awesome-icon/css/all.min.css') }}">
</head>

<body>
    <div class="container-fluid">
        <nav>
            <div class="nav-logo">
                <img src="../images/logo2.png" alt="">
                <div class="nav-text">
                    <h2 class="fw-bold">Membership Application Form</h2>
                    <p>Ready to become part of something special? We can't wait to welcome you.</p>
                </div>
            </div>
        </nav>




    @if($alreadySubmitted ?? false)

            {{-- Already submitted state --}}
            <div style="display:flex; justify-content:center; align-items:center; min-height:80vh; padding: 3rem 2rem 3rem 2rem;">
                <div style="
                    background:#fff;
                    border-radius:20px;
                    width:100%;
                    max-width:500px;
                    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                    overflow:hidden;
                    border: 1px solid #e8e8e8;
                ">

                    {{-- Header --}}
                    <div style="text-align:center; padding: 2.5rem 1.5rem 1.5rem;">
                        <div style="
                            width:70px; height:70px;
                            background:linear-gradient(135deg,#e8f5ee,#d0ede0);
                            border-radius:50%;
                            display:flex; align-items:center; justify-content:center;
                            margin:0 auto 1rem;
                        ">
                            <i class="fa-solid fa-circle-check" style="color:#1a4a3a; font-size:2rem;"></i>
                        </div>
                        <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin:0 0 0.4rem;">
                            Application Already Submitted
                        </h3>
                        <p style="font-size:0.85rem; color:#888; margin:0 0 1.5rem; line-height:1.5;">
                            You have already completed your membership application form.<br>
                            Please wait for the Board of Directors to review your application.
                        </p>
                    </div>

                    <hr style="margin:0; border-color:#f0f0f0;">

                    {{-- Info card --}}
                    <div style="padding: 1.5rem;">
                        <div style="
                            background:#f9f9f9;
                            border:1.5px solid #e8e8e8;
                            border-radius:14px;
                            padding:1rem 1.2rem;
                            margin-bottom:1.2rem;
                        ">
                            <div style="display:flex; justify-content:space-between; padding:0.45rem 0; border-bottom:1px dashed #ececec; font-size:0.84rem;">
                                <span style="color:#888; font-weight:500;">Member</span>
                                <span style="color:#1a4a3a; font-weight:700;">{{ $user->fullname }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:0.45rem 0; border-bottom:1px dashed #ececec; font-size:0.84rem;">
                                <span style="color:#888; font-weight:500;">Email</span>
                                <span style="color:#1a4a3a; font-weight:700;">{{ $user->email }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:0.45rem 0; font-size:0.84rem;">
                                <span style="color:#888; font-weight:500;">Status</span>
                                <span style="
                                    display:inline-flex; align-items:center; gap:6px;
                                    background:#fff8e1; border:1.5px solid #ffe082;
                                    color:#b8860b; border-radius:20px;
                                    padding:0.2rem 0.75rem; font-size:0.75rem; font-weight:700;
                                ">
                                    <span style="width:7px;height:7px;background:#e6a817;border-radius:50%;display:inline-block;"></span>
                                    Pending Review
                                </span>
                            </div>
                        </div>

                        {{-- Email notice --}}
                        <div style="
                            background:#f0fdf4; border:1.5px solid #bbf7d0;
                            border-radius:10px; padding:0.8rem 1rem;
                            display:flex; gap:10px; align-items:flex-start;
                            font-size:0.82rem; color:#166534; line-height:1.6;
                            margin-bottom:1.2rem;
                        ">
                            <i class="fa fa-envelope" style="margin-top:2px; color:#16a34a;"></i>
                            <p style="margin:0;">You will receive an <strong>email notification</strong> once your application has been reviewed and approved by the Board of Directors.</p>
                        </div>

                        {{-- Go to Login button --}}
                        <a href="{{ route('LoginPage') }}" style="
                            display:flex; align-items:center; justify-content:center; gap:8px;
                            width:100%; padding:0.85rem;
                            background: linear-gradient(135deg,#1a4a3a,#2d6a4f);
                            color:#fff; border:none; border-radius:12px;
                            font-size:0.95rem; font-weight:700;
                            text-decoration:none; box-sizing:border-box;
                        ">
                            <i class="fa fa-right-to-bracket"></i> Go to Login
                        </a>
                    </div>

                </div>
            </div>

    @else

        <div class="form-container-body">
            <form id="form" action="{{ route('applicationFormButton', $user->id) }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="form-box">
                    <div class="stepper">
                        <div class="step active">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <div class="circle">1</div>
                                <div class="label">First page</div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <div class="circle">2</div>
                                <div class="label">Second page</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="body-card">

                            {{-- ==================== STEP 1 ==================== --}}
                            <div class="form-step active">

                                <div class="card-share-header">
                                    <div class="card-logo">
                                        <i class="fa fa-file-lines"></i>
                                    </div>
                                    <p>KMPCATS</p>
                                </div>

                                <div class="header-card">
                                    <h2>Membership Application Form</h2>
                                    <p>Your membership application has been approved! You can now proceed to complete your information.</p>
                                </div>

                                {{-- PERSONAL INFORMATION --}}
                                <h3 class="mt-4">Personal Information</h3>
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-lg-6 mt-4">
                                            <label>Fullname</label>
                                            <input type="text" name="fullname" value="{{ $user->fullname ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-6 mt-4">
                                            <label>Username</label>
                                            <input type="text" name="username" value="{{ $user->username ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Email</label>
                                            <input type="email" name="email" value="{{ $user->email ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Date of Birth</label>
                                            <input type="date" name="date_of_birth" value="{{ $other->date_of_birth ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Place of Birth</label>
                                            <input type="text" name="place_of_birth" value="{{ $other->place_of_birth ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Contact No</label>
                                            <input type="text" name="contact_no" value="{{ $other->contact_no ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Present Address</label>
                                            <input type="text" name="present_address" value="{{ $other->present_address ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Permanent Address</label>
                                            <input type="text" name="permanent_address" value="{{ $other->permanent_address ?? '' }}" class="form-control mt-1">
                                        </div>
                                    </div>
                                </div>

                                {{-- OTHER PERSONAL INFORMATION --}}
                                <h3 class="mt-5">Other Personal Information</h3>
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-lg-4 mt-4">
                                            <label>Sex</label>
                                            <input type="text" name="sex" value="{{ $other->sex ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Civil Status</label>
                                            <input type="text" name="civil_status" value="{{ $other->civil_status ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Citizenship</label>
                                            <input type="text" name="citizenship" value="{{ $other->citizenship ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Height</label>
                                            <input type="text" name="height" value="{{ $other->height ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Weight</label>
                                            <input type="text" name="weight" value="{{ $other->weight ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Blood Type</label>
                                            <input type="text" name="blood_type" value="{{ $other->blood_type ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Other Skills/ Expertise</label>
                                            <input type="text" name="skills" value="{{ $other->skills ?? '' }}" class="form-control mt-1">
                                        </div>
                                    </div>
                                </div>

                                {{-- FAMILY BACKGROUND --}}
                                <h3 class="mt-5">Family Background</h3>
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-lg-4 mt-4">
                                            <label>Spouse Name</label>
                                            <input type="text" name="spouse_name" value="{{ $spouse->spouse_name ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Date of Birth</label>
                                            <input type="date" name="spouse_date_birth" value="{{ $spouse->spouse_date_birth ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Place of Birth</label>
                                            <input type="text" name="spouse_place_birth" value="{{ $spouse->spouse_place_birth ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Son</label>
                                            <input type="number" name="number_son" value="{{ $spouse->number_son ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Daughter</label>
                                            <input type="number" name="number_daughter" value="{{ $spouse->number_daughter ?? '' }}" class="form-control mt-1">
                                        </div>
                                        <div class="col-lg-4 mt-4">
                                            <label>Other Specification</label>
                                            <input type="text" name="other_spec" value="{{ $spouse->other_spec ?? '' }}" class="form-control mt-1">
                                        </div>
                                    </div>
                                </div>

                                {{-- VEHICLES INFORMATION --}}
                                <h3 class="mt-5">Vehicles Information</h3>
                                <div class="form-body">
                                    <div class="vehicles-grid mt-5" id="vehiclesGrid"></div>
                                </div>

                                {{-- GOVERNMENT INFORMATION --}}
                                <h3 class="mt-5">Government Information</h3>
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-lg-6 mt-4">
                                            <label>SSS ID</label>
                                            <div class="card-box mt-2">
                                                <input type="file" name="sss_id" class="form-control mt-1" style="font-size: 14px">
                                                <span>No file selected</span>
                                            </div>
                                            @if(!empty($governmentIds->sss_id))
                                                <small class="mt-1 d-block">
                                                    Current: <a href="{{ asset('storage/' . $governmentIds->sss_id) }}" target="_blank">View file</a>
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-lg-6 mt-4">
                                            <label>Philhealth ID</label>
                                            <div class="card-box mt-2">
                                                <input type="file" name="philhealth_id" class="form-control mt-1" style="font-size: 14px">
                                                <span>No file selected</span>
                                            </div>
                                            @if(!empty($governmentIds->philhealth_id))
                                                <small class="mt-1 d-block">
                                                    Current: <a href="{{ asset('storage/' . $governmentIds->philhealth_id) }}" target="_blank">View file</a>
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-lg-6 mt-4">
                                            <label>Pagibig ID</label>
                                            <div class="card-box mt-2">
                                                <input type="file" name="pagibig_id" class="form-control mt-1" style="font-size: 14px">
                                                <span>No file selected</span>
                                            </div>
                                            @if(!empty($governmentIds->pagibig_id))
                                                <small class="mt-1 d-block">
                                                    Current: <a href="{{ asset('storage/' . $governmentIds->pagibig_id) }}" target="_blank">View file</a>
                                                </small>
                                            @endif
                                        </div>
                                        <div class="col-lg-6 mt-4">
                                            <label>Tin ID</label>
                                            <div class="card-box mt-2">
                                                <input type="file" name="tin_id" class="form-control mt-1" style="font-size: 14px">
                                                <span>No file selected</span>
                                            </div>
                                            @if(!empty($governmentIds->tin_id))
                                                <small class="mt-1 d-block">
                                                    Current: <a href="{{ asset('storage/' . $governmentIds->tin_id) }}" target="_blank">View file</a>
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- EDUCATIONAL BACKGROUND --}}
                                <h3 class="mt-5">Educational Background</h3>
                                <div class="form-body">
                                    <div class="row">
                                        @php
                                            $eduLevels = ['Elementary', 'Secondary', 'Vocational/Trade Course', 'College'];
                                            $eduData   = $education ?? collect();
                                        @endphp

                                        @foreach($eduLevels as $index => $level)
                                        <div class="col-lg-6">
                                            <label class="mt-4">{{ strtoupper($level) }}</label>
                                            <input type="hidden" name="educational_level[]" value="{{ $level }}">
                                            @php $eduRecord = $eduData->firstWhere('educational_level', $level); @endphp
                                            <div class="d-flex gap-4">
                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="edu_status[{{ $index }}]"
                                                            value="Graduated"
                                                            {{ ($eduRecord->status ?? '') == 'Graduated' ? 'checked' : '' }}>
                                                        <label class="form-check-label">Graduated</label>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            name="edu_status[{{ $index }}]"
                                                            value="Undergraduate"
                                                            {{ ($eduRecord->status ?? '') == 'Undergraduate' ? 'checked' : '' }}>
                                                        <label class="form-check-label">Undergraduate</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 mt-2">
                                                    <input type="text" name="edu_specify[{{ $index }}]"
                                                        value="{{ $eduRecord->specify ?? '' }}"
                                                        class="form-control" placeholder="if not grad pls specify">
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="actions">
                            <button type="button" class="btn-prev" data-action="prevStep">
                                <i class="fa fa-chevron-left"></i>
                                <span>Previous</span>
                            </button>
                            <button type="button" class="btn-next" data-action="nextStep">
                                <span>Next page</span>
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <button type="submit" class="btn-submit" style="display:none;">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Move ALL scripts inside @else --}}
        @php
            $existingVehicles = [
                'UV'        => $vehicles->get('UV', collect())->pluck('plate_no')->toArray(),
                'TAXI'      => $vehicles->get('TAXI', collect())->pluck('plate_no')->toArray(),
                'BUS'       => $vehicles->get('BUS', collect())->pluck('plate_no')->toArray(),
                'MINI BUS'  => $vehicles->get('MINI BUS', collect())->pluck('plate_no')->toArray(),
                'JEEP'      => $vehicles->get('JEEP', collect())->pluck('plate_no')->toArray(),
                'MULTI-CAB' => $vehicles->get('MULTI-CAB', collect())->pluck('plate_no')->toArray(),
                'TRICYCLE'  => $vehicles->get('TRICYCLE', collect())->pluck('plate_no')->toArray(),
            ];
        @endphp

        <script nonce="{{ csp_nonce() }}">
            const EXISTING_VEHICLES = @json($existingVehicles);
        </script>

        <script src="{{ asset('js_folder/vehi_application.js') }}"></script>

        <script nonce="{{ csp_nonce() }}">
            let currentStep = 0;
            const steps = document.querySelectorAll(".form-step");
            const stepper = document.querySelectorAll(".step");
            const nextBtn = document.querySelector(".btn-next");
            const prevBtn = document.querySelector(".btn-prev");
            const submitBtn = document.querySelector(".btn-submit");

            updateSteps();

            function updateSteps() {
                steps.forEach((step, index) => {
                    step.classList.toggle("active", index === currentStep);
                    stepper[index].classList.toggle("active", index === currentStep);
                });
                prevBtn.style.display = currentStep === 0 ? "none" : "inline-block";
                if (currentStep === steps.length - 1) {
                    nextBtn.style.display = "none";
                    submitBtn.style.display = "inline-block";
                } else {
                    nextBtn.style.display = "inline-block";
                    submitBtn.style.display = "none";
                }
            }

            function nextStep() {
                const currentFormStep = steps[currentStep];
                const requiredFields = currentFormStep.querySelectorAll("input[required], select[required]");
                for (let field of requiredFields) {
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        return;
                    }
                }
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    updateSteps();
                }
            }

            function prevStep() {
                if (currentStep > 0) {
                    currentStep--;
                    updateSteps();
                }
            }
        </script>

    @endif

    </div>
</body>
</html>