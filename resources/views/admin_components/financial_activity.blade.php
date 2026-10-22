@extends('layouts.admin')

@section('title', 'Finance - CoopAdmin')

@section('content')
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
                    <span class="text-gray-900 font-medium">Finance</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Finance</h1>
        <p class="text-sm text-gray-500">Manage loan interest rates, penalties, dividend distributions, savings, and share capital</p>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        .ts-wrapper .ts-dropdown {
            z-index: 2 !important;
        }
        .modal > .modal-header {
            position: sticky;
            top: 0;
            z-index: 10000 !important;
        }
    </style>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 p-1 bg-gray-100 rounded-xl w-fit">
        <button class="tab-btn px-5 py-2 text-sm font-medium rounded-lg transition-all"
                data-tab="savings"
                data-action="switchFinanceTab" data-arg='["savings","|el|"]'>
            Savings
        </button>
        <button class="tab-btn px-5 py-2 text-sm font-medium rounded-lg transition-all"
                data-tab="share-capitals"
                data-action="switchFinanceTab" data-arg='["share-capitals","|el|"]'>
            Share Capital
        </button>
        @if(\App\Services\SoDGuard::isGeneralManager())
        <button class="tab-btn px-5 py-2 text-sm font-medium rounded-lg transition-all"
                data-tab="dividends"
                data-action="switchFinanceTab" data-arg='["dividends","|el|"]'>
            Disbursal Management
        </button>
        @endif
        <button class="tab-btn px-5 py-2 text-sm font-medium rounded-lg transition-all"
                data-tab="patronage-records"
                data-action="switchFinanceTab" data-arg='["patronage-records","|el|"]'>
            Patronage Refund
        </button>
    </div>

    <!-- Tab: Dividends -->
    <div id="tab-dividends" class="tab-content hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Disbursal Management</h2>
                <p class="text-sm text-gray-500">RA 9520 compliant dividend distribution and disbursement system</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('financial.activity') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="dividends">
                    <select name="year" data-submit-on-change class="select w-32">
                        @for($y = $currentYear; $y >= $currentYear - 10; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                        @foreach($years as $y)
                            @if($y < $currentYear - 10)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endif
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if(!$distribution)
            <!-- Generate Dividend Calculations -->
            <div class="card p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="calculator" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Generate Dividend Calculations</h2>
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
                            <div class="flex justify-between text-success-700 font-semibold">
                                <span>Dividend Pool ({{ $dividendFundPercentage ?? 60 }}% of remaining)</span>
                                <span id="previewDividendPool">₱0.00</span>
                            </div>
                            <div class="flex justify-between text-warning-700 font-semibold">
                                <span>Patronage Refund Pool ({{ $patronageFundPercentage ?? 40 }}% of remaining)</span>
                                <span id="previewPatronage">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="wand-2" class="w-4 h-4"></i>
                        Generate Calculations
                    </button>
                </form>
            </div>
        @else
            <!-- Statutory Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
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
                        <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                            <i data-lucide="gift" class="w-4 h-4 text-success-600"></i>
                        </div>
                    </div>
                    <p id="fund-amount" class="text-2xl font-bold text-success-700">₱{{ number_format($distribution->dividend_pool, 2) }}</p>
                    <p id="fund-pct-label" class="text-xs text-gray-400 mt-1">{{ $dividendFundPercentage }}% of remaining surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Distribution Status</p>
                        @if($approvedCount > 0)
                            <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                                <i data-lucide="clock" class="w-4 h-4 text-warning-600"></i>
                            </div>
                        @elseif($disbursedCount > 0)
                            <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success-600"></i>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                <i data-lucide="inbox" class="w-4 h-4 text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                    @if($approvedCount > 0)
                        <span class="badge badge-warning">Pending Disbursement</span>
                    @elseif($disbursedCount > 0)
                        <span class="badge badge-success">Fully Distributed</span>
                    @else
                        <span class="badge" style="background-color: #f3f4f6; color: #6b7280;">No Dividends</span>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $approvedCount }} approved ·
                        {{ $disbursedCount }} disbursed
                    </p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Reserve Fund</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($distribution->reserve_fund, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $distribution->reserve_fund_percentage ?? 10 }}% of net surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Cooperative Education and Training Fund</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($distribution->education_fund, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $distribution->cetf_percentage ?? 10 }}% of net surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Community Dev. Fund</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($distribution->community_fund, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $distribution->cdf_percentage ?? 3 }}% of net surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Optional Fund</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($distribution->optional_fund, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $distribution->optional_fund_percentage ?? 7 }}% of net surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Patronage Refund Pool</p>
                        <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                            <i data-lucide="percent" class="w-4 h-4 text-warning-600"></i>
                        </div>
                    </div>
                    <p id="patronage-amount" class="text-2xl font-bold text-warning-700">₱{{ number_format($distribution->patronage_refund_pool, 2) }}</p>
                    <p id="patronage-pct-label" class="text-xs text-gray-400">{{ $patronageFundPercentage }}% of remaining surplus</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm text-gray-500">Patronage Status</p>
                        @if($patronageApprovedCount > 0)
                            <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                                <i data-lucide="clock" class="w-4 h-4 text-warning-600"></i>
                            </div>
                        @elseif($patronageDisbursedCount > 0)
                            <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success-600"></i>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                <i data-lucide="inbox" class="w-4 h-4 text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                    @if($patronageApprovedCount > 0)
                        <span class="badge badge-warning">Pending Disbursement</span>
                    @elseif($patronageDisbursedCount > 0)
                        <span class="badge badge-success">Fully Distributed</span>
                    @else
                        <span class="badge" style="background-color: #f3f4f6; color: #6b7280;">Not Generated</span>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $patronageApprovedCount }} approved ·
                        {{ $patronageDisbursedCount }} disbursed
                    </p>
                </div>
            </div>

            <!-- Disbursement Action -->
            <div id="disburse-card" class="card p-4 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="send" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Disburse Dividends</h3>
                            <p id="disburse-count-text" class="text-xs text-gray-500">
                                {{ $approvedCount }} approved dividend(s) ready for disbursement
                            </p>
                        </div>
                    </div>
                    <div id="disburse-btn-container">
                        @if($pendingCount > 0)
                            <button data-action="approveAllDividends" id="approve-all-btn" class="btn btn-warning btn-lg">
                                <i data-lucide="badge-check" class="w-4 h-4"></i>
                                Approve All
                            </button>
                        @endif
                        @if($approvedCount > 0)
                            <button data-action="disburseAllDividends" id="disburse-all-btn" class="btn btn-primary btn-lg">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Disburse All
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Members Dividend Table -->
            <div id="dividends-table-container">
                <div class="card">
                    <div class="p-8 text-center text-gray-400">
                        <i data-lucide="loader" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading dividends table...</p>
                    </div>
                </div>
            </div>

            <!-- Patronage Refund Actions -->
            <div class="mt-6 mb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                            <i data-lucide="percent" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Patronage Refund Distribution</h3>
                            <p id="patronage-action-text" class="text-xs text-gray-500">
                                @if($patronageApprovedCount > 0)
                                    {{ $patronageApprovedCount }} approved patronage refund(s) ready for disbursement
                                @else
                                    Generate patronage refund allocations based on member activity
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2" id="patronage-action-btns">
                        <button data-action="generatePatronageRefunds" id="gen-patronage-btn" class="btn btn-warning btn-sm">
                            <i data-lucide="wand-2" class="w-4 h-4"></i>
                            Generate Patronage Refunds
                        </button>
                        @if($patronagePendingCount > 0)
                            <button data-action="approveAllPatronage" id="approve-all-patronage-btn" class="btn btn-warning btn-lg">
                                <i data-lucide="badge-check" class="w-4 h-4"></i>
                                Approve All
                            </button>
                        @endif
                        @if($patronageApprovedCount > 0)
                            <button data-action="disburseAllPatronage" id="disburse-patronage-btn" class="btn btn-primary btn-lg">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Disburse All
                            </button>
                        @endif
                        @if($approvedCount > 0 && $patronageApprovedCount > 0)
                            <button data-action="disburseBoth" id="disburse-both-btn" class="btn btn-success btn-sm">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                Disburse Both
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Patronage Table -->
            <div id="patronage-table-container">
                <div class="card">
                    <div class="p-8 text-center text-gray-400">
                        <i data-lucide="loader" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading patronage refund table...</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Disbursement Modal -->
        <div id="disburseModal" class="modal-overlay hidden">
            <div class="modal max-w-lg">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                                <i data-lucide="send" class="w-5 h-5 text-success-600"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Disburse Dividends</h2>
                                <p class="text-xs text-gray-500">Release all approved dividends for {{ $year }}</p>
                            </div>
                        </div>
                        <button data-action="closeDisburseModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                        </button>
                    </div>
                </div>

                <form id="disburseForm" method="POST" action="{{ route('dividends.disburse') }}" class="p-6">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">

                    <div class="mb-6">
                        <p class="text-sm text-gray-600 mb-4">
                            You are about to disburse <strong>{{ $approvedCount }}</strong> approved dividend(s)
                            totaling <strong>₱{{ number_format($totalSumApproved, 2) }}</strong>.
                        </p>

                        <label class="block text-sm font-medium text-gray-700 mb-2">Disbursement Type</label>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="disbursement_type" value="savings" checked class="accent-primary-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Add to Savings Account</span>
                                    <p class="text-xs text-gray-500">The dividend amount will be deposited to each member's savings balance</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="disbursement_type" value="share_capital" class="accent-primary-600">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Add to Share Capital</span>
                                    <p class="text-xs text-gray-500">The dividend amount will be added to each member's share capital</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" data-action="closeDisburseModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 transition-colors flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Confirm Disbursement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab: Patronage Records -->
    <div id="tab-patronage-records" class="tab-content hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Additional Patronage Records</h2>
                <p class="text-sm text-gray-500">Record patronage from services outside the lending system (gas, rice, oil, etc.)</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('financial.activity') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="patronage-records">
                    <select name="year" data-submit-on-change class="select w-32">
                        @for($y = $currentYear; $y >= $currentYear - 10; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                        @foreach($years as $y)
                            @if($y < $currentYear - 10)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endif
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div id="patronage-records-container">
            <div class="card">
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="loader" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                    <p class="text-sm">Loading patronage records...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Savings -->
    <div id="tab-savings" class="tab-content hidden">
        @include('admin_components.partials.finance_savings_tab')
    </div>

    <!-- Tab: Share Capital -->
    <div id="tab-share-capitals" class="tab-content hidden">
        @include('admin_components.partials.finance_sharecapital_tab')
    </div>

    @include('admin_components.partials.finance_savings_modals')
    @include('admin_components.partials.finance_sharecapital_modals')

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
        // Tab switching
        function switchFinanceTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('tab-' + tabId).classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('bg-primary-600', 'text-white');
                el.classList.add('text-gray-600', 'hover:bg-gray-200');
            });
            btn.classList.remove('text-gray-600', 'hover:bg-gray-200');
            btn.classList.add('bg-primary-600', 'text-white');

            // Persist active tab in the URL so refreshes/reloads stay on the same tab
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            history.replaceState({}, '', url);

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            if (tabId === 'dividends') {
                loadDividendsTable();
                loadPatronageTable();
            }
        }

        // Auto-activate tab from URL hash
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab') || 'savings';
            const btn = document.querySelector('.tab-btn[data-tab="' + tab + '"]');
            if (btn) {
                switchFinanceTab(tab, btn);
            } else {
                const firstBtn = document.querySelector('.tab-btn');
                if (firstBtn) {
                    switchFinanceTab(firstBtn.getAttribute('data-tab'), firstBtn);
                }
            }
        });

        function loadDividendsTable() {
            const year = '{{ $year }}';
            const url = '{{ route("dividends.table-partial") }}?year=' + encodeURIComponent(year);
            loadDividendsPage(url);
        }

        function loadDividendsPage(url) {
            const container = document.getElementById('dividends-table-container');
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Error loading dividends table:', error);
                container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load dividends table.</p></div>';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-dividends-page');
            if (btn && btn.dataset.pageUrl) loadDividendsPage(btn.dataset.pageUrl);
        });

        // Breakdown preview on net surplus input
        function updateBreakdownPreview() {
            const input = document.getElementById('netSurplusInput');
            const preview = document.getElementById('breakdownPreview');
            const val = parseFloat(input.value) || 0;

            if (val > 0) {
                preview.classList.remove('hidden');

                const reserve = val * ({{ $reserveFundPercentage ?? 10 }}/100);
                const cetf = val * ({{ $cetfPercentage ?? 10 }}/100);
                const cdf = val * ({{ $cdfPercentage ?? 3 }}/100);
                const optional = val * ({{ $optionalFundPercentage ?? 7 }}/100);
                const statutoryTotal = reserve + cetf + cdf + optional;
                const remaining = val - statutoryTotal;
                const dividendPool = remaining * ({{ $dividendFundPercentage ?? 60 }}/100);
                const patronage = remaining * ({{ $patronageFundPercentage ?? 40 }}/100);

                document.getElementById('previewReserve').textContent = '₱' + reserve.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewCETF').textContent = '₱' + cetf.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewCDF').textContent = '₱' + cdf.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewOptional').textContent = '₱' + optional.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewStatutoryTotal').textContent = '₱' + statutoryTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewDividendPool').textContent = '₱' + dividendPool.toLocaleString('en-PH', {minimumFractionDigits: 2});
                document.getElementById('previewPatronage').textContent = '₱' + patronage.toLocaleString('en-PH', {minimumFractionDigits: 2});
            } else {
                preview.classList.add('hidden');
            }
        }

        // Update dividend amount via AJAX
        function updateDividendAmount(id, value) {
            const amount = parseFloat(value);
            if (isNaN(amount) || amount < 0) return;

            fetch('/admin/dividends/' + id + '/update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ approved_amount: amount })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                } else {
                    showToast('Error', data.message || 'Update failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while updating', 'error');
            });
        }

        // Approve a single dividend
        function approveDividend(id) {
            if (!confirm('Approve this dividend?')) return;

            fetch('/admin/dividends/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    loadDividendsTable();
                } else {
                    showToast('Error', data.message || 'Approval failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        }

        // Approve all pending dividends via AJAX
        function approveAllDividends() {
            if (!confirm('Approve all pending dividends for {{ $year }}?')) return;

            const btn = document.getElementById('approve-all-btn');
            if (btn) btn.disabled = true;

            fetch('/admin/dividends/approve-all?year={{ $year }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    if (data.approvedCount !== undefined) {
                        var countText = document.getElementById('disburse-count-text');
                        if (countText) {
                            countText.textContent = data.approvedCount + ' approved dividend(s) ready for disbursement';
                        }
                    }
                    const approveBtn = document.getElementById('approve-all-btn');
                    if (approveBtn) approveBtn.style.display = 'none';
                    loadDividendsTable();
                } else {
                    showToast('Error', data.message || 'Approval failed', 'error');
                    if (btn) btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
                if (btn) btn.disabled = false;
            });
        }

        // Disburse a single dividend
        function disburseDividend(id) {
            if (!confirm('Disburse this dividend to savings account?')) return;

            fetch('/admin/dividends/' + id + '/disburse', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ disbursement_type: 'savings' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    if (data.html) {
                        document.getElementById('dividends-table-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    } else {
                        loadDividendsTable();
                    }
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
            });
        }

        // Disburse All Dividends via AJAX
        function disburseAllDividends() {
            if (!confirm('Disburse all approved dividends to savings accounts? This action cannot be undone.')) return;

            const btn = document.getElementById('disburse-all-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing...';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            fetch('/admin/dividends/disburse-all/{{ $year }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    disbursement_type: 'savings'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);

                    // Update the disburse card count text
                    var countText = document.getElementById('disburse-count-text');
                    if (countText) {
                        countText.textContent = data.approvedCount + ' approved dividend(s) ready for disbursement';
                    }

                    // Hide the button after successful disbursement
                    var btnContainer = document.getElementById('disburse-btn-container');
                    if (btnContainer) {
                        btnContainer.innerHTML = '<span class="text-sm text-green-600 font-medium"><i data-lucide="check-circle" class="w-4 h-4 inline"></i> All dividends disbursed</span>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }

                    // Refresh the dividends table
                    if (data.html) {
                        document.getElementById('dividends-table-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    } else {
                        loadDividendsTable();
                    }
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse All';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse All';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        }

        // Disbursement modal
        function closeDisburseModal() {
            document.getElementById('disburseModal').classList.add('hidden');
            document.getElementById('disburseModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // AJAX Disburse form handler
        document.getElementById('disburseForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDisburseModal();
                    showToast('Success', data.message);
                    if (data.html) {
                        document.getElementById('dividends-table-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    } else {
                        loadDividendsTable();
                    }
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
            });
        });

        // ——— Patronage Records (Finance Page) ——————————————————————————————————

        function loadPatronageRecords() {
            const year = '{{ $year }}';
            const url = '{{ route("patronage-records.partial") }}?year=' + encodeURIComponent(year);
            loadPatronageRecordsPage(url);
        }

        function loadPatronageRecordsPage(url) {
            const container = document.getElementById('patronage-records-container');
            if (!container) return;
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                console.error('Error loading patronage records:', error);
                container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load patronage records.</p></div>';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-patronage-records-page');
            if (btn && btn.dataset.pageUrl) loadPatronageRecordsPage(btn.dataset.pageUrl);
        });

        function openAddPatronageRecordModal() {
            document.getElementById('patronageModalTitle').textContent = 'Add Patronage Record';
            document.getElementById('patronageRecordId').value = '';
            document.getElementById('patronageRecordForm').reset();
            document.getElementById('patronageRecordUser').disabled = false;
            document.getElementById('patronageRecordForm').querySelector('input[name="year"]').value = '{{ $year }}';
            document.getElementById('patronageRecordSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Save Record';
            document.getElementById('patronageRecordModal').classList.remove('hidden');
            document.getElementById('patronageRecordModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function editPatronageRecord(id, source, description, amount) {
            document.getElementById('patronageModalTitle').textContent = 'Edit Patronage Record';
            document.getElementById('patronageRecordId').value = id;
            document.getElementById('patronageRecordSource').value = source;
            document.getElementById('patronageRecordDescription').value = description;
            document.getElementById('patronageRecordAmount').value = amount;
            document.getElementById('patronageRecordUser').disabled = true;
            document.getElementById('patronageRecordSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Update Record';
            document.getElementById('patronageRecordModal').classList.remove('hidden');
            document.getElementById('patronageRecordModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closePatronageRecordModal() {
            document.getElementById('patronageRecordModal').classList.add('hidden');
            document.getElementById('patronageRecordModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-edit-patronage-record');
            if (!btn) return;
            editPatronageRecord(
                Number(btn.dataset.recordId),
                btn.dataset.recordSource,
                btn.dataset.recordDescription,
                Number(btn.dataset.recordAmount)
            );
        });

        document.getElementById('patronage-records-container').addEventListener('submit', function(e) {
            const form = e.target;
            if (form.id !== 'patronageRecordForm') return;
            e.preventDefault();
            const formData = new FormData(form);
            const recordId = formData.get('record_id');
            const isEdit = recordId && recordId !== '';
            const url = isEdit ? '/patronage-records/' + recordId : '{{ route("patronage-records.store") }}';
            const method = isEdit ? 'PUT' : 'POST';

            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: method === 'PUT' ? 'POST' : method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closePatronageRecordModal();
                    showToast('Success', data.message);
                    if (data.html) {
                        document.getElementById('patronage-records-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else {
                        loadPatronageRecords();
                    }
                } else {
                    showToast('Error', data.message || 'Operation failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        });

        function deletePatronageRecord(id) {
            if (!confirm('Delete this patronage record? This action cannot be undone.')) return;

            fetch('/patronage-records/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    loadPatronageRecords();
                } else {
                    showToast('Error', data.message || 'Delete failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        }

        // Update tab switching to load patronage records
        var _origSwitchFinanceTab = switchFinanceTab;
        switchFinanceTab = function(tabId, btn) {
            _origSwitchFinanceTab(tabId, btn);
            if (tabId === 'patronage-records') {
                loadPatronageRecords();
            }
        };

        // ——— Patronage Refund Distribution Functions ———————————————————————————

        function loadPatronageTable() {
            const year = '{{ $year }}';
            const url = '{{ route("dividends.patronage-partial") }}?year=' + encodeURIComponent(year);
            loadPatronagePage(url);
        }

        function loadPatronagePage(url) {
            const container = document.getElementById('patronage-table-container');
            if (!container) return;
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                console.error('Error loading patronage table:', error);
                container.innerHTML = '<div class="card p-6 text-center text-red-500"><p>Failed to load patronage refund table.</p></div>';
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-patronage-page');
            if (btn && btn.dataset.pageUrl) loadPatronagePage(btn.dataset.pageUrl);
        });

        function generatePatronageRefunds() {
            if (!confirm('Generate patronage refund allocations for {{ $year }}? This will recalculate based on current patronage data.')) return;

            const btn = document.getElementById('gen-patronage-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Generating...';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            window.location.href = '{{ route("dividends.calculate-patronage") }}?year={{ $year }}';
        }

        function updatePatronageAmount(id, value) {
            const amount = parseFloat(value);
            if (isNaN(amount) || amount < 0) return;

            fetch('/admin/dividends/patronage/' + id + '/update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                } else {
                    showToast('Error', data.message || 'Update failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while updating', 'error');
            });
        }

        function approvePatronageRefund(id) {
            if (!confirm('Approve this patronage refund?')) return;

            fetch('/admin/dividends/patronage/' + id + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    loadPatronageTable();
                } else {
                    showToast('Error', data.message || 'Approval failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        }

        function disbursePatronageRefund(id) {
            if (!confirm('Disburse this patronage refund to savings account?')) return;

            fetch('/admin/dividends/patronage/' + id + '/disburse', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    if (data.html) {
                        document.getElementById('patronage-table-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else {
                        loadPatronageTable();
                    }
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
            });
        }

        function approveAllPatronage() {
            if (!confirm('Approve all pending patronage refunds for {{ $year }}?')) return;

            fetch('/admin/dividends/patronage/approve-all?year={{ $year }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    loadPatronageTable();
                    var actionText = document.getElementById('patronage-action-text');
                    if (actionText) actionText.textContent = 'All patronage refunds approved and ready for disbursement';
                    var btns = document.getElementById('patronage-action-btns');
                    if (btns) {
                        btns.innerHTML = '<button data-action="generatePatronageRefunds" id="gen-patronage-btn" class="btn btn-warning btn-sm"><i data-lucide="wand-2" class="w-4 h-4"></i> Generate Patronage Refunds</button>'
                            + '<button data-action="disburseAllPatronage" id="disburse-patronage-btn" class="btn btn-primary btn-lg"><i data-lucide="send" class="w-4 h-4"></i> Disburse All</button>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } else {
                    showToast('Error', data.message || 'Approval failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        }

        function disburseAllPatronage() {
            if (!confirm('Disburse all approved patronage refunds to savings accounts? This action cannot be undone.')) return;

            const btn = document.getElementById('disburse-patronage-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing...';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            fetch('/admin/dividends/patronage/disburse-all/{{ $year }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    if (data.html) {
                        document.getElementById('patronage-table-container').innerHTML = data.html;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else {
                        loadPatronageTable();
                    }
                    var actionText = document.getElementById('patronage-action-text');
                    if (actionText) actionText.textContent = 'All patronage refunds have been disbursed';
                    var btns = document.getElementById('patronage-action-btns');
                    if (btns) {
                        btns.innerHTML = '<span class="text-sm text-green-600 font-medium"><i data-lucide="check-circle" class="w-4 h-4 inline"></i> All patronage refunds disbursed</span>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse All';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="send" class="w-4 h-4"></i> Disburse All';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        }

        function disburseBoth() {
            if (!confirm('Disburse both dividends AND patronage refunds to savings accounts? This action cannot be undone.')) return;

            const btn = document.getElementById('disburse-both-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Disbursing both...';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            fetch('/admin/dividends/disburse-both/{{ $year }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    if (data.dividendHtml) {
                        document.getElementById('dividends-table-container').innerHTML = data.dividendHtml;
                    }
                    if (data.patronageHtml) {
                        document.getElementById('patronage-table-container').innerHTML = data.patronageHtml;
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    location.reload();
                } else {
                    showToast('Error', data.message || 'Disbursement failed', 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4"></i> Disburse Both';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred during disbursement', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="zap" class="w-4 h-4"></i> Disburse Both';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        }

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

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endsection
