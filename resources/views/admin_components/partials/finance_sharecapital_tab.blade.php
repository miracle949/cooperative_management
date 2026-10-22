<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Share Capitals</h2>
        <p class="text-sm text-gray-500">Manage member share capital contributions</p>
    </div>
    <div class="flex items-center gap-3">
        <button data-action="openModal" data-arg='["scContributionModal"]' class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Manage Share-capital
        </button>
        <button data-action="openModal" data-arg='["sellSharesModal"]' class="btn btn-primary"
            style="background: #1E2A4A; border-color: #1E2A4A;">
            <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
            Sell Shares
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="stat-card cursor-pointer hover:shadow-lg hover:border-primary-200 transition-all group"
        data-action="openShareCapitalsModal" data-arg='[false]'>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Contributions</p>
                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalContributions, 2) }}</p>
                <p class="text-xs text-success-500 mt-1 flex items-center">
                    <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i>
                    Total across all member accounts
                </p>
            </div>
            <div
                class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                <i data-lucide="coins" class="w-6 h-6 text-primary-600"></i>
            </div>
        </div>
    </div>

    <div class="stat-card cursor-pointer hover:shadow-lg hover:border-success-200 transition-all group"
        data-action="openShareCapitalsModal" data-arg='[true]'>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Eligible Accounts</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($scEligibleCount) }}</p>
                <p class="text-xs text-gray-500 mt-1 flex items-center">
                    <i data-lucide="users" class="w-3 h-3 mr-1"></i>
                    Accounts with 10+ paid-up shares
                </p>
            </div>
            <div
                class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center group-hover:bg-success-200 transition-colors">
                <i data-lucide="users" class="w-6 h-6 text-success-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('financial.activity') }}">
    <input type="hidden" name="tab" value="share-capitals">
    <div class="card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="sc_search" value="{{ request('sc_search') }}"
                        placeholder="Search by member name..." class="input pl-10">
                </div>
            </div>
            <div class="flex gap-2">
                <select name="sc_type" class="select w-40" data-submit-on-change>
                    <option value="all">All Types</option>
                    <option value="Deposit" {{ request('sc_type') === 'Deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="Withdrawal" {{ request('sc_type') === 'Withdrawal' ? 'selected' : '' }}>Withdrawal
                    </option>
                </select>
                <select name="sc_status" class="select w-32" data-submit-on-change>
                    <option value="all">All Status</option>
                    <option value="Completed" {{ request('sc_status') === 'Completed' ? 'selected' : '' }}>Completed
                    </option>
                    <option value="Pending" {{ request('sc_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
        </div>
    </div>
</form>

<!-- Pending GCash Deposits (Verification Required) -->
@if(isset($pendingSCDeposits) && $pendingSCDeposits->count() > 0)
    <div class="card mb-6">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="clock" class="w-5 h-5 text-warning-500"></i>
                Pending Deposits
                <span
                    class="ml-2 px-2 py-0.5 bg-warning-100 text-warning-600 text-xs font-semibold rounded-full">{{ $pendingSCDeposits->count() }}</span>
            </h2>
            <p class="text-sm text-gray-500">GCash deposits pending verification and completion</p>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Shares</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingSCDeposits as $dep)
                        <tr class="sc-deposit-row cursor-pointer hover:bg-gray-50 transition-colors" data-id="{{ $dep->id }}"
                            data-member="{{ ($dep->shareCapitalAccount->user->first_name ?? '') . ' ' . ($dep->shareCapitalAccount->user->last_name ?? '') }}"
                            data-shares="{{ $dep->shares }}" data-amount="{{ number_format($dep->total_amount, 2) }}"
                            data-method="{{ $dep->payment_method ?? 'N/A' }}" data-ref="{{ $dep->reference_no ?? 'N/A' }}"
                            data-gcash-ref="{{ $dep->gcash_reference_no ?? '' }}"
                            data-gcash-number="{{ $dep->gcash_number ?? '' }}"
                            data-date="{{ $dep->created_at->format('M d, Y g:i A') }}"
                            data-proof="{{ $dep->gcash_proof_path ? url('/proof/' . ltrim($dep->gcash_proof_path, '/')) : '' }}"
                            data-note="{{ $dep->note ?? '' }}">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-xs text-primary-600 font-medium">
                                            {{ strtoupper(substr($dep->shareCapitalAccount->user->first_name ?? 'U', 0, 1) . substr($dep->shareCapitalAccount->user->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900">
                                        {{ $dep->shareCapitalAccount->user->first_name ?? 'Unknown' }}
                                        {{ $dep->shareCapitalAccount->user->last_name ?? '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-sm font-medium text-gray-900">{{ $dep->shares }} shares</td>
                            <td class="text-sm font-semibold text-gray-900">₱{{ number_format($dep->total_amount, 2) }}</td>
                            <td class="text-sm text-gray-600">{{ $dep->payment_method ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-600">{{ $dep->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Transactions Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Member Name</th>
                    <th>Shares</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scTransactions as $tx)
                    @php
                        $scRowStatus = strtolower($tx->status ?? '');
                        $scMemberName = ($tx->shareCapitalAccount->user->first_name ?? '') . ' ' . ($tx->shareCapitalAccount->user->last_name ?? '');
                        $scIsPendingDeposit = $scRowStatus === 'pending' && $tx->type === 'Deposit';
                        $scIsConversion = $tx->type === \App\Http\Controllers\ShareCapital::CONVERSION_TYPE;
                        $scIsCredit = in_array($tx->type, ['Deposit', 'Subscription'], true) || $scIsConversion;
                    @endphp
                    <tr @if($scRowStatus === 'completed' || $scRowStatus === 'voided') style="cursor:pointer;"
                        data-void="{{ $scRowStatus === 'voided' ? '1' : '0' }}" data-reason="{{ $tx->void_reason ?? '' }}"
                        data-id="{{ $tx->id }}" data-type="{{ $scIsConversion ? 'Converted from Savings' : $tx->type }}" ...
                        data-method="{{ $scIsConversion ? '—' : ($tx->payment_method ?? 'N/A') }}"
                        data-status="{{ $tx->status }}" data-member="{{ $scMemberName }}" data-shares="{{ $tx->shares }}"
                        data-amount="{{ $scRowStatus === 'voided' ? 0 : $tx->total_amount }}"
                        data-method="{{ $tx->payment_method ?? 'N/A' }}" data-ref="{{ $tx->reference_no ?? 'N/A' }}"
                        data-date="{{ $tx->transaction_date }}" data-action="openSCRow" data-arg='["|event|","|el|"]'
                    @elseif($scIsPendingDeposit) {{-- Same attributes as the Pending Deposits card so it opens the
                            verification modal --}} class="sc-deposit-row cursor-pointer hover:bg-gray-50 transition-colors"
                            data-id="{{ $tx->id }}" data-member="{{ $scMemberName }}" data-shares="{{ $tx->shares }}"
                            data-amount="{{ number_format($tx->total_amount, 2) }}"
                            data-method="{{ $tx->payment_method ?? 'N/A' }}" data-ref="{{ $tx->reference_no ?? 'N/A' }}"
                            data-gcash-ref="{{ $tx->gcash_reference_no ?? '' }}"
                            data-gcash-number="{{ $tx->gcash_number ?? '' }}"
                            data-date="{{ $tx->created_at->format('M d, Y g:i A') }}"
                            data-proof="{{ $tx->gcash_proof_path ? url('/proof/' . ltrim($tx->gcash_proof_path, '/')) : '' }}"
                        data-note="{{ $tx->note ?? '' }}" @endif>
                        <td class="text-sm text-gray-900">{{ $tx->created_at->format('M d, Y') }}</td>
                        <td class="text-sm text-gray-600">{{ $tx->created_at->format('g:i A') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-xs text-primary-600 font-medium">
                                        {{ strtoupper(substr($tx->shareCapitalAccount->user->first_name ?? 'U', 0, 1) . substr($tx->shareCapitalAccount->user->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-900">
                                    {{ $tx->shareCapitalAccount->user->first_name ?? 'Unknown' }}
                                    {{ $tx->shareCapitalAccount->user->last_name ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-sm font-medium text-gray-900">
                            {{ $scIsCredit ? '+' : '-' }}{{ $tx->shares }}
                        </td>
                        <td
                            class="text-sm font-semibold {{ $scRowStatus === 'voided' ? 'text-gray-400' : 'text-gray-900' }}">
                            @if($scRowStatus === 'voided')
                                <span>—</span>
                            @else
                                ₱{{ number_format($tx->total_amount, 2) }}
                            @endif
                        </td>
                        <td>
                            @if($scIsConversion)
                                <span class="badge badge-success">Converted from Savings</span>
                            @elseif($tx->type === 'Deposit')
                                <span class="badge badge-success">Deposit</span>
                            @else
                                <span class="badge badge-danger">Withdrawal</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-600">
                            {{ $scIsConversion ? '—' : ($tx->payment_method ?? 'N/A') }}
                        </td>
                        <td>
                            @php $scStatus = strtolower($tx->status ?? ''); @endphp
                            @if($scStatus === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($scStatus === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($scStatus === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($scStatus === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @elseif($scStatus === 'voided')
                                <span class="badge badge-danger">Returned</span>
                            @else
                                <span class="badge"
                                    style="background: #e2e8f0; color: #475569;">{{ ucfirst($tx->status ?? 'Unknown') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
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
    @if($scTransactions->hasPages())
        <div class="flex items-center justify-between mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">
                Showing {{ $scTransactions->firstItem() ?? 1 }} to
                {{ $scTransactions->lastItem() ?? $scTransactions->count() }} of {{ $scTransactions->total() }} transactions
            </p>
            <div class="flex items-center gap-1">
                @if($scTransactions->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <a href="{{ $scTransactions->previousPageUrl() }}"
                        class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                @endif

                @foreach($scTransactions->getUrlRange(max(1, $scTransactions->currentPage() - 2), min($scTransactions->lastPage(), $scTransactions->currentPage() + 2)) as $page => $url)
                    @if($page == $scTransactions->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($scTransactions->hasMorePages())
                    <a href="{{ $scTransactions->nextPageUrl() }}"
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

<!-- Share Capital Releases (Resigned Members) -->
@if(isset($pendingReleases) && $pendingReleases->count() > 0)
    <div class="card mb-6">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="log-out" class="w-5 h-5 text-orange-500"></i>
                Share Capital Releases
                <span
                    class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">{{ $pendingReleases->count() }}</span>
            </h2>
            <p class="text-sm text-gray-500">Approved resignations waiting for 60-day holding period to end</p>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Shares</th>
                        <th>Amount</th>
                        <th>Release Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReleases as $pr)
                        @php
                            $scAccount = $pr->user->shareCapitalAccount ?? null;
                            $canRelease = now()->gte($pr->release_date);
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-red-400 flex items-center justify-center">
                                        <span
                                            class="text-white font-bold text-sm">{{ strtoupper(substr($pr->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($pr->user->last_name ?? '', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $pr->user->first_name ?? '' }}
                                        {{ $pr->user->last_name ?? '' }}</span>
                                </div>
                            </td>
                            <td class="text-sm font-medium text-gray-900">{{ $scAccount->total_shares ?? 0 }}</td>
                            <td class="text-sm font-semibold text-gray-900">
                                ₱{{ number_format($scAccount->total_amount ?? 0, 2) }}</td>
                            <td class="text-sm text-gray-600">
                                {{ $pr->release_date ? $pr->release_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                @if($canRelease)
                                    <span class="badge badge-success">Ready</span>
                                @else
                                    <span class="badge badge-warning">{{ now()->diffInDays($pr->release_date) }} days left</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('resignation.release', $pr->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-xs px-2 py-1" {{ $canRelease ? '' : 'disabled' }} style="{{ $canRelease ? '' : 'opacity:0.5;cursor:not-allowed;' }}">
                                        <i data-lucide="coins" class="w-3 h-3"></i>
                                        Disburse
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif