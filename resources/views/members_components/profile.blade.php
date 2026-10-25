@php
    $profileSeminarCompletion = \App\Models\SeminarCompletions_tbl::where('user_id', $user->id)->first();
    $profileSeminarsComplete = $profileSeminarCompletion
        && $profileSeminarCompletion->pmes_completed
        && $profileSeminarCompletion->fundamentals_completed
        && $profileSeminarCompletion->finance_completed;

    $profileIsStaff = !in_array(strtolower($user->role ?? ''), ['member', 'pending', 'inactive']);
    $profileMemberId = ($profileIsStaff ? 'ADM-' : 'MEM-') . str_pad($user->id, 4, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile</title>

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/profile.css?=4">
    <link rel="stylesheet" href="css_folder/loading.css?=12">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style>
        .modal-body::-webkit-scrollbar {
            display: none;
        }

        .modal-body {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.footer")
            @include("components.navbar2")
            <div class="main-parent">
                <div class="main-header">
                    <div class="profile-side">
                        <h3>My Profile</h3>
                        <p>Your Personal, membership and account information</p>
                    </div>
                    <!-- <div>
                        <button>
                            <i class="fa fa-pencil"></i>
                            Edit Profile
                        </button>
                    </div> -->
                    <div>
                        <button type="button" class="soa-btn" data-bs-toggle="modal" data-bs-target="#downloadSoaModal">
                            <i class="fa fa-download"></i>
                            <span class="soa-btn-long">Download SOA</span>
                            <span class="soa-btn-short">SOA</span>
                        </button>
                    </div>
                </div>

                <div class="main-personal-card">
                    <div class="personal-header">
                        <div class="header-parent">
                            <div class="header-icon">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="header-text">
                                <h5>KPMPCATS</h5>
                                <p>Cooperative Membership</p>
                            </div>
                        </div>
                        <div class="active">
                            <span>Active Member</span>
                        </div>
                    </div>

                    <div class="personal-body">
                        <div class="personal-sub-body">
                            {{-- Avatar + quick "change photo" button. Opens the same
                            Edit Personal Information modal, which now includes a
                            Profile Photo upload field. --}}
                            <div class="body-icon-wrap">
                                <div class="body-icon" style="overflow:hidden; padding:0;">
                                    @if($otherinfo && $otherinfo->profile_picture)
                                        <img src="{{ url('proof/' . $otherinfo->profile_picture) }}" alt="Profile Photo"
                                            style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                    @else
                                        {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}
                                    @endif
                                </div>
                                <button type="button" class="avatar-edit-btn" data-bs-toggle="modal"
                                    data-bs-target="#editPersonalInfoModal" title="Change profile photo">
                                    <i class="fa fa-camera"></i>
                                </button>
                            </div>
                            <div class="parent-box">
                                <div class="body-text">
                                    <h2>{{ $user->first_name }}
                                        {{ $user->middle_name ? $user->middle_name . ' ' : '' }}{{ $user->last_name }}
                                    </h2>
                                    <p>{{ $user->role }} · {{ $otherinfo->present_address ?? '-' }}</p>
                                </div>
                                <div class="since-parent">
                                    <div class="member member-no">
                                        <span>Member No</span>
                                        <strong>{{ $profileMemberId }}</strong>
                                    </div>
                                    <div class="member member-since">
                                        <span>Member Since</span>
                                        <strong>{{ $memberSince }}</strong>
                                    </div>
                                    <!-- <div class="member member-id">
                                        <span>Tax ID</span>
                                        <strong>••• •• 7742</strong>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="perforation"></div>
                </div>

                @if($profileSeminarsComplete)

                    <div class="parent-information">
                        <div class="personal-sub-information">
                            <div class="personal-information-1">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <h4>Personal Information</h4>
                                    </div>
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editPersonalInfoModal">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="personal-information-body">
                                    <div class="information">
                                        <span>Full name</span>
                                        <strong>{{ $user->first_name }}
                                            {{ $user->middle_name ? $user->middle_name . ' ' : '' }}{{ $user->last_name }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Date of Birth</span>
                                        <strong>{{ $otherinfo->date_of_birth ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Sex</span>
                                        <strong>{{ $otherinfo->sex ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Civil Status</span>
                                        <strong>{{ $otherinfo->civil_status ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Mobile Number</span>
                                        <strong>{{ $otherinfo->contact_no ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Email</span>
                                        <strong>{{ $email }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Present Address</span>
                                        <strong>{{ $otherinfo->present_address ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Permanent Address</span>
                                        <strong>{{ $otherinfo->permanent_address ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Weight</span>
                                        <strong>{{ $otherinfo->weight ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Height</span>
                                        <strong>{{ $otherinfo->height ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Blood Type</span>
                                        <strong>{{ $otherinfo->blood_type ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Citizenship</span>
                                        <strong>{{ $otherinfo->citizenship ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Place of Birth</span>
                                        <strong>{{ $otherinfo->place_of_birth ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Skills / Expertise</span>
                                        <strong>{{ $otherinfo->skills ?? '-' }}</strong>
                                    </div>
                                </div>
                                <!-- <div class="more-button">
                                                                                                                                                                        <button>
                                                                                                                                                                            View More
                                                                                                                                                                            <i class="fa fa-arrow-right"></i>
                                                                                                                                                                        </button>
                                                                                                                                                                    </div> -->
                            </div>

                            <div class="personal-information-1">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon">
                                            <i class="fa fa-briefcase"></i>
                                        </div>
                                        <h4>Employment & Membership</h4>
                                    </div>
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editEmploymentModal">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="personal-information-body">
                                    <div class="information">
                                        <span>Role</span>
                                        <strong>{{ $user->role ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Monthly Income</span>
                                        <strong>{{ $savedMonthlyIncome ? '₱' . number_format($savedMonthlyIncome, 2) : '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Member Category</span>
                                        <strong>{{ $otherinfo->membership_category ?? '-' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Status</span>
                                        <strong>{{ $otherinfo->membership_status ?? '-' }}</strong>
                                    </div>

                                    {{-- <div class="information">
                                        <span>Employer</span>
                                        <strong>{{ $otherinfo->employer ?? 'N/A' }}</strong>
                                    </div>

                                    <div class="information">
                                        <span>Standing</span>
                                        <strong>{{ $user->status ?? 'N/A' }}</strong>
                                    </div> --}}
                                </div>
                            </div>

                            <div class="personal-information-1">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon">
                                            <i class="fa fa-folder-open"></i>
                                        </div>
                                        <h4>Documents on File</h4>
                                    </div>
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editDocumentsModal">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="personal-information-body document-body">
                                    @php
                                        $docs = [
                                            'SSS ID' => $membergovernIds->sss_id ?? null,
                                            'Philhealth ID' => $membergovernIds->philhealth_id ?? null,
                                            'Pag Ibig ID' => $membergovernIds->pagibig_id ?? null,
                                            'Tin ID' => $membergovernIds->tin_id ?? null,
                                        ];
                                    @endphp
                                    @foreach($docs as $label => $path)
                                        <div class="doc-row">
                                            <i class="fa fa-file-lines doc-icon"></i>
                                            <div class="doc-name">{{ $label }}</div>
                                            <div class="doc-meta">
                                                {{ $path ? 'Uploaded' . ($membergovernIds->updated_at ? ' ' . $membergovernIds->updated_at->format('M d, Y') : '') : 'Not uploaded' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                        <div class="personal-sub-information">
                            {{-- Family Background — now reuses the same
                            personal-information-1 / personal-information-body
                            classes as "Personal Information" above, so the
                            label/value styling (grid, spacing, teal values)
                            matches instead of falling back to plain text. --}}
                            <div class="personal-information-1">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon"><i class="fa fa-people-roof"></i></div>
                                        <h4>Family Background</h4>
                                    </div>
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editFamilyModal">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="personal-information-body">
                                    <div class="information">
                                        <span>Spouse Name</span>
                                        <strong>{{ $family->spouse_name ?? '-' }}</strong>
                                    </div>
                                    <div class="information">
                                        <span>Spouse Date of Birth</span>
                                        <strong>{{ $family->spouse_date_birth ?? '-' }}</strong>
                                    </div>
                                    <div class="information">
                                        <span>Spouse Place of Birth</span>
                                        <strong>{{ $family->spouse_place_birth ?? '-' }}</strong>
                                    </div>
                                    <div class="information">
                                        <span>Number of Sons</span>
                                        <strong>{{ $family->number_son ?? '0' }}</strong>
                                    </div>
                                    <div class="information">
                                        <span>Number of Daughters</span>
                                        <strong>{{ $family->number_daughter ?? '0' }}</strong>
                                    </div>
                                    <div class="information">
                                        <span>Other Dependents</span>
                                        <strong>{{ $family->other_spec ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Vehicle Information — same class swap as above, and
                            reuses "document-body" (already used by "Documents on
                            File") instead of the undefined "document-body-2". --}}
                            <div class="personal-information-1">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon"><i class="fa fa-car"></i></div>
                                        <h4>Vehicle Information</h4>
                                    </div>
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editVehicleModal">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                                <div class="personal-information-body document-body">
                                    @forelse($vehicles as $vehicle)
                                        <div class="doc-row">
                                            <i class="fa fa-car doc-icon"></i>
                                            <div class="doc-name">{{ $vehicle->vehicle_type }}</div>
                                            <div class="doc-meta">Plate No: {{ $vehicle->plate_no }}</div>
                                        </div>
                                    @empty
                                        <p style="color:#9ca3af; font-size:13.5px; margin:0;">No vehicles on file.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="personal-information-2">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon">
                                            <i class="fa fa-wallet"></i>
                                        </div>
                                        <h4>Account Balance</h4>
                                    </div>
                                </div>
                                <div class="personal-information-body-2">
                                    <!-- <div class="personal-head">
                                                                                                                                                                            <p>Description</p>
                                                                                                                                                                            <p>Balance</p>
                                                                                                                                                                        </div> -->
                                    <div class="personal-parent">
                                        <div class="item item-share">
                                            <div class="stat-icon">
                                                <i class="fa fa-layer-group"></i>
                                            </div>
                                            <div class="fw-bold item-value item-value-share">
                                                <span>Share Capital Account</span>
                                                <strong>₱{{ number_format($shareCapitalBalance, 2) }}</strong>
                                            </div>
                                        </div>

                                        <div class="item item-savings">
                                            <div class="stat-icon">
                                                <i class="fa fa-piggy-bank"></i>
                                            </div>
                                            <div class="fw-bold item-value item-category-savings">
                                                <span>Savings Account</span>
                                                <strong>₱{{ number_format($savingsBalance, 2) }}</strong>
                                            </div>
                                        </div>

                                        <div class="item item-loan">
                                            <div class="stat-icon">
                                                <i class="fa fa-hand-holding-dollar"></i>
                                            </div>
                                            <div class="fw-bold item-value item-category-loan">
                                                <span>Loan Balance</span>
                                                <strong>₱{{ number_format($loanBalance, 2) }}</strong>
                                            </div>
                                        </div>

                                        <div class="item item-net">
                                            <div class="fw-bold item-value item-category-net">
                                                <span>Overall</span>
                                            </div>
                                            <div class="stat-delta">₱{{ number_format($overallBalance, 2) }}</div>
                                        </div>
                                    </div>
                                    <!-- <div class="personal-footer">
                                                                                                                                                                            <div class="item item-net"> 
                                                                                                                                                                                <div class="fw-bold item-category-net">
                                                                                                                                                                                    <span>Net Standing</span>

                                                                                                                                                                                    <strong>₱51,930.00</strong>
                                                                                                                                                                                </div>
                                                                                                                                                                                <div class="fw-bold item-value-net">₱51,930.00</div>
                                                                                                                                                                            </div>
                                                                                                                                                                        </div> -->
                                </div>
                            </div>

                            <div class="personal-information-2">
                                <div class="personal-information-header">
                                    <div class="header-text">
                                        <div class="header-icon">
                                            <i class="fa fa-chart-simple"></i>
                                        </div>
                                        <h4>Loan Repayment Progress</h4>
                                    </div>
                                </div>
                                <div class="personal-information-body-2">

                                    <div class="parent-sub-progress">
                                        @foreach($loansByType as $type => $data)
                                            <a href="{{ route('LoanApplication') }}"
                                                style="text-decoration:none; color:inherit; display:block;">
                                                <div class="progress-repay progress-personal" style="cursor:pointer;">
                                                    <div class="progress-header">
                                                        <strong>{{ $type }}</strong>
                                                        <span>₱{{ number_format($data['balance'], 2) }}</span>
                                                    </div>
                                                    <div class="progress-body">
                                                        <div class="parent-progress">
                                                            <div class="progress" style="width: {{ $data['progress'] }}%;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>

                                </div>
                            </div>


                        </div>

                        <div class="personal-information-1">
                            <div class="personal-information-header">
                                <div class="header-text">
                                    <div class="header-icon"><i class="fa fa-signature"></i></div>
                                    <h4>Signature on File</h4>
                                </div>
                            </div>
                            <div class="personal-information-body"
                                style="display:block; padding:20px 24px; display: flex; justify-content: center; align-items: center;">
                                @if($otherinfo && $otherinfo->signature)
                                    <img src="{{ $otherinfo->signature }}" alt="Signature"
                                        style="max-width:100%; max-height:120px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                @else
                                    <p style="color:#9ca3af; font-size:13.5px; margin:0;">No signature on file.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($profileSeminarsComplete)
                    @php
                        $resignation = \App\Models\ResignationRequest_tbl::where('user_id', $user->id)->latest()->first();
                        $resStatus = $resignation->status ?? null;
                        $userStatus = strtolower((string) ($user->status ?? 'active'));

                        // Block new requests while one is pending, or after an approved resignation
                        // (until the member is reactivated and status becomes 'active' again)
                        $resPending = $resStatus === 'pending';
                        $resApproved = $resStatus === 'approved' && $userStatus !== 'active';
                        $resBlocked = $resPending || $resApproved;
                        $resRejected = $resStatus === 'rejected' && !$resBlocked;

                        $submittedAt = $resignation?->created_at?->format('M d, Y');
                        $releaseDate = $resignation?->release_date ? \Carbon\Carbon::parse($resignation->release_date)->format('M d, Y') : null;
                    @endphp

                    @if($resPending)
                        {{-- STATE 1: Request submitted, waiting for admin review --}}
                        <div class="main-personal-card-resign"
                            style="margin-top: 1.5rem; border: 1px solid #fde68a; background: #fffbeb; padding: 20px; border-radius: 12px;">
                            <div style="" class="resign-body">
                                <div class="resign-sub-body">
                                    <div
                                        style="width:44px; height:44px; border-radius:50%; background:#fef3c7; display:flex; align-items:center; justify-content:center;">
                                        <i class="fa fa-hourglass-half" style="color:#d97706; font-size:18px;"></i>
                                    </div>
                                    <div>
                                        <h4 style="color:#b45309; margin:0 0 4px; font-size:19px; font-weight:600;">Resignation
                                            Request In Process</h4>
                                        <p style="color:#1e293b; margin:0; font-size:13.5px; max-width:520px;">
                                            Your request{{ $submittedAt ? ' submitted on ' . $submittedAt : '' }} is being
                                            reviewed by the admin.
                                            You will be notified once a decision is made.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" disabled
                                    style="background:#e5e7eb; color:#9ca3af; border:none; padding:10px 20px; border-radius:8px; cursor:not-allowed; font-weight:600; display:flex; align-items:center; gap:8px; white-space:nowrap; font-size:14.5px;">
                                    <i class="fa fa-clock"></i>
                                    <span>Request Pending</span>
                                </button>
                            </div>

                            <div
                                style="display:flex; gap:24px; flex-wrap:wrap; margin-top:16px; padding-top:14px; border-top:1px dashed #fcd34d; font-size:13px;">
                                <div>
                                    <span style="color:#6b7280; display:block;">Status</span>
                                    <strong style="color:#b45309;">Pending Review</strong>
                                </div>
                                <div>
                                    <span style="color:#6b7280; display:block;">Share Capital</span>
                                    <strong
                                        style="color:#111827;">{{ $resignation->withdraw_share_capital ? 'Withdraw (60-day holding)' : 'Leave with cooperative' }}</strong>
                                </div>
                            </div>
                        </div>

                    @elseif($resApproved)
                        {{-- STATE 2: Approved --}}
                        <div class="main-personal-card"
                            style="margin-top: 1.5rem; border: 1px solid #bfdbfe; background: #eff6ff; padding: 20px; border-radius: 12px;">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <div
                                        style="width:44px; height:44px; border-radius:50%; background:#dbeafe; display:flex; align-items:center; justify-content:center;">
                                        <i class="fa fa-circle-check" style="color:#2563eb; font-size:18px;"></i>
                                    </div>
                                    <div>
                                        <h4 style="color:#1d4ed8; margin:0 0 4px; font-size:19px; font-weight:600;">Resignation
                                            Approved</h4>
                                        <p style="color:#1e293b; margin:0; font-size:13.5px; max-width:520px;">
                                            @if($resignation->withdraw_share_capital && !$resignation->is_released)
                                                Your share capital will be released after the 60-day holding
                                                period{{ $releaseDate ? ' (on ' . $releaseDate . ')' : '' }}.
                                            @elseif($resignation->withdraw_share_capital)
                                                Your share capital has been released. Thank you for being part of the cooperative.
                                            @else
                                                Your resignation was approved. Your share capital remains with the cooperative.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <button type="button" disabled
                                    style="background:#e5e7eb; color:#9ca3af; border:none; padding:10px 20px; border-radius:8px; cursor:not-allowed; font-weight:600; display:flex; align-items:center; gap:8px; white-space:nowrap; font-size:14.5px;">
                                    <i class="fa fa-lock"></i>
                                    <span>Already Requested</span>
                                </button>
                            </div>
                        </div>

                    @else
                        {{-- STATE 3: No request (or previous one rejected) → normal form --}}
                        <div class="main-personal-card-resign" style="">
                            <div style="width: 100%;">
                                <h4 style="color: #dc2626; margin: 0 0 5px; font-size: 19px; font-weight: 600;">Leave the
                                    Cooperative?</h4>
                                <p style="color: #1e293b; margin: 0; font-size: 13.5px; width: 100%; max-width: 500px;">
                                    If you wish to resign, submit a resignation request. A 60-day holding period applies for
                                    share capital withdrawal.
                                </p>
                                @if($resRejected)
                                    <p style="color:#dc2626; margin:8px 0 0; font-size:13px;">
                                        <i class="fa fa-circle-info"></i>
                                        Your previous request was not
                                        approved{{ $resignation->rejection_reason ? ': ' . $resignation->rejection_reason : '.' }}
                                    </p>
                                @endif
                            </div>
                            <div>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#profileResignModal"
                                    style="background: #dc2626; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; white-space: nowrap; font-size: 14.5px;">
                                    <i class="fa fa-sign-out-alt"></i>
                                    <span>Request Resignation</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Personal Information Modal --}}
    <div class="modal fade" id="editPersonalInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-user"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Edit Personal Information</h5>
                            <p class="sm-modal-subtitle">Update your personal and contact details</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body" style="max-height: 68vh; overflow-y: auto;">
                    <form action="{{ route('UpdateProfileMember') }}" method="POST" enctype="multipart/form-data"
                        id="personalInfoForm">
                        @csrf
                        <input type="hidden" name="_form" value="personal">
                        @if ($errors->any() && old('_form') === 'personal')
                            <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{-- Profile Photo upload --}}
                        <div class="sm-field">
                            <label class="sm-label">Profile Photo</label>
                            <div style="display:flex; align-items:center; gap:16px;">
                                <div id="avatarPreviewWrap"
                                    style="width:60px; height:60px; border-radius:50%; overflow:hidden; background:#EDF0F5; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <img id="avatarPreviewImg"
                                        src="{{ ($otherinfo && $otherinfo->profile_picture) ? url('proof/' . $otherinfo->profile_picture) : '' }}"
                                        style="width:100%; height:100%; object-fit:cover; {{ ($otherinfo && $otherinfo->profile_picture) ? '' : 'display:none;' }}">
                                    <span id="avatarPreviewInitial"
                                        style="font-weight:600; color:#1E2A4A; font-size:20px; {{ ($otherinfo && $otherinfo->profile_picture) ? 'display:none;' : '' }}">
                                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                    </span>
                                </div>
                                <label for="upload_profile_picture" class="doc-upload-box"
                                    id="uploadBox_profile_picture" style="flex:1; min-width:0;">
                                    <div class="doc-upload-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                    <div class="doc-upload-text">
                                        <span class="doc-upload-title" id="uploadTitle_profile_picture">
                                            {{ ($otherinfo && $otherinfo->profile_picture) ? 'Replace photo' : 'Click to upload' }}
                                        </span>
                                        <span class="doc-upload-sub" id="uploadSub_profile_picture">JPG or PNG, max
                                            2MB</span>
                                    </div>
                                </label>
                                <input type="file" id="upload_profile_picture" name="profile_picture"
                                    class="doc-upload-input" accept="image/*">
                            </div>
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">First Name</label>
                                <input type="text" name="first_name" class="sm-input"
                                    value="{{ $user->first_name ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Middle Name</label>
                                <input type="text" name="middle_name" class="sm-input"
                                    value="{{ $user->middle_name ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Last Name</label>
                                <input type="text" name="last_name" class="sm-input"
                                    value="{{ $user->last_name ?? '' }}">
                            </div>
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">Contact Number</label>
                                <input type="text" name="contact_no" class="sm-input"
                                    value="{{ $otherinfo->contact_no ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="sm-input"
                                    value="{{ $otherinfo->date_of_birth ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Sex</label>
                                <select name="sex" class="sm-input">
                                    <option value="">Select</option>
                                    <option value="Male" {{ ($otherinfo->sex ?? '') == 'Male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="Female" {{ ($otherinfo->sex ?? '') == 'Female' ? 'selected' : '' }}>
                                        Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">Civil Status</label>
                                <select name="civil_status" class="sm-input">
                                    <option value="">Select</option>
                                    <option value="Single" {{ ($otherinfo->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ ($otherinfo->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Widowed" {{ ($otherinfo->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                    <option value="Divorced" {{ ($otherinfo->civil_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                </select>
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Citizenship</label>
                                <input type="text" name="citizenship" class="sm-input"
                                    value="{{ $otherinfo->citizenship ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Blood Type</label>
                                <input type="text" name="blood_type" class="sm-input"
                                    value="{{ $otherinfo->blood_type ?? '' }}">
                            </div>
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">Height</label>
                                <input type="text" name="height" class="sm-input"
                                    value="{{ $otherinfo->height ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Weight</label>
                                <input type="text" name="weight" class="sm-input"
                                    value="{{ $otherinfo->weight ?? '' }}">
                            </div>
                        </div>

                        <div class="sm-field">
                            <label class="sm-label">Present Address</label>
                            <textarea name="present_address" class="sm-input"
                                rows="2">{{ $otherinfo->present_address ?? '' }}</textarea>
                        </div>
                        <div class="sm-field">
                            <label class="sm-label">Permanent Address</label>
                            <textarea name="permanent_address" class="sm-input"
                                rows="2">{{ $otherinfo->permanent_address ?? '' }}</textarea>
                        </div>

                        <button type="submit" class="sm-btn-confirm"><i class="fa fa-check"></i> Confirm
                            Changes</button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Employment & Membership Modal --}}
    <div class="modal fade" id="editEmploymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-briefcase"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Edit Employment & Membership</h5>
                            <p class="sm-modal-subtitle">Update your work and income details</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body">
                    <form action="{{ route('UpdateProfileMember') }}" method="POST" id="employmentForm">
                        @csrf
                        <input type="hidden" name="_form" value="employment">

                        <div class="sm-summary-strip">
                            <span class="sm-summary-label">Current Role</span>
                            <span class="sm-summary-value">{{ $user->role ?? 'Member' }}</span>
                        </div>

                        <div class="sm-field">
                            <label class="sm-label">Monthly Income</label>
                            <div style="position:relative;">
                                <span class="sm-input-prefix">₱</span>
                                <input type="number" step="0.01" name="monthly_income"
                                    class="sm-input sm-input-prefixed" value="{{ $savedMonthlyIncome ?? '' }}" {{ $savedMonthlyIncome ? '' : 'disabled' }}>
                            </div>
                            @if(!$savedMonthlyIncome)
                                <small class="sm-hint-text">
                                    <i class="fa fa-lock"></i>
                                    This is filled in automatically once you submit a Loan Application with your
                                    monthly income. You'll be able to edit it here afterward.
                                </small>
                            @endif
                        </div>

                        <button type="submit" class="sm-btn-confirm" {{ $savedMonthlyIncome ? '' : 'disabled' }}>
                            <i class="fa fa-check"></i> Confirm Changes
                        </button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Documents on File Modal --}}
    <div class="modal fade" id="editDocumentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-folder-open"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Edit Documents on File</h5>
                            <p class="sm-modal-subtitle">Upload or replace your government IDs</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body">
                    <form action="{{ route('UpdateProfileMember') }}" method="POST" enctype="multipart/form-data"
                        id="documentsForm">
                        @csrf
                        <input type="hidden" name="_form" value="documents">
                        @if ($errors->any() && old('_form') === 'documents')
                            <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        @if ($errors->any())
                            @php
                                $reopen = [
                                    'personal' => 'editPersonalInfoModal',
                                    'documents' => 'editDocumentsModal',
                                    'family' => 'editFamilyModal',
                                    'employment' => 'editEmploymentModal',
                                    'vehicle' => 'editVehicleModal',
                                ][old('_form')] ?? null;
                            @endphp
                            @if($reopen)
                                <script nonce="{{ csp_nonce() }}">
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const b = document.createElement('button');
                                        b.type = 'button';
                                        b.style.display = 'none';
                                        b.dataset.bsToggle = 'modal';
                                        b.dataset.bsTarget = '#{{ $reopen }}';
                                        document.body.appendChild(b);
                                        b.click();
                                    });
                                </script>
                            @endif
                        @endif

                        @php
                            $docFields = [
                                'sss_id' => ['label' => 'SSS ID', 'value' => $membergovernIds->sss_id ?? null],
                                'philhealth_id' => ['label' => 'PhilHealth ID', 'value' => $membergovernIds->philhealth_id ?? null],
                                'pagibig_id' => ['label' => 'Pag-IBIG ID', 'value' => $membergovernIds->pagibig_id ?? null],
                                'tin_id' => ['label' => 'TIN ID', 'value' => $membergovernIds->tin_id ?? null],
                            ];
                        @endphp

                        @foreach($docFields as $fieldName => $doc)
                            <div class="sm-field">
                                <div
                                    style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                                    <label class="sm-label" style="margin-bottom:0;">{{ $doc['label'] }}</label>
                                    @if($doc['value'])
                                        <a href="#" class="doc-view-link" data-doc-src="{{ url('proof/' . $doc['value']) }}"
                                            data-doc-label="{{ $doc['label'] }}"
                                            style="font-size:12px; font-weight:600; color:#1E2A4A; text-decoration:none; display:flex; align-items:center; gap:4px;">
                                            <i class="fa fa-eye"></i> View current file
                                        </a>
                                    @endif
                                </div>
                                <label for="upload_{{ $fieldName }}" class="doc-upload-box" id="uploadBox_{{ $fieldName }}">
                                    <div class="doc-upload-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                    <div class="doc-upload-text">
                                        <span class="doc-upload-title" id="uploadTitle_{{ $fieldName }}">
                                            {{ $doc['value'] ? 'Replace file' : 'Click to upload' }}
                                        </span>
                                        <span class="doc-upload-sub" id="uploadSub_{{ $fieldName }}">
                                            {{ $doc['value'] ? 'A file is already on record' : 'JPG or PNG, max 2MB' }}
                                        </span>
                                    </div>
                                </label>
                                <input type="file" id="upload_{{ $fieldName }}" name="{{ $fieldName }}"
                                    class="doc-upload-input doc-file-input" accept="image/*" data-field="{{ $fieldName }}">
                            </div>
                        @endforeach

                        <button type="submit" class="sm-btn-confirm"><i class="fa fa-check"></i> Confirm
                            Changes</button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Family Background Modal --}}
    <div class="modal fade" id="editFamilyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-people-roof"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Edit Family Background</h5>
                            <p class="sm-modal-subtitle">Update your spouse and dependents information</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body">
                    <form action="{{ route('UpdateProfileMember') }}" method="POST" id="familyForm">
                        @csrf
                        <input type="hidden" name="_form" value="family">

                        <div class="sm-field">
                            <label class="sm-label">Spouse Name</label>
                            <input type="text" name="spouse_name" class="sm-input"
                                value="{{ $family->spouse_name ?? '' }}">
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">Spouse Date of Birth</label>
                                <input type="date" name="spouse_date_birth" class="sm-input"
                                    value="{{ $family->spouse_date_birth ?? '' }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Spouse Place of Birth</label>
                                <input type="text" name="spouse_place_birth" class="sm-input"
                                    value="{{ $family->spouse_place_birth ?? '' }}">
                            </div>
                        </div>

                        <div class="sm-field-row">
                            <div class="sm-field">
                                <label class="sm-label">Number of Sons</label>
                                <input type="number" min="0" name="number_son" class="sm-input"
                                    value="{{ $family->number_son ?? 0 }}">
                            </div>
                            <div class="sm-field">
                                <label class="sm-label">Number of Daughters</label>
                                <input type="number" min="0" name="number_daughter" class="sm-input"
                                    value="{{ $family->number_daughter ?? 0 }}">
                            </div>
                        </div>

                        <div class="sm-field">
                            <label class="sm-label">Other Dependents</label>
                            <input type="text" name="other_spec" class="sm-input"
                                value="{{ $family->other_spec ?? '' }}">
                        </div>

                        <button type="submit" class="sm-btn-confirm"><i class="fa fa-check"></i> Confirm
                            Changes</button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Vehicle Modal --}}
    <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-car"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Add Vehicle</h5>
                            <p class="sm-modal-subtitle">Add a vehicle to your file</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body">
                    <form action="{{ route('UpdateProfileMember') }}" method="POST" id="vehicleForm">
                        @csrf
                        <input type="hidden" name="_form" value="vehicle">

                        <div class="sm-field">
                            <label class="sm-label">Vehicle Type</label>
                            <select name="vehicle_type" class="sm-input" required>
                                <option value="">Select</option>
                                <option value="UV">UV</option>
                                <option value="TAXI">Taxi</option>
                                <option value="BUS">Bus</option>
                                <option value="MINI BUS">Mini Bus</option>
                                <option value="JEEP">Jeep</option>
                                <option value="MULTI-CAB">Multi-Cab</option>
                                <option value="TRICYCLE">Tricycle</option>
                            </select>
                        </div>

                        <div class="sm-field">
                            <label class="sm-label">Plate Number</label>
                            <input type="text" name="plate_no" class="sm-input" required>
                        </div>

                        <button type="submit" class="sm-btn-confirm"><i class="fa fa-check"></i> Add
                            Vehicle</button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Download SOA Modal --}}
    <div class="modal fade" id="downloadSoaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sm-modal-content">
                <div class="modal-header sm-modal-header">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="sm-modal-icon"><i class="fa fa-file-pdf"></i></div>
                        <div>
                            <h5 class="sm-modal-title">Statement of Account</h5>
                            <p class="sm-modal-subtitle">Download your monthly or annual report (PDF)</p>
                        </div>
                    </div>
                    <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body sm-modal-body">
                    @if(session('soa_error'))
                        <div class="alert alert-danger" style="border-radius:10px; font-size:13px;">
                            {{ session('soa_error') }}
                        </div>
                    @endif

                    <form id="soaForm" action="{{ route('DownloadSoa') }}" method="GET">
                        <div class="soa-toggle">
                            <label>
                                <input type="radio" name="type" value="monthly" checked>
                                <span><i class="fa fa-calendar-day"></i> Monthly</span>
                            </label>
                            <label>
                                <input type="radio" name="type" value="annual">
                                <span><i class="fa fa-calendar"></i> Annual</span>
                            </label>
                        </div>

                        <div class="sm-field" id="soaMonthWrap">
                            <label class="sm-label">Select Month</label>
                            <input type="month" name="month" id="soaMonth" class="sm-input"
                                value="{{ now()->format('Y-m') }}" max="{{ now()->format('Y-m') }}">
                        </div>

                        <div class="sm-field" id="soaYearWrap" style="display:none;">
                            <label class="sm-label">Select Year</label>
                            <select name="year" id="soaYear" class="sm-input" disabled>
                                @for($y = now()->year; $y >= max(2000, optional($user->created_at)->year ?? now()->year); $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <p class="soa-hint">
                            <i class="fa fa-circle-info"></i>
                            Includes your opening and closing balances, share capital and savings
                            transactions, loan repayments, and active loans.
                        </p>

                        <button type="submit" class="sm-btn-confirm"><i class="fa fa-download"></i> Download
                            PDF</button>
                        <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Document Preview Lightbox --}}
    <div id="doc-lightbox-overlay"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:100000; align-items:center; justify-content:center; padding:1.5rem;">
        <button type="button" id="doc-lightbox-close"
            style="position:absolute; top:20px; right:24px; background:#fff; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; color:#333; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa fa-times"></i>
        </button>
        <div style="max-width:90%; max-height:90vh; text-align:center;">
            <p id="doc-lightbox-title" style="color:#fff; font-weight:600; margin-bottom:12px; font-size:15px;"></p>
            <img id="doc-lightbox-img" src="" alt="Document preview"
                style="max-width:100%; max-height:80vh; border-radius:10px; background:#fff;">
        </div>
    </div>

    <style>
        .doc-upload-input {
            display: none;
        }

        .doc-upload-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1.5px dashed #d1d5db;
            border-radius: 10px;
            cursor: pointer;
            background: #f9fafb;
            transition: border-color .15s, background .15s;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }

        .doc-upload-box:hover {
            border-color: #1E2A4A;
            background: #f3f4f6;
        }

        .doc-upload-box.has-file {
            border-style: solid;
            border-color: #16a34a;
            background: #f0fdf4;
        }

        .doc-upload-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 8px;
            background: #eef1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1E2A4A;
            font-size: 14px;
        }

        .doc-upload-box.has-file .doc-upload-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .doc-upload-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            flex: 1;
            overflow: hidden;
        }

        .doc-upload-title {
            font-size: 13.5px;
            font-weight: 600;
            color: #111827;
        }

        .doc-upload-sub {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Avatar edit button — small camera badge over the main profile photo */
        .body-icon-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-edit-btn {
            position: absolute;
            right: -2px;
            bottom: -2px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--teal, #1E2A4A);
            color: #fff;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            padding: 0;
        }

        .avatar-edit-btn:hover {
            filter: brightness(1.1);
        }

        /* Locked Monthly Income field, until a value exists from a loan
           application (or was set here previously). */
        .sm-input:disabled {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }
    </style>

    <script nonce="{{ csp_nonce() }}">
        function shortName(name, max = 28) {
            if (name.length <= max) return name;
            const dot = name.lastIndexOf('.');
            const ext = dot > -1 ? name.slice(dot) : '';
            return name.slice(0, max - ext.length - 3) + '...' + ext;
        }

        function handleDocUploadChange(fieldName, input) {
            const box = document.getElementById('uploadBox_' + fieldName);
            const title = document.getElementById('uploadTitle_' + fieldName);
            const sub = document.getElementById('uploadSub_' + fieldName);

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                box.classList.add('has-file');
                title.textContent = 'File selected';
                sub.textContent = shortName(file.name);
            } else {
                box.classList.remove('has-file');
                title.textContent = 'Click to upload';
                sub.textContent = 'JPG or PNG, max 2MB';
            }
        }

        document.getElementById('upload_profile_picture')?.addEventListener('change', function () {
            handleAvatarUploadChange(this);
        });

        document.querySelectorAll('.doc-file-input').forEach(function (input) {
            input.addEventListener('change', function () {
                handleDocUploadChange(this.dataset.field, this);
            });
        });

        // Same pattern as handleDocUploadChange, plus it swaps the small round
        // avatar preview inside the modal so the member can see the photo
        // they're about to save before confirming.
        function handleAvatarUploadChange(input) {
            const box = document.getElementById('uploadBox_profile_picture');
            const title = document.getElementById('uploadTitle_profile_picture');
            const sub = document.getElementById('uploadSub_profile_picture');
            const previewImg = document.getElementById('avatarPreviewImg');
            const previewInitial = document.getElementById('avatarPreviewInitial');

            if (input.files && input.files.length > 0) {
                const file = input.files[0];

                if (file.size > 2 * 1024 * 1024) {
                    alert('Photo is too large. Maximum size is 2MB.');
                    input.value = '';
                    return;
                }

                box.classList.add('has-file');
                title.textContent = 'Photo selected';
                sub.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    previewInitial.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                box.classList.remove('has-file');
                title.textContent = 'Click to upload';
                sub.textContent = 'JPG or PNG, max 2MB';
            }
        }

        document.addEventListener('click', function (e) {
            const link = e.target.closest('.doc-view-link');
            if (link) {
                e.preventDefault();
                document.getElementById('doc-lightbox-img').src = link.dataset.docSrc;
                document.getElementById('doc-lightbox-title').textContent = link.dataset.docLabel;
                document.getElementById('doc-lightbox-overlay').style.display = 'flex';
            }
        });

        document.getElementById('doc-lightbox-close')?.addEventListener('click', function () {
            document.getElementById('doc-lightbox-overlay').style.display = 'none';
        });

        document.getElementById('doc-lightbox-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) this.style.display = 'none';
        });
    </script>

    {{-- AOS animation link js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script nonce="{{ csp_nonce() }}">
        AOS.init();
    </script>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            const form = document.getElementById('soaForm');
            if (!form) return;

            const monthWrap = document.getElementById('soaMonthWrap');
            const yearWrap = document.getElementById('soaYearWrap');
            const monthInput = document.getElementById('soaMonth');
            const yearSelect = document.getElementById('soaYear');

            function sync() {
                const type = form.querySelector('input[name="type"]:checked').value;
                const monthly = type === 'monthly';
                monthWrap.style.display = monthly ? '' : 'none';
                yearWrap.style.display = monthly ? 'none' : '';
                monthInput.disabled = !monthly;   // disabled fields are not submitted
                yearSelect.disabled = monthly;
            }

            form.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', sync));
            sync();

            // The PDF downloads in place, so close the popup right after submitting
            form.addEventListener('submit', function () {
                setTimeout(() => {
                    document.querySelector('#downloadSoaModal [data-bs-dismiss="modal"]')?.click();
                }, 700);
            });
        })();
    </script>

    <script nonce="{{ csp_nonce() }}">
        const soaMember = {{ Js::from($soaData) }};

        document.getElementById('printSoaBtn')?.addEventListener('click', function () {
            const member = soaMember;

            const soaEsc = (value) => {
                const div = document.createElement('div');
                div.textContent = String(value ?? '');
                return div.innerHTML;
            };
            const fmt = (n) => '₱' + (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const fullName = soaEsc([member.first_name, member.last_name].filter(Boolean).join(' '));
            const memberId = 'MEM-' + String(member.id).padStart(4, '0');
            const asOf = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
            const memberSince = member.created_at
                ? new Date(member.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' })
                : '—';

            const loans = Array.isArray(member.active_loans) ? member.active_loans : [];
            const loanRows = loans.length > 0
                ? loans.map(l => `
                    <tr>
                        <td>${soaEsc(l.reference_no || '—')}</td>
                        <td>${soaEsc(l.lending_type || '—')}</td>
                        <td class="num">${fmt(l.lending_amount)}</td>
                        <td class="num">${fmt(l.monthly_payment)}</td>
                        <td class="num">${fmt(l.total_payment)}</td>
                    </tr>`).join('')
                : '<tr><td colspan="5">No active loans</td></tr>';

            const scAmount = Number(member.sc_total_amount) || 0;
            const savingsBalance = Number(member.savings_balance) || 0;

            const html = `<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Statement of Account - ${fullName}</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Courier New', Courier, monospace; font-size: 10px; color: #000; padding: 15px 20px; background: #fff; }
            .header { margin-bottom: 18px; text-align: center; }
            .header .coop-name { font-size: 14px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
            .header .title { font-size: 13px; font-weight: 700; margin-top: 4px; }
            .header .subtitle { font-size: 10px; color: #333; margin-top: 2px; }
            table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 16px; }
            thead th { background: #e0e0e0; border: 1px solid #000; padding: 5px 8px; font-weight: 700; text-align: center; text-transform: uppercase; font-size: 9px; }
            tbody td { border: 1px solid #000; padding: 4px 8px; }
            .num { text-align: right; font-family: 'Courier New', Courier, monospace; }
            .totals-row td { font-weight: 700; background: #e8e8e8; border-top: 2px solid #000; }
            .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; margin: 14px 0 4px; letter-spacing: 0.5px; }
            .toolbar { margin-bottom: 12px; }
            .toolbar button { padding: 6px 18px; font-size: 12px; cursor: pointer; background: #1E2A4A; color: #fff; border: none; border-radius: 4px; margin-right: 6px; }
            .toolbar .close { background: #6c757d; }
            .footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #000; display: flex; justify-content: space-between; font-size: 8px; color: #555; }
            @@media print {
                body { padding: 10px 15px; font-size: 9px; }
                thead th { font-size: 8px; padding: 4px 6px; }
                tbody td { padding: 3px 6px; }
                .toolbar { display: none !important; }
            }
            @@page { size: portrait; margin: 15mm; }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <button id="btn-print">Print / Save as PDF</button>
            <button id="btn-close" class="close">Close</button>
        </div>
    
        <div class="header">
            <div class="coop-name">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
            <div class="title">STATEMENT OF ACCOUNT</div>
            <div class="subtitle">${fullName} &middot; ${memberId} &middot; As of ${asOf}</div>
        </div>
    
        <table>
            <tbody>
                <tr><td><strong>MEMBER NAME</strong></td><td>${fullName}</td></tr>
                <tr><td><strong>MEMBERSHIP NO.</strong></td><td>${memberId}</td></tr>
                <tr><td><strong>MEMBERSHIP CATEGORY</strong></td><td>${soaEsc(member.membership_category || 'Investor Associate')}</td></tr>
                <tr><td><strong>EMAIL</strong></td><td>${soaEsc(member.email)}</td></tr>
                <tr><td><strong>CONTACT NO.</strong></td><td>${soaEsc(member.contact_no)}</td></tr>
                <tr><td><strong>PRESENT ADDRESS</strong></td><td>${soaEsc(member.present_address)}</td></tr>
                <tr><td><strong>MEMBER SINCE</strong></td><td>${memberSince}</td></tr>
                <tr><td><strong>STATEMENT DATE</strong></td><td>${asOf}</td></tr>
            </tbody>
        </table>
    
        <div class="section-label">Account Balances</div>
        <table>
            <thead>
                <tr><th>Account</th><th>Details</th><th>Status</th><th>Amount</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Share Capital</td>
                    <td>${Number(member.sc_total_shares) || 0} shares</td>
                    <td>${soaEsc(member.sc_status || 'No Account')}</td>
                    <td class="num">${fmt(scAmount)}</td>
                </tr>
                <tr>
                    <td>Regular Savings</td>
                    <td>Current balance</td>
                    <td>${soaEsc(member.savings_status || 'No Account')}</td>
                    <td class="num">${fmt(savingsBalance)}</td>
                </tr>
                <tr>
                    <td>Savings Interest Accrued</td>
                    <td>&mdash;</td>
                    <td>&mdash;</td>
                    <td class="num">${fmt(member.savings_interest)}</td>
                </tr>
                <tr class="totals-row">
                    <td colspan="3">TOTAL EQUITY (Share Capital + Savings)</td>
                    <td class="num">${fmt(scAmount + savingsBalance)}</td>
                </tr>
            </tbody>
        </table>
    
        <div class="section-label">Active Loans</div>
        <table>
            <thead>
                <tr><th>Reference</th><th>Type</th><th class="num">Loan Amount</th><th class="num">Monthly</th><th class="num">Total Payable</th></tr>
            </thead>
            <tbody>${loanRows}</tbody>
        </table>
    
        <div class="footer">
            <span>Generated on ${asOf}</span>
            <span>Kingsland Pala-Pala Multi-Purpose Cooperative</span>
        </div>
    </body>
    </html>`;

            const w = window.open('', '_blank', 'width=900,height=760');
            if (!w) {
                alert('Popup blocked. Please allow popups for this site.');
                return;
            }
            w.document.open();
            w.document.write(html);
            w.document.close();
            w.focus();

            // Buttons are wired from this page, so the popup needs no inline script
            w.document.getElementById('btn-print')?.addEventListener('click', () => w.print());
            w.document.getElementById('btn-close')?.addEventListener('click', () => w.close());
            setTimeout(() => w.print(), 400);
        });
    </script>
</body>

{{-- Reset Password Modal --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sm-modal-content">
            <div class="modal-header sm-modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="sm-modal-icon"><i class="fa fa-lock"></i></div>
                    <div>
                        <h5 class="sm-modal-title">Reset Password</h5>
                        <p class="sm-modal-subtitle">Enter your current password and choose a new one</p>
                    </div>
                </div>
                <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body sm-modal-body">
                <form id="resetPasswordForm">
                    @csrf
                    <div class="sm-field">
                        <label class="sm-label">Current Password</label>
                        <div class="password-input-wrap">
                            <input type="password" class="sm-input" id="current_password" name="current_password"
                                required>
                            <button type="button" class="password-toggle-btn" data-toggle-target="current_password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="sm-error-text" id="current_password_error"></small>
                    </div>
                    <div class="sm-field">
                        <label class="sm-label">New Password</label>
                        <div class="password-input-wrap">
                            <input type="password" class="sm-input" id="new_password" name="new_password" minlength="8"
                                required>
                            <button type="button" class="password-toggle-btn" data-toggle-target="new_password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="sm-hint-text">Minimum 8 characters</small>
                    </div>
                    <div class="sm-field">
                        <label class="sm-label">Confirm New Password</label>
                        <div class="password-input-wrap">
                            <input type="password" class="sm-input" id="new_password_confirmation"
                                name="new_password_confirmation" minlength="8" required>
                            <button type="button" class="password-toggle-btn"
                                data-toggle-target="new_password_confirmation">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <small class="sm-error-text" id="confirm_password_error"></small>
                    </div>

                    <button type="submit" class="sm-btn-confirm" id="resetPasswordSubmitBtn">
                        <i class="fa fa-check"></i> Update Password
                    </button>
                    <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .password-input-wrap {
        position: relative;
    }

    .password-input-wrap .sm-input {
        padding-right: 40px;
    }

    .password-toggle-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        font-size: 14px;
    }

    .password-toggle-btn:hover {
        color: #374151;
    }

    .sm-error-text {
        color: #dc2626;
        font-size: 12px;
        display: block;
        margin-top: 4px;
        min-height: 14px;
    }

    .sm-hint-text {
        color: #6b7280;
        font-size: 12px;
        display: block;
        margin-top: 4px;
    }
</style>

<script nonce="{{ csp_nonce() }}">
    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-toggle-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    document.getElementById('resetPasswordForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const currentPasswordError = document.getElementById('current_password_error');
        const confirmPasswordError = document.getElementById('confirm_password_error');
        currentPasswordError.textContent = '';
        confirmPasswordError.textContent = '';

        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('new_password_confirmation').value;

        if (newPassword !== confirmPassword) {
            confirmPasswordError.textContent = 'Passwords do not match.';
            return;
        }

        const submitBtn = document.getElementById('resetPasswordSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';

        try {
            const response = await fetch('{{ route("ChangePassword") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    current_password: document.getElementById('current_password').value,
                    new_password: newPassword,
                    new_password_confirmation: confirmPassword,
                }),
            });

            const data = await response.json();

            if (data.success) {
                document.querySelector('#resetPasswordModal [data-bs-dismiss="modal"]')?.click();
                document.getElementById('resetPasswordForm').reset();
                showProfileToast(data.message || 'Password updated successfully.');
            } else {
                currentPasswordError.textContent = data.message || 'Something went wrong.';
            }
        } catch (err) {
            currentPasswordError.textContent = 'Something went wrong. Please try again.';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    function showProfileToast(message) {
        const existing = document.getElementById('profileToast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.id = 'profileToast';
        toast.innerHTML = `<i class="fa fa-check-circle"></i><div><p>${message}</p></div>`;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('hide');
            toast.addEventListener('animationend', () => toast.remove());
        }, 3000);
    }
</script>

{{-- Resignation Confirm Modal --}}
<div class="modal fade" id="resignConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-body" style="padding: 28px 24px 24px; text-align: center;">
                <div
                    style="width:56px; height:56px; border-radius:50%; background:#fef2f2; display:flex; align-items:center; justify-content:center; margin: 0 auto 16px;">
                    <i class="fa fa-triangle-exclamation" style="color:#dc2626; font-size:22px;"></i>
                </div>
                <h5 style="font-weight:700; color:#111827; margin-bottom:8px;">Submit Resignation Request?</h5>
                <p style="font-size:14px; color:#6b7280; margin-bottom:0;">
                    This action will be reviewed by admin. Are you sure you want to proceed?
                </p>
            </div>
            <div style="padding: 0 24px 24px; display:flex; gap:12px;">
                <button type="button" data-bs-dismiss="modal"
                    style="flex:1; padding:12px; background:#f3f4f6; color:#374151; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size: 14.5px;">
                    Cancel
                </button>
                <button type="button" id="resignConfirmOkBtn"
                    style="flex:1; padding:12px; background:#dc2626; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size: 14.5px;">
                    Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden trigger: lets JS open the confirm modal via Bootstrap's own
data-API, without needing a global `bootstrap` object --}}
<button type="button" id="openResignConfirmTrigger" data-bs-toggle="modal" data-bs-target="#resignConfirmModal"
    style="display:none;"></button>

{{-- Resignation Modal --}}
<div class="modal fade" id="profileResignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" style="font-weight: 700; color: #111827;">Request Resignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resignationForm" style="padding: 24px;">
                @csrf
                <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Please select your preference for your
                    share capital:</p>
                <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:24px;">
                    <label class="resign-option"
                        style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                        <input type="radio" name="withdraw_share_capital" value="1" style="accent-color:#1E2A4A;"
                            required>
                        <div>
                            <strong style="display:block; color:#111827; font-size:15px;">Withdraw Share
                                Capital</strong>
                            <span style="font-size:13px; color:#6b7280;">I want my share capital paid out after 60
                                days</span>
                        </div>
                    </label>
                    <label class="resign-option"
                        style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer;">
                        <input type="radio" name="withdraw_share_capital" value="0" style="accent-color:#1E2A4A;"
                            required>
                        <div>
                            <strong style="display:block; color:#111827; font-size:15px;">Leave Share Capital</strong>
                            <span style="font-size:13px; color:#6b7280;">I leave my share capital with the
                                cooperative</span>
                        </div>
                    </label>
                </div>
                <small class="sm-error-text" id="resignation_error"></small>
                <div style="display:flex; gap:12px;">
                    <button type="button" data-bs-dismiss="modal"
                        style="flex:1; padding:12px; background:#f3f4f6; color:#374151; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size: 14.5px;">Cancel</button>
                    <button type="submit" id="resignationSubmitBtn"
                        style="flex:1; padding:12px; background:#dc2626; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size: 14.5px;">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    let pendingResignationForm = null;

    document.getElementById('resignationForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const errorEl = document.getElementById('resignation_error');
        errorEl.textContent = '';

        pendingResignationForm = e.target;

        // Open the confirm modal via Bootstrap's own data-API (click a real
        // trigger) instead of calling `bootstrap.Modal...` directly.
        document.getElementById('openResignConfirmTrigger').click();
    });

    document.getElementById('resignConfirmOkBtn')?.addEventListener('click', async function () {
        if (!pendingResignationForm) return;

        // Close the confirm modal by clicking its own dismiss button.
        document.querySelector('#resignConfirmModal [data-bs-dismiss="modal"]')?.click();

        const form = pendingResignationForm;
        const errorEl = document.getElementById('resignation_error');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('resignationSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';

        try {
            const response = await fetch('{{ route('resignation.request') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success !== false) {
                document.querySelector('#profileResignModal [data-bs-dismiss="modal"]')?.click();
                form.reset();
                showProfileToast(data.message || 'Resignation request submitted successfully.');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                errorEl.textContent = data.message || 'Something went wrong. Please try again.';
            }
        } catch (err) {
            errorEl.textContent = 'Something went wrong. Please try again.';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            pendingResignationForm = null;
        }
    });

    @if($profileSeminarsComplete && $resPending)
        // While the request is pending, re-check periodically. Once the admin
        // approves, the reload hits the middleware and lands on the inactive page.
        setInterval(() => window.location.reload(), 30000);
    @endif
</script>

@if (session('success'))
    <div class="toast-message" id="profileToast">
        <i class="fa fa-check-circle"></i>
        <div>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    <script nonce="{{ csp_nonce() }}">
        setTimeout(() => {
            const msg = document.getElementById('profileToast');
            if (msg) {
                msg.classList.add('hide');
                msg.addEventListener('animationend', () => msg.remove());
            }
        }, 3000);
    </script>
@endif

<style>
    .toast-message {
        position: fixed;
        right: 20px;
        top: 20px;
        padding: 1rem 1.5rem;
        color: #16a34a;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        border: 1px solid #E2E8E5;
        width: 280px;
        display: flex;
        align-items: center;
        border-radius: 10px;
        gap: 1rem;
        z-index: 99999;
        overflow: hidden;
        animation: toastSlideIn .4s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .toast-message::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        background-color: #16a34a;
        width: 5px;
    }

    .toast-message p {
        margin: 0;
        font-weight: 600;
        color: #111827;
        font-size: 13.5px;
    }

    .toast-message.hide {
        animation: toastFadeOut .4s ease-in forwards;
    }

    @keyframes toastSlideIn {
        from {
            opacity: 0;
            transform: translateX(60px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes toastFadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }

        to {
            opacity: 0;
            transform: translateX(60px);
        }
    }
</style>

</html>