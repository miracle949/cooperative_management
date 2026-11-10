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
                    <h2>Membership <em class="accent">Application</em> Form</h2>
                    <p>Ready to become part of something special? We can't wait to welcome you.</p>
                </div>
            </div>
        </nav>

    @if($alreadySubmitted ?? false)

        {{-- Already submitted state --}}
        @php
            $isApproved = strtolower((string) ($other->approval_status ?? '')) === 'approved';
        @endphp
        <div class="status-wrap">
            <div class="status-card">

                <div class="status-head">
                    <div class="status-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="status-title">Application <em class="accent">{{ $isApproved ? 'Completed' : 'Submitted' }}</em></h3>
                    <p class="status-sub">
                        @if($isApproved)
                            Thank you! Your information has been received.<br>
                            Your membership is already approved, so you can log in now.
                        @else
                            You have already completed your membership application form.<br>
                            Please wait for the Board of Directors to review your application.
                        @endif
                    </p>
                </div>

                <hr class="m-0" style="border-color: var(--border);">

                <div class="status-body">
                    <div class="status-info">
                        <div class="status-row">
                            <span class="k">Member</span>
                            <span class="v">{{ trim(implode(' ', array_filter([$user->first_name ?? '', $user->middle_name ?? '', $user->last_name ?? '']))) ?: ($user->fullname ?? '') }}</span>
                        </div>
                        <div class="status-row">
                            <span class="k">Email</span>
                            <span class="v">{{ $user->email }}</span>
                        </div>
                        <div class="status-row">
                            <span class="k">Status</span>
                            @if($isApproved)
                                <span class="status-pill approved"><span class="dot"></span>Approved</span>
                            @else
                                <span class="status-pill"><span class="dot"></span>Pending Review</span>
                            @endif
                        </div>
                    </div>

                    @unless($isApproved)
                    <div class="status-note">
                        <i class="fa fa-envelope"></i>
                        <p>You will receive an <strong>email notification</strong> once your application has been reviewed and approved by the Board of Directors.</p>
                    </div>
                    @endunless

                    <a href="{{ request()->url() }}?edit=1" class="status-btn status-btn-light">
                        <i class="fa fa-pen"></i> Update my information
                    </a>

                    <a href="{{ route('LoginPage') }}" class="status-btn" style="margin-top:0.7rem">
                        <i class="fa fa-right-to-bracket"></i> Go to Login
                    </a>
                </div>

            </div>
        </div>

    @else

        <div class="form-container-body">
            @if ($errors->any())
                <div class="form-alert" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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
                                    <p>KPMPCATS</p>
                                </div>

                                <div class="header-card">
                                    <h2>Membership <em class="accent">Application</em> Form</h2>
                                    <p>Your membership application has been approved! You can now proceed to complete your information.</p>
                                </div>

                                {{-- PERSONAL INFORMATION --}}
                                <h3 class="mt-4">Personal Information</h3>
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-lg-6 mt-4">
                                            <label>Fullname</label>
                                            <input type="text" name="fullname" value="{{ trim(implode(' ', array_filter([$user->first_name ?? '', $user->middle_name ?? '', $user->last_name ?? '']))) ?: ($user->fullname ?? '') }}" class="form-control mt-1" readonly>
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
                                        @foreach([
                                            'sss_id'        => 'SSS ID',
                                            'philhealth_id' => 'Philhealth ID',
                                            'pagibig_id'    => 'Pagibig ID',
                                            'tin_id'        => 'Tin ID',
                                        ] as $field => $title)
                                        <div class="col-lg-6 mt-4">
                                            <label>{{ $title }}</label>
                                            <div class="card-box mt-2">
                                                <input type="file" name="{{ $field }}" class="form-control mt-1" style="font-size: 14px">
                                                <span>No file selected</span>
                                            </div>
                                            @if(!empty($governmentIds->$field))
                                                <small class="mt-1 d-block">
                                                    Current: <a href="{{ asset('storage/' . $governmentIds->$field) }}" target="_blank">View file</a>
                                                </small>
                                            @endif
                                        </div>
                                        @endforeach
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