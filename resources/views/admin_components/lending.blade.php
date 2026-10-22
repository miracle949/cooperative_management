@extends('layouts.admin')

@section('title', 'Member Assistance Processing - CoopAdmin')

@section('content')
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="text-sm text-gray-500">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600">
                        <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                    </a>
                </li>
                <li class="flex items-center">
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-400"></i>
                    <span class="text-gray-900 font-medium">Member Assistance Processing</span>
                </li>
            </ol>
        </nav>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Loans</h1>
            <p class="text-sm text-gray-500">Manage member loan applications and approvals</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            @php
                $loanTabs = [
                    'pending' => 'Pending',
                    'approved' => 'On-going',
                    'completed' => 'Completed',
                    'declined' => 'Declined',
                    'all' => 'All',
                ];
            @endphp
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg">
                @foreach($loanTabs as $tabValue => $tabLabel)
                <button data-action="filterByStatus" data-arg='["{{ $tabValue }}"]'
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all {{ ($statusFilter ?? 'pending') === $tabValue ? 'bg-primary-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-200' }}">
                    {{ $tabLabel }}
                </button>
                @endforeach
            </div>

        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
            <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
            <span class="text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Loan Applications Table -->
    <div class="card">
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Loan Applications</h3>
                <p class="text-sm text-gray-500">All member assistance requests and their status</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('lendings') }}" class="flex items-center gap-2">
                    @if(request('status') && request('status') !== 'all')
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by member, reference, or type..." class="input pl-9 pr-3 py-1.5 text-sm w-64" style="padding-left: 2.25rem;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-3 py-1.5 text-sm">Search</button>
                    @if($search)
                        <a href="{{ route('lendings', ['status' => request('status', 'all')]) }}" class="btn btn-outline btn-sm px-3 py-1.5 text-sm">Clear</a>
                    @endif
                </form>
                <span class="badge badge-primary whitespace-nowrap">{{ $loans->total() }} Total</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Loan Amount</th>
                        <th>Loan Type</th>
                        <th>Duration</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="loansTableBody">
                    @forelse($loans as $loan)
                        <tr class="cursor-pointer loan-row" data-status="{{ strtolower($loan->status) }}" data-action="openLoanModal" data-arg='[{{ $loop->index }}]'>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-600 font-semibold">
                                            {{ strtoupper(substr($loan->user->first_name ?? 'U', 0, 1) . substr($loan->user->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $loan->user->first_name ?? 'Unknown' }} {{ $loan->user->last_name ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500">MEM-{{ sprintf('%03d', $loan->user_id ?? 0) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm font-semibold text-gray-900">₱{{ number_format($loan->lending_amount, 0) }}</td>
                            <td class="text-sm text-gray-600">{{ $loan->lending_type }}</td>
                            <td class="text-sm text-gray-600">{{ $loan->lending_type_term }}</td>
                            <td class="text-sm text-gray-600">{{ $loan->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($loan->status === 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($loan->status === 'Approved')
                                    <span class="badge badge-success">On-going</span>
                                @elseif($loan->status === 'Completed')
                                    <span class="badge badge-primary">Completed</span>
                                @else
                                    <span class="badge badge-danger">Declined</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-50"></i>
                                    <p>No loan applications found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @php
            $paginator = $loans->appends([
                'status' => $statusFilter ?? 'pending',
                'search' => $search ?? '',
            ]);
            $currentPage = $loans->currentPage();
            $lastPage = $loans->lastPage();
        @endphp
        <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between rounded-b-xl bg-white">
            <p class="text-sm text-gray-500">
                Showing <span class="font-medium text-gray-700">{{ $loans->firstItem() ?? 0 }}</span>
                to <span class="font-medium text-gray-700">{{ $loans->lastItem() ?? 0 }}</span>
                of <span class="font-medium text-gray-700">{{ $loans->total() }}</span> members
            </p>
            <div class="flex items-center gap-1.5">
                @if($loans->onFirstPage())
                    <span class="w-9 h-9 flex items-center justify-center text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">&lt;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">&lt;</a>
                @endif

                @for($i = 1; $i <= $lastPage; $i++)
                    @if($i == $currentPage)
                        <span class="w-9 h-9 flex items-center justify-center text-sm font-bold text-white rounded-lg select-none" style="background-color: #1E2A4A;">{{ $i }}</span>
                    @else
                        <a href="{{ $paginator->url($i) }}"
                           class="w-9 h-9 flex items-center justify-center text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">{{ $i }}</a>
                    @endif
                @endfor

                @if($loans->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">&gt;</a>
                @else
                    <span class="w-9 h-9 flex items-center justify-center text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">&gt;</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
    </div>

    <!-- Overdue Penalties Table -->
    @if(count($penalizedLoans ?? []) > 0)
    <div class="card mt-6">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Overdue Penalties</h3>
                <p class="text-sm text-gray-500">Loans with applied late fees</p>
            </div>
            <span class="badge badge-danger">{{ count($penalizedLoans) }} Overdue</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Loan Amount</th>
                        <th>Due Date</th>
                        <th>Months Overdue</th>
                        <th>Late Fee</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($penalizedLoans as $penalty)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <span class="text-red-600 font-semibold">
                                            {{ strtoupper(substr(explode(' ', $penalty['member_name'])[0] ?? 'U', 0, 1) . substr(explode(' ', $penalty['member_name'])[1] ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $penalty['member_name'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm font-semibold text-gray-900">₱{{ number_format($penalty['lending_amount'], 0) }}</td>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($penalty['due_date'])->format('M d, Y') }}</td>
                            <td class="text-sm text-gray-600">{{ $penalty['months_overdue'] }}</td>
                            <td class="text-sm font-semibold text-red-600">₱{{ number_format($penalty['late_fee'], 2) }}</td>
                            <td>
                                <span class="badge badge-danger">Overdue</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="card mt-6">
        <div class="p-6 text-center">
            <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 text-green-500"></i>
            <p class="text-gray-500">No overdue penalties at this time</p>
        </div>
    </div>
    @endif

    <!-- Loan Detail Modal -->
    <div id="loanDetailModal" class="modal-overlay hidden">
        <div class="modal max-w-2xl">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold" style="color: #fff; margin: 0;">Loan Application Details</h2>
                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 13px;" id="modalReferenceNo">LN-XXXX</p>
                    </div>
                    <button data-action="closeModal" data-arg='["loanDetailModal"]' style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>

            <form id="loanActionForm" method="POST">
                @csrf
                <div class="p-6 space-y-6">
                    <!-- Member Info -->
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                        <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-primary-600 text-xl font-bold" id="modalInitials">--</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900" id="modalMemberName">Member Name</h3>
                            <p class="text-sm text-gray-500" id="modalMemberId">MEM-XXX</p>
                            <div class="flex gap-4 mt-2">
                                <span class="text-sm text-gray-600">
                                    <i data-lucide="phone" class="w-4 h-4 inline mr-1"></i>
                                    <span id="modalContact">N/A</span>
                                </span>
                                <span class="text-sm text-gray-600">
                                    <i data-lucide="mail" class="w-4 h-4 inline mr-1"></i>
                                    <span id="modalEmail">N/A</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Details -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 border border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Loan Amount</p>
                            <p class="text-2xl font-bold text-gray-900" id="modalLoanAmount">₱0</p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Interest Rate</p>
                            <p class="text-2xl font-bold text-gray-900" id="modalInterestRate">0%</p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Duration</p>
                            <p class="text-2xl font-bold text-gray-900" id="modalDuration">0 months</p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Monthly Payment</p>
                            <p class="text-2xl font-bold text-gray-900" id="modalMonthlyPayment">₱0</p>
                        </div>
                    </div>

                    <!-- Total Payables & Payment Progress -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-primary-50 border border-primary-100 rounded-xl">
                            <p class="text-sm text-primary-600 mb-1">Total Payables</p>
                            <p class="text-2xl font-bold text-gray-900" id="modalTotalPayables">₱0</p>
                            <p class="text-xs text-gray-500 mt-1" id="modalTotalPayablesBreakdown">Loan + Interest</p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Payment Progress</p>
                            <div class="flex items-center justify-between">
                                <p class="text-lg font-bold text-gray-900" id="modalPaymentProgress">0 of 0</p>
                                <span class="text-xs font-medium text-primary-600" id="modalPaymentProgressPct">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-primary-600 h-2 rounded-full transition-all" id="modalPaymentProgressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Proceeds -->
                    <div class="p-4 border border-gray-200 rounded-xl">
                        <p class="text-sm text-gray-500 mb-1">Net Proceeds</p>
                        <p class="text-xl font-bold text-gray-900" id="modalNetProceeds">₱0</p>
                        <p class="text-xs text-gray-500 mt-1" id="modalNetProceedsHint">Amount released to borrower</p>
                    </div>

                    <!-- Loan Charges Breakdown -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Loan Charges</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 border border-gray-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Processing Fee</p>
                                    <p class="text-xs text-gray-500">Processing & collection</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900" id="modalProcessingFee">₱0.00</p>
                            </div>
                            <div class="p-3 border border-gray-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Service &amp; Legal Fee</p>
                                    <p class="text-xs text-gray-500">One-time fee</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900" id="modalServiceFee">₱0.00</p>
                            </div>
                            <div class="p-3 border border-gray-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Loan Protection Plan</p>
                                    <p class="text-xs text-gray-500">Per month of term</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900" id="modalProtectionFee">₱0.00</p>
                            </div>
                            <div class="p-3 border border-gray-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Retention / CBU</p>
                                    <p class="text-xs text-gray-500">Held as capital build-up</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900" id="modalRetentionFee">₱0.00</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-dashed border-gray-200">
                            <span class="text-sm font-semibold text-gray-700">Total Charges</span>
                            <span class="text-base font-bold text-red-600" id="modalTotalCharges">₱0.00</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-2">Purpose</p>
                        <p class="text-gray-900" id="modalPurpose">Purpose description</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-2">Monthly Income</p>
                        <p class="text-gray-900" id="modalMonthlyIncome">₱0</p>
                    </div>

                    <!-- Documents -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Attached Documents</h4>
                        <div class="space-y-2" id="modalDocuments">
                            <!-- Documents will be populated by JS -->
                        </div>
                    </div>

                    <!-- Decline Reason (hidden by default) -->
                    <div id="declineReasonSection" class="hidden">
                        <h4 class="font-medium text-gray-900 mb-3">Decline Reason</h4>
                        <textarea name="decline_reason" id="declineReasonInput" class="form-control" rows="3" placeholder="Enter reason for declining this loan application..."></textarea>
                        @error('decline_reason')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Declined Reason Display (if already declined) -->
                    <div id="declinedReasonDisplay" class="hidden">
                        <h4 class="font-medium text-gray-900 mb-3">Decline Reason</h4>
                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-800" id="declinedReasonText"></p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100">
                    <div class="flex gap-3 justify-end" id="actionButtons">
                        <button type="button" id="declineBtn" data-action="showDeclineReason"
                            class="btn btn-danger flex-1">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Decline
                        </button>
                        <button type="submit" formaction="" id="approveBtn"
                            class="btn btn-success flex-1">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Approve
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="fixed inset-0 bg-black/80 z-[60] hidden flex items-center justify-center p-4" data-action="closePreview">
        <div class="relative max-w-4xl w-full" data-action="stop-propagation">
            <button data-action="closePreview" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
            <img id="previewImage" src="" class="w-full h-auto max-h-[85vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <!-- New Loan Modal -->
    <div id="newLoanModal" class="modal-overlay hidden">
        <div class="modal max-w-4xl" style="border-radius: 16px; max-height: 90vh;">
            <!-- modal-header: decorative navy bar (sticky) -->
            <div class="modal-header" style="position: sticky; top: 0; z-index: 100; background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="file-text" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">New Loan Application</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Create a new loan for a member</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["newLoanModal"]' style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>

            <!-- modal-body: scrollable form area -->
            <div class="modal-body" style="padding: 1.5rem;">
                <form action="{{ route('loan.create-admin') }}" method="POST" enctype="multipart/form-data" id="adminLoanForm">
                    @csrf

                    <!-- Member Selection — top of body, well-spaced from header -->
                    <div class="mb-6 mt-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Select Member <span class="text-red-500">*</span></label>
                        <select name="member_id" id="member-select-el" class="select w-full" required>
                            <option value="">Select a member...</option>
                            @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }} (MEM-{{ sprintf('%03d', $member->id) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Loan Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Loan Type <span class="text-red-500">*</span></label>
                            <select name="lending_type" class="input w-full" data-action="updateTermOptions" required>
                                <option value="">Select loan type</option>
                                @foreach(array_keys($loanSettings) as $lt)
                                    <option value="{{ $lt }}">{{ $lt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Loan Amount -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Loan Amount (₱) <span class="text-red-500">*</span></label>
                            <input type="number" name="lending_amount" placeholder="Enter amount (max ₱25,000)" class="input w-full" data-action="clamp-loan-amount" data-trigger="input" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Loan Term -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Loan Term <span class="text-red-500">*</span></label>
                            <!-- Non-Business Term (default) -->
                            <select id="termNonBusiness" class="input w-full" data-action="adminRecalculate">
                                <option value="">Select loan term</option>
                                <option value="6 months">6 months</option>
                            </select>
                            <!-- Business Term (hidden by default) -->
                            <select id="termBusiness" class="input w-full" style="display: none;" data-action="adminRecalculate">
                                <option value="">Select loan term</option>
                                <option value="6 months">6 months</option>
                                <option value="12 months">12 months</option>
                            </select>
                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="lending_type_term" id="hiddenTerm" value="">
                        </div>

                        <!-- Monthly Income -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Monthly Income (₱) <span class="text-red-500">*</span></label>
                            <input type="number" name="monthly_income" placeholder="Enter monthly income" class="input w-full" required>
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Purpose of Loan <span class="text-red-500">*</span></label>
                        <textarea name="purpose_loan" class="input w-full" rows="3" placeholder="Describe the purpose of your loan..." required></textarea>
                    </div>

                    <!-- Documents -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Valid ID</label>
                            <input type="file" name="valid_id" class="input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Proof of Income</label>
                            <input type="file" name="proof_of_income" class="input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>

                    <!-- Hidden calculated fields -->
                    <input type="hidden" name="monthly_payment" id="adminHiddenMonthly">
                    <input type="hidden" name="total_payment" id="adminHiddenTotal">
                    <input type="hidden" name="total_interest" id="adminHiddenInterest">

                    <!-- Loan Calculation -->
                    <div class="bg-gray-50 border-2 border-dashed border-primary-600/30 rounded-xl p-5 mb-4">
                        <h4 class="text-sm font-bold text-primary-600 mb-3 flex items-center gap-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            Loan Summary
                        </h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Loan Type:</span>
                                <span id="adminCalcType" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Interest Rate:</span>
                                <span id="adminCalcRate" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Loan Term:</span>
                                <span id="adminCalcTerm" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Interest:</span>
                                <span id="adminCalcInterest" class="font-semibold text-gray-900">-</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-dashed border-gray-200">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Loan Charges</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="flex justify-between py-1">
                                    <span class="text-gray-500">Processing Fee:</span>
                                    <span id="adminCalcProcessing" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-gray-500">Service &amp; Legal Fee:</span>
                                    <span id="adminCalcService" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-gray-500">Loan Protection:</span>
                                    <span id="adminCalcProtection" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-gray-500">Retention / CBU:</span>
                                    <span id="adminCalcRetention" class="font-semibold text-gray-900">-</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-gray-500">Net Proceeds:</span>
                                    <span id="adminCalcNet" class="font-semibold text-primary-600">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-dashed border-gray-200 flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700">Estimated Monthly Payment:</span>
                            <span id="adminCalcMonthly" class="text-lg font-bold text-primary-600">₱0.00</span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-400">Total Payment:</span>
                            <span id="adminCalcTotal" class="text-sm font-semibold text-gray-700">₱0.00</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" data-action="closeModal" data-arg='["newLoanModal"]' class="flex-1 px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Create & Approve Loan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Keep Tom Select dropdown below the sticky modal header */
        .ts-wrapper .ts-dropdown {
            z-index: 2 !important;
        }
        .modal > .modal-header {
            position: sticky;
            top: 0;
            z-index: 3;
        }
    </style>

    <!-- Hidden data for JavaScript -->
    <script type="application/json" id="loansData">
        @json($loans->toArray()['data'])
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script nonce="{{ csp_nonce() }}">
    (function () {
        var A = window.CSP_actions;
        if (!A) return;
        A.register('clamp-loan-amount', function (e, el) {
            el.value = Math.min(el.value, 25000);
            adminRecalculate();
        });
    })();
    (function() {
    let loansData = [];
    
    try {
        const loansDataEl = document.getElementById('loansData');
        if (loansDataEl) {
            loansData = JSON.parse(loansDataEl.textContent || '[]');
            console.log('Loans data loaded:', loansData.length, 'loans');
        }
    } catch (e) {
        console.error('Error parsing loans data:', e);
    }

    window.filterByStatus = function(status) {
        let url = '{{ route("lendings") }}?status=' + status;
        const search = new URLSearchParams(window.location.search).get('search');
        if (search) url += '&search=' + encodeURIComponent(search);
        window.location.href = url;
    };

    // Debug: make openLoanModal globally accessible
    window.debugLoans = function() {
        console.log('loansData:', loansData);
        console.log('Number of loans:', loansData.length);
        if (loansData.length > 0) {
            console.log('First loan:', loansData[0]);
        }
    };

    window.openLoanModal = function(index) {
        const loan = loansData[index];
        if (!loan) {
            console.error('Loan not found at index:', index);
            return;
        }

        // Populate modal fields
        document.getElementById('modalReferenceNo').textContent = loan.reference_no || 'N/A';
        document.getElementById('modalMemberName').textContent = (loan.user?.first_name || 'Unknown') + ' ' + (loan.user?.last_name || '');
        document.getElementById('modalMemberId').textContent = 'MEM-' + String(loan.user_id || 'XXX').padStart(3, '0');
        document.getElementById('modalInitials').textContent = ((loan.user?.first_name || 'U')[0] + (loan.user?.last_name || '')[0]).toUpperCase();
        document.getElementById('modalContact').textContent = loan.user?.contact_no || 'N/A';
        document.getElementById('modalEmail').textContent = loan.user?.email || 'N/A';
        document.getElementById('modalLoanAmount').textContent = '₱' + parseFloat(loan.lending_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        document.getElementById('modalDuration').textContent = loan.lending_type_term || 'N/A';
        document.getElementById('modalMonthlyPayment').textContent = '₱' + parseFloat(loan.monthly_payment || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('modalPurpose').textContent = loan.purpose_loan || 'N/A';
        document.getElementById('modalMonthlyIncome').textContent = '₱' + parseFloat(loan.monthly_income || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Interest rate display - use dynamic rates from database
        const interestRates = @json($loanSettings ?? []);
        const interestRateValue = interestRates[loan.lending_type];
        document.getElementById('modalInterestRate').textContent = (interestRateValue !== undefined && interestRateValue !== null) ? interestRateValue + '%' : 'N/A';

        // Total payables (loan amount + total interest)
        const loanAmount = parseFloat(loan.lending_amount || 0);
        const totalPayment = parseFloat(loan.total_payment);
        const totalInterest = parseFloat(loan.total_interest);
        document.getElementById('modalTotalPayables').textContent = '₱' + (isNaN(totalPayment) ? loanAmount : totalPayment).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('modalTotalPayablesBreakdown').textContent = isNaN(totalInterest)
            ? 'Loan + Interest'
            : 'Loan ₱' + loanAmount.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + ' + Interest ₱' + totalInterest.toLocaleString('en-PH', { minimumFractionDigits: 2 });

        // Net proceeds (actual amount released to borrower)
        const netProceeds = parseFloat(loan.net_proceeds ?? loan.lending_amount ?? 0) || 0;
        document.getElementById('modalNetProceeds').textContent = '₱' + netProceeds.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('modalNetProceedsHint').textContent =
            loan.net_proceeds_adjustment_type === 'add'
                ? 'Charges added back — full loan released'
                : 'Amount released to borrower';

        // Loan Charges breakdown — mirrors member LoanStatus page
        const processingFee = parseFloat(loan.processing_fee_rate || 0);
        const serviceFee = parseFloat(loan.service_fee_rate || 0);
        const protectionFee = parseFloat(loan.loan_protection_fee || 0);
        const retentionFee = parseFloat(loan.retention_paid_rate || 0) + parseFloat(loan.retention_unpaid_rate || 0);
        const feesOnly = processingFee + serviceFee + protectionFee + retentionFee;

        // Total Charges = Interest + Fees, matching the member LoanStatus hero stat
        const loanInterest = parseFloat(loan.total_interest || 0);
        const totalChargesWithInterest = feesOnly + loanInterest;

        const chargeMoney = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('modalProcessingFee').textContent = chargeMoney(processingFee);
        document.getElementById('modalServiceFee').textContent = chargeMoney(serviceFee);
        document.getElementById('modalProtectionFee').textContent = chargeMoney(protectionFee);
        document.getElementById('modalRetentionFee').textContent = chargeMoney(retentionFee);
        document.getElementById('modalTotalCharges').textContent = chargeMoney(totalChargesWithInterest);

        // Payment progress (payments made vs total term)
        const paymentsMade = Array.isArray(loan.repayments) ? loan.repayments.length : 0;
        const termMatch = String(loan.lending_type_term || '').match(/\d+/);
        const totalPaymentsCount = termMatch ? parseInt(termMatch[0], 10) : (paymentsMade > 0 ? paymentsMade : 1);
        const isCompleted = String(loan.status || '').toLowerCase() === 'completed';
        const displayed = isCompleted ? totalPaymentsCount : paymentsMade;
        const pct = totalPaymentsCount > 0 ? Math.min(100, Math.round((displayed / totalPaymentsCount) * 100)) : 0;
        document.getElementById('modalPaymentProgress').textContent = displayed + ' of ' + totalPaymentsCount;
        document.getElementById('modalPaymentProgressPct').textContent = pct + '%';
        document.getElementById('modalPaymentProgressBar').style.width = pct + '%';

        // Documents — every attachment column the member form can populate
        const docFields = [
            ['valid_id', 'Valid ID'],
            ['proof_of_income', 'Proof of Income'],
            ['proof_of_emergency', 'Proof of Emergency'],
            ['business_permit', 'Business Permit'],
            ['financial_statement', 'Financial Statement'],
            ['school_id', 'School ID'],
            ['cor', 'Certificate of Registration (COR)'],
            ['cog', 'Certificate of Good Standing (COG)'],
        ];

        const docsContainer = document.getElementById('modalDocuments');
        docsContainer.innerHTML = '';

        docFields.forEach(function (pair) {
            const field = pair[0];
            const label = pair[1];
            if (loan[field]) {
                docsContainer.innerHTML += window.createDocRow(label, loan[field]);
            }
        });

        if (docsContainer.innerHTML === '') {
            docsContainer.innerHTML = '<p class="text-gray-500 text-sm">No documents attached</p>';
        }

        // Handle status-based UI
        const declineBtn = document.getElementById('declineBtn');
        const approveBtn = document.getElementById('approveBtn');
        const declineReasonSection = document.getElementById('declineReasonSection');
        const declinedReasonDisplay = document.getElementById('declinedReasonDisplay');
        const declineReasonInput = document.getElementById('declineReasonInput');

        declineReasonSection.classList.add('hidden');
        declinedReasonDisplay.classList.add('hidden');

        if (loan.status === 'Pending') {
            window.currentLoanId = loan.id;
            declineBtn.classList.remove('hidden');
            approveBtn.classList.remove('hidden');
            document.getElementById('actionButtons').classList.remove('hidden');
            declineBtn.innerHTML = '<i data-lucide="x-circle" class="w-4 h-4"></i> Decline';
            declineReasonInput.value = '';
            declineReasonInput.removeAttribute('required');
            approveBtn.formAction = '/loan/approve/' + loan.id;
        } else {
            declineBtn.classList.add('hidden');
            approveBtn.classList.add('hidden');
            document.getElementById('actionButtons').classList.add('hidden');

            if (loan.status === 'Declined' && loan.decline_reason) {
                declinedReasonDisplay.classList.remove('hidden');
                document.getElementById('declinedReasonText').textContent = loan.decline_reason;
            }
        }

        // Show modal
        const modal = document.getElementById('loanDetailModal');
        modal.classList.remove('hidden');
        modal.style.display = '';
        document.body.style.overflow = 'hidden';
        
        // Re-init lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    window.createDocRow = function(name, path) {
        const extension = path.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
        const url = '/proof/' + encodeURI(String(path).replace(/^\/+/, ''));
    
        if (isImage) {
            return `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <i data-lucide="image" class="w-5 h-5 text-gray-500"></i>
                        <span class="text-sm text-gray-900">${name}</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-action="previewImage" data-arg='["${url}"]' class="btn btn-outline text-sm py-1">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            View
                        </button>
                        <a href="${url}" target="_blank" class="btn btn-outline text-sm py-1">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            `;
        }
    
        return `
            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-text" class="w-5 h-5 text-gray-500"></i>
                    <span class="text-sm text-gray-900">${name}</span>
                </div>
                <a href="${url}" target="_blank" class="btn btn-outline text-sm py-1">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Download
                </a>
            </div>
        `;
    };

    window.previewImage = function(src) {
        document.getElementById('previewImage').src = src;
        document.getElementById('imagePreviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    window.closePreview = function() {
        document.getElementById('imagePreviewModal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.showDeclineReason = function() {
        const section = document.getElementById('declineReasonSection');
        const input = document.getElementById('declineReasonInput');
        const declineBtn = document.getElementById('declineBtn');
        const approveBtn = document.getElementById('approveBtn');
    
        // Second click (reason box already open) = confirm and submit
        if (!section.classList.contains('hidden')) {
            window.submitDecline();
            return;
        }
    
        // First click = open the reason box
        section.classList.remove('hidden');
        input.setAttribute('required', 'required');
        declineBtn.innerHTML = '<i data-lucide="x-circle" class="w-4 h-4"></i> Confirm Decline';
        approveBtn.classList.add('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
        input.focus();
    };
    
    window.submitDecline = function() {
        const input = document.getElementById('declineReasonInput');
        if (!input.value.trim()) {
            alert('Please enter a reason for declining');
            input.focus();
            return;
        }
        if (!window.currentLoanId) return;
    
        const form = document.getElementById('loanActionForm');
        form.action = '/loan/decline/' + window.currentLoanId;
        form.submit();
    };

    // Close preview on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const previewModal = document.getElementById('imagePreviewModal');
            if (!previewModal.classList.contains('hidden')) {
                closePreview();
            }
        }
    });

    // Update term options based on loan type (show/hide approach)
    window.updateTermOptions = function() {
        console.log('updateTermOptions called');
        const type = document.querySelector('select[name="lending_type"]').value;
        const termNonBusiness = document.getElementById('termNonBusiness');
        const termBusiness = document.getElementById('termBusiness');
        const hiddenTerm = document.getElementById('hiddenTerm');
        
        if (type === 'Business Loan') {
            termNonBusiness.style.display = 'none';
            termBusiness.style.display = 'block';
            // Auto-select first valid term (6 months)
            if (termBusiness.options.length > 1) {
                termBusiness.selectedIndex = 1;
            }
            hiddenTerm.value = termBusiness.value;
        } else {
            termNonBusiness.style.display = 'block';
            termBusiness.style.display = 'none';
            // Auto-select first valid term (6 months)
            if (termNonBusiness.options.length > 1) {
                termNonBusiness.selectedIndex = 1;
            }
            hiddenTerm.value = termNonBusiness.value;
        }
        
        // Now run calculation since term is now selected
        adminRecalculate();
    };

    // Admin loan calculation
    @php
        $loanChargeData = $loanChargeSettings
            ? $loanChargeSettings->mapWithKeys(function ($s) {
                return [$s->loan_type => [
                    'rate' => (float) ($s->interest_rate ?? 0),
                    'processing' => (float) ($s->processing_fee_rate ?? 0),
                    'service' => (float) ($s->service_fee_rate ?? 0),
                    'protection' => (float) ($s->loan_protection_fee ?? 0),
                    'retention' => (float) ($s->retention_unpaid_rate ?? 0),
                ]];
            })->all()
            : [];
    @endphp
    window.adminRecalculate = function() {
        console.log('adminRecalculate called');
        const type = document.querySelector('select[name="lending_type"]').value;
        const amount = parseFloat(document.querySelector('input[name="lending_amount"]').value) || 0;
        
        // Get term from visible dropdown
        const termNonBusiness = document.getElementById('termNonBusiness');
        const termBusiness = document.getElementById('termBusiness');
        const hiddenTerm = document.getElementById('hiddenTerm');
        const term = termBusiness.style.display === 'block' ? termBusiness.value : termNonBusiness.value;
        // Sync hidden input only if term has value
        if (term) {
            hiddenTerm.value = term;
        }

        const LOAN_CHARGES = @json($loanChargeData ?? []);

        const adminMoney = n => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const termMonths = {
            '6 months': 6,
            '12 months': 12
        };

        const cfg = LOAN_CHARGES[type] || {};
        const rate = cfg.rate || 0;
        const months = termMonths[term] || 0;

        if (amount > 0 && months > 0) {
            const monthlyInterest = rate / 100;
            // Diminishing balance, equal principal — mirrors LoanCalculationService.
            // The final principal installment absorbs rounding so Σ principal === amount.
            const basePrincipal = Math.round((amount / months) * 100) / 100;
            const lastPrincipal = Math.round((amount - basePrincipal * (months - 1)) * 100) / 100;
            let balance = amount;
            let totalInterest = 0;
            let totalPayment = 0;
            for (let m = 1; m <= months; m++) {
                const principalDue = m === months ? lastPrincipal : basePrincipal;
                const interestDue = Math.round(balance * monthlyInterest * 100) / 100;
                totalInterest += interestDue;
                totalPayment += principalDue + interestDue;
                balance = Math.round((balance - principalDue) * 100) / 100;
            }
            totalInterest = Math.round(totalInterest * 100) / 100;
            totalPayment = Math.round(totalPayment * 100) / 100;
            const monthlyPayment = totalPayment / months;

            // Charges — mirrors lendingProgram server math (retention uses the unpaid rate).
            const processing = Math.round(amount * ((cfg.processing || 0) / 100) * 100) / 100;
            const service = Math.round(amount * ((cfg.service || 0) / 100) * 100) / 100;
            const protection = Math.round((cfg.protection || 0) * months * 100) / 100;
            const retention = Math.round(amount * ((cfg.retention || 0) / 100) * 100) / 100;
            const netProceeds = Math.round((amount - processing - service - protection - retention) * 100) / 100;

            document.getElementById('adminCalcType').textContent = type || '-';
            document.getElementById('adminCalcRate').textContent = rate + '% / mo';
            document.getElementById('adminCalcTerm').textContent = term || '-';
            document.getElementById('adminCalcInterest').textContent = adminMoney(totalInterest);
            document.getElementById('adminCalcProcessing').textContent = adminMoney(processing);
            document.getElementById('adminCalcService').textContent = adminMoney(service);
            document.getElementById('adminCalcProtection').textContent = adminMoney(protection);
            document.getElementById('adminCalcRetention').textContent = adminMoney(retention);
            document.getElementById('adminCalcNet').textContent = adminMoney(netProceeds);
            document.getElementById('adminCalcMonthly').textContent = adminMoney(monthlyPayment);
            document.getElementById('adminCalcTotal').textContent = adminMoney(totalPayment);

            // Set hidden fields (informational only — the server recomputes authoritatively)
            document.getElementById('adminHiddenMonthly').value = monthlyPayment.toFixed(2);
            document.getElementById('adminHiddenTotal').value = totalPayment.toFixed(2);
            document.getElementById('adminHiddenInterest').value = totalInterest.toFixed(2);
        } else {
            document.getElementById('adminCalcType').textContent = '-';
            document.getElementById('adminCalcRate').textContent = '-';
            document.getElementById('adminCalcTerm').textContent = '-';
            document.getElementById('adminCalcInterest').textContent = '-';
            document.getElementById('adminCalcProcessing').textContent = '-';
            document.getElementById('adminCalcService').textContent = '-';
            document.getElementById('adminCalcProtection').textContent = '-';
            document.getElementById('adminCalcRetention').textContent = '-';
            document.getElementById('adminCalcNet').textContent = '-';
            document.getElementById('adminCalcMonthly').textContent = '₱0.00';
            document.getElementById('adminCalcTotal').textContent = '₱0.00';
        }
    };

    // Initialize modal without full form reset - only reset term dropdowns
    let modalInitialized = false;
    function initModalOnOpen() {
        if (modalInitialized) return;
        
        const modal = document.getElementById('newLoanModal');
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const modal = mutation.target;
                    if (!modal.classList.contains('hidden')) {
                        // Modal opened - only reset term dropdowns, not entire form
                        console.log('Modal opened - initializing...');
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        
                        // Reset only the term dropdowns, NOT the entire form
                        document.getElementById('termNonBusiness').style.display = 'block';
                        document.getElementById('termNonBusiness').value = '';
                        document.getElementById('termBusiness').style.display = 'none';
                        document.getElementById('termBusiness').value = '';
                        document.getElementById('hiddenTerm').value = '';
                        
                        // Reset calculation display only
                        document.getElementById('adminCalcType').textContent = '-';
                        document.getElementById('adminCalcRate').textContent = '-';
                        document.getElementById('adminCalcTerm').textContent = '-';
                        document.getElementById('adminCalcInterest').textContent = '-';
                        document.getElementById('adminCalcProcessing').textContent = '-';
                        document.getElementById('adminCalcService').textContent = '-';
                        document.getElementById('adminCalcProtection').textContent = '-';
                        document.getElementById('adminCalcRetention').textContent = '-';
                        document.getElementById('adminCalcNet').textContent = '-';
                        document.getElementById('adminCalcMonthly').textContent = '₱0.00';
                        document.getElementById('adminCalcTotal').textContent = '₱0.00';
                        
                        modalInitialized = true;
                    } else {
                        // Modal closed - allow re-initialization
                        modalInitialized = false;
                    }
                }
            });
        });
        
        observer.observe(modal, { attributes: true });
    }
    
    // Initialize on page load
    initModalOnOpen();

    // Tom Select integration
    if (document.querySelector('#member-select-el')) {
        new TomSelect('#member-select-el', {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: 'Search for a member...',
        });
    }

    // Ensure hidden fields are populated on form submit
    const adminLoanForm = document.getElementById('adminLoanForm');
    if (adminLoanForm) {
        adminLoanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const type = document.querySelector('select[name="lending_type"]').value;
            const amount = parseFloat(document.querySelector('input[name="lending_amount"]').value) || 0;
            
            if (!type || !amount) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Update term options first
            window.updateTermOptions();
            
            // Then recalculate to populate hidden fields
            window.adminRecalculate();
            
            // Submit the form after a brief delay to let DOM update
            setTimeout(() => {
                e.target.submit();
            }, 200);
        });
    }
    })();
    </script>
@endsection
