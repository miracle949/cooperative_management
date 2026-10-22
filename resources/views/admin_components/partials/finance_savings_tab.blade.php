<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Savings</h2>
        <p class="text-sm text-gray-500">Manage member savings and contributions</p>
    </div>
    <div class="flex items-center gap-3">
        <button data-action="openModal" data-arg='["addContributionModal"]' class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Manage Savings
        </button>
        <button data-action="openConvertToSCModal" class="btn btn-outline">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Convert to Share Capital
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

    <div class="stat-card cursor-pointer hover:shadow-lg hover:border-primary-200 transition-all group"
        data-action="openSavingsBalanceModal">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Savings Balance</p>
                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalSavingsBalance, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1 flex items-center">
                    <i data-lucide="piggy-bank" class="w-3 h-3 mr-1"></i>
                    Total across all savings accounts
                </p>
            </div>
            <div
                class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                <i data-lucide="piggy-bank" class="w-6 h-6 text-primary-600"></i>
            </div>
        </div>
    </div>

    <div class="stat-card cursor-pointer hover:shadow-lg hover:border-success-200 transition-all group"
        data-action="openInterestEligibilityModal">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Interest Eligibility</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($eligibleCount) }}</p>
                <p class="text-xs text-gray-500 mt-1 flex items-center">
                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                    Members eligible for interest ({{ $sirSettings->frequency_label }})
                </p>
            </div>
            <div
                class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center group-hover:bg-success-200 transition-colors">
                <i data-lucide="percent" class="w-6 h-6 text-success-600"></i>
            </div>
        </div>
    </div>
</div>

