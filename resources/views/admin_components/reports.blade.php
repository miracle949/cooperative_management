@extends('layouts.admin')

@section('title', 'Reports - CoopAdmin')

@section('content')
    <style>
        @@media print {
            @page {
                size: landscape;
                margin: 10mm;
            }
            body {
                background: #fff !important;
                min-height: auto !important;
            }
            aside,
            header,
            .no-print,
            .modal-overlay,
            #userDropdownMenu {
                display: none !important;
            }
            .flex-1.ml-60 {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .flex.min-h-screen {
                display: block !important;
            }
            .tab-btn {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .tab-btn.bg-primary-600 {
                background-color: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .stat-card,
            .card,
            .badge,
            .bg-success-500,
            .bg-primary-500,
            .bg-warning-500,
            .bg-danger-100,
            .bg-success-100,
            .bg-warning-100,
            .bg-primary-100 {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .border-l-success-500,
            .border-l-danger-500,
            .border-l-primary-500,
            .border-l-warning-500 {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            canvas {
                max-height: 220px;
            }
            .table-container {
                overflow: visible !important;
            }
            .table {
                font-size: 11px;
            }
            .table th {
                background: #f1f3f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .grid {
                break-inside: avoid;
            }
            .card {
                break-inside: avoid;
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
            .stat-card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
            .mb-6 {
                margin-bottom: 12px !important;
            }
            .p-6 {
                padding: 12px !important;
            }
            .p-4 {
                padding: 8px !important;
            }
            .hidden {
                display: none !important;
            }
            .modal-overlay {
                display: none !important;
            }
            .max-h-\[60vh\] {
                max-height: none !important;
            }
            .overflow-y-auto {
                overflow: visible !important;
            }
            .sticky {
                position: static !important;
            }
        }
    </style>

    <!-- Breadcrumb -->
    <div class="mb-6 no-print">
        <nav class="text-sm text-gray-500">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600">
                        <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                    </a>
                </li>
                <li class="flex items-center">
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-400"></i>
                    <span class="text-gray-900 font-medium">Reports</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
            <p class="text-sm text-gray-500">Generate and export financial reports</p>
        </div>
        @isset($cooperativeStats)
        <button type="button" data-action="openModal" data-arg='["inputTransactionModal"]' class="btn btn-warning no-print">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Input New Transaction
        </button>
        @endisset
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 border-b border-gray-200 no-print">
        <a href="{{ route('reports.daily') }}"
            class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ isset($date) && !($statementView ?? false) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="calendar" class="w-4 h-4 inline mr-1.5"></i>
            Daily Report
        </a>
        <a href="{{ route('reports', ['view' => 'monthly']) }}"
            class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ (!isset($date) && !($statementView ?? false)) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1.5"></i>
            Monthly Report
        </a>
        <a href="{{ route('reports.statement') }}"
            class="tab-btn px-5 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ ($statementView ?? false) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="file-check" class="w-4 h-4 inline mr-1.5"></i>
            Statement of Operations
        </a>
    </div>

    @if (!isset($date) && !($statementView ?? false))
        {{-- ===================== MONTHLY REPORT TAB ===================== --}}
        <!-- Filter Panel -->
        <form method="GET" action="{{ route('reports') }}" class="no-print">
            <div class="card p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="from_date" class="input" value="{{ $fromDate }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="to_date" class="input" value="{{ $toDate }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                        <select name="report_type" class="select">
                            <option value="all" {{ $reportType === 'all' ? 'selected' : '' }}>All Reports</option>
                            <option value="savings" {{ $reportType === 'savings' ? 'selected' : '' }}>Savings Report</option>
                            <option value="loans" {{ $reportType === 'loans' ? 'selected' : '' }}>Loan Report</option>
                            <option value="share_capital" {{ $reportType === 'share_capital' ? 'selected' : '' }}>Share Capital Report</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2 flex items-end">
                        <button type="submit" class="btn btn-primary w-full">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="stat-card cursor-pointer hover:shadow-lg hover:border-danger-200 transition-all group" data-action="openWithdrawalsModal">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Withdrawals</p>
                        <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalWithdrawals, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center">
                            <i data-lucide="mouse-pointer-click" class="w-3 h-3 mr-1"></i>
                            Click for details
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 rounded-xl flex items-center justify-center group-hover:bg-danger-200 transition-colors">
                        <i data-lucide="arrow-up-circle" class="w-6 h-6 text-danger-600"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card cursor-pointer hover:shadow-lg hover:border-warning-200 transition-all group" data-action="openLoansModal">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Loans Issued</p>
                        <p class="text-2xl font-bold text-gray-900">₱{{ number_format($loansIssued, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center">
                            <i data-lucide="mouse-pointer-click" class="w-3 h-3 mr-1"></i>
                            Click for details
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center group-hover:bg-warning-200 transition-colors">
                        <i data-lucide="banknote" class="w-6 h-6 text-warning-600"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card cursor-pointer hover:shadow-lg hover:border-primary-200 transition-all group" data-action="openNetIncomeModal">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Net Income</p>
                        <p class="text-2xl font-bold text-gray-900">₱{{ number_format($netIncome, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center">
                            <i data-lucide="mouse-pointer-click" class="w-3 h-3 mr-1"></i>
                            Click for breakdown
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                        <i data-lucide="trending-up" class="w-6 h-6 text-primary-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Visualization -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Monthly Trend</h2>
                        <p class="text-sm text-gray-500">Transaction trends over time</p>
                    </div>
                </div>

                <div class="flex gap-1 mb-4 border-b border-gray-200 no-print">
                    <a href="{{ route('reports', array_merge(request()->query(), ['chart' => 'all'])) }}"
                        class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $chartType === 'all' ? 'bg-primary-50 text-primary-600 border-b-2 border-primary-500' : 'text-gray-500 hover:text-gray-700' }}">
                        All
                    </a>
                    <a href="{{ route('reports', array_merge(request()->query(), ['chart' => 'savings'])) }}"
                        class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $chartType === 'savings' ? 'bg-primary-50 text-primary-600 border-b-2 border-primary-500' : 'text-gray-500 hover:text-gray-700' }}">
                        Savings
                    </a>
                    <a href="{{ route('reports', array_merge(request()->query(), ['chart' => 'lending'])) }}"
                        class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $chartType === 'lending' ? 'bg-warning-50 text-warning-600 border-b-2 border-warning-500' : 'text-gray-500 hover:text-gray-700' }}">
                        Lending
                    </a>
                    <a href="{{ route('reports', array_merge(request()->query(), ['chart' => 'sharecapital'])) }}"
                        class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $chartType === 'sharecapital' ? 'bg-success-50 text-success-600 border-b-2 border-success-500' : 'text-gray-500 hover:text-gray-700' }}">
                        Share Capital
                    </a>
                </div>

                <div class="relative h-64">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Transaction Distribution</h2>
                        <p class="text-sm text-gray-500">By category</p>
                    </div>
                </div>

                @php
                    $totalAmount = $totalDeposits + $totalWithdrawals + $loansIssued;
                    $savingsPct = $totalAmount > 0 ? round(($totalDeposits / $totalAmount) * 100) : 0;
                    $loansPct = $totalAmount > 0 ? round(($loansIssued / $totalAmount) * 100) : 0;
                    $withdrawsPct = $totalAmount > 0 ? round(($totalWithdrawals / $totalAmount) * 100) : 0;
                @endphp

                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Savings Deposits</span>
                            <span class="text-sm font-medium text-gray-900">{{ $savingsPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-4">
                            <div class="bg-success-500 h-4 rounded-full" style="width: {{ $savingsPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Loan Disbursements</span>
                            <span class="text-sm font-medium text-gray-900">{{ $loansPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-4">
                            <div class="bg-primary-500 h-4 rounded-full" style="width: {{ $loansPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Withdrawals</span>
                            <span class="text-sm font-medium text-gray-900">{{ $withdrawsPct }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-4">
                            <div class="bg-warning-500 h-4 rounded-full" style="width: {{ $withdrawsPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Transactions -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Detailed Transactions</h2>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reference No.</th>
                            <th>Member Name</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($tx->created_at)->format('M d, Y') }}</td>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                            <td class="text-sm text-gray-600">{{ $tx->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900">{{ $tx->member_name ?? 'Unknown' }}</td>
                            <td>
                                @if($tx->type === 'deposit')
                                    <span class="badge badge-success">Deposit</span>
                                @elseif($tx->type === 'withdraw')
                                    <span class="badge badge-danger">Withdrawal</span>
                                @else
                                    <span class="badge badge-primary">{{ $tx->category ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td class="text-sm font-semibold {{ $tx->type === 'deposit' ? 'text-success-500' : 'text-danger-500' }}">
                                {{ $tx->type === 'deposit' ? '+' : '-' }}₱{{ number_format($tx->amount, 2) }}
                            </td>
                            <td>
                                @if($tx->type === 'deposit')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($tx->type === 'withdraw')
                                    <span class="badge badge-danger">Withdrawal</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($tx->type ?? 'N/A') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-50"></i>
                                    <p>No transactions found for the selected date range</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export / Print Actions -->
        <div class="card p-6 mb-6 no-print">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Export Report</h3>
                    <p class="text-sm text-gray-500">Download this report in your preferred format</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('reports', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Export CSV
                    </a>
                    <a href="{{ route('reports.journal.detailed', ['from_date' => $fromDate, 'to_date' => $toDate, 'print' => 1]) }}"
                       class="btn btn-outline" target="_blank">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Detailed Journal
                    </a>
                    <a href="{{ route('reports.journal.summary', ['from_date' => $fromDate, 'to_date' => $toDate, 'print' => 1]) }}"
                       class="btn btn-outline" target="_blank">
                        <i data-lucide="book" class="w-4 h-4"></i>
                        Print Journal Summary
                    </a>
                </div>
            </div>
        </div>
    @elseif(isset($date) && !($statementView ?? false))
        {{-- ===================== DAILY REPORT TAB ===================== --}}
        <!-- Date Filter -->
        <form method="GET" action="{{ route('reports.daily') }}" class="no-print">
            <div class="card p-6 mb-6">
                <div class="flex flex-row items-end gap-3 flex-wrap">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                        <input type="date" name="date" class="input" value="{{ $date }}">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card border-l-4 border-l-success-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Inflow</p>
                <p class="text-2xl font-bold text-success-700">₱{{ number_format($summary['grand_inflow'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Deposits + Contributions + Repayments + Investments</p>
            </div>
            <div class="stat-card border-l-4 border-l-danger-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Outflow</p>
                <p class="text-2xl font-bold text-danger-700">₱{{ number_format($summary['grand_outflow'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Withdrawals + Disbursements + Coop Expenses</p>
            </div>
            <div class="stat-card border-l-4 border-l-primary-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Net Flow</p>
                <p class="text-2xl font-bold text-primary-700">₱{{ number_format($summary['grand_inflow'] - $summary['grand_outflow'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Inflow − Outflow</p>
            </div>
            <div class="stat-card border-l-4 border-l-warning-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Transactions</p>
                <p class="text-2xl font-bold text-gray-900">
                    {{ $summary['savings_deposits_count'] + $summary['savings_withdrawals_count'] + $summary['sc_deposits_count'] + $summary['sc_withdrawals_count'] + $summary['loans_disbursed_count'] + $summary['repayments_count'] + $summary['cooperative_expenses_count'] + $summary['cooperative_investments_count'] }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Across all domains</p>
            </div>
        </div>

        <!-- Domain Summary Mini-Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="piggy-bank" class="w-4 h-4 text-success-600"></i>
                    Savings
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Deposits</span>
                        <span class="font-medium text-success-700">₱{{ number_format($summary['savings_deposits_total'], 2) }} ({{ $summary['savings_deposits_count'] }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Withdrawals</span>
                        <span class="font-medium text-danger-700">₱{{ number_format($summary['savings_withdrawals_total'], 2) }} ({{ $summary['savings_withdrawals_count'] }})</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between font-semibold">
                        <span>Net</span>
                        <span class="{{ $summary['savings_net'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">₱{{ number_format($summary['savings_net'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="coins" class="w-4 h-4 text-primary-600"></i>
                    Share Capital
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Contributions</span>
                        <span class="font-medium text-success-700">₱{{ number_format($summary['sc_deposits_total'], 2) }} ({{ $summary['sc_deposits_count'] }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Withdrawals</span>
                        <span class="font-medium text-danger-700">₱{{ number_format($summary['sc_withdrawals_total'], 2) }} ({{ $summary['sc_withdrawals_count'] }})</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between font-semibold">
                        <span>Net</span>
                        <span class="{{ $summary['sc_net'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">₱{{ number_format($summary['sc_net'], 2) }}</span>
                    </div>
                </div>
            </div>

                    <div class="card p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="banknote" class="w-4 h-4 text-warning-600"></i>
                    Loans
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Disbursed</span>
                        <span class="font-medium text-warning-700">₱{{ number_format($summary['loans_disbursed_total'], 2) }} ({{ $summary['loans_disbursed_count'] }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Repayments</span>
                        <span class="font-medium text-success-700">₱{{ number_format($summary['repayments_total'], 2) }} ({{ $summary['repayments_count'] }})</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between font-semibold">
                        <span>Outstanding</span>
                        <span class="text-warning-700">₱{{ number_format($summary['loans_disbursed_total'] - $summary['repayments_total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-primary-600"></i>
                    Cooperative Capital Outlays
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Expenses</span>
                        <span class="font-medium text-danger-700">₱{{ number_format($summary['cooperative_expenses_total'], 2) }} ({{ $summary['cooperative_expenses_count'] }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Investments</span>
                        <span class="font-medium text-primary-700">₱{{ number_format($summary['cooperative_investments_total'], 2) }} ({{ $summary['cooperative_investments_count'] }})</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between font-semibold">
                        <span>Net</span>
                        <span class="{{ $summary['cooperative_net'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">₱{{ number_format($summary['cooperative_net'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings Transactions -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                        <i data-lucide="piggy-bank" class="w-4 h-4 text-success-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Savings Transactions</h3>
                        <p class="text-xs text-gray-500">{{ $savingsTransactions->count() }} transaction(s)</p>
                    </div>
                </div>
                <div class="flex gap-4 text-sm">
                    <span class="text-success-700 font-medium">Deposits: ₱{{ number_format($summary['savings_deposits_total'], 2) }}</span>
                    <span class="text-danger-700 font-medium">Withdrawals: ₱{{ number_format($summary['savings_withdrawals_total'], 2) }}</span>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Reference No.</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($savingsTransactions as $tx)
                        <tr>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                            <td class="text-sm text-gray-600 font-mono">{{ $tx->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900">{{ $tx->member_name ?? 'Unknown' }}</td>
                            <td>
                                @if($tx->type === 'deposit')
                                    <span class="badge badge-success">Deposit</span>
                                @else
                                    <span class="badge badge-danger">Withdrawal</span>
                                @endif
                            </td>
                            <td class="text-sm font-semibold {{ $tx->type === 'deposit' ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $tx->type === 'deposit' ? '+' : '-' }}₱{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="text-sm text-gray-600">{{ $tx->payment_method ?? 'N/A' }}</td>
                            <td><span class="badge badge-success">Completed</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No savings transactions on this date</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Share Capital Transactions -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                        <i data-lucide="coins" class="w-4 h-4 text-primary-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Share Capital Transactions</h3>
                        <p class="text-xs text-gray-500">{{ $shareCapitalTransactions->count() }} transaction(s)</p>
                    </div>
                </div>
                <div class="flex gap-4 text-sm">
                    <span class="text-success-700 font-medium">In: ₱{{ number_format($summary['sc_deposits_total'], 2) }}</span>
                    <span class="text-danger-700 font-medium">Out: ₱{{ number_format($summary['sc_withdrawals_total'], 2) }}</span>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Reference No.</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Shares</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shareCapitalTransactions as $tx)
                        <tr>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                            <td class="text-sm text-gray-600 font-mono">{{ $tx->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900">{{ $tx->member_name ?? 'Unknown' }}</td>
                            <td>
                                @if(in_array($tx->type, ['Deposit', 'Subscription']))
                                    <span class="badge badge-success">{{ $tx->type }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $tx->type }}</span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-900">{{ $tx->shares ?? 0 }}</td>
                            <td class="text-sm font-semibold text-gray-900">₱{{ number_format($tx->total_amount, 2) }}</td>
                            <td class="text-sm text-gray-600">{{ $tx->payment_method ?? 'N/A' }}</td>
                            <td>
                                @if(in_array($tx->status, ['Completed', 'Approved']))
                                    <span class="badge badge-success">{{ $tx->status }}</span>
                                @elseif($tx->status === 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">{{ $tx->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No share capital transactions on this date</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loan Disbursements -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-4 h-4 text-warning-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Loan Disbursements</h3>
                        <p class="text-xs text-gray-500">{{ $loanDisbursements->count() }} loan(s)</p>
                    </div>
                </div>
                <span class="text-sm font-medium text-warning-700">Total: ₱{{ number_format($summary['loans_disbursed_total'], 2) }}</span>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Reference No.</th>
                            <th>Member</th>
                            <th>Loan Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loanDisbursements as $loan)
                        <tr>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($loan->created_at)->format('g:i A') }}</td>
                            <td class="text-sm text-gray-600 font-mono">{{ $loan->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900">{{ $loan->member_name ?? 'Unknown' }}</td>
                            <td class="text-sm text-gray-900">{{ $loan->lending_type ?? 'N/A' }}</td>
                            <td class="text-sm font-semibold text-warning-700">₱{{ number_format($loan->lending_amount, 2) }}</td>
                            <td><span class="badge badge-success">Approved</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No loan disbursements on this date</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loan Repayments -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-success-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Loan Repayments</h3>
                        <p class="text-xs text-gray-500">{{ $loanRepayments->count() }} repayment(s)</p>
                    </div>
                </div>
                <span class="text-sm font-medium text-success-700">Total: ₱{{ number_format($summary['repayments_total'], 2) }}</span>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Reference No.</th>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loanRepayments as $repayment)
                        <tr>
                            <td class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($repayment->created_at)->format('g:i A') }}</td>
                            <td class="text-sm text-gray-600 font-mono">{{ $repayment->reference_no ?? 'N/A' }}</td>
                            <td class="text-sm text-gray-900">{{ $repayment->member_name ?? 'Unknown' }}</td>
                            <td class="text-sm font-semibold text-success-700">₱{{ number_format($repayment->amount_paid, 2) }}</td>
                            <td class="text-sm text-gray-600">{{ $repayment->payment_method ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No repayments on this date</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cooperative Capital Outlays & Expenditures -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                        <i data-lucide="book-open" class="w-4 h-4 text-primary-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Cooperative Capital Outlays & Expenditures</h3>
                        <p class="text-xs text-gray-500">{{ $cooperativeTransactions->count() }} transaction(s)</p>
                    </div>
                </div>
                <span class="text-sm font-medium text-primary-700">Total: ₱{{ number_format($cooperativeTransactions->sum('amount'), 2) }}</span>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cooperativeTransactions as $ct)
                        <tr>
                            <td class="text-sm text-gray-900 max-w-xs truncate">{{ $ct->description }}</td>
                            <td><span class="badge badge-primary">{{ $ct->category }}</span></td>
                            <td>
                                @if($ct->transaction_type === 'expense')
                                    <span class="badge badge-danger">Expense</span>
                                @else
                                    <span class="badge badge-success">Investment</span>
                                @endif
                            </td>
                            <td class="text-sm font-semibold {{ $ct->transaction_type === 'expense' ? 'text-danger-600' : 'text-primary-600' }}">
                                {{ $ct->transaction_type === 'expense' ? '-' : '+' }}₱{{ number_format($ct->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No cooperative transactions on this date</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 text-sm flex gap-6">
                <span class="text-danger-700 font-medium">Expenses: ₱{{ number_format($cooperativeExpenses->sum('amount'), 2) }} ({{ $cooperativeExpenses->count() }})</span>
                <span class="text-primary-700 font-medium">Investments: ₱{{ number_format($cooperativeInvestments->sum('amount'), 2) }} ({{ $cooperativeInvestments->count() }})</span>
            </div>
        </div>

        <!-- Export / Print Actions (Daily) -->
        <div class="card p-6 mb-6 no-print">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Export Report</h3>
                    <p class="text-sm text-gray-500">Download this report in your preferred format</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('reports.daily', ['date' => $date, 'export' => 'csv']) }}"
                        class="btn btn-outline">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        Export CSV
                    </a>
                    <a href="{{ route('reports.journal.detailed', ['from_date' => $date, 'to_date' => $date, 'print' => 1]) }}"
                       class="btn btn-outline" target="_blank">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Detailed Journal
                    </a>
                    <a href="{{ route('reports.journal.summary', ['from_date' => $date, 'to_date' => $date, 'print' => 1]) }}"
                       class="btn btn-outline" target="_blank">
                        <i data-lucide="book" class="w-4 h-4"></i>
                        Print Journal Summary
                    </a>
                </div>
            </div>
        </div>
    @elseif($statementView ?? false)
        {{-- ===================== STATEMENT OF OPERATIONS TAB ===================== --}}
        <!-- Filter Panel -->
        <form method="GET" action="{{ route('reports.statement') }}" class="no-print" id="statement-period-form">
            <div class="card p-6 mb-6">
                <div class="flex flex-row items-end gap-3 flex-wrap">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Reporting Period
                        </label>

                        <select
                            name="period"
                            id="statement-period"
                            class="input"
                            data-submit-on-change
                        >
                            <option value="this_month"
                                {{ request('period', 'this_year') === 'this_month' ? 'selected' : '' }}>
                                This Month
                            </option>

                            <option value="last_month"
                                {{ request('period', 'this_year') === 'last_month' ? 'selected' : '' }}>
                                Last Month
                            </option>

                            <option value="this_quarter"
                                {{ request('period', 'this_year') === 'this_quarter' ? 'selected' : '' }}>
                                This Quarter
                            </option>

                            <option value="this_year"
                                {{ request('period', 'this_year') === 'this_year' ? 'selected' : '' }}>
                                This Year (default)
                            </option>

                            <option value="last_year"
                                {{ request('period', 'this_year') === 'last_year' ? 'selected' : '' }}>
                                Last Year
                            </option>

                            <option value="custom"
                                {{ request('period', 'this_year') === 'custom' ? 'selected' : '' }}>
                                Custom Date Range
                            </option>
                        </select>
                    </div>

                    <div
                        id="statement-custom-dates"
                        style="{{ ($period ?? 'this_year') === 'custom' ? '' : 'display:none;' }}"
                    >
                        <div class="flex flex-row items-end gap-3 flex-wrap">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    From Date
                                </label>

                                <input
                                    type="date"
                                    name="from_date"
                                    class="input"
                                    value="{{ $fromDate }}"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    name="to_date"
                                    class="input"
                                    value="{{ $toDate }}"
                                >
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </form>

        <!-- Period Label -->
        <div class="mb-6">
            <p class="text-sm text-gray-500">For the period <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</span></p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="stat-card border-l-4 border-l-success-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Revenue</p>
                <p class="text-2xl font-bold text-success-700">₱{{ number_format($totalRevenue, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Interest + Service Fees + Late Fees</p>
            </div>
            <div class="stat-card border-l-4 border-l-danger-500">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Expenses</p>
                <p class="text-2xl font-bold text-danger-700">₱{{ number_format($totalExpenses, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Cooperative operational expenses</p>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-success-100 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4 text-success-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Revenue / Income</h3>
                    <p class="text-xs text-gray-500">Income earned during the period</p>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Income Type</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-sm text-gray-900">Interest Income</td>
                            <td class="text-sm font-semibold text-right text-success-700">₱{{ number_format($interestIncome, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-gray-900">Service Fee Income</td>
                            <td class="text-sm font-semibold text-right text-success-700">₱{{ number_format($serviceFeeIncome, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-gray-900">Late Fee Income</td>
                            <td class="text-sm font-semibold text-right text-success-700">₱{{ number_format($lateFeeIncome, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50 font-bold">
                            <td class="text-sm text-gray-900">Total Revenue</td>
                            <td class="text-sm text-right text-success-700">₱{{ number_format($totalRevenue, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-danger-100 flex items-center justify-center">
                    <i data-lucide="trending-down" class="w-4 h-4 text-danger-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Expenses</h3>
                    <p class="text-xs text-gray-500">Cooperative operational expenses during the period</p>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expensesByCategory as $expense)
                        <tr>
                            <td class="text-sm text-gray-900">{{ $expense->category }}</td>
                            <td class="text-sm text-gray-600 text-center">{{ $expense->count }}</td>
                            <td class="text-sm font-semibold text-right text-danger-700">₱{{ number_format($expense->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-8">
                                <div class="flex flex-col items-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <p>No expenses recorded for the selected period</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                        @if($expensesByCategory->isNotEmpty())
                        <tr class="bg-gray-50 font-bold">
                            <td class="text-sm text-gray-900">Total Expenses</td>
                            <td class="text-sm text-center">{{ $expensesByCategory->sum('count') }}</td>
                            <td class="text-sm text-right text-danger-700">₱{{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Print Action -->
        <div class="card p-6 mb-6 no-print">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Print Report</h3>
                    <p class="text-sm text-gray-500">Print or save as PDF</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('reports.statement.print', ['from_date' => $fromDate, 'to_date' => $toDate, 'print' => 1]) }}"
                       class="btn btn-outline" target="_blank">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Statement of Operations
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- ===================== MODALS (Monthly Report) ===================== -->
    <!-- Deposits Modal -->
    <div id="depositsModal" class="modal-overlay hidden">
        <div class="modal max-w-4xl">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 text-success-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Savings Deposits</h2>
                            <p class="text-xs text-gray-500">All deposit transactions</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["depositsModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-success-50 rounded-lg p-4 border border-success-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Deposits</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ number_format($totalDeposits ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Transactions</p>
                        <p class="text-xl font-bold text-gray-900">{{ $depositsCount ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Average</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ ($depositsCount ?? 0) > 0 ? number_format(($totalDeposits ?? 0) / $depositsCount, 2) : 0 }}</p>
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 mb-4">Recent Deposits</h4>
                <div class="space-y-3">
                    @forelse($deposits ?? [] as $deposit)
                    <div class="flex items-center gap-4 p-4 bg-success-50 rounded-lg border border-success-100">
                        <div class="w-10 h-10 bg-success-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 text-success-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $deposit->member_name ?? 'Member' }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($deposit->created_at)->format('M d, Y') }} {{ $deposit->reference_no ? '• ' . $deposit->reference_no : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-success-600">+₱{{ number_format($deposit->amount, 2) }}</p>
                            <span class="badge badge-success">Completed</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                        <p>No deposits found</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="closeModal" data-arg='["depositsModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('financial.activity', ['tab' => 'savings']) }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Go to Savings
                </a>
            </div>
        </div>
    </div>

    <!-- Withdrawals Modal -->
    <div id="withdrawalsModal" class="modal-overlay hidden">
        <div class="modal max-w-4xl">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center">
                            <i data-lucide="arrow-up-circle" class="w-5 h-5 text-danger-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Withdrawals</h2>
                            <p class="text-xs text-gray-500">All withdrawal transactions</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["withdrawalsModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-danger-50 rounded-lg p-4 border border-danger-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Withdrawals</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ number_format($totalWithdrawals ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Transactions</p>
                        <p class="text-xl font-bold text-gray-900">{{ $withdrawalsCount ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Average</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ ($withdrawalsCount ?? 0) > 0 ? number_format(($totalWithdrawals ?? 0) / $withdrawalsCount, 2) : 0 }}</p>
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 mb-4">Recent Withdrawals</h4>
                <div class="space-y-3">
                    @forelse($withdrawals ?? [] as $withdrawal)
                    <div class="flex items-center gap-4 p-4 bg-danger-50 rounded-lg border border-danger-100">
                        <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="arrow-up-circle" class="w-5 h-5 text-danger-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $withdrawal->member_name ?? 'Member' }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($withdrawal->created_at)->format('M d, Y') }} {{ $withdrawal->reference_no ? '• ' . $withdrawal->reference_no : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-danger-600">-₱{{ number_format($withdrawal->amount, 2) }}</p>
                            <span class="badge badge-danger">Completed</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                        <p>No withdrawals found</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="closeModal" data-arg='["withdrawalsModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('financial.activity', ['tab' => 'savings']) }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Go to Savings
                </a>
            </div>
        </div>
    </div>

    <!-- Loans Modal -->
    <div id="loansModal" class="modal-overlay hidden">
        <div class="modal max-w-4xl">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                            <i data-lucide="banknote" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Loans Issued</h2>
                            <p class="text-xs text-gray-500">All loan disbursements</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["loansModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-warning-50 rounded-lg p-4 border border-warning-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Loans</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ number_format($loansIssued ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Loans Count</p>
                        <p class="text-xl font-bold text-gray-900">{{ $loansCount ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 text-center">
                        <p class="text-xs text-gray-500 mb-1">Average Loan</p>
                        <p class="text-xl font-bold text-gray-900">₱{{ ($loansCount ?? 0) > 0 ? number_format(($loansIssued ?? 0) / $loansCount, 2) : 0 }}</p>
                    </div>
                </div>
                <h4 class="font-semibold text-gray-900 mb-4">Recent Loans</h4>
                <div class="space-y-3">
                    @forelse($loans ?? [] as $loan)
                    <div class="flex items-center gap-4 p-4 bg-warning-50 rounded-lg border border-warning-100">
                        <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="banknote" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $loan['member_name'] ?? 'Member' }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($loan['created_at'])->format('M d, Y') }} {{ isset($loan['reference_no']) ? '• ' . $loan['reference_no'] : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-warning-600">₱{{ number_format($loan['amount'], 2) }}</p>
                            <span class="badge badge-warning">{{ $loan['purpose'] ?? 'Loan' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                        <p>No loans found</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="closeModal" data-arg='["loansModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('lendings') }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Go to Lending
                </a>
            </div>
        </div>
    </div>

    <!-- Net Income Breakdown Modal -->
    <div id="netIncomeModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Net Income Breakdown</h2>
                            <p class="text-xs text-gray-500">Financial summary</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["netIncomeModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-primary-50 to-success-50 rounded-xl p-6 border border-primary-100 mb-6 text-center">
                    <p class="text-xs text-gray-500 mb-1">Net Income</p>
                    <p class="text-3xl font-bold text-gray-900">₱{{ number_format($netIncome ?? 0, 2) }}</p>
                </div>
                <h4 class="font-semibold text-gray-900 mb-4">Income Sources</h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-success-50 rounded-lg border border-success-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-success-100 rounded-full flex items-center justify-center">
                                <i data-lucide="arrow-down-circle" class="w-5 h-5 text-success-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Deposits</span>
                        </div>
                        <span class="text-sm font-semibold text-success-600">+₱{{ number_format($totalDeposits ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-danger-50 rounded-lg border border-danger-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center">
                                <i data-lucide="arrow-up-circle" class="w-5 h-5 text-danger-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Withdrawals</span>
                        </div>
                        <span class="text-sm font-semibold text-danger-600">-₱{{ number_format($totalWithdrawals ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-warning-50 rounded-lg border border-warning-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center">
                                <i data-lucide="banknote" class="w-5 h-5 text-warning-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Loans Issued</span>
                        </div>
                        <span class="text-sm font-semibold text-warning-600">₱{{ number_format($loansIssued ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="border-t border-gray-200 mt-6 pt-6">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Net Income</span>
                        <span class="text-lg font-bold text-primary-600">₱{{ number_format($netIncome ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button data-action="closeModal" data-arg='["netIncomeModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
            </div>
        </div>
    </div>

    {{-- Input Transaction Modal --}}
    @isset($cooperativeStats)
    <div id="inputTransactionModal" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
        <div class="modal max-w-2xl bg-white rounded-2xl shadow-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="book-open" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Record Cooperative Transaction</h2>
                        <p class="text-sm text-gray-500">Log expenses and investments for the cooperative</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["inputTransactionModal"]' class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="cooperativeTransactionForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" id="categorySelect" class="select" required data-action="toggleCategoryOther" data-arg='["|el|"]'>
                                <option value="">Select category</option>
                                <option value="Vehicle Purchase">Vehicle Purchase</option>
                                <option value="Bank Investment">Bank Investment</option>
                                <option value="Office Equipment">Office Equipment</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div id="categoryOtherWrap" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Specific Category</label>
                            <input type="text" name="category_other" id="categoryOtherInput" class="input" placeholder="Please specify category..." maxlength="120">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                            <select name="transaction_type" class="select" required>
                                <option value="">Select type</option>
                                <option value="expense">Expense</option>
                                <option value="investment">Investment</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="input" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Date</label>
                            <input type="hidden" name="transaction_date" value="{{ date('Y-m-d') }}">
                            <input type="date" value="{{ date('Y-m-d') }}" class="input bg-gray-100 text-gray-600 cursor-not-allowed" disabled>
                            <p class="text-xs text-gray-400 mt-1">Auto-set to today's date</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="input" placeholder="Describe the transaction..." required></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" data-action="closeModal" data-arg='["inputTransactionModal"]' class="px-4 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Record Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endisset

    <script nonce="{{ csp_nonce() }}">
        function openDepositsModal() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            openModal('depositsModal');
        }
        function openWithdrawalsModal() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            openModal('withdrawalsModal');
        }
        function openLoansModal() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            openModal('loansModal');
        }
        function openNetIncomeModal() {
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            openModal('netIncomeModal');
        }

        function toggleCategoryOther(selectEl) {
            const wrap = document.getElementById('categoryOtherWrap');
            const input = document.getElementById('categoryOtherInput');
            if (selectEl.value === 'Other') {
                wrap.classList.remove('hidden');
                input.setAttribute('required', 'required');
            } else {
                wrap.classList.add('hidden');
                input.removeAttribute('required');
                input.value = '';
            }
        }

        document.getElementById('cooperativeTransactionForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);

            fetch('{{ route('cooperative.transactions.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    form.reset();
                    toggleCategoryOther(form.querySelector('[name="category"]'));
                    form.querySelector('[name="transaction_date"]').value = '{{ date('Y-m-d') }}';
                    closeModal('inputTransactionModal');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast('Error', data.message || 'Failed to record transaction', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred', 'error');
            });
        });
    </script>

    @if (!isset($date) && !($statementView ?? false))
    <script nonce="{{ csp_nonce() }}">
        const chartType = '{{ $chartType }}';
        const months = @json($months);
        const savingsData = @json($savingsTrend);
        const lendingData = @json($lendingTrend);
        const shareCapitalData = @json($shareCapitalTrend);

        const ctx = document.getElementById('trendChart').getContext('2d');

        let datasets = [];

        if (chartType === 'all') {
            datasets = [
                { label: 'Savings', data: savingsData, borderColor: '#4F46E5', backgroundColor: 'rgba(79, 70, 229, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#4F46E5', pointBorderColor: '#fff', pointRadius: 4, pointHoverRadius: 6 },
                { label: 'Lending', data: lendingData, borderColor: '#F59E0B', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#F59E0B', pointBorderColor: '#fff', pointRadius: 4, pointHoverRadius: 6 },
                { label: 'Share Capital', data: shareCapitalData, borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#10B981', pointBorderColor: '#fff', pointRadius: 4, pointHoverRadius: 6 }
            ];
        } else if (chartType === 'savings') {
            datasets = [{ label: 'Savings', data: savingsData, borderColor: '#4F46E5', backgroundColor: 'rgba(79, 70, 229, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#4F46E5', pointBorderColor: '#fff', pointRadius: 5, pointHoverRadius: 7 }];
        } else if (chartType === 'lending') {
            datasets = [{ label: 'Lending', data: lendingData, borderColor: '#F59E0B', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#F59E0B', pointBorderColor: '#fff', pointRadius: 5, pointHoverRadius: 7 }];
        } else if (chartType === 'sharecapital') {
            datasets = [{ label: 'Share Capital', data: shareCapitalData, borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#10B981', pointBorderColor: '#fff', pointRadius: 5, pointHoverRadius: 7 }];
        }

        new Chart(ctx, {
            type: 'line',
            data: { labels: months, datasets: datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: chartType === 'all', position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 12 } } },
                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', titleColor: '#fff', bodyColor: '#fff', padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return context.dataset.label + ': ₱' + context.raw.toLocaleString() + 'K'; } } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' }, ticks: { font: { size: 11 }, callback: function(value) { return '₱' + value + 'K'; } } }
                }
            }
        });
    </script>
    @endif
@endsection
