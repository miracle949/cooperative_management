<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Savings</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="css_folder/savings.css">
    <link rel="stylesheet" href="css_folder/savings_modal.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style>
        .sm-ref-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--lavender-tint);
            border: 1px dashed var(--border);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            margin: 0.75rem 0;
            gap: 0.5rem;
        }

        .sm-ref-label {
            font-size: 0.75rem;
            color: #000000;
            font-weight: 500;
            white-space: nowrap;
        }

        .sm-ref-value {
            font-family: monospace;
            font-size: 0.88rem;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 0.05em;
            word-break: break-all;
            text-align: right;
        }

        .sm-copy-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #6B7B74;
            font-size: 0.85rem;
            padding: 2px 6px;
            border-radius: 4px;
            transition: color 0.2s;
            flex-shrink: 0;
        }

        .sm-copy-btn:hover {
            color: #1a1a1a;
        }

        .sm-btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.6rem;
            border-radius: 10px;
            background: transparent;
            color: var(--teal);
            border: 1.5px solid var(--teal);
            font-family: inherit;
            font-size: 0.87rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        .sm-btn-download:hover {
            background: #1a1a1a;
            color: #fff;
        }

        .tx-ref {
            color: var(--muted);
            font-weight: 500;
        }

        /* ═══ SAVINGS RECEIPT OVERLAY ═══ */
        #sv-receipt-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }

        #sv-receipt-overlay.active {
            display: flex;
        }

        #sv-receipt-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: svModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes svModalIn {
            from { opacity: 0; transform: translateY(28px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .sv-receipt-header {
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid var(--line);
        }

        .sv-receipt-header .sv-check-circle {
            width: 60px;
            height: 60px;
            background-color: var(--teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .sv-receipt-header .sv-check-circle i {
            color: #fff;
            font-size: 26px;
        }

        .sv-receipt-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .sv-receipt-header p {
            color: var(--muted);
            font-size: 0.82rem;
            margin: 0;
        }

        .sv-receipt-body {
            padding: 1.5rem;
        }

        .sv-receipt-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #e8e8e8;
            font-size: 0.85rem;
        }

        .sv-receipt-row:last-child {
            border-bottom: none;
        }

        .sv-receipt-row .label {
            color: #888;
            font-weight: 500;
        }

        .sv-receipt-row .value {
            color: #1a1a1a;
            font-weight: 700;
            text-align: right;
        }

        .sv-receipt-row .value.highlight {
            color: #1a1a1a;
            font-size: 13.6px;
        }

        .sv-ref-badge {
            background: #f4f4f4;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            letter-spacing: 0.5px;
            color: #333;
            font-family: monospace;
            font-size: 0.82rem;
        }

        .sv-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff8e1;
            border: 1.5px solid #ffe082;
            color: #b8860b;
            border-radius: 20px;
            padding: 0.2rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .sv-status-badge .dot {
            width: 7px;
            height: 7px;
            background: #e6a817;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .sv-receipt-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            border-radius: 0 0 20px 20px;
            background: #fff;
        }

        .sv-btn-download {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--teal);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .sv-btn-download:hover {
            opacity: 0.88;
        }

        .sv-btn-close-modal {
            width: 100%;
            padding: 0.7rem;
            background: transparent;
            color: #888;
            border: 1.5px solid #e8e8e8;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .sv-btn-close-modal:hover {
            background: #f5f5f5;
            color: #333;
        }

        .tx-voided-row { background: #fef2f2; }
        .tx-voided-row:hover { background: #fde8e8; }
        .status.voided {
            background: #fdecec;
            border: 1.5px solid #f5c6c6;
            color: #c0392b;
            border-radius: 10px;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* ═══ VOID REASON OVERLAY ═══ */
        #sv-void-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }
        #sv-void-overlay.active { display: flex; }
        #sv-void-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            margin: auto;
            animation: svModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }
        .sv-void-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--line);
        }
        .sv-void-header .sv-void-circle {
            width: 60px;
            height: 60px;
            background-color: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }
        .sv-void-header .sv-void-circle i { color: #fff; font-size: 26px; }
        .sv-void-header h2 { color: #1a1a1a; font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem; }
        .sv-void-header p { color: var(--muted); font-size: 0.82rem; margin: 0; }
        .sv-void-body {
            padding: 1.5rem;
            text-align: center;
        }
        .sv-void-body .sv-void-label {
            font-size: 0.78rem;
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .sv-void-body .sv-void-value {
            font-size: 1rem;
            font-weight: 700;
            color: #c0392b;
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .sv-void-footer {
            padding: 0 1.5rem 1.5rem;
        }
        .sv-btn-void-close {
            width: 100%;
            padding: 0.7rem;
            background: transparent;
            color: #888;
            border: 1.5px solid #e8e8e8;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .sv-btn-void-close:hover { background: #f5f5f5; color: #333; }
    </style>
</head>

<body>

    <div class="container-fluid p-0 m-0">

        @include("components.offcanvas")
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")
            <div class="main-sub-parent">
                <div class="main-parent">
                    <div class="main-header">
                        <h3>Savings Overview</h3>
                        <p>Last updated {{ $lastUpdated }} ·
                            {{ $monthsActive == 0 ? 'Less than a month' : $monthsActive . ' ' . ($monthsActive == 1 ? 'month' : 'months') }}
                            active
                        </p>
                    </div>

                    {{-- ══ GATED WRAPPER — blurs & locks all savings stats when Share Capital isn't met ══ --}}
                    <main>
                        <div class="card-box-parent">
                            <div class="card-box-text">
                                <h3>My Savings Balance</h3>
                                <h2>₱ <b>{{ number_format($totalSavingsBalance, 2) }}</b></h2>
                                <div class="hero-sub">
                                    Last updated {{ $lastUpdated }} ·
                                    {{ $monthsActive == 0 ? 'Less than a month' : $monthsActive . ' ' . ($monthsActive == 1 ? 'month' : 'months') }}
                                    active
                                </div>
                            </div>
                            <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                                <div class="card-box-buttons">

                                    {{-- Deposit --}}
                                    @if($hasShareCapital)
                                        <div class="card-box" data-bs-toggle="modal" data-bs-target="#depositModal"
                                            style="cursor:pointer;">
                                            <div class="card-icon">
                                                <img src="{{ asset('images/arrow-icon.png') }}" alt="">
                                            </div>
                                            <div>
                                                <p>Deposit</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card-box card-box-disabled"
                                            title="You must have active share capital to use savings.">
                                            <div class="card-icon">
                                                <img src="{{ asset('images/arrow-icon.png') }}" alt="">
                                            </div>
                                            <div>
                                                <p>Deposit</p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Withdraw --}}
                                    @if($hasShareCapital)
                                        <div class="card-box" data-bs-toggle="modal" data-bs-target="#withdrawModal"
                                            style="cursor:pointer;">
                                            <div class="card-icon">
                                                <img src="{{ asset('images/arrow-icon.png') }}" alt="">
                                            </div>
                                            <div>
                                                <p>Withdraw</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card-box card-box-disabled"
                                            title="You must have active share capital to use savings.">
                                            <div class="card-icon">
                                                <img src="{{ asset('images/arrow-icon.png') }}" alt="">
                                            </div>
                                            <div>
                                                <p>Withdraw</p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ★ MOVED: "Open TD" now lives on the Time Deposit page --}}

                                </div>
                            </div>
                        </div>

                        <!-- @if(!$hasShareCapital)
                                    <div class="gate-shield">
                                        <div class="gate-lock"><i class="fa-solid fa-lock"></i></div>
                                        <div class="gate-msg">Savings stats are locked</div>
                                        <div class="gate-sub">
                                            Please <a href="{{ route('ShareCapitalMember') }}">subscribe to Share Capital</a>
                                            first to unlock your savings stats.
                                        </div>
                                    </div>
                                @endif -->
                    </main>

                    {{-- ══ STATS CARDS — its own gated/hover-lock block ══ --}}
                    <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                        <section id="section1">
                            <div class="main-card-box">
                                <div class="card-box tw:bg-white">
                                    <div class="card-header-icon">
                                        <p>Interest Accrued</p>
                                        <div class="card-icon d-flex justify-content-center align-items-center">
                                            <i class="fa-solid fa-percent"></i>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4>₱ {{ number_format($estimatedQuarterInterest, 2) }}</h4>
                                        <span>{{ number_format($regularSavingsRate, 2) }}% p.a. · credited
                                            {{ $regularSavingsFrequency }}</span>
                                    </div>
                                </div>

                                <div class="card-box tw:bg-white">
                                    <div class="card-header-icon">
                                        <p>Monthly Average</p>
                                        <div class="card-icon d-flex justify-content-center align-items-center">
                                            <i class="fa-solid fa-arrow-trend-up"></i>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4>₱ {{ number_format($monthlyAverage, 2) }}</h4>
                                        <span>Per month average</span>
                                    </div>
                                </div>

                                <div class="card-box tw:bg-white">
                                    <div class="card-header-icon">
                                        <p>Total Months</p>
                                        <div class="card-icon d-flex justify-content-center align-items-center">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4>{{ $totalMonths }} Months</h4>
                                        <span>Months saving</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        @if(!$hasShareCapital)
                            <div class="gate-shield">
                                <div class="gate-lock"><i class="fa-solid fa-lock"></i></div>
                                <div class="gate-msg">Savings stats are locked</div>
                                <div class="gate-sub">
                                    Please <a href="{{ route('ShareCapitalMember') }}">subscribe to Share Capital</a>
                                    first to unlock your savings stats.
                                </div>
                            </div>
                        @endif
                    </div>


                    <div class="ask-box">
                        <div class="ask-body">
                        </div>
                    </div>

                    {{-- ══ BREAKDOWN + GROWTH GRAPH — its own gated/hover-lock block ══ --}}
                    <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                        <div class="parent-panel">
                            <div class="panel graph">
                                <div class="panel-head"
                                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div>
                                        <h3>Savings Growth</h3>
                                        <p>{{ $growthYear === now()->year ? 'Net deposits over the last 6 months' : "Net deposits for {$growthYear}" }}
                                        </p>
                                    </div>
                                    <select class="sm-filter-select" id="growthYearSelect"
                                        data-action="changeGrowthYear" data-arg='["|value|"]'>
                                        @foreach($availableGrowthYears as $y)
                                            <option value="{{ $y }}" {{ $growthYear == $y ? 'selected' : '' }}>{{ $y }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="panel-body">
                                    <div class="chart-wrap">
                                        @foreach ($savingsGrowth as $month)
                                            <div class="bar-col {{ $month['is_current'] ? 'active' : '' }}">
                                                <div class="bar" style="height:{{ $month['height_percent'] }}%">
                                                    <div class="bar-tooltip">
                                                        <div class="bar-tooltip-title">{{ $month['label'] }}</div>
                                                        <div class="bar-tooltip-row">
                                                            <span
                                                                class="bar-tooltip-dot {{ $month['is_current'] ? 'dot-gold' : 'dot-blue' }}"></span>
                                                            <span class="bar-tooltip-label">Net Savings:</span>
                                                            <span class="bar-tooltip-value">
                                                                {{ $month['net'] >= 0 ? '₱' : '-₱' }}{{ number_format(abs($month['net']), 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="bar-month">{{ $month['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="chart-legend">
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--blue);"></span>Prior months</div>
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--gold);"></span>Current month</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!$hasShareCapital)
                            <div class="gate-shield">
                                <div class="gate-lock"><i class="fa-solid fa-lock"></i></div>
                                <div class="gate-msg">Savings breakdown is locked</div>
                                <div class="gate-sub">
                                    Please <a href="{{ route('ShareCapitalMember') }}">subscribe to Share Capital</a>
                                    first to unlock your breakdown and growth chart.
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ══ TRANSACTION HISTORY — its own gated/hover-lock block ══ --}}
                    <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                        <section id="section2">
                            <div class="card-box-parent">
                                <div class="d-flex justify-content-between align-items-center card-box-title">
                                    <div class="title">
                                        <h3>Transaction History</h3>
                                        <p>View your monthly transactions breakdown</p>
                                    </div>
                                    <div class="gap-3 print">
                                        <button class="py-2 px-3 tw:text-white" style="border-radius: 10px">
                                            <i class="fa-solid fa-download"></i> CSV
                                        </button>
                                        <button class="py-2 px-3 tw:text-white" style="border-radius: 10px">
                                            <i class="fa fa-solid fa-download"></i> PDF
                                        </button>
                                    </div>
                                </div>

                                <div class="sm-tab-group">
                                    <a href="{{ route('savings.index', array_merge(request()->except('type', 'page'), ['type' => 'all'])) }}"
                                        class="sm-tab {{ $type === 'all' ? 'active' : '' }}">All</a>
                                    <a href="{{ route('savings.index', array_merge(request()->except('type', 'page'), ['type' => 'deposit'])) }}"
                                        class="sm-tab {{ $type === 'deposit' ? 'active' : '' }}">Deposits</a>
                                    <a href="{{ route('savings.index', array_merge(request()->except('type', 'page'), ['type' => 'withdrawal'])) }}"
                                        class="sm-tab {{ $type === 'withdrawal' ? 'active' : '' }}">Withdrawals</a>
                                </div>

                                <form method="GET" action="{{ route('savings.index') }}" class="sm-tx-toolbar"
                                    id="sm-tx-filter-form">
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <div class="sm-search-box">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" name="ref" value="{{ $ref }}"
                                            placeholder="Search by reference no.">
                                    </div>
                                    <input type="date" class="sm-filter-select" name="date" value="{{ $date }}"
                                        data-action="sm-submit-tx-filter">

                                    <select name="status" class="sm-filter-select" data-submit-on-change>
                                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                                        @foreach($availableStatuses as $s)
                                            <option value="{{ strtolower($s) }}" {{ $status === strtolower($s) ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>

                                    @if($ref !== '' || $date !== '' || $status !== 'all')
                                        <a href="{{ route('savings.index', ['type' => $type]) }}"
                                            class="sm-filter-clear">Clear filters</a>
                                    @endif
                                </form>

                                <div class="card-box">
                                    <div class="overflow-x-auto">
                                        <table class="table table-scroll m-0">
                                            <thead>
                                                <tr style="border-bottom: 1px solid rgba(0,0,0,0.2);">
                                                    <th class="text-start">Type</th>
                                                    <th class="text-start">Reference No.</th>
                                                    <th class="text-start">Date</th>
                                                    <th class="text-start">Amount</th>
                                                    <th class="text-start">Status</th>
                                                    <th class="text-start">Receipt</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($transactions as $tx)
                                                    <tr class="{{ strtolower($tx->status ?? '') === 'voided' ? 'tx-voided-row' : '' }}"
                                                        @if(strtolower($tx->status ?? '') === 'voided')
                                                            style="cursor:pointer;" data-reason="{{ $tx->void_reason }}"
                                                            data-action="showVoidReason" data-arg='["|el|"]'
                                                        @endif
                                                    >
                                                        <td class="text-start">
                                                            @if($tx->type === 'deposit' && str_starts_with($tx->reference_no ?? '', 'DISB'))
                                                                <div class="deposit">Loan Disbursement</div>
                                                            @elseif($tx->type === 'deposit' && str_starts_with($tx->reference_no ?? '', 'PAT'))
                                                                <div class="deposit">Patronage Refund</div>
                                                            @elseif($tx->type === 'deposit')
                                                                <div class="deposit">Deposit</div>
                                                            @elseif($tx->type === \App\Http\Controllers\ShareCapital::CONVERSION_TYPE)
                                                                <div class="withdraw">Savings to Share Capital Conversion</div>
                                                            @elseif(str_starts_with($tx->reference_no ?? '', 'LNPAY'))
                                                                <div class="withdraw">Loan Repay</div>
                                                            @else
                                                                <div class="withdraw">Withdrawal</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-start">
                                                            @if ($tx->reference_no)
                                                                <span class="tx-ref">{{ $tx->reference_no }}</span>
                                                            @else
                                                                <span style="color:#000000;font-size:0.78rem">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-start">
                                                            {{ \Carbon\Carbon::parse($tx->transaction_date)->format('m/d/Y') }}
                                                        </td>
                                                        <td class="text-start"
                                                            style="font-weight:700; color:{{ in_array(strtolower($tx->status ?? ''), ['voided', 'rejected']) ? 'var(--muted)' : (in_array($tx->type, ['withdrawal', \App\Http\Controllers\ShareCapital::CONVERSION_TYPE]) ? 'var(--red)' : 'var(--green)') }}">
                                                            @if (in_array(strtolower($tx->status ?? ''), ['voided', 'rejected']))
                                                                ₱ {{ number_format($tx->amount, 2) }}
                                                            @else
                                                                {{ in_array($tx->type, ['withdrawal', \App\Http\Controllers\ShareCapital::CONVERSION_TYPE]) ? '-' : '+' }} ₱
                                                                {{ number_format($tx->amount, 2) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $displayStatus = $tx->status ?? 'completed';
                                                            @endphp

                                                            @if ($displayStatus === 'pending')
                                                                <span class="status pending">Pending</span>
                                                            @elseif (in_array($displayStatus, ['approved', 'completed']))
                                                                <span
                                                                    class="status approved">{{ ucfirst($displayStatus) }}</span>
                                                            @elseif ($displayStatus === 'released')
                                                                <span class="status released">Released</span>
                                                            @elseif ($displayStatus === 'deducted')
                                                                <span class="status deducted">Deducted</span>
                                                            @elseif ($displayStatus === 'rejected')
                                                                <span class="status rejected">Rejected</span>
                                                            @elseif ($displayStatus === 'credited')
                                                                <span class="status credited">Credited</span>
                                                            @elseif ($displayStatus === 'locked')
                                                                <span class="status locked">Locked</span>
                                                            @elseif ($displayStatus === 'voided')
                                                                <span class="status voided">Voided</span>
                                                            @else
                                                                <span class="status">{{ ucfirst($displayStatus) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-start">
                                                            @if ($tx->reference_no && in_array($tx->type, ['deposit', 'withdrawal']) && strtolower($tx->status ?? '') === 'completed')
                                                                <a href="{{ route('savings.receipt', $tx->reference_no) }}"
                                                                    title="Download Receipt"
                                                                    style="color: var(--teal);font-size: 18px;">
                                                                    <i class="fa-solid fa-file-arrow-down"></i>
                                                                </a>
                                                            @else
                                                                <span style="color:#c4c4c4;font-size:0.78rem">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5">
                                                            <i class="fa-solid fa-folder-open fa-2x mb-3"
                                                                style="color: var(--muted);"></i>
                                                            <p style="color:var(--muted);margin-top:0.5rem;">No
                                                                transactions yet.</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    @if ($transactions->total() > 0)
                                        <div class="sm-pagination-wrap">
                                            <div class="sm-pagination-info">
                                                Showing <b>{{ $transactions->lastItem() }}</b> of
                                                <b>{{ $transactions->total() }}</b> transactions
                                            </div>

                                            @if ($transactions->hasPages())
                                                <div class="sm-pagination">
                                                    @if ($transactions->onFirstPage())
                                                        <span class="sm-page-btn disabled"><i
                                                                class="fa-solid fa-chevron-left"></i></span>
                                                    @else
                                                        <a href="{{ $transactions->previousPageUrl() }}" class="sm-page-btn">
                                                            <i class="fa-solid fa-chevron-left"></i>
                                                        </a>
                                                    @endif

                                                    @for ($i = 1; $i <= $transactions->lastPage(); $i++)
                                                        <a href="{{ $transactions->url($i) }}"
                                                            class="sm-page-btn {{ $i == $transactions->currentPage() ? 'active' : '' }}">
                                                            {{ $i }}
                                                        </a>
                                                    @endfor

                                                    @if ($transactions->hasMorePages())
                                                        <a href="{{ $transactions->nextPageUrl() }}" class="sm-page-btn">
                                                            <i class="fa-solid fa-chevron-right"></i>
                                                        </a>
                                                    @else
                                                        <span class="sm-page-btn disabled"><i
                                                                class="fa-solid fa-chevron-right"></i></span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>

                        @if(!$hasShareCapital)
                            <div class="gate-shield">
                                <div class="gate-lock"><i class="fa-solid fa-lock"></i></div>
                                <div class="gate-msg">Transaction history is locked</div>
                                <div class="gate-sub">
                                    Please <a href="{{ route('ShareCapitalMember') }}">subscribe to Share Capital</a>
                                    first to unlock your transaction history.
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ══ /GATED WRAPPER ══ --}}

                </div>
            </div>
        </div>


        {{-- ============================================================
        DEPOSIT MODAL
        ============================================================ --}}
        <div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content sm-modal-content">

                    <div class="modal-header sm-modal-header" style="padding: 24px 20px;">
                        <div class="modal-text">
                            <div class="sm-modal-icon sm-deposit-icon">
                                <img src="images/arrow-icon.png" alt="">
                            </div>
                            <div class="sm-modal-text">
                                <h1 class="modal-title sm-modal-title" id="depositModalLabel">Deposit Savings</h1>
                                <p class="sm-modal-subtitle">Add funds to your savings account</p>
                            </div>
                        </div>
                        <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form action="{{ route('savings.deposit') }}" method="POST" enctype="multipart/form-data" id="deposit-form">
                        @csrf
                        <input type="hidden" name="_form" value="deposit">

                        <div class="modal-body sm-modal-body" style="padding: 1.25rem 1.5rem;">
                            <div class="sm-balance-pill">
                                <span class="sm-pill-label">My Savings Balance</span>
                                <span class="sm-pill-value">₱ {{ number_format($totalSavingsBalance, 2) }}</span>
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="depositAmount">Amount to Deposit</label>
                                <div class="sm-amount-wrap">
                                    <span class="sm-amount-prefix">₱</span>
                                    <input class="form-input sm-form-input @error('amount') sm-input-error @enderror"
                                        type="number" id="depositAmount" name="amount" placeholder="0.00" min="1"
                                        step="0.01" value="{{ old('amount') }}" required />
                                </div>
                                <div class="sm-quick-amounts">
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["depositAmount",500]'>₱500</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["depositAmount",1000]'>₱1,000</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["depositAmount",1500]'>₱1,500</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["depositAmount",2000]'>₱2,000</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["depositAmount",5000]'>₱5,000</button>
                                </div>
                                @error('amount')
                                    <div class="sm-error-msg show">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="depositPaymentMethod">Payment Method</label>
                                <select class="form-select" name="payment_method" id="depositPaymentMethod"
                                    style="border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px;  font-size: 14px; color: #333;"
                                    required>
                                    <option value="" disabled selected>Select payment method...</option>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ strtolower($pm->method_name) }}" {{ old('payment_method') === strtolower($pm->method_name) ? 'selected' : '' }}>{{ $pm->method_name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="sm-error-msg show" style="margin-top:6px;">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="deposit-gcash-box" style="display:none; margin: 1rem 0;">
                                @if($gcashPaymentMethod && $gcashPaymentMethod->has_qr_code && $gcashPaymentMethod->qr_code_image_path)
                                    <div
                                        style="background: linear-gradient(135deg, #f0f7ff 0%, #e8f4ff 100%); border: 1.5px solid #c2deff; border-radius: 12px; padding: 1rem 1.2rem; text-align: center;">
                                        <p style="margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #0056b3;">
                                            <i class="fa-solid fa-mobile-screen-button"></i> Scan to Pay via GCash
                                        </p>
                                        <img src="{{ asset('storage/' . $gcashPaymentMethod->qr_code_image_path) }}"
                                            alt="GCash QR Code"
                                            style="width: 220px; height: 220px; max-width: 100%; object-fit: contain; border-radius: 10px; border: 1px solid #c2deff; background: #fff; padding: 12px; display: block; margin: 0 auto;">
                                        <p style="margin: 10px 0 0; font-size: 11px; color: #5a8ac4;">
                                            Scan this using your GCash app, then upload your payment screenshot below.
                                        </p>
                                        <p style="margin: 6px 0 0; font-size: 11px;">
                                            <a href="#"
                                                class="js-open-qr-lightbox"
                                                data-qr-src="{{ asset('storage/' . $gcashPaymentMethod->qr_code_image_path) }}"
                                                style="color: #0056b3; font-weight: 600;">
                                                <i class="fa fa-up-right-and-down-left-from-center"></i> View full-size QR
                                            </a>
                                        </p>
                                    </div>
                                @else
                                    <div
                                        style="background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 12px; padding: 1rem 1.2rem;">
                                        <p style="margin: 0; font-size: 13px; color: #856404;">
                                            <i class="fa fa-triangle-exclamation"></i> No GCash QR code has been set up yet.
                                            Please contact the admin.
                                        </p>
                                    </div>
                                @endif

                                <div style="margin-top: 1rem;">
                                    <label
                                        style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                        GCash Reference Number <span style="color: #e53e3e;">*</span>
                                    </label>
                                    <p id="deposit-ref-used-msg"
                                        style="display:none; margin:0 0 6px; color:#e53e3e; font-size:12px; font-weight:600;">
                                        <i class="fa fa-circle-exclamation"></i> This reference number has already been used for a transaction.
                                    </p>
                                    <input type="text" name="gcash_reference_no" id="deposit-gcash-ref-input"
                                        maxlength="13" pattern="\d{13}" placeholder="e.g. 1234567890123"
                                        style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box; height: 46px;">
                                    <p style="margin: 4px 0 0; font-size: 11px; color: #888;">
                                        Enter the 13-digit reference number from your GCash transaction.
                                    </p>
                                </div>

                                <div style="margin-top: 0.8rem;">
                                    <label
                                        style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                        Upload Payment Screenshot <span style="font-size: 11px; color: #bbb;">(GCash
                                            proof)</span>
                                    </label>
                                    <input type="file" name="gcash_proof" id="deposit-gcash-proof-input"
                                        accept="image/png,image/jpeg,image/jpg"
                                        style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box;"
                                        class="form-control">
                                    <div id="deposit-gcash-proof-preview" style="display:none; margin-top:10px;">
                                        <img id="deposit-gcash-proof-preview-img"
                                            style="width:100%; height:180px; object-fit:cover; border-radius:8px; border:1px solid #e0e0e0;">
                                    </div>
                                </div>
                            </div>

                            <div style="background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 10px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                                <p style="margin: 0; font-size: 12px; color: #856404;">
                                    <i class="fa fa-triangle-exclamation"></i>
                                    Deposits via GCash are <strong>pending verification</strong>. Please enter the correct GCash reference number and attach a screenshot of your payment. Submitting false or fraudulent entries will result in account suspension.
                                </p>
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="depositNote">Note (optional)</label>
                                <input class="sm-form-input" type="text" id="depositNote" name="note"
                                    style="width: 100%;padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px;color: #333;  box-sizing: border-box; height: 46px;"
                                    placeholder="e.g. Monthly contribution" value="{{ old('note') }}" />
                            </div>
                        </div>

                        <div class="modal-footer sm-modal-footer"
                            style="background: #f8f9fa; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 1rem 1.6rem; display: flex;justify-content: center;align-items: center; gap: 8px;">
                            <div id="deposit-confirm-btn-wrap">
                                <button type="submit" class="sm-btn-confirm sm-deposit-confirm" id="deposit-confirm-btn">
                                    <i class="fa-solid fa-circle-arrow-down"></i> Confirm Deposit
                                </button>
                            </div>
                            <button type="button" class="sm-btn-cancel done" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        {{-- ============================================================
        WITHDRAW MODAL
        ============================================================ --}}
        <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content sm-modal-content">

                    <div class="modal-header sm-modal-header" style="padding: 24px 20px;">
                        <div class="modal-text">
                            <div class="sm-modal-icon sm-withdraw-icon">
                                <img src="images/arrow-icon.png" alt="">
                            </div>
                            <div class="sm-modal-text">
                                <h1 class="modal-title sm-modal-title" id="withdrawModalLabel">Withdraw Savings</h1>
                                <p class="sm-modal-subtitle">Withdraw funds from your savings account</p>
                            </div>
                        </div>
                        <button type="button" class="sm-modal-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form action="{{ route('savings.withdraw') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form" value="withdraw">
                        <input type="hidden" name="payment_method" value="gcash">

                        <div class="modal-body sm-modal-body">
                            <div class="sm-balance-pill">
                                <span class="sm-pill-label">My Savings Balance</span>
                                <span class="sm-pill-value">₱ {{ number_format($totalSavingsBalance, 2) }}</span>
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="withdrawAmount">Amount to Withdraw</label>
                                <div class="sm-amount-wrap">
                                    <span class="sm-amount-prefix">₱</span>
                                    <input class="sm-form-input @error('amount') sm-input-error @enderror" type="number"
                                        id="withdrawAmount" name="amount" placeholder="0.00" min="1" step="0.01"
                                        value="{{ old('amount') }}" required />
                                </div>
                                <div class="sm-quick-amounts">
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["withdrawAmount",500]'>₱500</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["withdrawAmount",1000]'>₱1,000</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["withdrawAmount",1500]'>₱1,500</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["withdrawAmount",2000]'>₱2,000</button>
                                    <button type="button" class="sm-quick-btn"
                                        data-action="setSavingsAmount" data-arg='["withdrawAmount",{{ $totalSavingsBalance }}]'>All</button>
                                </div>
                                @error('amount')
                                    <div class="sm-error-msg show">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="withdrawGcashNumber">GCash Mobile Number</label>
                                <input class="sm-form-input @error('gcash_number') sm-input-error @enderror" type="text"
                                    id="withdrawGcashNumber" name="gcash_number"
                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #e0e0e0; font-size: 14px; color: #333; box-sizing: border-box; height: 46px;"
                                    placeholder="e.g. 09123456789"
                                    value="{{ old('gcash_number', Auth::user()->otherinfo->contact_no ?? '') }}" required />
                                @error('gcash_number')
                                    <div class="sm-error-msg show">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm-form-group">
                                <label class="sm-form-label" for="withdrawNote">Note (optional)</label>
                                <input class="sm-form-input" type="text" id="withdrawNote" name="note"
                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; color: #333;  box-sizing: border-box;  height: 46px;"
                                    placeholder="e.g. Emergency expense" value="{{ old('note') }}" />
                            </div>
                        </div>

                        <div class="modal-footer sm-modal-footer">
                            <div id="withdraw-confirm-btn-wrap">
                                <button type="submit" class="sm-btn-confirm sm-withdraw-confirm">
                                    <i class="fa-solid fa-circle-arrow-up"></i> Confirm Withdraw
                                </button>
                            </div>
                            <button type="button" class="sm-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        {{-- ============================================================
        RECEIPT OVERLAY — Deposit (matches share-capital style)
        ============================================================ --}}
        @if(session('deposit_success'))
            <div id="sv-receipt-overlay" class="active">
                <div id="sv-receipt-modal">
                    <div class="sv-receipt-header">
                        <div class="sv-check-circle"><i class="fa-solid fa-check"></i></div>
                        <h2>Deposit Request Submitted!</h2>
                        <p>Your deposit request is pending for approval.</p>
                    </div>

                    <div class="sv-receipt-body" id="sv-receipt-printable">
                        <div class="sv-receipt-row">
                            <span class="label">Organization</span>
                            <span class="value">KMPCATS</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Member</span>
                            <span class="value">{{ session('deposit_member', Auth::user()->name ?? 'Member') }}</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Transaction Type</span>
                            <span class="value">Deposit</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Amount</span>
                            <span class="value highlight">₱{{ number_format(session('deposit_amount', 0), 2) }}</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Payment Method</span>
                            <span class="value">{{ session('deposit_method', '—') }}</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Reference No.</span>
                            <span class="value"><span class="sv-ref-badge">{{ session('deposit_reference', '—') }}</span></span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Date</span>
                            <span class="value">{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</span>
                        </div>
                        <div class="sv-receipt-row">
                            <span class="label">Status</span>
                            <span class="value">
                                <span class="sv-status-badge pending"><span class="dot"></span> Pending Approval</span>
                            </span>
                        </div>
                    </div>

                    <div class="sv-receipt-footer">
                        <button class="sv-btn-download" data-action="svDownloadReceipt">
                            <i class="fa-solid fa-download"></i> Download Receipt
                        </button>
                        <button class="sv-btn-close-modal" data-action="svCloseModal">Close</button>
                    </div>
                </div>
            </div>

            <div id="sv-receipt-data" data-member="{{ session('deposit_member', Auth::user()->name ?? 'Member') }}"
                data-type="Deposit"
                data-amount="{{ number_format(session('deposit_amount', 0), 2) }}"
                data-method="{{ session('deposit_method', '—') }}"
                data-ref="{{ session('deposit_reference', '—') }}"
                data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}"
                data-status="Pending" style="display:none;">
            </div>
        @endif

        {{-- ============================================================
        VOID REASON OVERLAY — Savings
        ============================================================ --}}
        <div id="sv-void-overlay">
            <div id="sv-void-modal">
                <div class="sv-void-header">
                    <div class="sv-void-circle"><i class="fa-solid fa-ban"></i></div>
                    <h2>Transaction Voided</h2>
                    <p>This transaction has been voided by the admin.</p>
                </div>
                <div class="sv-void-body">
                    <div class="sv-void-label">Reason</div>
                    <div class="sv-void-value" id="sv-void-reason-text"></div>
                </div>
                <div class="sv-void-footer">
                    <button class="sv-btn-void-close" data-action="svCloseVoidModal">Close</button>
                </div>
            </div>
        </div>

    </div>{{-- end container-fluid --}}

    {{-- QR Lightbox --}}
    <div id="qr-lightbox-overlay"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:100000; align-items:center; justify-content:center;">
        <button type="button" data-action="closeQrLightbox"
            style="position:absolute; top:20px; right:24px; background:#fff; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; color:#333; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa fa-times"></i>
        </button>
        <img id="qr-lightbox-img" src="" alt="GCash QR Code"
            style="max-width:90%; max-height:85vh; border-radius:12px;">
    </div>

    @error('amount')
        <div class="sm-error-msg show">{{ $message }}</div>
    @enderror

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;
            A.register('sm-submit-tx-filter', function (e, el) { document.getElementById('sm-tx-filter-form').submit(); });
        })();

        AOS.init();
    </script>

    <script nonce="{{ csp_nonce() }}">
        @if ($errors->any() && old('_form') === 'deposit')
            document.getElementById('triggerDepositModal').click();
        @endif
    </script>

    <script nonce="{{ csp_nonce() }}">
        const smRefInput = document.querySelector('.sm-search-box input[name="ref"]');
        const smFilterForm = document.getElementById('sm-tx-filter-form');
        let smSearchDebounce;

        if (smRefInput) {
            smRefInput.addEventListener('input', function () {
                clearTimeout(smSearchDebounce);
                smSearchDebounce = setTimeout(() => smFilterForm.submit(), 500);
            });

            if (smRefInput.value) {
                smRefInput.focus();
                const val = smRefInput.value;
                smRefInput.value = '';
                smRefInput.value = val;
            }
        }

        function changeGrowthYear(year) {
            const url = new URL(window.location.href);
            url.searchParams.set('growth_year', year);
            window.location.href = url.toString();
        }

        function openQrLightbox(src) {
            document.getElementById('qr-lightbox-img').src = src;
            document.getElementById('qr-lightbox-overlay').style.display = 'flex';
        }

        function closeQrLightbox() {
            document.getElementById('qr-lightbox-overlay').style.display = 'none';
        }

        document.getElementById('qr-lightbox-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) closeQrLightbox();
        });

        document.addEventListener('click', function (e) {
            const link = e.target.closest('.js-open-qr-lightbox');
            if (link) {
                e.preventDefault();
                openQrLightbox(link.dataset.qrSrc);
            }
        });
    </script>

    <script nonce="{{ csp_nonce() }}">
        function setSavingsAmount(inputId, val) {
            document.getElementById(inputId).value = val;
        }

        function copyRef(elementId) {
            const text = document.getElementById(elementId).textContent.trim();
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.currentTarget;
                btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1500);
            });
        }

        let depositRefUsed = false;
        const depositRefInput = document.getElementById('deposit-gcash-ref-input');
        const depositRefUsedMsg = document.getElementById('deposit-ref-used-msg');
        const depositConfirmBtn = document.getElementById('deposit-confirm-btn');

        function setDepositRefState(used) {
            depositRefUsed = used;
            if (depositRefUsedMsg) depositRefUsedMsg.style.display = used ? 'block' : 'none';
            if (depositConfirmBtn) {
                depositConfirmBtn.disabled = used;
                depositConfirmBtn.style.opacity = used ? 0.55 : 1;
                depositConfirmBtn.style.cursor = used ? 'not-allowed' : 'pointer';
            }
        }

        let depositRefTimer = null;
        if (depositRefInput) {
            depositRefInput.addEventListener('input', function () {
                clearTimeout(depositRefTimer);
                const v = this.value.trim();
                if (v.length !== 13 || !/^\d{13}$/.test(v)) {
                    setDepositRefState(false);
                    return;
                }
                depositRefTimer = setTimeout(function () {
                    fetch('{{ route('reference.check') }}?ref=' + encodeURIComponent(v))
                        .then(function (r) { return r.json(); })
                        .then(function (d) { setDepositRefState(!!d.used); })
                        .catch(function () { setDepositRefState(false); });
                }, 300);
            });
        }

        document.getElementById('deposit-form')?.addEventListener('submit', function (e) {
            if (depositRefUsed) {
                e.preventDefault();
                if (depositRefUsedMsg) depositRefUsedMsg.style.display = 'block';
            }
        });

        document.getElementById('depositPaymentMethod')?.addEventListener('change', function () {
            const isGcash = this.value === 'gcash';
            document.getElementById('deposit-gcash-box').style.display = isGcash ? 'block' : 'none';
            document.getElementById('deposit-gcash-proof-input').required = isGcash;
            document.getElementById('deposit-gcash-ref-input').required = isGcash;
            // Confirm button stays visible — GCash now submits through the same form.
        });

        document.getElementById('deposit-gcash-proof-input')?.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('deposit-gcash-proof-preview-img').src = e.target.result;
                    document.getElementById('deposit-gcash-proof-preview').style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        document.getElementById('depositModal')?.addEventListener('show.bs.modal', function () {
            document.getElementById('depositPaymentMethod').value = '';
            document.getElementById('deposit-gcash-box').style.display = 'none';
            document.getElementById('deposit-gcash-ref-input').value = '';
            setDepositRefState(false);
            document.getElementById('deposit-confirm-btn-wrap').style.display = 'block';
        });

        document.getElementById('withdrawModal')?.addEventListener('show.bs.modal', function () {
            document.getElementById('withdrawGcashNumber').value = '{{ Auth::user()->otherinfo->contact_no ?? '' }}';
            document.getElementById('withdraw-confirm-btn-wrap').style.display = 'block';
        });

        window.addEventListener('DOMContentLoaded', function () {

            @if ($errors->any() && old('_form') === 'deposit')
                document.getElementById('triggerDepositModal').click();
            @endif

            @if ($errors->any() && old('_form') === 'withdraw')
                document.getElementById('triggerWithdrawModal').click();
            @endif

            @if (session('withdraw_success'))
                // Overlay is server-rendered — no JS trigger needed
            @endif

        });

        /* ═══ SAVINGS RECEIPT OVERLAY ═══ */
        function svCloseModal() {
            const overlay = document.getElementById('sv-receipt-overlay');
            if (overlay) overlay.remove();
        }

        document.getElementById('sv-receipt-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) svCloseModal();
        });

        function svReceiptRow(label, value) {
            return `<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e8e8e8;font-size:0.84rem;">
                <span style="color:#888;">${label}</span>
                <span style="color:#1a1a1a;font-weight:700;">${value}</span>
            </div>`;
        }

        function svDownloadReceipt() {
            const d = document.getElementById('sv-receipt-data')?.dataset;
            if (!d) return;

            const wrapper = document.createElement('div');
            wrapper.style.cssText = `
                position: fixed; left: -9999px; top: 0;
                width: 400px; background: #fff;
                border-radius: 20px; overflow: hidden;
                box-shadow: 0 8px 40px rgba(0,0,0,0.15);
            `;

            wrapper.innerHTML = `
                <div style="background-color:var(--teal);padding:2rem 1.5rem 1.2rem;text-align:center;">
                    <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Deposit Request Submitted!</div>
                    <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">Your deposit request is pending for approval.</div>
                </div>
                <div style="padding:1.2rem 1.5rem;">
                    ${svReceiptRow('Organization', 'KMPCATS')}
                    ${svReceiptRow('Member', d.member)}
                    ${svReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                    ${svReceiptRow('Amount', '<strong style="color:var(--teal)">&#8369;' + d.amount + '</strong>')}
                    ${svReceiptRow('Payment Method', d.method)}
                    ${svReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                    ${svReceiptRow('Date & Time', d.date)}
                    ${svReceiptRow('Status', '<span style="color:#b8860b;font-weight:700;font-size:0.72rem;">⏳ Pending Approval</span>')}
                    <div style="text-align:center;margin-top:12px;color:#aaa;font-size:0.72rem;">KMPCATS Savings Receipt</div>
                </div>
            `;

            document.body.appendChild(wrapper);

            if (typeof html2canvas !== 'undefined') {
                html2canvas(wrapper, { scale: 2, useCORS: true }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = `KMPCATS_Savings_Receipt_${d.ref}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    wrapper.remove();
                });
            } else {
                wrapper.style.left = '0';
                wrapper.style.top = '50%';
                wrapper.style.transform = 'translateY(-50%)';
                wrapper.style.zIndex = '999999';
                alert('Screenshot library loading — press Ctrl+P to save as PDF, then close this receipt.');
            }
        }

        /* ═══ VOID REASON MODAL ═══ */
        const VOID_LABELS = {
            wrong_amount: 'Wrong amount entered',
            duplicate_payment: 'Duplicate payment',
            fraudulent: 'Fraudulent / suspicious transaction',
            other_member: 'Sent by wrong member',
            technical_error: 'System / technical error',
            other: 'Other'
        };
        function getVoidLabel(key) { return VOID_LABELS[key] || key || 'No reason provided'; }

        function showVoidReason(el) {
            var reason = getVoidLabel(el.dataset.reason);
            document.getElementById('sv-void-reason-text').textContent = reason;
            document.getElementById('sv-void-overlay').classList.add('active');
        }
        function svCloseVoidModal() {
            document.getElementById('sv-void-overlay').classList.remove('active');
        }
        document.getElementById('sv-void-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) svCloseVoidModal();
        });

    </script>

</body>

</html>