{{-- Pending Deposit Requests --}}
@if($pendingDeposits->isNotEmpty())
    <div class="card mb-6">
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-warning-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 text-warning-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            Pending Deposit Requests
                            <span
                                class="ml-1 px-2 py-0.5 bg-warning-100 text-warning-600 text-xs font-semibold rounded-full">{{ $pendingDeposits->count() }}</span>
                        </h3>
                        <p class="text-xs text-gray-500">{{ $pendingDeposits->count() }} awaiting confirmation</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date Requested</th>
                        <th>Member</th>
                        <th>Contact</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>GCash Ref No</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingDeposits as $pd)
                        @php
                            $pdMember = $pd->savingsAccount->user ?? null;
                            $pdContact = $pd->gcash_number ?? ($pdMember->otherinfo->contact_no ?? 'N/A');
                            $pdBalance = number_format($pd->savingsAccount->balance, 2);
                        @endphp
                        <tr class="js-deposit-detail cursor-pointer hover:bg-gray-50 transition-colors"
                            data-deposit-id="{{ $pd->id }}"
                            data-deposit-member="{{ ($pdMember->first_name ?? '') . ' ' . ($pdMember->last_name ?? '') }}"
                            data-deposit-balance="{{ $pdBalance }}" data-deposit-amount="{{ number_format($pd->amount, 2) }}"
                            data-deposit-contact="{{ $pdContact }}" data-deposit-method="{{ $pd->payment_method ?? 'cash' }}"
                            data-deposit-proof="{{ $pd->gcash_proof_path ?? '' }}"
                            data-deposit-ref="{{ $pd->gcash_reference_no ?? '' }}">
                            <td class="text-sm text-gray-900">{{ $pd->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-xs text-primary-600 font-medium">
                                            {{ strtoupper(substr($pdMember->first_name ?? 'U', 0, 1) . substr($pdMember->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">
                                        {{ $pdMember->first_name ?? 'Unknown' }} {{ $pdMember->last_name ?? '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600">{{ $pdContact }}</td>
                            <td class="text-sm font-semibold text-success-600">₱{{ number_format($pd->amount, 2) }}</td>
                            <td class="text-sm text-gray-600">{{ ucfirst($pd->payment_method ?? 'N/A') }}</td>
                            <td class="text-sm text-gray-600">{{ $pd->gcash_reference_no ?? '—' }}</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Pending Withdrawal Requests --}}
@if($pendingWithdrawals->isNotEmpty())
    <div class="card mb-6">
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-warning-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 text-warning-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Pending Withdrawal Requests</h3>
                        <p class="text-xs text-gray-500">{{ $pendingWithdrawals->count() }} awaiting disbursement</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date Requested</th>
                        <th>Member</th>
                        <th>Contact</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingWithdrawals as $pw)
                        @php
                            $pwMember = $pw->savingsAccount->user ?? null;
                            $pwContact = $pw->gcash_number ?? ($pwMember->otherinfo->contact_no ?? 'N/A');
                            $pwBalance = number_format($pw->savingsAccount->balance, 2);
                        @endphp
                        <tr>
                            <td class="text-sm text-gray-900">{{ $pw->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-xs text-primary-600 font-medium">
                                            {{ strtoupper(substr($pwMember->first_name ?? 'U', 0, 1) . substr($pwMember->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">
                                        {{ $pwMember->first_name ?? 'Unknown' }} {{ $pwMember->last_name ?? '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600">{{ $pwContact }}</td>
                            <td class="text-sm font-semibold text-red-600">₱{{ number_format($pw->amount, 2) }}</td>
                            <td class="text-sm text-gray-600">{{ ucfirst($pw->payment_method ?? 'N/A') }}</td>
                            <td class="text-right">
                                <button type="button"
                                    class="js-disburse-withdrawal btn btn-xs px-2 py-1 bg-warning-500 text-white hover:bg-warning-600 transition-colors"
                                    data-withdrawal-id="{{ $pw->id }}"
                                    data-withdrawal-member="{{ ($pwMember->first_name ?? '') . ' ' . ($pwMember->last_name ?? '') }}"
                                    data-withdrawal-balance="{{ $pwBalance }}"
                                    data-withdrawal-amount="{{ number_format($pw->amount, 2) }}"
                                    data-withdrawal-contact="{{ $pwContact }}">
                                    <i data-lucide="banknote" class="w-3 h-3 inline-block mr-1"></i> Disburse
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Filters -->
<form method="GET" action="{{ route('financial.activity') }}">
    <input type="hidden" name="tab" value="savings">
    <div class="card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by member name..." class="input pl-10">
                </div>
            </div>
            <div class="flex gap-2">
                <select name="type" class="select w-40" data-submit-on-change>
                    <option value="all">All Types</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdraw" {{ request('type') === 'withdraw' ? 'selected' : '' }}>Withdrawal</option>
                </select>
                <select name="status" class="select w-32" data-submit-on-change>
                    <option value="all">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Returned</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </div>
    </div>
</form>

<!-- Transactions Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($savingsTransactions as $tx)
                    @php
                        $txRowStatus = strtolower($tx->status ?? '');
                        $txIsInterest = strtolower($tx->type ?? '') === 'interest'
                            || strtolower($tx->payment_method ?? '') === 'auto-credited'
                            || str_starts_with((string) ($tx->reference_no ?? ''), 'INT-');
                        $txIsConversion = str_starts_with((string) ($tx->reference_no ?? ''), 'CNV')
                            || strtolower($tx->payment_method ?? '') === 'conversion';
                        $txTypeLabel = $txIsInterest
                            ? 'Interest'
                            : ($txIsConversion ? 'Converted to Share Capital' : ($tx->type === 'deposit' ? 'Deposit' : 'Withdrawal'));
                    @endphp
                    <tr @if($txRowStatus === 'completed' || $txRowStatus === 'voided') style="cursor:pointer;"
                        data-void="{{ $txRowStatus === 'voided' ? '1' : '0' }}" data-reason="{{ $tx->void_reason ?? '' }}"
                        data-ref="{{ $tx->reference_no ?? '' }}"
                        data-member="{{ ($tx->savingsAccount->user->first_name ?? '') . ' ' . ($tx->savingsAccount->user->last_name ?? '') }}"
                        data-amount="{{ $txRowStatus === 'voided' ? 0 : $tx->amount }}"
                        data-method="{{ $txIsConversion ? '—' : ($tx->payment_method ?? 'N/A') }}"
                        data-type="{{ $txTypeLabel }}" data-is-interest="{{ $txIsInterest ? '1' : '0' }}"
                        data-date="{{ $tx->created_at ? $tx->created_at->format('M d, Y h:i A') : '' }}"
                        data-balance="{{ $tx->balance_after ?? 0 }}" data-note="{{ $tx->note ?? 'N/A' }}"
                    data-action="openSavingsRow" data-arg='["|event|","|el|"]' @endif>
                        <td class="text-sm text-gray-900">{{ $tx->created_at->format('M d, Y') }}</td>
                        <td class="text-sm text-gray-600">{{ $tx->created_at->format('g:i A') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-xs text-primary-600 font-medium">
                                        {{ strtoupper(substr($tx->savingsAccount->user->first_name ?? 'U', 0, 1) . substr($tx->savingsAccount->user->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900">
                                    {{ $tx->savingsAccount->user->first_name ?? 'Unknown' }}
                                    {{ $tx->savingsAccount->user->last_name ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td
                            class="text-sm font-semibold {{ $txRowStatus === 'voided' ? 'text-gray-400' : ($txIsConversion ? 'text-red-600' : 'text-gray-900') }}">
                            @if($txRowStatus === 'voided')
                                <span>—</span>
                            @else
                                {{ $txIsConversion ? '-' : '' }}₱{{ number_format($tx->amount, 2) }}
                            @endif
                        </td>
                        <td>
                            @if($txIsInterest)
                                <span class="badge" style="background:#dbeafe;color:#1d4ed8;">Interest</span>
                            @elseif($txIsConversion)
                                <span class="badge badge-danger">Converted to Share Capital</span>
                            @elseif($tx->type === 'deposit')
                                <span class="badge badge-success">Deposit</span>
                            @else
                                <span class="badge badge-danger">Withdrawal</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-600">{{ $txIsConversion ? '—' : ($tx->payment_method ?? 'N/A') }}</td>
                        <td>
                            @php $txStatus = strtolower($tx->status ?? ''); @endphp
                            @if($txStatus === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($txStatus === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($txStatus === 'voided')
                                <span class="badge badge-danger">Returned</span>
                            @elseif($txStatus === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @elseif($txStatus === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @else
                                <span class="badge"
                                    style="background: #e2e8f0; color: #475569;">{{ ucfirst($tx->status ?? 'Unknown') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="flex flex-col items-center text-gray-500">
                                <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-50"></i>
                                <p>No transactions found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($savingsTransactions->hasPages())
        <div class="flex items-center justify-between mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">
                Showing {{ $savingsTransactions->firstItem() ?? 1 }} to
                {{ $savingsTransactions->lastItem() ?? $savingsTransactions->count() }} of
                {{ $savingsTransactions->total() }} transactions
            </p>
            <div class="flex items-center gap-1">
                @if($savingsTransactions->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <a href="{{ $savingsTransactions->previousPageUrl() }}"
                        class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                @endif

                @foreach($savingsTransactions->getUrlRange(max(1, $savingsTransactions->currentPage() - 2), min($savingsTransactions->lastPage(), $savingsTransactions->currentPage() + 2)) as $page => $url)
                    @if($page == $savingsTransactions->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($savingsTransactions->hasMorePages())
                    <a href="{{ $savingsTransactions->nextPageUrl() }}"
                        class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                @else
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>