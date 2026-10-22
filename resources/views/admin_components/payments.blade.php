@extends('layouts.admin')

@section('title', 'Payments - CoopAdmin')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .rp-inline-close:hover {
            background: #f5f5f5 !important;
            color: #333 !important;
        }

        .rp-inline-download:hover {
            opacity: 0.88;
        }
    </style>

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
                    <span class="text-gray-900 font-medium">Payments</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
            <p class="text-sm text-gray-500">View all loan repayment transactions made by members</p>
        </div>
        <div class="flex items-center gap-3">
            <button data-action="openRecordPaymentModal" class="btn btn-primary">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                Record Payment
            </button>
            @php
                $methodTabs = ['all' => 'All', 'Cash' => 'Cash', 'GCash' => 'GCash'];
                foreach ($paymentMethods as $pm) {
                    if (!isset($methodTabs[$pm->method_name])) {
                        $methodTabs[$pm->method_name] = $pm->method_name;
                    }
                }
            @endphp
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg flex-wrap">
                @foreach($methodTabs as $tabValue => $tabLabel)
                    <a href="{{ route('payments', ['method' => $tabValue]) }}"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all no-underline {{ $method === $tabValue ? 'bg-primary-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-200' }}">
                        {{ $tabLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Pending Repayments (Verification Required) -->
    @if(isset($pendingRepayments) && $pendingRepayments->count() > 0)
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-warning-500"></i>
                    Pending Repayments
                    <span
                        class="ml-2 px-2 py-0.5 bg-warning-100 text-warning-600 text-xs font-semibold rounded-full">{{ $pendingRepayments->count() }}</span>
                </h2>
                <p class="text-sm text-gray-500">Member repayments pending verification and completion</p>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Loan Ref</th>
                            <th>Payment #</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $pendingGroups = $pendingRepayments->groupBy(fn($p) => $p->lending_id . '|' . ($p->reference_no ?: 'no-ref-' . $p->id));
                        @endphp
                        @foreach($pendingGroups as $grp)
                            @php
                                $grp = $grp->sortBy('payment_number')->values();
                                $pr = $grp->first();
                                $grpTotal = $grp->sum(fn($p) => $p->amount_paid + (float) ($p->late_fee ?? 0));
                            @endphp

                            @if($grp->count() > 1)
                                    <tr class="repay-group-row cursor-pointer hover:bg-gray-50 transition-colors"
                                        data-member="{{ ($pr->user->first_name ?? '') . ' ' . ($pr->user->last_name ?? '') }}"
                                        data-loan-ref="{{ $pr->lending->reference_no ?? 'N/A' }}"
                                        data-loan-type="{{ $pr->lending->lending_type ?? 'N/A' }}" data-method="{{ $pr->payment_method }}"
                                        data-gcash-ref="{{ $pr->gcash_reference_no ?? '' }}"
                                        data-reference-no="{{ $pr->reference_no ?? '' }}"
                                        data-date="{{ $pr->created_at->format('M d, Y g:i A') }}"
                                        data-proof="{{ $pr->payment_proof_path ? url('/proof/' . ltrim($pr->payment_proof_path, '/')) : '' }}"
                                        data-total="{{ number_format($grpTotal, 2) }}" data-items="{{ json_encode($grp->map(fn($p) => [
                                    'id' => $p->id,
                                    'number' => $p->payment_number,
                                    'amount' => (float) $p->amount_paid,
                                    'penalty' => (float) ($p->late_fee ?? 0),
                                ])->values()) }}">
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                                    <span class="text-xs text-primary-600 font-medium">
                                                        {{ strtoupper(substr($pr->user->first_name ?? 'U', 0, 1) . substr($pr->user->last_name ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm text-gray-900">{{ $pr->user->first_name ?? 'Unknown' }}
                                                    {{ $pr->user->last_name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-sm text-gray-900 font-medium">{{ $pr->lending->reference_no ?? 'N/A' }}</td>
                                        <td class="text-sm text-gray-600">
                                            {{ $grp->first()->payment_number }}–{{ $grp->last()->payment_number }}
                                            <span
                                                class="ml-1 px-1.5 py-0.5 bg-warning-100 text-warning-600 text-[10px] font-semibold rounded-full">{{ $grp->count() }}
                                                installments</span>
                                        </td>
                                        <td class="text-sm font-semibold text-gray-900">₱{{ number_format($grp->sum('amount_paid'), 2) }}
                                        </td>
                                        <td class="text-sm text-gray-600">{{ $pr->payment_method }}</td>
                                        <td class="text-sm text-gray-600">{{ $pr->created_at->format('M d, Y') }}</td>
                                    </tr>
                            @else
                                {{-- KEEP YOUR EXISTING <tr class="repay-pending-row ..."> ... </tr> HERE, unchanged --}}
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Loan Reference</th>
                        <th>Loan Type</th>
                        <th>Payment #</th>
                        <th>Amount Payables</th>
                        <th>Payment Date</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Reference No.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php $rpRowStatus = strtolower($payment->status ?? 'completed'); @endphp
                        <tr @if($rpRowStatus === 'completed' || $rpRowStatus === 'voided') style="cursor:pointer;"
                            data-void="{{ $rpRowStatus === 'voided' ? '1' : '0' }}"
                            data-reason="{{ $payment->void_reason ?? '' }}"
                            data-member="{{ ($payment->user->first_name ?? '') . ' ' . ($payment->user->last_name ?? '') }}"
                            data-loanref="{{ $payment->lending->reference_no ?? 'N/A' }}"
                            data-loantype="{{ $payment->lending->lending_type ?? 'N/A' }}"
                            data-paymentnum="{{ $payment->payment_number }}"
                            data-amount="{{ $rpRowStatus === 'voided' ? '0.00' : number_format($payment->amount_paid + (float) ($payment->late_fee ?? 0), 2) }}"
                            data-method="{{ $payment->payment_method }}" data-ref="{{ $payment->reference_no ?? 'N/A' }}"
                            data-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y g:i A') }}"
                        data-action="openRepayRow" data-arg='["|event|","|el|"]' @endif>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-600 font-semibold">
                                            {{ strtoupper(substr($payment->user->first_name ?? 'U', 0, 1) . substr($payment->user->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $payment->user->first_name ?? 'Unknown' }} {{ $payment->user->last_name ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500">MEM-{{ sprintf('%03d', $payment->user_id ?? 0) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-gray-900 font-medium">{{ $payment->lending->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-600">{{ $payment->lending->lending_type ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-600">
                                {{ $payment->payment_number }}@if(($payment->payment_sequence ?? 1) > 1)<span
                                class="ml-1 px-1.5 py-0.5 bg-warning-100 text-warning-600 text-[10px] font-semibold rounded-full">{{ $payment->payment_sequence }}x</span>@endif
                            </td>
                            <td
                                class="text-sm font-semibold {{ $rpRowStatus === 'voided' ? 'text-gray-400' : 'text-gray-900' }}">
                                @if($rpRowStatus === 'voided')
                                    <span>—</span>
                                @else
                                    ₱{{ number_format($payment->amount_paid, 2) }}
                                @endif
                            </td>
                            <td class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                            </td>
                            <td>
                                @if($payment->payment_method === 'GCash')
                                    <span class="badge badge-primary">GCash</span>
                                @else
                                    <span class="badge badge-success">{{ $payment->payment_method }}</span>
                                @endif
                            </td>
                            <td>
                                @if(($payment->status ?? 'Completed') === 'Completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($payment->status === 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($payment->status === 'voided')
                                    <span class="badge badge-danger">Returned</span>
                                @else
                                    <span class="badge badge-success">Completed</span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500">{{ $payment->reference_no ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="credit-card" class="w-12 h-12 mb-3 opacity-50"></i>
                                    <p>No payments found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ $payments->firstItem() ?? 0 }}-{{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }}
                payments
            </p>
            <div>
                @if($payments->hasPages())
                    <div class="flex items-center gap-1">
                        @if($payments->onFirstPage())
                            <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                        @else
                            <a href="{{ $payments->appends(['method' => $method])->previousPageUrl() }}"
                                class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif

                        @foreach($payments->appends(['method' => $method])->getUrlRange(max(1, $payments->currentPage() - 2), min($payments->lastPage(), $payments->currentPage() + 2)) as $page => $url)
                            @if($page == $payments->currentPage())
                                <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($payments->hasMorePages())
                            <a href="{{ $payments->appends(['method' => $method])->nextPageUrl() }}"
                                class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @else
                            <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="recordPaymentModal" class="modal-overlay hidden">
        <div class="modal max-w-lg" style="border-radius: 16px;">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="credit-card" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Record Payment</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Manually record a
                                loan repayment</p>
                        </div>
                    </div>
                    <button data-action="closeRecordPaymentModal"
                        style="background: none; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div style="padding: 1.25rem;">
                <form id="recordPaymentForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="reference_no" id="rpReferenceNo">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Member <span
                                class="text-red-500">*</span></label>
                        <select name="member_id" id="rpMemberSelect" class="select" style="width: 100%;"
                            data-action="loadMemberLoans" required>
                            @foreach($allMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Active Loan <span
                                class="text-red-500">*</span></label>
                        <select name="lending_id" id="rpLoanSelect" class="select" style="width: 100%;" required>
                            <option value="">Select a member first</option>
                        </select>
                        <p id="rpLoanInfo" class="text-xs text-gray-400 mt-1" style="display: none;"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount Payables</label>
                        <div class="mb-2">
                            <select id="rpPaymentType" class="select" style="width: 100%;"
                                data-action="updatePayableAmount">
                                <option value="monthly">Monthly Bill Only</option>
                                <option value="full">Full Payment</option>
                            </select>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
                            <input type="number" name="amount_paid" id="rpAmount" class="input pl-10" placeholder="0.00"
                                step="0.01" min="0" style="width: 100%; padding-left: 2.5rem;" readonly>
                        </div>
                        <p id="rpAmountBreakdown" class="text-xs text-gray-400 mt-1" style="display:none;"></p>
                    </div>
                </form>

                <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                    <button data-action="openRecordPaymentConfirm"
                        style="width: 100%; padding: 0.7rem; background: #1E2A4A; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Record Payment
                    </button>
                    <button data-action="closeRecordPaymentModal"
                        style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div id="confirmPaymentModal" class="modal-overlay hidden">
        <div class="modal max-w-md" style="border-radius: 16px;">
            <div style="background: linear-gradient(135deg, #14532D 0%, #166534 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="shield-check" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Confirm Payment</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Please review the
                                payment details</p>
                        </div>
                    </div>
                    <button data-action="closeConfirmPaymentModal"
                        style="background: none; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div style="padding: 1.25rem;">
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Member</span>
                        <span id="cfMember" class="text-sm font-semibold text-gray-900"></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Amount Payable</span>
                        <span id="cfAmount" class="text-sm font-bold text-gray-900"></span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Payment Date</span>
                        <span id="cfDate" class="text-sm font-semibold text-gray-900"></span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500">Reference Number</span>
                        <span id="cfReference" class="text-sm font-mono font-semibold text-gray-900"></span>
                    </div>
                </div>

                <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                    <button id="rpConfirmBtn" data-action="confirmRecordPayment"
                        style="width: 100%; padding: 0.7rem; background: #15803D; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Payment
                    </button>
                    <button data-action="closeConfirmPaymentModal"
                        style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function openRecordPaymentModal() {
            const modal = document.getElementById('recordPaymentModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
            const ts = document.getElementById('rpMemberSelect')?.tomselect;
            if (ts) ts.clear();
            document.getElementById('rpLoanSelect').innerHTML = '<option value="">Select a member first</option>';
            document.getElementById('rpLoanInfo').style.display = 'none';
            document.getElementById('rpAmount').value = '';
            document.getElementById('rpAmountBreakdown').style.display = 'none';
            document.getElementById('rpReferenceNo').value = '';
            rpPayable = null;
        }

        function closeRecordPaymentModal() {
            const modal = document.getElementById('recordPaymentModal');
            modal.classList.add('hidden');
            modal.style.display = '';
            document.body.style.overflow = '';
        }

        function loadMemberLoans() {
            const memberId = document.getElementById('rpMemberSelect').value;
            const loanSelect = document.getElementById('rpLoanSelect');
            const loanInfo = document.getElementById('rpLoanInfo');

            loanSelect.innerHTML = '<option value="">Loading...</option>';
            loanInfo.style.display = 'none';

            if (!memberId) {
                loanSelect.innerHTML = '<option value="">Select a member first</option>';
                return;
            }

            fetch('/loans/member/' + memberId + '/active')
                .then(r => r.json())
                .then(loans => {
                    if (loans.length === 0) {
                        loanSelect.innerHTML = '<option value="">No active loans found</option>';
                        return;
                    }
                    let html = '<option value="">Select loan</option>';
                    loans.forEach(loan => {
                        html += `<option value="${loan.id}" data-monthly="${loan.monthly_payment}" data-total="${loan.total_payment}" data-amount="${loan.lending_amount}">
                                                                                            ${loan.reference_no} — ${loan.lending_type} (₱${parseFloat(loan.lending_amount).toLocaleString()})
                                                                                        </option>`;
                    });
                    loanSelect.innerHTML = html;
                })
                .catch(() => {
                    loanSelect.innerHTML = '<option value="">Error loading loans</option>';
                });
        }

        document.getElementById('rpLoanSelect')?.addEventListener('change', function () {
            const loanInfo = document.getElementById('rpLoanInfo');
            const selected = this.options[this.selectedIndex];
            if (selected && selected.value) {
                const amount = parseFloat(selected.dataset.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                const monthly = parseFloat(selected.dataset.monthly || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                loanInfo.textContent = 'Loan Amount: ₱' + amount + ' · Monthly Due: ₱' + monthly;
                loanInfo.style.display = 'block';

                // fetch computed payable (includes penalty if overdue)
                fetch('/loans/' + selected.value + '/payable')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            rpPayable = data;
                            updatePayableAmount();

                            // Scheduled loans: surface the exact current installment.
                            if (data.has_schedule && data.installment) {
                                loanInfo.textContent = 'Loan Amount: ₱' + amount
                                    + ' · Current Installment: ₱' + parseFloat(data.installment).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                    + ' · full installment required (partial payments not allowed)';
                            }
                        } else {
                            rpPayable = null;
                            document.getElementById('rpAmount').value = '';
                            document.getElementById('rpAmountBreakdown').style.display = 'none';
                        }
                    })
                    .catch(() => {
                        rpPayable = null;
                        document.getElementById('rpAmount').value = '';
                        document.getElementById('rpAmountBreakdown').style.display = 'none';
                    });
            } else {
                loanInfo.style.display = 'none';
                rpPayable = null;
                document.getElementById('rpAmount').value = '';
                document.getElementById('rpAmountBreakdown').style.display = 'none';
            }
        });

        let rpPayable = null;

        function updatePayableAmount() {
            const type = document.getElementById('rpPaymentType').value;
            const amountEl = document.getElementById('rpAmount');
            const bdEl = document.getElementById('rpAmountBreakdown');

            if (!rpPayable) {
                amountEl.value = '';
                bdEl.style.display = 'none';
                return;
            }

            const isFull = type === 'full';
            const amount = isFull ? rpPayable.full_total : rpPayable.total;
            amountEl.value = amount;

            const baseLabel = isFull ? 'Remaining Balance' : 'Base';
            const baseValue = isFull ? rpPayable.remaining : rpPayable.base;
            let breakdown = baseLabel + ': ₱' + parseFloat(baseValue).toLocaleString(undefined, { minimumFractionDigits: 2 });
            if (rpPayable.penalty > 0) {
                breakdown += ' · Penalty: ₱' + parseFloat(rpPayable.penalty).toLocaleString(undefined, { minimumFractionDigits: 2 });
            }
            bdEl.textContent = breakdown;
            bdEl.style.display = 'block';
        }

        function generateReferenceNumber() {
            const d = new Date();
            const pad = n => String(n).padStart(2, '0');
            return 'ADMIN-' + d.getFullYear() + pad(d.getMonth() + 1) + pad(d.getDate()) + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds());
        }

        function openRecordPaymentConfirm() {
            const form = document.getElementById('recordPaymentForm');
            const formData = new FormData(form);

            if (!formData.get('member_id')) {
                showToast('Error', 'Please select a member');
                return;
            }
            if (!formData.get('lending_id')) {
                showToast('Error', 'Please select an active loan');
                return;
            }
            if (!formData.get('amount_paid') || parseFloat(formData.get('amount_paid')) < 0) {
                showToast('Error', 'Invalid amount payable');
                return;
            }

            const memberSel = document.getElementById('rpMemberSelect');
            const memberName = memberSel.options[memberSel.selectedIndex]?.text || '';
            const reference = generateReferenceNumber();

            document.getElementById('rpReferenceNo').value = reference;

            const amount = parseFloat(formData.get('amount_paid'));
            document.getElementById('cfMember').textContent = memberName;
            document.getElementById('cfAmount').textContent = '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2 });
            document.getElementById('cfDate').textContent = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('cfReference').textContent = reference;

            const modal = document.getElementById('confirmPaymentModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        }

        function closeConfirmPaymentModal() {
            const modal = document.getElementById('confirmPaymentModal');
            modal.classList.add('hidden');
            modal.style.display = '';
            document.body.style.overflow = '';
        }

        function confirmRecordPayment() {
            const form = document.getElementById('recordPaymentForm');
            const formData = new FormData(form);

            const btn = document.getElementById('rpConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';

            fetch('{{ route("payments.record") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            let msg = 'Failed to record payment.';
                            if (err.message) msg = err.message;
                            if (err.errors) msg = Object.values(err.errors).flat().join('\n');
                            return { success: false, message: msg };
                        }).catch(() => ({ success: false, message: 'Server error. Please try again.' }));
                    }
                    return response.json();
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Payment';
                    if (data.success) {
                        closeRecordPaymentModal();
                        closeConfirmPaymentModal();
                        showToast('Success', data.message);
                        form.reset();
                        document.getElementById('rpLoanSelect').innerHTML = '<option value="">Select a member first</option>';
                        document.getElementById('rpLoanInfo').style.display = 'none';
                        document.getElementById('rpReferenceNo').value = '';
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast('Error', data.message || 'Failed to record payment');
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Payment';
                    showToast('Error', 'Something went wrong. Please try again.');
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.querySelector('#rpMemberSelect');
            if (el && typeof TomSelect !== 'undefined') {
                new TomSelect('#rpMemberSelect', {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: 'Search for a member...',
                });
            }
        });
    </script>

    <!-- Repayment Detail Modal -->
    <div id="repaymentDetailModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="eye" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Repayment Details</h2>
                            <p class="text-xs text-gray-500">Review and act on this repayment request</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["repaymentDetailModal"]'
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Member</span>
                        <span id="rpDetailMember" class="text-sm font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Loan Reference</span>
                        <span id="rpDetailLoanRef" class="text-sm font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Loan Type</span>
                        <span id="rpDetailLoanType" class="text-sm text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Payment #</span>
                        <span id="rpDetailPaymentNum" class="text-sm text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Payment Method</span>
                        <span id="rpDetailMethod" class="text-sm text-gray-900">—</span>
                    </div>
                    <div id="rpDetailGcashRefRow" class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">GCash Ref No.</span>
                        <span id="rpDetailGcashRef" class="text-sm font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Reference No.</span>
                        <span id="rpDetailRefNo" class="text-sm font-mono text-gray-900">—</span>
                    </div>
                    <div id="rpDetailLateFeeRow" class="hidden justify-between items-center">
                        <span class="text-sm text-gray-500">Late Fee</span>
                        <span id="rpDetailLateFee" class="text-sm font-semibold text-danger-600">—</span>
                    </div>
                    <div id="rpDetailNotesRow" class="hidden justify-between items-center">
                        <span class="text-sm text-gray-500">Notes</span>
                        <span id="rpDetailNotes" class="text-sm text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Date</span>
                        <span id="rpDetailDate" class="text-sm text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                        <span class="text-sm font-medium text-gray-700">Amount</span>
                        <div class="text-right">
                            <span id="rpDetailAmount" class="text-lg font-bold text-success-600">—</span>
                            <p id="rpDetailAmountNote" class="text-xs text-gray-400 mt-0.5 hidden"></p>
                        </div>
                    </div>
                </div>

                <div id="rpDetailProofSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member's Payment Proof</label>
                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <img id="rpDetailProofImg" src="" alt="Payment proof"
                            class="w-full max-h-64 object-contain bg-gray-50">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="openRepaymentVoidConfirm" id="rpDetailVoidBtn"
                    class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors flex items-center gap-2">
                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                    Mark as Returned
                </button>
                <button data-action="confirmCompleteRepayment" id="rpDetailCompleteBtn"
                    class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Mark as Complete
                </button>
            </div>
        </div>
    </div>

    <div id="repaymentGroupModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="layers" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Grouped Repayment</h2>
                            <p id="rpGroupSub" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["repaymentGroupModal"]'
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4" style="max-height:65vh; overflow-y:auto;">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Member</span><span
                            id="rpGroupMember" class="text-sm font-semibold text-gray-900">—</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Loan Reference</span><span
                            id="rpGroupLoanRef" class="text-sm font-semibold text-gray-900">—</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Loan Type</span><span
                            id="rpGroupLoanType" class="text-sm text-gray-900">—</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Method</span><span
                            id="rpGroupMethod" class="text-sm text-gray-900">—</span></div>
                    <div id="rpGroupGcashRow" class="flex justify-between"><span class="text-sm text-gray-500">GCash Ref
                            No.</span><span id="rpGroupGcash" class="text-sm font-semibold text-gray-900">—</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Reference No.</span><span
                            id="rpGroupRef" class="text-sm font-mono text-gray-900">—</span></div>
                    <div class="flex justify-between"><span class="text-sm text-gray-500">Date</span><span id="rpGroupDate"
                            class="text-sm text-gray-900">—</span></div>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Installments</p>
                    <div id="rpGroupList" class="space-y-2"></div>
                </div>

                <div class="flex justify-between items-center bg-gray-900 text-white rounded-xl px-4 py-3">
                    <span class="text-sm font-medium">Total</span>
                    <span id="rpGroupTotal" class="text-lg font-bold">₱0.00</span>
                </div>

                <div id="rpGroupProofSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member's Payment Proof</label>
                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <img id="rpGroupProofImg" src="" alt="Payment proof"
                            class="w-full max-h-64 object-contain bg-gray-50">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="openRepaymentVoidConfirm"
                    class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors flex items-center gap-2">
                    <i data-lucide="undo-2" class="w-4 h-4"></i> Mark all as Returned
                </button>
                <button id="rpGroupCompleteBtn" data-action="confirmCompleteRepayment"
                    class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Mark all as Complete
                </button>
            </div>
        </div>
    </div>

    <!-- Void Repayment Confirm Modal -->
    <div id="repaymentVoidModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-danger-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Return Loan Repayment</h2>
                            <p class="text-xs text-gray-500">Are you sure you want to mark this repayment as returned?</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["repaymentVoidModal"]'
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Member</span>
                        <span id="rpVoidMember" class="text-sm font-semibold text-gray-900">—</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                        <span class="text-sm font-medium text-gray-700">Amount</span>
                        <span id="rpVoidAmount" class="text-lg font-bold text-danger-600">—</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Returning</label>
                    <select id="rpVoidReason"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-danger-500 focus:border-danger-500">
                        <option value="" disabled selected>Select a reason...</option>
                        <option value="wrong_amount">Wrong amount entered</option>
                        <option value="duplicate_payment">Duplicate payment</option>
                        <option value="fraudulent">Fraudulent / suspicious transaction</option>
                        <option value="other_member">Sent by wrong member</option>
                        <option value="technical_error">System / technical error</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="openRepaymentDetailFromVoid"
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Go
                    Back</button>
                <button id="rpVoidConfirmBtn" data-action="submitRepaymentVoid" disabled
                    class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                    Confirm Return
                </button>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        let currentRepaymentIds = [];
        let currentGroupMeta = null;

        function rpMoney(n) {
            return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Runs the request for every id, one after another, stopping on the first failure.
        function rpRunForAll(ids, suffix, extra) {
            return ids.reduce(function (chain, id) {
                return chain.then(function () {
                    var fd = new FormData();
                    fd.append('_token', '{{ csrf_token() }}');
                    if (extra) Object.keys(extra).forEach(function (k) { fd.append(k, extra[k]); });
                    return fetch('/repayments/' + id + '/' + suffix, {
                        method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (r) { return r.json(); })
                        .then(function (d) { if (!d.success) throw new Error(d.message || 'Request failed'); });
                });
            }, Promise.resolve());
        }

        document.querySelectorAll('.repay-group-row').forEach(function (row) {
            row.addEventListener('click', function () {
                var d = this.dataset, items = [];
                try { items = JSON.parse(d.items || '[]'); } catch (e) { }

                currentRepaymentIds = items.map(function (i) { return i.id; });
                currentGroupMeta = { member: d.member, amount: '₱' + d.total };

                document.getElementById('rpGroupSub').textContent = items.length + ' installments submitted together';
                document.getElementById('rpGroupMember').textContent = d.member || '—';
                document.getElementById('rpGroupLoanRef').textContent = d.loanRef || '—';
                document.getElementById('rpGroupLoanType').textContent = d.loanType || '—';
                document.getElementById('rpGroupMethod').textContent = d.method || '—';
                document.getElementById('rpGroupGcash').textContent = d.gcashRef || '—';
                document.getElementById('rpGroupRef').textContent = d.referenceNo || '—';
                document.getElementById('rpGroupDate').textContent = d.date || '—';
                document.getElementById('rpGroupTotal').textContent = '₱' + d.total;

                var isGcash = (d.method || '').toLowerCase() === 'gcash';
                document.getElementById('rpGroupGcashRow').style.display = isGcash ? 'flex' : 'none';

                document.getElementById('rpGroupList').innerHTML = items.map(function (it) {
                    return '<div class="flex justify-between items-center border border-gray-200 rounded-lg px-3 py-2">' +
                        '<span class="text-sm font-medium text-gray-900">Installment #' + it.number + '</span>' +
                        '<span class="text-sm font-semibold text-gray-900">' + rpMoney(it.amount) +
                        (it.penalty > 0 ? '<span class="block text-xs text-danger-600">+ ' + rpMoney(it.penalty) + ' late fee</span>' : '') +
                        '</span></div>';
                }).join('');

                var img = document.getElementById('rpGroupProofImg');
                if (d.proof) {
                    img.src = d.proof;
                    document.getElementById('rpGroupProofSection').classList.remove('hidden');
                } else {
                    document.getElementById('rpGroupProofSection').classList.add('hidden');
                }

                openModal('repaymentGroupModal');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        });

        document.querySelectorAll('.repay-pending-row').forEach(row => {
            row.addEventListener('click', function () {
                currentRepaymentId = this.dataset.id;
                currentRepaymentIds = [this.dataset.id];
                currentGroupMeta = null;
                document.getElementById('rpDetailMember').textContent = this.dataset.member || '—';
                document.getElementById('rpDetailLoanRef').textContent = this.dataset.loanRef || '—';
                document.getElementById('rpDetailLoanType').textContent = this.dataset.loanType || '—';
                document.getElementById('rpDetailPaymentNum').textContent = this.dataset.paymentNumber + ((this.dataset.paymentSequence || 1) > 1 ? ' (' + this.dataset.paymentSequence + 'x)' : '') || '—';
                document.getElementById('rpDetailMethod').textContent = this.dataset.method || '—';
                document.getElementById('rpDetailGcashRef').textContent = this.dataset.gcashRef || '—';
                document.getElementById('rpDetailRefNo').textContent = this.dataset.referenceNo || '—';

                var isGcash = this.dataset.method && this.dataset.method.toLowerCase() === 'gcash';
                var gcashRefRow = document.getElementById('rpDetailGcashRefRow');
                if (isGcash) {
                    gcashRefRow.classList.remove('hidden');
                    gcashRefRow.classList.add('flex');
                } else {
                    gcashRefRow.classList.add('hidden');
                    gcashRefRow.classList.remove('flex');
                }
                document.getElementById('rpDetailAmount').textContent = '₱' + this.dataset.amount;
                document.getElementById('rpDetailDate').textContent = this.dataset.date || '—';

                var amountNote = document.getElementById('rpDetailAmountNote');
                var lateFeeRow = document.getElementById('rpDetailLateFeeRow');
                if (this.dataset.lateFee) {
                    amountNote.textContent = 'includes ₱' + this.dataset.lateFee + ' late fee';
                    amountNote.classList.remove('hidden');
                    document.getElementById('rpDetailLateFee').textContent = '₱' + this.dataset.lateFee;
                    lateFeeRow.classList.remove('hidden');
                    lateFeeRow.classList.add('flex');
                } else {
                    amountNote.textContent = '';
                    amountNote.classList.add('hidden');
                    lateFeeRow.classList.add('hidden');
                    lateFeeRow.classList.remove('flex');
                }

                var notesRow = document.getElementById('rpDetailNotesRow');
                if (this.dataset.notes) {
                    document.getElementById('rpDetailNotes').textContent = this.dataset.notes;
                    notesRow.classList.remove('hidden');
                    notesRow.classList.add('flex');
                } else {
                    notesRow.classList.add('hidden');
                    notesRow.classList.remove('flex');
                }

                var proofSection = document.getElementById('rpDetailProofSection');
                var proofImg = document.getElementById('rpDetailProofImg');
                if (this.dataset.proof) {
                    proofImg.src = this.dataset.proof;
                    proofSection.classList.remove('hidden');
                } else {
                    proofSection.classList.add('hidden');
                }

                openModal('repaymentDetailModal');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        });

        document.getElementById('rpVoidReason').addEventListener('change', function () {
            document.getElementById('rpVoidConfirmBtn').disabled = !this.value;
        });

        function openRepaymentVoidConfirm() {
            if (currentGroupMeta) {
                document.getElementById('rpVoidAmount').textContent = currentGroupMeta.amount;
                document.getElementById('rpVoidMember').textContent = currentGroupMeta.member;
            } else {
                var row = document.querySelector('.repay-pending-row[data-id="' + currentRepaymentId + '"]');
                if (row) {
                    document.getElementById('rpVoidAmount').textContent = '₱' + row.dataset.amount;
                    document.getElementById('rpVoidMember').textContent = row.dataset.member;
                }
            }
            document.getElementById('rpVoidReason').value = '';
            document.getElementById('rpVoidConfirmBtn').disabled = true;
            closeModal(currentGroupMeta ? 'repaymentGroupModal' : 'repaymentDetailModal');
            openModal('repaymentVoidModal');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function openRepaymentDetailFromVoid() {
            closeModal('repaymentVoidModal');
            openModal(currentGroupMeta ? 'repaymentGroupModal' : 'repaymentDetailModal');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function submitRepaymentVoid() {
            var reason = document.getElementById('rpVoidReason').value;
            if (!reason) { showToast('Error', 'Please select a reason for returning.'); return; }
            if (!currentRepaymentIds.length) return;

            var btn = document.getElementById('rpVoidConfirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            rpRunForAll(currentRepaymentIds, 'void', { void_reason: reason })
                .then(function () {
                    closeModal('repaymentVoidModal');
                    showToast('Success', 'Repayment marked as returned.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                })
                .catch(function (err) {
                    showToast('Error', err.message || 'Failed to return repayment');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="undo-2" class="w-4 h-4"></i> Confirm Return';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
        }

        function confirmCompleteRepayment() {
            if (!currentRepaymentIds.length) return;

            var isGroup = !!currentGroupMeta;
            var btn = document.getElementById(isGroup ? 'rpGroupCompleteBtn' : 'rpDetailCompleteBtn');
            var label = isGroup ? 'Mark all as Complete' : 'Mark as Complete';
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            rpRunForAll(currentRepaymentIds, 'complete')
                .then(function () {
                    closeModal(isGroup ? 'repaymentGroupModal' : 'repaymentDetailModal');
                    showToast('Success', 'Repayment confirmed successfully.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                })
                .catch(function (err) {
                    showToast('Error', err.message || 'Failed to complete repayment');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> ' + label;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
        }

        const REPAY_VOID_LABELS = {
            wrong_amount: 'Wrong amount entered',
            duplicate_payment: 'Duplicate payment',
            fraudulent: 'Fraudulent / suspicious transaction',
            other_member: 'Sent by wrong member',
            technical_error: 'System / technical error',
            other: 'Other'
        };
        function repayGetVoidLabel(key) { return REPAY_VOID_LABELS[key] || key || 'No reason provided'; }

        window.showRepayVoidReason = function (d) {
            d = d || {};
            const label = repayGetVoidLabel(d.reason);
            const existingModal = document.getElementById('repayVoidReasonModal');
            if (existingModal) { existingModal.remove(); }

            const amountFmt = Number(d.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });

            function row(label2, value) {
                return '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #f0e2e2; font-size:0.85rem;">'
                    + '<span style="color:#888; font-weight:500; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.3px;">' + label2 + '</span>'
                    + '<span style="color:#1a1a1a; font-weight:600; text-align:right; max-width:60%; word-break:break-word;">' + value + '</span>'
                    + '</div>';
            }

            const modal = document.createElement('div');
            modal.id = 'repayVoidReasonModal';
            modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';
            modal.innerHTML = `
                                                                                <div style="background:#fff; border-radius:20px; width:100%; max-width:400px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:repayVoidIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                                                                                    <style>
                                                                                        @keyframes repayVoidIn {
                                                                                            from { opacity: 0; transform: translateY(28px) scale(0.97); }
                                                                                            to { opacity: 1; transform: translateY(0) scale(1); }
                                                                                        }
                                                                                    </style>
                                                                                    <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                                                                                        <div style="width:60px;height:60px;background:#c0392b;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                                                                                        </div>
                                                                                        <h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">Repayment Returned</h2>
                                                                                        <p style="color:#6b7280; font-size:0.82rem; margin:0;">This loan repayment has been returned by the admin.</p>
                                                                                    </div>
                                                                                    <div style="padding:0.75rem 1.5rem 0;">
                                                                                        <div style="background:#fafafa; border-radius:12px; padding:0.25rem 1rem;">
                                                                                            ${row('Loan Type', d.loantype || '—')}
                                                                                            ${row('Loan Reference', d.loanref || '—')}
                                                                                            ${row('Payment #', d.paymentnum || '—')}
                                                                                            ${row('Member', d.member || '—')}
                                                                                            ${row('Reference No.', d.ref || '—')}
                                                                                            ${row('Date & Time', d.date || '—')}
                                                                                            ${row('Method', d.method || '—')}
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="padding:1.25rem 1.5rem;">
                                                                                        <div style="font-size:0.78rem; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.4rem;">Reason</div>
                                                                                        <div style="font-size:1rem; font-weight:700; color:#c0392b; background:#fdecec; border:1px solid #f5c6c6; border-radius:10px; padding:0.75rem 1rem;">${label}</div>
                                                                                    </div>
                                                                                    <div style="padding:0 1.5rem 1.5rem;">
                                                                                        <button data-action="remove-element" data-target="repayVoidReasonModal" class="rp-inline-close" style="width:100%; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>
                                                                                    </div>
                                                                                </div>
                                                                            `;
            document.body.appendChild(modal);
        };

        let currentRepayReceipt = null;
        window.openRepayRow = function (e, row) {
            if (e && e.target && e.target.closest && e.target.closest('a, button')) return;
            const d = row.dataset;
            if (d.void === '1') {
                showRepayVoidReason(d);
                return;
            }
            viewRepayReceipt(d.member, d.loanref, d.paymentnum, d.amount, d.method, d.ref, d.date, d.loantype);
        };
        const REPAY_NAVY = '#1E2A4A';
        const REPAY_NAVY_LIGHT = '#eef1f8';

        function payReceiptRow(label, value, highlight) {
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0;border-bottom:1px dashed #e8e8e8;font-size:0.85rem;">' +
                '<span style="color:#888;font-weight:500;">' + label + '</span>' +
                '<span style="color:' + (highlight ? REPAY_NAVY : '#1a1a1a') + ';font-weight:' + (highlight ? '800' : '600') + ';text-align:right;max-width:55%;word-break:break-word;' + (highlight ? 'font-size:1rem;' : 'color:#1a1a1a;font-weight:600;') + '">' + value + '</span>' +
                '</div>';
        }

        function repayRefBadge(ref) {
            return '<span style="display:inline-block; background:' + REPAY_NAVY_LIGHT + '; color:' + REPAY_NAVY + '; padding:0.2rem 0.6rem; border-radius:6px; letter-spacing:0.5px; font-family:monospace; font-size:0.78rem; font-weight:600;">' + ref + '</span>';
        }

        function repayStatusBadge() {
            return '<span style="display:inline-flex; align-items:center; gap:6px; background:#d1fae5; border:1.5px solid #a7f3d0; color:#065f46; border-radius:20px; padding:0.2rem 0.75rem; font-size:0.75rem; font-weight:700;"><span style="width:7px; height:7px; background:#059669; border-radius:50%; flex-shrink:0;"></span> Completed</span>';
        }

        window.viewRepayReceipt = function (member, loanRef, paymentNumber, amount, method, ref, date, loanType, notes) {
            currentRepayReceipt = {
                member: member, loanRef: loanRef, paymentNumber: paymentNumber,
                amount: amount, method: method, ref: ref, date: date, loanType: loanType, notes: notes || ''
            };

            const existingModal = document.getElementById('repayReceiptModal');
            if (existingModal) { existingModal.remove(); }

            const modal = document.createElement('div');
            modal.id = 'repayReceiptModal';
            modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';

            modal.innerHTML = `
                                                                                <div style="background:#fff; border-radius:20px; width:100%; max-width:420px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:repayRecIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                                                                                    <style>
                                                                                        @keyframes repayRecIn {
                                                                                            from { opacity: 0; transform: translateY(28px) scale(0.97); }
                                                                                            to { opacity: 1; transform: translateY(0) scale(1); }
                                                                                        }
                                                                                    </style>
                                                                                    <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                                                                                        <div style="width:60px;height:60px;background:${REPAY_NAVY};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                        </div>
                                                                                        <h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">Payment Recorded</h2>
                                                                                        <p style="color:#6b7280; font-size:0.82rem; margin:0;">KMPCATS Cooperative -- Official Receipt</p>
                                                                                    </div>
                                                                                    <div style="padding:0.6rem 1.5rem;">
                                                                                        ${payReceiptCard()}
                                                                                    </div>
                                                                                    <div style="padding:0 1.5rem 1.5rem; display:flex; gap:0.6rem;">
                                                                                        <button data-action="remove-element" data-target="repayReceiptModal" class="rp-inline-close" style="flex:1; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>
                                                                                        <button data-action="downloadRepayReceipt" class="rp-inline-download" style="flex:1; padding:0.8rem; background:${REPAY_NAVY}; color:#fff; border:none; border-radius:12px; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:opacity .2s;">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                                                            Download
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            `;
            document.body.appendChild(modal);
        };

        function payReceiptCard() {
            const d = currentRepayReceipt || {};
            return `
                                                                                ${payReceiptRow('Organization', 'KMPCATS')}
                                                                                ${payReceiptRow('Member', d.member)}
                                                                                ${payReceiptRow('Loan Reference', d.loanRef)}
                                                                                ${payReceiptRow('Loan Type', d.loanType)}
                                                                                ${payReceiptRow('Payment #', d.paymentNumber)}
                                                                                ${payReceiptRow('Amount', '&#8369;' + d.amount, true)}
                                                                                ${payReceiptRow('Payment Method', d.method)}
                                                                                ${payReceiptRow('Reference No.', repayRefBadge(d.ref))}
                                                                                ${payReceiptRow('Date & Time', d.date)}
                                                                                ${payReceiptRow('Status', repayStatusBadge())}
                                                                            `;
        }

        window.downloadRepayReceipt = function () {
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:fixed; left:-9999px; top:0; width:400px; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.15);';
            wrapper.innerHTML = `
                                                                                <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e8e8e8;">
                                                                                    <div style="width:60px;height:60px;background:${REPAY_NAVY};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                    </div>
                                                                                    <div style="color:#1a1a1a;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">Payment Recorded</div>
                                                                                    <div style="color:#6b7280;font-size:0.82rem;margin:0;">KMPCATS Cooperative -- Official Receipt</div>
                                                                                </div>
                                                                                <div style="padding:0.6rem 1.5rem;">
                                                                                    ${payReceiptCard()}
                                                                                </div>
                                                                                <div style="padding:0.8rem 1.5rem 1.2rem;text-align:center;border-top:1px dashed #e8e8e8;">
                                                                                    <div style="color:#aaa;font-size:0.72rem;">This receipt is system-generated and serves as official proof of your transaction.</div>
                                                                                </div>
                                                                            `;
            document.body.appendChild(wrapper);

            const doCapture = function () {
                html2canvas(wrapper, { scale: 2, useCORS: true, backgroundColor: null }).then(function (canvas) {
                    const link = document.createElement('a');
                    link.download = 'KMPCATS-Repayment-Receipt-' + (currentRepayReceipt ? currentRepayReceipt.ref : '') + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    wrapper.remove();
                });
            };

            if (typeof html2canvas !== 'undefined') {
                doCapture();
            } else {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = doCapture;
                document.head.appendChild(script);
            }
        }
    </script>
@endsection