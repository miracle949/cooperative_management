@extends('layouts.admin')

@section('title', 'Dividend Management - CoopAdmin')

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
                    <span class="text-gray-900 font-medium">Annual Distribution</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Annual Distribution Management</h1>
            <p class="text-sm text-gray-500">RA 9520 compliant dividend & patronage refund distribution system</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('dividends.index') }}" class="flex items-center gap-2">
                <select name="year" data-submit-on-change class="select w-32">
                    @for ($y = $currentYear; $y >= $currentYear - 10; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                    @foreach ($years as $y)
                        @if ($y < $currentYear - 10)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endif
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$distribution)
        <!-- Section: Net Surplus Input -->
        <div class="card p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                    <i data-lucide="calculator" class="w-5 h-5 text-primary-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Generate Annual Distribution</h2>
                    <p class="text-sm text-gray-500">Enter the annual net surplus to compute member dividends per RA 9520</p>
                </div>
            </div>

            <form method="POST" action="{{ route('dividends.calculate') }}" class="max-w-xl">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Annual Net Surplus (₱)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
                        <input type="number" name="net_surplus" id="netSurplusInput" step="0.01" min="1"
                            class="input pl-10 text-lg font-bold text-gray-900" placeholder="0.00"
                            data-action="updateBreakdownPreview" data-trigger="input" required>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">The net surplus for fiscal year {{ $year }}</p>
                </div>

                <!-- Live breakdown preview -->
                <div id="breakdownPreview" class="bg-gray-50 rounded-lg p-4 mb-4 hidden">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Statutory Breakdown Preview (RA 9520)</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Reserve Fund ({{ $reserveFundPercentage ?? 10 }}%)</span>
                            <span class="font-semibold" id="previewReserve">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>CETF ({{ $cetfPercentage ?? 10 }}%)</span>
                            <span class="font-semibold" id="previewCETF">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Community Dev. Fund ({{ $cdfPercentage ?? 3 }}%)</span>
                            <span class="font-semibold" id="previewCDF">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Optional Fund ({{ $optionalFundPercentage ?? 7 }}%)</span>
                            <span class="font-semibold" id="previewOptional">₱0.00</span>
                        </div>
                        <hr class="border-gray-300">
                        <div class="flex justify-between text-gray-500">
                            <span>Statutory Total ({{ $statutoryTotalPercentage ?? 30 }}%)</span>
                            <span class="font-semibold" id="previewStatutoryTotal">₱0.00</span>
                        </div>
                        <hr class="border-gray-300">
                        <div class="flex justify-between text-gray-500">
                            <span>Remaining Surplus ({{ $remainingSurplusPercentage ?? 70 }}%)</span>
                            <span class="font-semibold" id="previewRemaining">₱0.00</span>
                        </div>
                        <hr class="border-gray-300">
                        <div class="flex justify-between text-success-700 font-semibold">
                            <span id="previewDividendLabel">Dividend Pool ({{ $dividendFundPercentage }}% of remaining)</span>
                            <span id="previewDividendPool">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-warning-700 font-semibold">
                            <span id="previewPatronageLabel">Patronage Refund Pool ({{ 100 - $dividendFundPercentage }}% of remaining)</span>
                            <span id="previewPatronage">₱0.00</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-lucide="wand-2" class="w-4 h-4"></i>
                    Generate Distribution
                </button>
            </form>
        </div>
    @else
        <!-- Statutory Breakdown Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Net Surplus</p>
                    <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-primary-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($distribution->net_surplus, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Fiscal Year {{ $year }}</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Dividend Fund</p>
                    <div class="flex items-center gap-1">
                        <button data-action="openDividendFundModal" class="p-1 rounded hover:bg-gray-100 transition-colors" title="Edit Fund Percentage">
                            <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-400"></i>
                        </button>
                        <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                            <i data-lucide="gift" class="w-4 h-4 text-success-600"></i>
                        </div>
                    </div>
                </div>
                <p id="fund-amount" class="text-2xl font-bold text-success-700">₱{{ number_format($distribution->dividend_pool, 2) }}</p>
                <p id="fund-pct-label" class="text-xs text-gray-400 mt-1">{{ $dividendFundPercentage }}% of remaining surplus</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Patronage Refund Fund</p>
                    <div class="flex items-center gap-1">
                        <button data-action="openPatronageFundModal" class="p-1 rounded hover:bg-gray-100 transition-colors" title="Edit Patronage Fund Percentage">
                            <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-400"></i>
                        </button>
                        <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                            <i data-lucide="percent" class="w-4 h-4 text-warning-600"></i>
                        </div>
                    </div>
                </div>
                <p id="patronage-amount" class="text-2xl font-bold text-warning-700">₱{{ number_format($distribution->patronage_refund_pool, 2) }}</p>
                <p id="patronage-pct-label" class="text-xs text-gray-400">{{ $patronageFundPercentage }}% of remaining surplus</p>
            </div>

            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Distribution Status</p>
                    <div class="w-8 h-8 rounded-lg bg-{{ $distribution->status === 'released' ? 'success' : 'warning' }}-100 flex items-center justify-center">
                        <i data-lucide="{{ $distribution->status === 'released' ? 'check-circle' : 'clock' }}" class="w-4 h-4 text-{{ $distribution->status === 'released' ? 'success' : 'warning' }}-600"></i>
                    </div>
                </div>
                @if ($distribution->status === 'draft')
                    <span class="badge badge-warning">Draft</span>
                @elseif ($distribution->status === 'released')
                    <span class="badge badge-success">Released</span>
                @endif
                <p class="text-xs text-gray-400 mt-1">
                    {{ $approvedCount }} div approved · {{ $disbursedCount }} disbursed<br>
                    {{ $patronageApprovedCount }} pat approved · {{ $patronageDisbursedCount }} disbursed
                </p>
            </div>
        </div>

        <!-- Statutory Funds -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <p class="text-sm text-gray-500">Reserve Fund</p>
                <p class="text-lg font-bold text-gray-900">₱{{ number_format($distribution->reserve_fund, 2) }}</p>
                <p class="text-xs text-gray-400">{{ $distribution->reserve_fund_percentage ?? 10 }}%</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">CETF</p>
                <p class="text-lg font-bold text-gray-900">₱{{ number_format($distribution->education_fund, 2) }}</p>
                <p class="text-xs text-gray-400">{{ $distribution->cetf_percentage ?? 10 }}%</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Community Dev.</p>
                <p class="text-lg font-bold text-gray-900">₱{{ number_format($distribution->community_fund, 2) }}</p>
                <p class="text-xs text-gray-400">{{ $distribution->cdf_percentage ?? 3 }}%</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Optional Fund</p>
                <p class="text-lg font-bold text-gray-900">₱{{ number_format($distribution->optional_fund, 2) }}</p>
                <p class="text-xs text-gray-400">{{ $distribution->optional_fund_percentage ?? 7 }}%</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Total Statutory Allocation</p>
                <p class="text-lg font-bold text-gray-900">{{ $distribution->statutory_total_percentage ?? 30 }}%</p>
                <p class="text-xs text-gray-400">of net surplus</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Remaining Surplus</p>
                <p class="text-lg font-bold text-success-700">₱{{ number_format($distribution->remaining_surplus, 2) }}</p>
                <p class="text-xs text-gray-400">{{ number_format(100 - ($distribution->statutory_total_percentage ?? 30), 2) }}% of net surplus</p>
            </div>
        </div>

        <!-- Action Bar -->
        <div id="disburse-card" class="card p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="send" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Distribution Actions</h3>
                        <p class="text-xs text-gray-500">
                            {{ $approvedCount }} dividend(s) and {{ $patronageApprovedCount }} patronage refund(s) ready
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if ($distribution->status !== 'released' && ($approvedCount > 0 || $patronageApprovedCount > 0))
                        @if ($approvedCount > 0)
                            <button data-action="disburseAllDividends" id="disburse-all-btn" class="btn btn-primary btn-sm">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Disburse Dividends
                            </button>
                        @endif
                        @if ($patronageApprovedCount > 0)
                            <button data-action="disburseAllPatronage" id="disburse-patronage-btn" class="btn btn-warning btn-sm">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Disburse Patronage
                            </button>
                        @endif
                        @if ($approvedCount > 0 && $patronageApprovedCount > 0)
                            <button data-action="disburseBoth" id="disburse-both-btn" class="btn btn-success btn-sm">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                Disburse Both
                            </button>
                        @endif
                    @endif
                    @if ($distribution->status !== 'released')
                        <button data-action="resetDistribution" class="btn btn-sm" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Patronage Actions -->
        <div class="card p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                        <i data-lucide="percent" class="w-5 h-5 text-warning-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Patronage Refund Distribution</h3>
                        <p class="text-xs text-gray-500">
                            @if($patronageApprovedCount > 0)
                                {{ $patronageApprovedCount }} approved patronage refund(s) ready
                            @else
                                Generate patronage refund allocations based on member patronage
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button data-action="generatePatronageRefunds" id="gen-patronage-btn" class="btn btn-warning btn-sm">
                        <i data-lucide="wand-2" class="w-4 h-4"></i>
                        Generate Patronage Refunds
                    </button>
                </div>
            </div>
        </div>

        <!-- Dividend Table -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">
                <i data-lucide="gift" class="w-5 h-5 inline text-success-600"></i>
                Dividend Distribution
            </h3>
            <div id="dividends-table-container">
                <div class="card">
                    <div class="p-8 text-center text-gray-400">
                        <i data-lucide="loader" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading dividends table...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patronage Table -->
        <div>
            <h3 class="text-lg font-bold text-gray-900 mb-3">
                <i data-lucide="percent" class="w-5 h-5 inline text-warning-600"></i>
                Patronage Refund Distribution
            </h3>
            <div id="patronage-table-container">
                <div class="card">
                    <div class="p-8 text-center text-gray-400">
                        <i data-lucide="loader" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading patronage refund table...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Dividend Fund Percentage Modal -->
    <div id="dividendFundModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="percent" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Edit Fund Split</h2>
                            <p class="text-xs text-gray-500">Adjust fund percentages for {{ $year }}</p>
                        </div>
                    </div>
                    <button data-action="closeDividendFundModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <form id="dividendFundForm" class="p-6">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dividend Fund (%)</label>
                    <div class="relative">
                        <input type="number" name="dividend_fund_percentage" id="fundPercentageInput"
                            step="0.01" min="1" max="99"
                            value="{{ $dividendFundPercentage }}"
                            class="input pr-8" required>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">%</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Remaining {{ 100 - $dividendFundPercentage }}% goes to Patronage Refund Pool</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-action="closeDividendFundModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 transition-colors flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Save & Recalculate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patronage Fund Percentage Modal -->
    <div id="patronageFundModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                            <i data-lucide="percent" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Edit Patronage Fund</h2>
                            <p class="text-xs text-gray-500">Adjust the patronage refund fund percentage for {{ $year }}</p>
                        </div>
                    </div>
                    <button data-action="closePatronageFundModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <form id="patronageFundForm" class="p-6">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patronage Refund Fund (%)</label>
                    <div class="relative">
                        <input type="number" name="patronage_fund_percentage" id="patronageFundPercentageInput"
                            step="0.01" min="1" max="99"
                            value="{{ $patronageFundPercentage }}"
                            class="input pr-8" required>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">%</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Remaining {{ $dividendFundPercentage }}% goes to Dividend Fund</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-action="closePatronageFundModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-warning-600 text-white font-medium rounded-lg hover:bg-warning-700 transition-colors flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Save & Recalculate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patronage Breakdown Modal -->
    <div id="patronageBreakdownModal" class="modal-overlay hidden" style="display:none;">
        <div class="modal max-w-3xl">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                            <i data-lucide="user" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900" id="breakdown-member-name">Member</h2>
                            <p class="text-xs text-gray-500" id="breakdown-subtitle">Patronage Breakdown</p>
                        </div>
                    </div>
                    <button data-action="closePatronageBreakdownModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto" id="breakdown-content">
                <div class="flex items-center justify-center py-8">
                    <i data-lucide="loader" class="w-6 h-6 animate-spin text-gray-400"></i>
                    <span class="ml-2 text-sm text-gray-500">Loading breakdown...</span>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function() {
            loadDividendsTable();
            loadPatronageTable();
        });

        function loadDividendsTable() {
            const year = '{{ $year }}';
            const url = '{{ route("dividends.table-partial") }}?year=' + encodeURIComponent(year);
            const container = document.getElementById('dividends-table-container');
            if (!container) return;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { container.innerHTML = html; if (typeof lucide !== 'undefined') lucide.createIcons(); })
            .catch(e => { container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load.</p></div>'; });
        }

        function loadPatronageTable() {
            const year = '{{ $year }}';
            const url = '{{ route("dividends.patronage-partial") }}?year=' + encodeURIComponent(year);
            const container = document.getElementById('patronage-table-container');
            if (!container) return;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { container.innerHTML = html; if (typeof lucide !== 'undefined') lucide.createIcons(); })
            .catch(e => { container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load.</p></div>'; });
        }

        function loadPatronagePage(url) {
            const container = document.getElementById('patronage-table-container');
            if (!container) return;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { container.innerHTML = html; if (typeof lucide !== 'undefined') lucide.createIcons(); })
            .catch(e => { container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load.</p></div>'; });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-patronage-page');
            if (btn && btn.dataset.pageUrl) loadPatronagePage(btn.dataset.pageUrl);
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-dividends-page');
            if (btn && btn.dataset.pageUrl && typeof loadDividendsPage === 'function') loadDividendsPage(btn.dataset.pageUrl);
        });

        // ─── Breakdown Preview ─────────────────────────────────────────────────
        function updateBreakdownPreview() {
            const input = document.getElementById('netSurplusInput');
            const preview = document.getElementById('breakdownPreview');
            const val = parseFloat(input.value) || 0;

            if (val > 0) {
                preview.classList.remove('hidden');
                const reserve = val * ({{ $reserveFundPercentage ?? 10 }}/100), cetf = val * ({{ $cetfPercentage ?? 10 }}/100), cdf = val * ({{ $cdfPercentage ?? 3 }}/100), optional = val * ({{ $optionalFundPercentage ?? 7 }}/100);
                const statutoryTotal = reserve + cetf + cdf + optional;
                const remaining = val - statutoryTotal;
                const divPct = {{ $dividendFundPercentage / 100 }};
                const dividendPool = remaining * divPct;
                const patronage = remaining * ({{ $patronageFundPercentage / 100 }});

                document.getElementById('previewReserve').textContent = '₱' + reserve.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewCETF').textContent = '₱' + cetf.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewCDF').textContent = '₱' + cdf.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewOptional').textContent = '₱' + optional.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewStatutoryTotal').textContent = '₱' + statutoryTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewRemaining').textContent = '₱' + remaining.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewDividendPool').textContent = '₱' + dividendPool.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewPatronage').textContent = '₱' + patronage.toLocaleString('en-PH', {minimumFractionDigits: 2});
            } else {
                preview.classList.add('hidden');
            }
        }

        // ─── Dividend CRUD ─────────────────────────────────────────────────────
        function updateDividendAmount(id, value) {
            const amount = parseFloat(value);
            if (isNaN(amount) || amount < 0) return;
            fetch('/admin/dividends/' + id + '/update', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}' },
                body: JSON.stringify({ approved_amount: amount })
            }).then(r => r.json()).then(d => { if (d.success) showToast('Success', d.message); else showToast('Error', d.message, 'error'); });
        }

        function approveDividend(id) {
            if (!confirm('Approve this dividend?')) return;
            fetch('/admin/dividends/' + id + '/approve', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}' }
            }).then(r => r.json()).then(d => { if (d.success) { showToast('Success', d.message); loadDividendsTable(); } else showToast('Error', d.message, 'error'); });
        }

        function disburseDividend(id) {
            if (!confirm('Disburse this dividend to savings?')) return;
            fetch('/admin/dividends/' + id + '/disburse', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ disbursement_type: 'savings' })
            }).then(r => r.json()).then(d => {
                if (d.success) { showToast('Success', d.message); if (d.html) { document.getElementById('dividends-table-container').innerHTML = d.html; if (typeof lucide !== 'undefined') lucide.createIcons(); } else loadDividendsTable(); }
                else showToast('Error', d.message, 'error');
            });
        }

        function disburseAllDividends() {
            if (!confirm('Disburse all approved dividends?')) return;
            const btn = document.getElementById('disburse-all-btn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }
            fetch('/admin/dividends/disburse-all/{{ $year }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ disbursement_type: 'savings' })
            }).then(r => r.json()).then(d => {
                if (d.success) { showToast('Success', d.message); location.reload(); }
                else { showToast('Error', d.message, 'error'); if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse Dividends'; if (typeof lucide !== 'undefined') lucide.createIcons(); } }
            });
        }

        // ─── Patronage Refund Functions ────────────────────────────────────────
        function generatePatronageRefunds() {
            if (!confirm('Generate patronage refund allocations for {{ $year }}?')) return;
            window.location.href = '{{ route("dividends.calculate-patronage") }}?year={{ $year }}';
        }

        function updatePatronageAmount(id, value) {
            const amount = parseFloat(value);
            if (isNaN(amount) || amount < 0) return;
            fetch('/admin/dividends/patronage/' + id + '/update', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}' },
                body: JSON.stringify({ amount: amount })
            }).then(r => r.json()).then(d => { if (d.success) showToast('Success', d.message); else showToast('Error', d.message, 'error'); });
        }

        function approvePatronageRefund(id) {
            if (!confirm('Approve this patronage refund?')) return;
            fetch('/admin/dividends/patronage/' + id + '/approve', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}' }
            }).then(r => r.json()).then(d => { if (d.success) { showToast('Success', d.message); loadPatronageTable(); } else showToast('Error', d.message, 'error'); });
        }

        function disbursePatronageRefund(id) {
            if (!confirm('Disburse this patronage refund to savings?')) return;
            fetch('/admin/dividends/patronage/' + id + '/disburse', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(d => {
                if (d.success) { showToast('Success', d.message); if (d.html) { document.getElementById('patronage-table-container').innerHTML = d.html; if (typeof lucide !== 'undefined') lucide.createIcons(); } else loadPatronageTable(); }
                else showToast('Error', d.message, 'error');
            });
        }

        function disburseAllPatronage() {
            if (!confirm('Disburse all approved patronage refunds?')) return;
            const btn = document.getElementById('disburse-patronage-btn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }
            fetch('/admin/dividends/patronage/disburse-all/{{ $year }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(d => {
                if (d.success) { showToast('Success', d.message); location.reload(); }
                else { showToast('Error', d.message, 'error'); if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse Patronage'; if (typeof lucide !== 'undefined') lucide.createIcons(); } }
            });
        }

        function disburseBoth() {
            if (!confirm('Disburse BOTH dividends and patronage refunds to savings?')) return;
            const btn = document.getElementById('disburse-both-btn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing both...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }
            fetch('/admin/dividends/disburse-both/{{ $year }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(d => {
                if (d.success) { showToast('Success', d.message); location.reload(); }
                else { showToast('Error', d.message, 'error'); if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4"></i> Disburse Both'; if (typeof lucide !== 'undefined') lucide.createIcons(); } }
            });
        }

        function resetDistribution() {
            if (!confirm('Reset the annual distribution for {{ $year }}? Approved records will be reverted to pending.')) return;
            window.location.href = '{{ route("dividends.reset", $year) }}';
        }

        // ─── Fund Percentage Modal ─────────────────────────────────────────────
        function openDividendFundModal() {
            document.getElementById('dividendFundModal').classList.remove('hidden');
            document.getElementById('dividendFundModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        function closeDividendFundModal() {
            document.getElementById('dividendFundModal').classList.add('hidden');
            document.getElementById('dividendFundModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.getElementById('dividendFundForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('{{ route("dividends.update-fund-percentage") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    closeDividendFundModal();
                    showToast('Success', data.message);
                    document.getElementById('fund-amount').textContent = '₱' + data.dividend_pool;
                    document.getElementById('fund-pct-label').textContent = data.dividend_fund_percentage + '% of remaining surplus';
                    document.getElementById('patronage-amount').textContent = '₱' + data.patronage_refund_pool;
                    document.getElementById('patronage-pct-label').textContent = data.patronage_fund_percentage + '% of remaining surplus';
                    loadDividendsTable();
                    loadPatronageTable();
                } else {
                    showToast('Error', data.message || 'Update failed', 'error');
                }
            }).catch(e => { showToast('Error', 'An error occurred', 'error'); });
        });

        // ─── Patronage Fund Percentage Modal ───────────────────────────────────
        function openPatronageFundModal() {
            document.getElementById('patronageFundModal').classList.remove('hidden');
            document.getElementById('patronageFundModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        function closePatronageFundModal() {
            document.getElementById('patronageFundModal').classList.add('hidden');
            document.getElementById('patronageFundModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.getElementById('patronageFundForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const patronagePct = parseFloat(formData.get('patronage_fund_percentage')) || 0;
            const dividendPct = Math.round((100 - patronagePct) * 100) / 100;
            formData.set('dividend_fund_percentage', dividendPct);

            fetch('{{ route("dividends.update-fund-percentage") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    closePatronageFundModal();
                    showToast('Success', data.message);
                    document.getElementById('fund-amount').textContent = '₱' + data.dividend_pool;
                    document.getElementById('fund-pct-label').textContent = data.dividend_fund_percentage + '% of remaining surplus';
                    document.getElementById('patronage-amount').textContent = '₱' + data.patronage_refund_pool;
                    document.getElementById('patronage-pct-label').textContent = data.patronage_fund_percentage + '% of remaining surplus';
                    loadDividendsTable();
                    loadPatronageTable();
                } else {
                    showToast('Error', data.message || 'Update failed', 'error');
                }
            }).catch(e => { showToast('Error', 'An error occurred', 'error'); });
        });

        function openPatronageBreakdown(id) {
            var modal = document.getElementById('patronageBreakdownModal');
            var content = document.getElementById('breakdown-content');
            document.getElementById('breakdown-member-name').textContent = 'Loading...';
            document.getElementById('breakdown-subtitle').textContent = 'Patronage Breakdown';
            content.innerHTML = '<div class="flex items-center justify-center py-8"><i data-lucide="loader" class="w-6 h-6 animate-spin text-gray-400"></i><span class="ml-2 text-sm text-gray-500">Loading...</span></div>';
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            fetch('/admin/dividends/patronage/' + id + '/breakdown', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) { content.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load.</p>'; return; }
                var r = data.record, t = data.totals;
                document.getElementById('breakdown-member-name').textContent = r.member_name;
                var basisLabel = data.basis === 'net_repayment' ? 'Net Repayment (excl. late fees)' : 'Total Repayment (incl. late fees)';
                document.getElementById('breakdown-subtitle').textContent = 'FY ' + r.year + ' · ' + r.status.charAt(0).toUpperCase() + r.status.slice(1) + ' · Basis: ' + basisLabel;
                var h = '<div class="grid grid-cols-3 gap-3 mb-6">';
                h += '<div class="p-3 bg-primary-50 rounded-lg text-center"><p class="text-xs text-primary-600 font-medium">Total Patronage</p><p class="text-lg font-bold text-primary-700">₱' + formatBreakdownNum(r.total_patronage) + '</p></div>';
                h += '<div class="p-3 bg-warning-50 rounded-lg text-center"><p class="text-xs text-warning-600 font-medium">Member Share</p><p class="text-lg font-bold text-warning-700">' + (r.allocation_ratio * 100).toFixed(2) + '%</p></div>';
                h += '<div class="p-3 bg-success-50 rounded-lg text-center"><p class="text-xs text-success-600 font-medium">Refund Amount</p><p class="text-lg font-bold text-success-700">₱' + formatBreakdownNum(r.amount) + '</p></div></div>';
                h += '<div class="mb-6"><h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><i data-lucide="landmark" class="w-4 h-4 text-primary-600"></i> Loan Repayment Patronage</h3>';
                if (data.loan_repayments.length > 0) {
                    h += '<div class="border border-gray-200 rounded-lg overflow-hidden"><table class="w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Date</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Type</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Amount Paid</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Principal</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Interest</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Service Fee</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Late Fee</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Patronage</th></tr></thead><tbody>';
                    data.loan_repayments.forEach(function(rep) {
                        var badge = rep.is_fallback ? '<span class="ml-1 text-[10px] font-semibold text-gray-400">legacy</span>' : '';
                        h += '<tr class="border-t border-gray-100"><td class="px-3 py-2 text-gray-700">' + rep.date + '</td><td class="px-3 py-2 text-gray-500">' + (rep.loan_type||'-') + '</td><td class="px-3 py-2 text-right text-gray-900">₱' + formatBreakdownNum(rep.amount_paid) + '</td><td class="px-3 py-2 text-right text-gray-500">₱' + formatBreakdownNum(rep.principal_paid) + '</td><td class="px-3 py-2 text-right text-success-600 font-medium">₱' + formatBreakdownNum(rep.interest_paid) + '</td><td class="px-3 py-2 text-right text-gray-500">₱' + formatBreakdownNum(rep.service_fee_paid) + '</td><td class="px-3 py-2 text-right ' + (rep.late_fee > 0 ? 'text-danger-600' : 'text-gray-500') + '">₱' + formatBreakdownNum(rep.late_fee) + '</td><td class="px-3 py-2 text-right font-semibold text-primary-700">₱' + formatBreakdownNum(rep.patronage) + badge + '</td></tr>';
                    });
                    var totalAmt = data.loan_repayments.reduce(function(s,r){return s+r.amount_paid;},0);
                    h += '<tr class="bg-gray-50 font-semibold"><td class="px-3 py-2" colspan="2">Totals</td><td class="px-3 py-2 text-right text-gray-900">₱' + formatBreakdownNum(totalAmt) + '</td><td class="px-3 py-2 text-right text-gray-500">₱' + formatBreakdownNum(t.interest+t.service_fee+t.late_fee) + '</td><td class="px-3 py-2 text-right text-success-600">₱' + formatBreakdownNum(t.interest) + '</td><td class="px-3 py-2 text-right text-gray-500">₱' + formatBreakdownNum(t.service_fee) + '</td><td class="px-3 py-2 text-right text-gray-500">₱' + formatBreakdownNum(t.late_fee) + '</td><td class="px-3 py-2 text-right text-primary-700">₱' + formatBreakdownNum(t.loan_patronage) + '</td></tr>';
                    h += '</tbody></table></div>';
                    var basisNote = data.basis === 'net_repayment' ? 'Interest + Service Fee (late fees excluded)' : 'Interest + Service Fee + Late Fee';
                    h += '<p class="text-xs text-gray-500 mt-2">Patronage from loans = ' + basisNote + ' (principal excluded). Legacy records without an income breakdown count the full amount paid.</p>';
                } else { h += '<p class="text-sm text-gray-500 py-3 px-4 bg-gray-50 rounded-lg">No loan repayments recorded for this year.</p>'; }
                h += '</div>';
                h += '<div class="mb-6"><h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4 text-warning-600"></i> Additional Patronage Records</h3>';
                if (data.additional_records.length > 0) {
                    h += '<div class="border border-gray-200 rounded-lg overflow-hidden"><table class="w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Source</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Description</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500">Amount</th></tr></thead><tbody>';
                    data.additional_records.forEach(function(rec) {
                        h += '<tr class="border-t border-gray-100"><td class="px-3 py-2 text-gray-700 font-medium">' + (rec.source||'-') + '</td><td class="px-3 py-2 text-gray-500">' + (rec.description||'-') + '</td><td class="px-3 py-2 text-right text-warning-600 font-medium">₱' + formatBreakdownNum(rec.amount) + '</td></tr>';
                    });
                    h += '<tr class="bg-gray-50 font-semibold"><td class="px-3 py-2" colspan="2">Total Additional Patronage</td><td class="px-3 py-2 text-right text-warning-600">₱' + formatBreakdownNum(t.additional_patronage) + '</td></tr>';
                    h += '</tbody></table></div>';
                } else { h += '<p class="text-sm text-gray-500 py-3 px-4 bg-gray-50 rounded-lg">No additional patronage records for this year.</p>'; }
                h += '</div>';
                h += '<div class="p-4 bg-primary-50 rounded-lg border border-primary-100"><div class="flex justify-between items-center"><p class="text-xs text-primary-600 font-medium">Total Patronage (Loan + Additional)</p><p class="text-lg font-bold text-primary-700">₱' + formatBreakdownNum(t.total_patronage) + '</p></div>';
                if (data.fallback_count > 0) { h += '<p class="text-xs text-gray-400 mt-1">' + data.fallback_count + ' legacy repayment(s) used the amount-paid fallback.</p>'; }
                if (!t.matches_record) { h += '<p class="text-xs text-danger-600 mt-1">Note: this total differs from the stored patronage (' + formatBreakdownNum(r.total_patronage) + ') — recalibrate the distribution to sync.</p>'; }
                h += '</div>';
                content.innerHTML = h;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }).catch(function() { content.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load.</p>'; });
        }
        function closePatronageBreakdownModal() {
            var m = document.getElementById('patronageBreakdownModal');
            m.classList.add('hidden'); m.style.display = 'none'; document.body.style.overflow = 'auto';
        }
        function formatBreakdownNum(num) { return parseFloat(num||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    </script>
@endsection
