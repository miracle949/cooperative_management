<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Loan Status</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/loan_status.css?=6">
    <link rel="stylesheet" href="css_folder/loading.css?=8">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    {{-- Modal open/close animation --}}
    <style>
        #repayModal {
            display: none;
        }

        #repayModal .modal-dialog {
            opacity: 0;
            transform: translateY(-24px) scale(0.96);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        #repayModal.show .modal-dialog {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        #repay-backdrop {
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        #repay-backdrop.show {
            opacity: 1;
        }

        .mobile-modal {
            display: none;
        }

        .mobile-modal .modal-dialog {
            opacity: 0;
            transform: translateY(-24px) scale(0.96);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .mobile-modal.show .modal-dialog {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        #repay-backdrop {
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        #repay-backdrop.show {
            opacity: 1;
        }

        /* ── Receipt Modal Overlay ── */
        #loan-receipt-overlay {
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

        #loan-receipt-overlay.active {
            display: flex;
        }

        #loan-receipt-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: lrModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes lrModalIn {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .lr-receipt-header {
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid var(--line, #e5e7eb);
        }

        .lr-receipt-header .check-circle {
            width: 60px;
            height: 60px;
            background-color: var(--teal, #14825a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .lr-receipt-header .check-circle i {
            color: #fff;
            font-size: 26px;
        }

        .lr-receipt-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .lr-receipt-header p {
            color: var(--muted, #6b7280);
            font-size: 0.82rem;
            margin: 0;
        }

        .lr-receipt-body {
            padding: 1rem 1.5rem 1.25rem;
        }

        .lr-receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.55rem 0;
            border-bottom: 1px solid var(--line, #f0f0f0);
        }

        .lr-receipt-row:last-child {
            border-bottom: none;
        }

        .lr-receipt-row .label {
            font-size: 0.78rem;
            color: var(--muted, #6b7280);
        }

        .lr-receipt-row .value {
            font-size: 0.82rem;
            font-weight: 600;
            color: #1a1a1a;
            text-align: right;
            max-width: 55%;
            word-break: break-word;
        }

        .lr-receipt-row .value.highlight {
            color: var(--teal, #14825a);
            /* font-size: 0.95rem; */
            font-size: 13.12px;
        }

        .lr-ref-badge {
            display: inline-block;
            background: var(--teal-light, #e8f5e9);
            color: var(--teal, #14825a);
            padding: 0.2rem 0.6rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .lr-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #fef3c7;
            color: #b45309;
        }

        .lr-status-badge.completed {
            background: #d1fae5;
            color: #065f46;
        }

        .lr-status-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .lr-receipt-footer {
            display: flex;
            gap: 0.6rem;
            padding: 0.75rem 1.5rem 1.25rem;
        }

        .lr-btn-download {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem 0;
            border: none;
            border-radius: 12px;
            background: var(--teal, #14825a);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .lr-btn-close {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 0;
            border: 1px solid var(--line, #e5e7eb);
            border-radius: 12px;
            background: #fff;
            color: #374151;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .lr-btn-close:hover {
            background: #f9fafb;
        }

        /* .tx-voided-row {
            background: #fef2f2;
        } */

        .tx-voided-row:hover {
            background: #fde8e8;
        }

        .badge-voided {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fdecec;
            border: 1px solid #f5c6c6;
            color: #c0392b;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Loan hero status pill — make the "Overdue" label text red too,
           not just the dot, to match the badge treatment elsewhere. */
        .left-text .status.status-overdue {
            color: #c0392b;
        }

        .lsc-status.lsc-status-overdue {
            background: #fdecec;
            color: #c0392b;
            border: 1px solid #f5c6c6;
        }

        /* ═══ VOID REASON OVERLAY ═══ */
        #lr-void-overlay {
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

        #lr-void-overlay.active {
            display: flex;
        }

        #lr-void-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            margin: auto;
            animation: lrModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        .lr-void-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--line, #e5e7eb);
        }

        .lr-void-header .lr-void-circle {
            width: 60px;
            height: 60px;
            background-color: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .lr-void-header .lr-void-circle i {
            color: #fff;
            font-size: 26px;
        }

        .lr-void-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .lr-void-header p {
            color: var(--muted, #6b7280);
            font-size: 0.82rem;
            margin: 0;
        }

        .lr-void-body {
            padding: 1.5rem;
            text-align: center;
        }

        .lr-void-body .lr-void-label {
            font-size: 0.78rem;
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .lr-void-body .lr-void-value {
            font-size: 1rem;
            font-weight: 700;
            color: #c0392b;
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .lr-void-body .lr-void-amount-wrap {
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 12px;
            padding: 0.9rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .lr-void-body .lr-void-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #c0392b;
            line-height: 1.2;
        }

        .lr-void-body .lr-void-details {
            text-align: left;
            background: #fafafa;
            border-radius: 12px;
            padding: 0.25rem 1rem;
            margin-bottom: 1rem;
        }

        .lr-void-body .lr-void-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #f0e2e2;
            font-size: 0.85rem;
        }

        .lr-void-body .lr-void-row:last-child {
            border-bottom: none;
        }

        .lr-void-body .lr-void-row-label {
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.3px;
        }

        .lr-void-body .lr-void-row-value {
            color: #1a1a1a;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }

        .lr-void-footer {
            padding: 0 1.5rem 1.5rem;
        }

        .lr-btn-void-close {
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

        .lr-btn-void-close:hover {
            background: #f5f5f5;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")

        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            <main>
                <div class="parent-main">
                    @if(!$selectedLoan && $loans->isNotEmpty())
                        <div class="header-main">
                            <h3>Loan Repayments</h3>
                            <p>Manage your loan repayments by tracking payment history, upcoming due dates, and outstanding
                                balances.</p>
                        </div>
                    @endif

                    {{-- @if(!$selectedLoan && $loans->isNotEmpty())
                    <div class="parent-header">
                        <div class="filter-parent">

                            <div class="search-parent">
                                <i class="fa fa-search"></i>
                                <input type="search" id="loan-search" placeholder="Search by reference or type of loan">
                            </div>

                            <div class="filters-parent">
                                <div class="loan-type">
                                    <select id="loan-type-filter" class="form-select">
                                        <option value="" selected>-- Select Loan Type --</option>
                                        @foreach($loans->pluck('display_type')->unique() as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="reference">
                                    <input type="date" id="loan-date-filter" class="form-select"
                                        title="Filter by date applied" style="font-size: 13.5px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif --}}

                    @if($loans->isEmpty())
                        <!-- <div class="loan-hero" style="display:flex;align-items:center;justify-content:center;padding:40px;">
                                                                                                                                                                                                                                                                                                                                                                                        <p style="color:var(--teal);margin:0;">You have no approved loans yet.</p>
                                                                                                                                                                                                                                                                                                                                                                                    </div> -->
                        <div class="header-main">
                            <h3>Loan Repayments</h3>
                            <p>Manage your loan repayments by tracking payment history, upcoming due dates, and outstanding
                                balances.</p>
                        </div>
                        <div class="no-approved-loans" style=" ">
                            <div
                                style="width:56px;height:56px;border-radius:50%;background: #EDF0F5;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                <i class="fa fa-hourglass-half" style="color:var(--teal);font-size:22px;"></i>
                            </div>
                            <p style="color:var(--muted);margin:0; font-size: 14px;">You have no approved loans yet.</p>
                        </div>
                    @elseif(!$selectedLoan)
                        <div class="loan-select-section">
                            {{-- <div class="loan-grid-pagination-bar">
                                <span id="loan-grid-count">Showing {{ $loans->count() }} of {{ $loans->count() }}
                                </span>
                                <div class="pg-controls" id="loan-grid-pg"></div>
                            </div> --}}
                            <div class="loan-tabs-bar">
                                <div class="loan-tabs">
                                    <button type="button" class="loan-tab-btn active" data-status-filter="all"
                                        data-action="filterLoanStatusTab" data-arg='["all","|el|"]'>All</button>
                                    <button type="button" class="loan-tab-btn" data-status-filter="active"
                                        data-action="filterLoanStatusTab" data-arg='["active","|el|"]'>Active</button>
                                    <button type="button" class="loan-tab-btn" data-status-filter="completed"
                                        data-action="filterLoanStatusTab" data-arg='["completed","|el|"]'>Completed</button>
                                    <button type="button" class="loan-tab-btn" data-status-filter="overdue"
                                        data-action="filterLoanStatusTab" data-arg='["overdue","|el|"]'>Overdue</button>
                                </div>
                                {{-- <div class="pg-controls" id="loan-grid-pg"></div> --}}
                            </div>

                            <div class="loan-select-grid" id="loan-select-grid">
                                @foreach($loans as $loan)
                                    @php $cardStatus = strtolower($loan->card_status) === 'overdue' ? 'Active' : $loan->card_status; @endphp
                                    <div class="loan-select-card" data-ref="{{ strtolower($loan->reference_no) }}"
                                        data-type="{{ $loan->display_type }}"
                                        data-date="{{ \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d') }}"
                                        data-status="{{ strtolower($cardStatus) }}" data-action="navigateToLoan"
                                        data-arg='["{{ $loan->id }}"]'>
                                        <div class="lsc-top">
                                            <div class="lsc-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                                            <span class="lsc-status lsc-status-{{ strtolower($cardStatus) }}">
                                                {{ $cardStatus }}
                                            </span>
                                        </div>

                                        <div class="parent-type-status">
                                            <div>
                                                <h6 class="lsc-type">{{ $loan->display_type }}</h6>
                                                <p class="lsc-ref">{{ $loan->reference_no }}</p>
                                            </div>
                                            <div>
                                                <span class="lsc-status lsc-status-{{ strtolower($cardStatus) }}">
                                                    {{ $cardStatus }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="lsc-amount">
                                            <span>Principal</span>
                                            <p>₱{{ number_format($loan->lending_amount, 2) }}</p>
                                        </div>

                                        <div class="lsc-progress">
                                            <div class="lsc-progress-bar">
                                                <div class="lsc-progress-fill"
                                                    style="width:{{ $loan->progress_percent ?? 0 }}%;"></div>
                                            </div>
                                            <div class="amount-to-paid">
                                                <span>{{ $loan->payments_made ?? 0 }}/{{ $loan->total_payments ?? 0 }}
                                                    paid</span>
                                                <i class="fa fa-arrow-right lsc-arrow"></i>
                                            </div>
                                        </div>

                                        <div class="lsc-footer">
                                            <span><i class="fa fa-calendar"></i>
                                                {{ \Carbon\Carbon::parse($loan->created_at)->format('M d, Y') }}</span>
                                            <i class="fa fa-arrow-right lsc-arrow"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="loan-grid-empty" class="no-selected-loans" style="display:none;">
                                <div
                                    style="width:56px;height:56px;border-radius:50%;background:#EDF0F5;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                                    <i class="fa fa-inbox" style="color:var(--teal);font-size:22px;"></i>
                                </div>
                                <p id="loan-grid-empty-text" style="color:var(--muted);margin:0;font-size:14px;"></p>
                            </div>

                            <div class="loan-grid-pagination-bar">

                                <span id="loan-grid-count">Showing {{ $loans->count() }} of {{ $loans->count() }}
                                </span>
                                <div class="pg-controls" id="loan-grid-pg"></div>

                            </div>
                        </div>
                    @else
                        {{-- HERO --}}
                        <div class="back-loan-status">
                            <a href="{{ route('LoanStatus') }}">
                                <i class="fa fa-chevron-left"></i>
                                Back to Repayment page
                            </a>
                        </div>
                        <div class="loan-hero" id="loan-hero-section">
                            <div class="loan-hero-parent">
                                <div class="left-hero">
                                    <div class="left-text">
                                        <div class="status status-{{ strtolower($loanStatusLabel) }}">{{ $loanStatusLabel }}
                                        </div>
                                        <h3>{{ $selectedLoan->display_type }}</h3>
                                        <p><b>{{ $selectedLoan->reference_no }}</b> · Active
                                            {{ \Carbon\Carbon::parse($selectedLoan->created_at)->format('F d, Y') }}
                                        </p>
                                    </div>

                                    <h5>₱{{ number_format($fullBalanceRemaining, 2) }}</h5>

                                </div>
                                <div class="right-hero">
                                    <div class="payment-button payment-desktop">
                                        <button data-action="handleMakePaymentClick" data-arg='["monthly"]' {{ $fullBalanceRemaining <= 0 ? 'disabled style=opacity:.5;cursor:not-allowed;' : '' }}>
                                            <i class="fa fa-peso-sign"></i>
                                            <span>Make a Payment</span>
                                        </button>

                                        <!-- @if($selectedLoan->disbursed_at)
                                                                                                                                                                                                                                                                                                                                                                                                                        <button disabled style="opacity:.6;cursor:not-allowed;background:#e8f5ee;color:#1e7a4e;border:1px solid rgba(30,122,78,.3);">
                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fa fa-circle-check"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                            <span>Disbursed</span>
                                                                                                                                                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                                                                                                                                                    @else
                                                                                                                                                                                                                                                                                                                                                                                                                        <form action="{{ route('loan.disburse') }}" method="POST" style="margin:0;">
                                                                                                                                                                                                                                                                                                                                                                                                                            @csrf
                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="hidden" name="lending_id" value="{{ $selectedLoan->id }}">
                                                                                                                                                                                                                                                                                                                                                                                                                            <button type="submit">
                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fa fa-hand-holding-dollar"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                <span>Disburse Loan</span>
                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                        </form>
                                                                                                                                                                                                                                                                                                                                                                                                                    @endif -->
                                    </div>
                                </div>
                            </div>
                            <div class="parent-progress">
                                <div class="progress-header">
                                    {{-- <p>Repayment Progress</p>
                                    <span>{{ $lendingStatus->payments_made ?? 0 }} of
                                        {{ $lendingStatus->total_payments ?? 0 }} payments made</span> --}}
                                </div>
                                <div class="progress-body">
                                    <div class="progress-sub">
                                        <div class="progress" style="width: {{ $progressPercent }}%;"></div>
                                    </div>
                                </div>
                                <div class="progress-footer">
                                    <span>
                                        {{ $lendingStatus->payments_made ?? 0 }} of
                                        {{ $lendingStatus->total_payments ?? 0 }} payments made
                                        @if(($pendingCount ?? 0) > 0)
                                            · <span style="color:#b45309;font-weight:600;">{{ $pendingCount }} pending
                                                verification</span>
                                        @endif
                                    </span>
                                    <span><strong>₱{{ number_format($remainingPrincipal, 0) }}</strong> remaining </span>
                                </div>

                                @if(($selectedLoan->net_proceeds_adjustment_type ?? null) === 'add')
                                    <div class="net-proceeds-note"
                                        style="margin-top:10px;padding:10px 12px;background:#f0f7f4;border:1px solid #cfe6dc;border-radius:8px;font-size:12.5px;color:var(--muted);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                        <span><i class="fa fa-circle-info" style="color:var(--teal);"></i>
                                            Net Proceeds Adjustment — charges added back, you received the full loan amount
                                        </span>
                                        <strong style="color:var(--teal);white-space:nowrap;">
                                            +₱{{ number_format($processingFee + $serviceFee + $loanProtectionFee + $retentionFee, 2) }}
                                        </strong>
                                    </div>
                                @endif
                            </div>
                            <div class="perforation"></div>
                            <div class="scroll-wrap alh-wrap">
                                <button type="button" class="scroll-arrow left" aria-label="Scroll left"><i
                                        class="fa fa-chevron-left"></i></button>
                                <button type="button" class="scroll-arrow right" aria-label="Scroll right"><i
                                        class="fa fa-chevron-right"></i></button>
                                <div class="alh-parent">
                                    <div class="alh-stat">
                                        <span>Principal Amount</span>
                                        <h5>₱{{ number_format($selectedLoan->lending_amount, 2) }}</h5>
                                        <p>Applied
                                            {{ \Carbon\Carbon::parse($selectedLoan->created_at)->format('F d, Y') }}
                                        </p>
                                    </div>
                                    <div class="alh-stat">
                                        <span>Total Interest</span>
                                        <h5>₱{{ number_format($totalInterest, 2) }}</h5>
                                        <p>{{ number_format($interestRate, 2) }}% rate</p>
                                    </div>
                                    <div class="alh-stat">
                                        <span>Total Charges</span>
                                        <h5>₱{{ number_format($totalChargesWithInterest, 2) }}</h5>
                                        <p>Interest + fees</p>
                                    </div>
                                    <div class="alh-stat">
                                        <span>Net Proceeds</span>
                                        <h5>₱{{ number_format($netProceeds, 2) }}</h5>
                                        <p>Amount released to you</p>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-button payment-mobile">
                                <button data-action="handleMakePaymentClick" data-arg='["monthly"]' {{ $fullBalanceRemaining <= 0 ? 'disabled style=opacity:.5;cursor:not-allowed;' : '' }}>
                                    <i class="fa fa-peso-sign"></i>
                                    <span>Make a Payment</span>
                                </button>

                                <!-- @if($selectedLoan->disbursed_at)
                                                                                                                                                                                                                                                                                                                                                                                                                <button disabled style="opacity:.6;cursor:not-allowed;background:#e8f5ee;color:#1e7a4e;border:1px solid rgba(30,122,78,.3);">
                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fa fa-circle-check"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                    <span>Disbursed</span>
                                                                                                                                                                                                                                                                                                                                                                                                                </button>
                                                                                                                                                                                                                                                                                                                                                                                                            @else
                                                                                                                                                                                                                                                                                                                                                                                                                <form action="{{ route('loan.disburse') }}" method="POST" style="margin:0;">
                                                                                                                                                                                                                                                                                                                                                                                                                    @csrf
                                                                                                                                                                                                                                                                                                                                                                                                                    <input type="hidden" name="lending_id" value="{{ $selectedLoan->id }}">
                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="submit">
                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fa fa-hand-holding-dollar"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                        <span>Disburse Loan</span>
                                                                                                                                                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                                                                                                                                                </form>
                                                                                                                                                                                                                                                                                                                                                                                                            @endif -->
                            </div>

                        </div>

                        {{-- 3 SUMMARY BOXES --}}
                        <div class="scroll-wrap loan-wrap">
                            <button type="button" class="scroll-arrow left" aria-label="Scroll left"><i
                                    class="fa fa-chevron-left"></i></button>
                            <button type="button" class="scroll-arrow right" aria-label="Scroll right"><i
                                    class="fa fa-chevron-right"></i></button>
                            <div class="loan-parent-box" style="padding: 0">
                                <div class="loan-box">
                                    <div class="loan-header">
                                        <h5>Monthly Due</h5>
                                        <div class="loan-icon"><i class="fa fa-calendar-day"></i></div>
                                    </div>
                                    <p id="monthly-due-value">
                                        ₱{{ number_format($currentDueAmount + $currentOverduePenalty, 2) }}</p>
                                    <span>Every {{ \Carbon\Carbon::parse($selectedLoan->created_at)->format('jS') }}</span>
                                </div>

                                <div class="loan-box" id="due-date-stat">
                                    <div class="loan-header">
                                        <h5>Due Date</h5>
                                        <div class="loan-icon"><i class="fa fa-calendar-xmark"></i></div>
                                    </div>
                                    @if($overdueDate)
                                        <p class="stat-value">{{ $overdueDate->format('F d') }}</p>
                                        <span class="stat-sub badge-overdue">
                                            <i class="fa fa-triangle-exclamation"></i>
                                            #{{ $overdueInstallmentNumber }} · {{ $overdueDaysCount }}
                                            day{{ $overdueDaysCount == 1 ? '' : 's' }} overdue
                                        </span>
                                    @else
                                        <p class="stat-value">—</p>
                                        <span class="stat-sub">No overdue payments</span>
                                    @endif
                                </div>

                                <div class="loan-box" id="next-due-stat">
                                    <div class="loan-header">
                                        <h5>Next Due</h5>
                                        <div class="loan-icon"><i class="fa fa-calendar-check"></i></div>
                                    </div>
                                    <p class="stat-value">
                                        {{ $displayNextDueDate ? $displayNextDueDate->format('F d') : '—' }}
                                    </p>
                                    @if($displayDaysAway === null)
                                        <span class="stat-sub">Fully paid</span>
                                    @elseif($displayDaysAway == 0)
                                        <span class="stat-sub badge-upcoming-next" style="padding: 0;">Due today</span>
                                    @else
                                        <span class="stat-sub badge-upcoming-next" style="padding: 0;">{{ $displayDaysAway }}
                                            days
                                            away</span>
                                    @endif
                                </div>

                                {{-- <div class="loan-box">
                                    <div class="loan-header">
                                        <h5>Total Payable</h5>
                                        <div class="loan-icon"><i class="fa fa-check"></i></div>
                                    </div>
                                    <p>₱{{ number_format($totalPayable, 2) }}</p>
                                    <span>Principal + interest + charges</span>
                                </div> --}}

                                <div class="loan-box" id="penalty-box">
                                    <div class="loan-header">
                                        <h5>Penalty</h5>
                                        <div class="loan-icon"><i class="fa fa-triangle-exclamation"></i></div>
                                    </div>
                                    <p class="penalty-value" style="{{ $penaltyAmount > 0 ? 'color: var(--coral);' : '' }}">
                                        ₱{{ number_format($penaltyAmount, 2) }}</p>
                                    <span class="penalty-caption">
                                        @if($penaltyAmount > 0 && ($lendingStatus->penalty_amount ?? 0) == 0)
                                            Will apply on next payment
                                        @elseif($penaltyAmount > 0)
                                            Overdue penalty applied
                                        @else
                                            No penalties applied
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- SCHEDULE & CHARGES --}}
                        <div class="schedule-charges">
                            <div class="schedule-parent">
                                <div class="schedule-header" data-action="openScheduleModal" style="cursor:pointer;">
                                    <!-- <div class="header-tag">
                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="header-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fa fa-calendar-check"></i> 
                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                Payment Schedule</div> -->
                                    <div>
                                        <div class="header-tag">Payment Schedule</div>
                                        <p>View your upcoming loan payment</p>
                                    </div>
                                    <span>{{ $lendingStatus->payments_made ?? 0 }} of
                                        {{ $lendingStatus->total_payments ?? 0 }} paid</span>

                                    <i class="fa fa-arrow-right"></i>
                                </div>
                                <div class="schedule-body">
                                    @forelse($paymentSchedule as $row)
                                        <div class="pay-item schedule-row"
                                            data-status="{{ $row['paid'] ? 'paid' : (($row['pending'] ?? false) ? 'pending' : ($row['overdue'] ? 'overdue' : ($row['is_next'] ? 'active' : 'upcoming'))) }}"
                                            data-number="{{ $row['number'] }}" data-date="{{ $row['date'] }}"
                                            data-amount="{{ $row['amount'] + ($row['penalty'] ?? 0) }}"
                                            data-action="selectScheduleRow" data-arg='["|el|"]'>
                                            <div class="item">
                                                <div class="item-icon">
                                                    <span>{{ $row['number'] }}</span>
                                                </div>
                                                <p>{{ $row['date'] }}</p>
                                            </div>
                                            <div class="item-amount">
                                                <p>₱{{ number_format($row['amount'] + ($row['penalty'] ?? 0), 2) }}</p>
                                                @if($row['paid'])
                                                    <p class="paid"><i class="fa fa-check"></i> Paid</p>
                                                @elseif($row['pending'] ?? false)
                                                    <p class="badge-pending-item"><i class="fa fa-hourglass-half"></i> Pending
                                                    </p>
                                                @elseif($row['voided'] ?? false)
                                                    <p class="badge-voided"><i class="fa fa-ban"></i> Returned</p>
                                                @elseif($row['overdue'])
                                                    <p class="badge-overdue-item"><i class="fa fa-triangle-exclamation"></i>
                                                        Overdue
                                                    </p>
                                                @else
                                                    <p class="badge-upcoming"><i class="fa fa-clock"></i> Upcoming</p>
                                                @endif

                                                @if(($row['penalty'] ?? 0) > 0)
                                                    <p
                                                        style="margin:2px 0 0; font-size:12px; color:var(--red); background-color: var(--red-tint);">
                                                        + ₱{{ number_format($row['penalty'], 2) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p style="color:#999;padding:1rem;">No schedule available.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="charges-parent">
                                <div class="charges-header" data-action="openChargesModal" style="cursor:pointer;">
                                    <!-- <div class="header-tag">
                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="header-icon">
                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fa fa-money-check-dollar"></i> 
                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                    Loan Charges</div> -->
                                    <div>
                                        <div class="header-tag">Loan Charges</div>
                                        <p>View your breakdown loan charges</p>
                                    </div>
                                    <div>
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                                <div class="charges-body">
                                    <div class="pay-item">
                                        <div class="parent-item">
                                            <div class="item">
                                                <div class="icon"><i class="fa fa-calculator"></i></div>
                                                <div><span>Processing Fee</span>
                                                    <p>Processing & collection</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item-amount">
                                            <p>₱{{ number_format($processingFee, 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="pay-item">
                                        <div class="parent-item">
                                            <div class="item">
                                                <div class="icon"><i class="fa fa-file-contract"></i></div>
                                                <div><span>Service & Legal Fee</span>
                                                    <p>One-time fee</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item-amount">
                                            <p>₱{{ number_format($serviceFee, 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="pay-item">
                                        <div class="parent-item">
                                            <div class="item">
                                                <div class="icon"><i class="fa fa-shield-halved"></i></div>
                                                <div><span>Loan Protection Plan</span>
                                                    <p>Per month of term</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item-amount">
                                            <p>₱{{ number_format($loanProtectionFee, 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="pay-item">
                                        <div class="parent-item">
                                            <div class="item">
                                                <div class="icon"><i class="fa fa-piggy-bank"></i></div>
                                                <div><span>Retention / CBU</span>
                                                    <p>Held as capital build-up</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item-amount">
                                            <p>₱{{ number_format($retentionFee, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="charges-footer">
                                    <div class="total-charges">
                                        <span>Total Charges</span>
                                        <p>₱{{ number_format($totalCharges, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PAYMENT HISTORY Table--}}
                        @php
                            $phGroups = $paymentHistory->groupBy(fn($p) => ($p->reference_no ?: 'no-ref-' . $p->id) . '|' . strtolower($p->status ?? ''));
                        @endphp

                        @php
                            $phItems = fn($g) => $g->map(fn($p) => [
                                'number' => $p->payment_number,
                                'amount' => strtolower($p->status ?? '') === 'voided' ? 0 : (float) $p->amount_paid,
                                'penalty' => (float) ($p->late_fee ?? 0),
                                'due' => $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('M d, Y') : '—',
                            ])->values();
                        @endphp
                        <div class="loan-history-mobile">
                            <div class="loan-header-mobile">
                                <div class="header-tag">Payment History</div>
                            </div>
                            <div class="loan-body-mobile">
                                @forelse($phGroups as $group)
                                    @php
                                        $group = $group->sortBy('payment_number')->values();
                                        $first = $group->first();
                                        $isGroup = $group->count() > 1;
                                        $gStatus = strtolower($first->status ?? '');
                                        $gAmount = $gStatus === 'voided' ? 0 : $group->sum('amount_paid');
                                    @endphp

                                    @if($isGroup)
                                        <div class="ph-card ph-group-card" style="cursor:pointer;"
                                            data-ref="{{ $first->reference_no }}"
                                            data-date="{{ \Carbon\Carbon::parse($first->payment_date)->format('M d, Y') }} · {{ \Carbon\Carbon::parse($first->created_at)->format('h:i A') }}"
                                            data-method="{{ $first->payment_method }}" data-status="{{ $gStatus }}"
                                            data-total="{{ number_format($gAmount + $group->sum(fn($p) => (float) ($p->late_fee ?? 0)), 2) }}"
                                            data-items="{{ json_encode($phItems($group)) }}">
                                            <div class="ph-card-top">
                                                <div class="ph-card-icon"><i class="fa fa-receipt"></i></div>
                                                <div class="ph-card-info">
                                                    <p class="ph-card-ref">
                                                        {{ $first->reference_no }}
                                                    </p>
                                                    <p class="ph-card-meta">
                                                        {{ $group->count() }} installments ·
                                                        {{ \Carbon\Carbon::parse($first->payment_date)->format('M d, Y') }}
                                                    </p>
                                                </div>
                                                @if($gStatus === 'voided')
                                                    <span class="badge-voided"><i class="fa fa-ban"></i> Returned</span>
                                                @elseif($gStatus === 'pending')
                                                    <span class="badge-pending"><i class="fa fa-hourglass-half"></i> Pending</span>
                                                @else
                                                    <span class="badge-paid">Paid</span>
                                                @endif
                                            </div>
                                            <div class="ph-card-bottom">
                                                <div class="ph-card-detail"><span>Total</span>
                                                    <p>₱{{ number_format($gAmount, 2) }}</p>
                                                </div>
                                                <div class="ph-card-detail"><span>Method</span>
                                                    <p>{{ $first->payment_method }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @foreach($isGroup ? [] : $group as $payment)
                                        @php
                                            $wasLate = $payment->due_date && \Carbon\Carbon::parse($payment->payment_date)->gt(\Carbon\Carbon::parse($payment->due_date));
                                        @endphp
                                        <div class="ph-card ph-row-mobile {{ strtolower($payment->status ?? '') === 'voided' ? 'tx-voided-row' : '' }}"
                                            @if(strtolower($payment->status ?? '') === 'voided') style="cursor:pointer;"
                                                data-reason="{{ $payment->void_reason }}" data-amount="{{ $payment->amount_paid }}"
                                                data-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}"
                                                data-method="{{ $payment->payment_method }}" data-ref="{{ $payment->reference_no }}"
                                                data-loanref="{{ $payment->loan_reference_no ?? ($payment->loan_ref ?? '') }}"
                                                data-paymentnum="{{ $payment->payment_number ?? '' }}" data-action="showLoanVoidReason"
                                            data-arg='["|el|"]' @endif>
                                            <div class="ph-card-top">
                                                <div class="ph-card-icon"><i class="fa fa-receipt"></i></div>
                                                <div class="ph-card-info">
                                                    <p class="ph-card-ref">
                                                        @if($isGroup) Installment #{{ $payment->payment_number }} @else
                                                        {{ $payment->reference_no }} @endif
                                                    </p>
                                                    <p class="ph-card-meta">
                                                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }} ·
                                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('h:i A') }}
                                                    </p>
                                                </div>
                                                @if(strtolower($payment->status ?? '') === 'voided')
                                                    <span class="badge-voided"><i class="fa fa-ban"></i> Returned</span>
                                                @elseif(strtolower($payment->status ?? '') === 'pending')
                                                    <span class="badge-pending"><i class="fa fa-hourglass-half"></i> Pending</span>
                                                @elseif($wasLate)
                                                    <span class="badge-overdue"><i class="fa fa-triangle-exclamation"></i>
                                                        Overdue</span>
                                                @else
                                                    <span class="badge-paid">Paid</span>
                                                @endif
                                            </div>
                                            <div class="ph-card-bottom">
                                                <div class="ph-card-detail">
                                                    <span>Amount</span>
                                                    <p>₱{{ number_format(strtolower($payment->status ?? '') === 'voided' ? 0 : $payment->amount_paid, 2) }}
                                                    </p>
                                                </div>
                                                <div class="ph-card-detail">
                                                    <span>Penalty</span>
                                                    <p>
                                                        @if(($payment->late_fee ?? 0) > 0)
                                                            <span
                                                                style="color:var(--coral);font-weight:600;">₱{{ number_format($payment->late_fee, 2) }}</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="ph-card-detail">
                                                    <span>Method</span>
                                                    <p>{{ $payment->payment_method }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @empty
                                    <p style="color:#999;padding:1.5rem;text-align:center;">No payment history yet.</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="loan-history">
                            <div class="loan-header">
                                <div class="header-tag">Payment History</div>
                                <p>View your payment history below.</p>
                            </div>
                            <div class="loan-body">
                                <div class="parent-table">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Reference No.</th>
                                                <th>Payment Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th>Penalty</th>
                                                <th>Penalty Date</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ph-tbody">
                                            @forelse($phGroups as $gi => $group)
                                                @php
                                                    $group = $group->sortBy('payment_number')->values();
                                                    $first = $group->first();
                                                    $isGroup = $group->count() > 1;
                                                    $gStatus = strtolower($first->status ?? '');
                                                    $gLate = $group->contains(fn($p) => $p->due_date && \Carbon\Carbon::parse($p->payment_date)->gt(\Carbon\Carbon::parse($p->due_date)));
                                                    $gAmount = $gStatus === 'voided' ? 0 : $group->sum('amount_paid');
                                                    $gPenalty = $group->sum(fn($p) => (float) ($p->late_fee ?? 0));
                                                @endphp

                                                @if($isGroup)
                                                    <tr class="ph-row ph-group-row" data-group="g{{ $gi }}" style="cursor:pointer;"
                                                        data-ref="{{ $first->reference_no }}"
                                                        data-date="{{ \Carbon\Carbon::parse($first->payment_date)->format('M d, Y') }} · {{ \Carbon\Carbon::parse($first->created_at)->format('h:i A') }}"
                                                        data-method="{{ $first->payment_method }}" data-status="{{ $gStatus }}"
                                                        data-total="{{ number_format($gAmount + $gPenalty, 2) }}"
                                                        data-items="{{ json_encode($phItems($group)) }}">
                                                        <td>
                                                            {{ $first->reference_no }}
                                                            (<span class="ph-count-badge">{{ $group->count() }}
                                                                installments</span>)
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($first->payment_date)->format('M d, Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($first->created_at)->format('h:i A') }}
                                                        </td>
                                                        <td>₱{{ number_format($gAmount, 2) }}</td>
                                                        <td>
                                                            @if($gPenalty > 0)
                                                                <span
                                                                    style="color: var(--coral); font-weight: 600;">₱{{ number_format($gPenalty, 2) }}</span>
                                                            @else
                                                                <span style="color: #aaa;">—</span>
                                                            @endif
                                                        </td>
                                                        <td><span style="color: #aaa;">—</span></td>
                                                        <td>{{ $first->payment_method }}</td>
                                                        <td>
                                                            @if($gStatus === 'voided')
                                                                <span class="badge-voided"><i class="fa fa-ban"></i> Returned</span>
                                                            @elseif($gStatus === 'pending')
                                                                <span class="badge-pending"><i class="fa fa-hourglass-half"></i>
                                                                    Pending</span>
                                                            @elseif($gLate)
                                                                <span class="badge-overdue"><i class="fa fa-triangle-exclamation"></i>
                                                                    Paid (Overdue)</span>
                                                            @else
                                                                <span class="badge-paid"> Paid</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif

                                                @foreach($isGroup ? [] : $group as $payment)
                                                    <tr class="{{ $isGroup ? 'ph-child' : 'ph-row' }} {{ strtolower($payment->status ?? '') === 'voided' ? 'tx-voided-row' : '' }}"
                                                        @if($isGroup) data-group="g{{ $gi }}" @endif @if(strtolower($payment->status ?? '') === 'voided') style="cursor:pointer;"
                                                            data-reason="{{ $payment->void_reason }}"
                                                            data-amount="{{ $payment->amount_paid }}"
                                                            data-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}"
                                                            data-method="{{ $payment->payment_method }}"
                                                            data-ref="{{ $payment->reference_no }}"
                                                            data-loanref="{{ $payment->loan_reference_no ?? ($payment->loan_ref ?? '') }}"
                                                            data-paymentnum="{{ $payment->payment_number ?? '' }}"
                                                        data-action="showLoanVoidReason" data-arg='["|el|"]' @endif>
                                                        <td>
                                                            @if($isGroup) Installment #{{ $payment->payment_number }} @else
                                                            {{ $payment->reference_no }} @endif
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('h:i A') }}
                                                        </td>
                                                        <td>₱{{ number_format(strtolower($payment->status ?? '') === 'voided' ? 0 : $payment->amount_paid, 2) }}
                                                        </td>
                                                        <td>
                                                            @if(($payment->late_fee ?? 0) > 0)
                                                                <span
                                                                    style="color: var(--coral); font-weight: 600;">₱{{ number_format($payment->late_fee, 2) }}</span>
                                                            @else
                                                                <span style="color: #aaa;">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($payment->penalty_applied_at)
                                                                {{ \Carbon\Carbon::parse($payment->penalty_applied_at)->format('M d, Y · h:i A') }}
                                                            @else
                                                                <span style="color: #aaa;">—</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $payment->payment_method }}</td>
                                                        <td>
                                                            @php
                                                                $wasLate = $payment->due_date && \Carbon\Carbon::parse($payment->payment_date)->gt(\Carbon\Carbon::parse($payment->due_date));
                                                            @endphp
                                                            @if(strtolower($payment->status ?? '') === 'voided')
                                                                <span class="badge-voided"><i class="fa fa-ban"></i> Returned</span>
                                                            @elseif(strtolower($payment->status ?? '') === 'pending')
                                                                <span class="badge-pending"><i class="fa fa-hourglass-half"></i>
                                                                    Pending</span>
                                                            @elseif($wasLate)
                                                                <span class="badge-overdue"><i class="fa fa-triangle-exclamation"></i>
                                                                    Paid (Overdue)</span>
                                                            @else
                                                                <span class="badge-paid"> Paid</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="8" style="text-align:center;color:#999;padding:1.5rem;">No
                                                        payment history yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($paymentHistory->isNotEmpty())
                                    <div class="sc-table-footer">
                                        <span id="ph-footer-count">Showing {{ min(10, $phGroups->count()) }} of
                                            {{ $phGroups->count() }} payments</span>
                                        <div class="sc-pagination" id="ph-pagination"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </main>
        </div>

        {{-- REPAYMENT MODAL --}}
        <div class="modal fade mobile-modal" id="repayModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content"
                    style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">

                    {{-- Modal Header --}}
                    <div class="modal-header"
                        style="background: #ffffff; padding: 1.4rem 1.6rem; border-bottom: 1px solid var(--line);">
                        <div class="modal-parent">
                            <div class="modal-icon">
                                <i class="fa fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <h5 class="modal-title"
                                    style="color: #1a1a1a; font-size: 17px; font-weight: 600; margin: 0;">
                                    Make a Payment
                                </h5>
                                <p style="color: var(--muted); font-size: 13.5px; margin: 3.2px 0 0;">
                                    {{ $selectedLoan->display_type ?? '' }} —
                                    ₱{{ number_format($selectedLoan->lending_amount ?? 0, 2) }}
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" style="color: var(--muted); font-size: 16px;"
                            data-action="closeRepayModal"></button>
                    </div>

                    <div class="modal-body" style="padding: 1.6rem; background: #fff;">

                        {{-- Payment Type Toggle --}}

                        <form action="{{ route('repayment.store') }}" method="POST" id="cash-repay-form"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="lending_id" value="{{ $selectedLoan->id ?? '' }}">
                            <input type="hidden" name="member_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="payment_number" value="{{ $nextPaymentNumber }}">
                            <input type="hidden" name="payment_type" id="cash-payment-type" value="monthly">

                            {{-- Installments to Pay --}}
                            <div style="margin-bottom: 1.1rem;">
                                <div class="inst-head">
                                    <label class="inst-label">Installments to Pay</label>
                                    @if($payableInstallments->count() > 1)
                                        <button type="button" class="inst-select-all" id="inst-select-all">Select
                                            all</button>
                                    @endif
                                </div>

                                @if($payableInstallments->isEmpty())
                                    <div class="inst-empty">
                                        <i class="fa fa-circle-check"></i>
                                        All remaining installments are already paid or awaiting verification.
                                    </div>
                                @else
                                    <div class="inst-list">
                                        @foreach($payableInstallments as $idx => $inst)
                                            <label class="inst-card {{ $inst['overdue'] ? 'is-overdue' : '' }}">
                                                <input type="checkbox" class="inst-check" name="installments[]"
                                                    value="{{ $inst['number'] }}" data-amount="{{ $inst['amount'] }}"
                                                    data-penalty="{{ $inst['penalty'] }}" {{ $idx === 0 ? 'checked' : '' }}>
                                                <span class="inst-box"><i class="fa fa-check"></i></span>
                                                <span class="inst-num">{{ $inst['number'] }}</span>
                                                <span class="inst-info">
                                                    <span class="inst-date">{{ $inst['date'] }}</span>
                                                    @if($inst['overdue'])
                                                        <span class="inst-tag"><i class="fa fa-triangle-exclamation"></i>
                                                            Overdue</span>
                                                    @else
                                                        <span class="inst-tag"><i class="fa fa-clock"></i> Upcoming</span>
                                                    @endif
                                                </span>
                                                <span class="inst-amt">
                                                    ₱{{ number_format($inst['amount'], 2) }}
                                                    @if($inst['penalty'] > 0)
                                                        <small>+ ₱{{ number_format($inst['penalty'], 2) }} late fee</small>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="inst-summary" id="inst-summary"></div>
                                    <p class="inst-hint">
                                        <i class="fa fa-circle-info"></i>
                                        Installments are paid in order — selecting one also selects every earlier
                                        installment.
                                    </p>
                                @endif
                            </div>

                            {{-- Amount --}}
                            <div style="margin-bottom: 1.1rem;">
                                <label
                                    style="font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                    Amount to Pay (₱)
                                </label>
                                <div style="position: relative;">
                                    <span
                                        style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--teal); font-weight: 500; font-size: 15px;">₱</span>
                                    <input type="number" name="amount_paid" id="repay-amount-input" class="form-control"
                                        value="{{ $currentDueAmount }}"
                                        style="padding-left: 28px; border-radius: 10px; border: 1.5px solid #e0e0e0; font-size: 14px; font-weight: 500; color: var(--teal); height: 46px;"
                                        readonly>
                                </div>
                                <div id="penalty-breakdown"
                                    style="margin: 8px 0 0; font-size: 12.5px; line-height: 1.5; color: #856404; background: #fef3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 8px 10px; display: none;">
                                    <i class="fa fa-triangle-exclamation" style="color: #b8860b;"></i>
                                    <span id="penalty-breakdown-text"></span>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div style="margin-bottom: 1.1rem;">
                                <label
                                    style="font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                    Payment Method
                                </label>
                                <select name="payment_method" id="repay-method" class="form-select"
                                    data-action="handleMethodChange" data-arg='["|value|"]'
                                    style="border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px; font-size: 14px; color: #333;">
                                    <option value="" disabled selected>Select payment method...</option>
                                    @foreach($paymentMethods as $pm)
                                        @php
                                            $pmQr = ($pm->has_qr_code && $pm->qr_code_image_path)
                                                ? url('/proof/' . ltrim($pm->qr_code_image_path, '/')) : '';
                                            $pmNeedsProof = strtolower(trim($pm->method_name)) !== 'cash';
                                        @endphp
                                        <option value="{{ $pm->method_name }}" data-qr="{{ $pmQr }}"
                                            data-account-name="{{ $pm->account_name ?? '' }}"
                                            data-account-number="{{ $pm->account_number ?? '' }}"
                                            data-instructions="{{ $pm->instructions ?? '' }}"
                                            data-requires-proof="{{ $pmNeedsProof ? 1 : 0 }}"
                                            data-ref-length="{{ $pm->reference_length ?? '' }}">
                                            {{ $pm->method_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p id="repay-method-error"
                                    style="display:none; margin:6px 0 0; color:#e53e3e; font-size:12px; font-weight:600;">
                                    <i class="fa fa-circle-exclamation"></i> Please select a payment method.
                                </p>
                            </div>

                            {{-- Payment proof section (any method that needs proof) --}}
                            <div id="gcash-section" style="display: none;">
                                <div style="border-top: 1.5px dashed #e8e8e8; margin: 1.2rem 0;"></div>

                                <div id="method-details"></div>

                                <div style="margin-top: 1.1rem;">
                                    <label
                                        style="font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                        Upload Payment Screenshot <span
                                            style="color:#aaa; font-weight:400; text-transform:none;">(proof of
                                            payment)</span>
                                    </label>
                                    <input type="file" name="gcash_proof" id="gcash-proof-input"
                                        accept="image/png,image/jpeg,image/jpg" class="form-control"
                                        style="border-radius: 10px; border: 1.5px solid #e0e0e0; font-size: 14px; padding: 8px;">
                                    <div id="gcash-proof-preview" style="display:none; margin-top:10px;">
                                        <img id="gcash-proof-preview-img"
                                            style="width:100%; height:180px; object-fit:cover; border-radius:8px; border:1px solid #e0e0e0;">
                                    </div>
                                </div>

                                <div style="margin-top: 1.1rem;">
                                    <label id="ref-label"
                                        style="font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                        Reference Number <span style="color: red;">*</span>
                                    </label>
                                    <p id="repay-ref-used-msg"
                                        style="display:none; margin:0 0 6px; color:#e53e3e; font-size:12px; font-weight:600;">
                                        <i class="fa fa-circle-exclamation"></i> This reference number has already been
                                        used for a transaction.
                                    </p>
                                    <input type="text" name="gcash_reference_no" id="repay-gcash-ref"
                                        class="form-control" placeholder="Reference number"
                                        style="border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px; font-size: 14px; color: #333;">
                                </div>

                                <div
                                    style="background: #fef3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 10px 12px; margin-top: 1rem;">
                                    <p style="margin: 0; font-size: 12px; color: #856404;">
                                        <i class="fa fa-triangle-exclamation"></i>
                                        Submitting false or manipulated payment details will result in account
                                        suspension and potential legal action.
                                    </p>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div id="notes-section" style="margin-top: 1.1rem;">
                                <label
                                    style="font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                                    Notes (optional)
                                </label>
                                <textarea name="notes" id="repay-notes-input" class="form-control" rows="2"
                                    placeholder="Additional remarks..."
                                    style="border-radius: 10px; border: 1.5px solid #e0e0e0; font-size: 14px; color: #333; resize: none;"></textarea>
                            </div>
                        </form>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer"
                        style="background: #f8f9fa; border-top: 1px solid rgba(0,0,0,0.1); padding: 1rem 1.6rem; display: flex; justify-content: center; align-items: center; flex-direction: column; gap: 8px;">
                        <button type="button" id="confirm-pay-btn" class="btn w-100"
                            style="background: var(--teal); color: white; border-radius: 8px; font-size: 14px; font-weight: 600; padding: 10px 22px; border: none; display: flex; align-items: center; gap: 6px; justify-content: center;">
                            <i class="fa-solid fa-check" style="font-size: 12px;"></i>
                            Confirm Payment
                        </button>
                        <button type="button" class="btn w-100 text-center" data-action="closeRepayModal"
                            style="border-radius: 8px; font-size: 14px; padding: 10px 18px; border: 1.5px solid #e0e0e0; color: var(--muted);">
                            Cancel
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Payment Schedule Modal (mobile) --}}
        <div class="modal fade mobile-modal" id="scheduleModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content"
                    style="border:none;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                    <div class="modal-header"
                        style="background:#fff;padding:1.4rem 1.6rem;border-bottom:1px solid var(--line);">
                        <div class="modal-parent">
                            <div class="modal-icon"><i class="fa fa-calendar-check"></i></div>
                            <div>
                                <h5 class="modal-title" style="color:#1a1a1a;font-size:17px;font-weight:600;margin:0;">
                                    Payment Schedule</h5>
                                <p style="color:var(--muted);font-size:13.5px;margin:3.2px 0 0;">
                                    {{ $lendingStatus->payments_made ?? 0 }} of
                                    {{ $lendingStatus->total_payments ?? 0 }} paid
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" style="color:var(--muted);font-size:16px;"
                            data-action="closeModal" data-arg='["scheduleModal"]'></button>
                    </div>
                    <div class="modal-body" style="padding:0;background:#fff;max-height:70vh;overflow-y:auto;">
                        @forelse($paymentSchedule as $row)
                            <div class="pay-item schedule-row"
                                data-status="{{ $row['paid'] ? 'paid' : (($row['pending'] ?? false) ? 'pending' : ($row['overdue'] ? 'overdue' : ($row['is_next'] ? 'active' : 'upcoming'))) }}"
                                data-number="{{ $row['number'] }}" data-date="{{ $row['date'] }}"
                                data-amount="{{ $row['amount'] + ($row['penalty'] ?? 0) }}" data-action="pick-schedule-row"
                                data-arg='["|el|"]'>
                                <div class="item">
                                    <div class="item-icon"><span>{{ $row['number'] }}</span></div>
                                    <p>{{ $row['date'] }}</p>
                                </div>
                                <div class="item-amount">
                                    <p>₱{{ number_format($row['amount'] + ($row['penalty'] ?? 0), 2) }}</p>
                                    @if($row['paid'])
                                        <p class="paid"><i class="fa fa-check"></i> Paid</p>
                                    @elseif($row['pending'] ?? false)
                                        <p class="badge-pending-item"><i class="fa fa-hourglass-half"></i> Pending</p>
                                    @elseif($row['voided'] ?? false)
                                        <p class="badge-voided"><i class="fa fa-ban"></i> Returned</p>
                                    @elseif($row['overdue'])
                                        <p class="badge-overdue-item"><i class="fa fa-triangle-exclamation"></i> Overdue</p>
                                    @else
                                        <p class="badge-upcoming"><i class="fa fa-clock"></i> Upcoming</p>
                                    @endif
                                    @if(($row['penalty'] ?? 0) > 0)
                                        <p
                                            style="margin:2px 0 0;font-size:12px;color:var(--red);background-color:var(--red-tint);">
                                            + ₱{{ number_format($row['penalty'], 2) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p style="color:#999;padding:1rem;">No schedule available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Loan Charges Modal (mobile) --}}
        <div class="modal fade mobile-modal" id="chargesModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                <div class="modal-content"
                    style="border:none;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                    <div class="modal-header"
                        style="background:#fff;padding:1.4rem 1.6rem;border-bottom:1px solid var(--line);">
                        <div class="modal-parent">
                            <div class="modal-icon"><i class="fa fa-money-check-dollar"></i></div>
                            <div>
                                <h5 class="modal-title" style="color:#1a1a1a;font-size:17px;font-weight:600;margin:0;">
                                    Loan Charges</h5>
                                <p style="color:var(--muted);font-size:13.5px;margin:3.2px 0 0;">Breakdown of your loan
                                    charges</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" style="color:var(--muted);font-size:16px;"
                            data-action="closeModal" data-arg='["chargesModal"]'></button>
                    </div>
                    <div class="modal-body" style="padding:0;background:#fff;max-height:70vh;overflow-y:auto;">
                        <div class="pay-item">
                            <div class="parent-item">
                                <div class="item">
                                    <div class="icon"><i class="fa fa-calculator"></i></div>
                                    <div><span>Processing Fee</span>
                                        <p>Processing & collection</p>
                                    </div>
                                </div>
                            </div>
                            <div class="item-amount">
                                <p>₱{{ number_format($processingFee, 2) }}</p>
                            </div>
                        </div>
                        <div class="pay-item">
                            <div class="parent-item">
                                <div class="item">
                                    <div class="icon"><i class="fa fa-file-contract"></i></div>
                                    <div><span>Service & Legal Fee</span>
                                        <p>One-time fee</p>
                                    </div>
                                </div>
                            </div>
                            <div class="item-amount">
                                <p>₱{{ number_format($serviceFee, 2) }}</p>
                            </div>
                        </div>
                        <div class="pay-item">
                            <div class="parent-item">
                                <div class="item">
                                    <div class="icon"><i class="fa fa-shield-halved"></i></div>
                                    <div><span>Loan Protection Plan</span>
                                        <p>Per month of term</p>
                                    </div>
                                </div>
                            </div>
                            <div class="item-amount">
                                <p>₱{{ number_format($loanProtectionFee, 2) }}</p>
                            </div>
                        </div>
                        <div class="pay-item">
                            <div class="parent-item">
                                <div class="item">
                                    <div class="icon"><i class="fa fa-piggy-bank"></i></div>
                                    <div><span>Retention / CBU</span>
                                        <p>Held as capital build-up</p>
                                    </div>
                                </div>
                            </div>
                            <div class="item-amount">
                                <p>₱{{ number_format($retentionFee, 2) }}</p>
                            </div>
                        </div>
                        <div class="total-charges"
                            style="padding:18px 20px;display:flex;justify-content:space-between;align-items:center;background-color:var(--lavender-tint);">
                            <span style="color:#1a1a1a;font-weight:600;font-size:13.5px;">Total Charges</span>
                            <p style="color:var(--coral);font-weight:700;font-size:13.5px;margin:0;">
                                ₱{{ number_format($totalCharges, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden form that submits to storeRepayment --}}
        <form id="repay-form" action="{{ route('repayment.store') }}" method="POST" style="display:none;"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="lending_id" value="{{ $selectedLoan->id ?? '' }}">
            <input type="hidden" name="payment_number" value="{{ $nextPaymentNumber }}">
            <input type="hidden" name="amount_paid" id="form-amount-paid">
            <input type="hidden" name="payment_method" id="form-payment-method">
            <input type="hidden" name="payment_type" id="form-payment-type">
            <input type="hidden" name="reference_no" id="form-reference-no">
            <input type="hidden" name="notes" id="form-notes">
            <input type="hidden" name="gcash_reference_no" id="form-gcash-ref">
        </form>

        {{-- Backdrop --}}
        <div id="repay-backdrop"
            style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1040;"></div>

        {{-- REPAYMENT MODAL --}}

    </div>

    {{-- Receipt overlay + error alert (outside container-fluid so no parent clips it) --}}
    @if(session('success'))
        <div id="loan-receipt-overlay" class="active">
            <div id="loan-receipt-modal">
                <div class="lr-receipt-header">
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                    <h2>Payment Submitted!</h2>
                    <p>Your payment is pending admin verification.</p>
                </div>

                <div class="lr-receipt-body" id="loan-receipt-printable">
                    <div class="lr-receipt-row">
                        <span class="label">Organization</span>
                        <span class="value">KMPCATS</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Member</span>
                        <span
                            class="value">{{ session('loan_receipt_member', Auth::user()->first_name ?? 'Member') }}</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Loan Reference</span>
                        <span class="value"><span
                                class="lr-ref-badge">{{ session('loan_receipt_lending_ref', '—') }}</span></span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Payment #</span>
                        <span class="value">{{ session('loan_receipt_payment_number', '—') }}</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Amount</span>
                        <span class="value highlight">₱{{ number_format(session('loan_receipt_amount', 0), 2) }}</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Payment Method</span>
                        <span class="value">{{ session('loan_receipt_method', '—') }}</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Reference No.</span>
                        <span class="value"><span class="lr-ref-badge">{{ session('loan_receipt_ref', '—') }}</span></span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Date</span>
                        <span class="value">{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</span>
                    </div>
                    <div class="lr-receipt-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="lr-status-badge"><span class="dot"></span> Pending Approval</span>
                        </span>
                    </div>
                </div>

                <div class="lr-receipt-footer">
                    <button class="lr-btn-download" data-action="loanDownloadReceipt">
                        <i class="fa-solid fa-download"></i> Download Receipt
                    </button>
                    <button class="lr-btn-close" data-action="loanCloseReceipt">Close</button>
                </div>
            </div>
        </div>

        <div id="loan-receipt-data" data-member="{{ session('loan_receipt_member', Auth::user()->first_name ?? 'Member') }}"
            data-lending-ref="{{ session('loan_receipt_lending_ref', '—') }}"
            data-payment-number="{{ session('loan_receipt_payment_number', '—') }}"
            data-amount="{{ number_format(session('loan_receipt_amount', 0), 2) }}"
            data-method="{{ session('loan_receipt_method', '—') }}" data-ref="{{ session('loan_receipt_ref', '—') }}"
            data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}"
            data-status="{{ session('loan_receipt_status', 'Pending') }}" style="display:none;">
        </div>

        {{-- Success toast --}}
        {{-- <div id="lr-toast" class="lr-toast" role="status" aria-live="polite">
            <div class="lr-toast-icon"><i class="fa-solid fa-check"></i></div>
            <div class="lr-toast-body">
                <p class="lr-toast-title">Payment submitted</p>
                <p class="lr-toast-msg">
                    ₱{{ number_format(session('loan_receipt_amount', 0), 2) }}
                    for installment #{{ session('loan_receipt_payment_number', '—') }}
                    is now pending admin verification.
                </p>
            </div>
            <button type="button" class="lr-toast-close" id="lr-toast-close" aria-label="Close">
                <i class="fa fa-times"></i>
            </button>
            <div class="lr-toast-bar"></div>
        </div> --}}
    @endif

    <div id="ph-group-overlay">
        <div id="ph-group-modal">
            <div class="phg-head">
                <div>
                    <h2>Payment Breakdown</h2>
                    <p id="phg-sub"></p>
                </div>
                <span id="phg-status"></span>
            </div>
            <div class="phg-meta">
                <div class="phg-meta-row"><span>Reference No.</span><span id="phg-ref">—</span></div>
                <div class="phg-meta-row"><span>Date</span><span id="phg-date">—</span></div>
                <div class="phg-meta-row"><span>Method</span><span id="phg-method">—</span></div>
            </div>
            <div class="phg-list" id="phg-list"></div>
            <div class="phg-total"><span>Total</span><strong id="phg-total">₱0.00</strong></div>
            <div class="phg-foot"><button type="button" class="phg-close" id="phg-close-btn">Close</button></div>
        </div>
    </div>

    {{-- VOID REASON OVERLAY — Loan Repayment --}}
    <div id="lr-void-overlay">
        <div id="lr-void-modal">
            <div class="lr-void-header">
                <div class="lr-void-circle"><i class="fa-solid fa-ban"></i></div>
                <h2>Payment Returned</h2>
                <p>This payment has been returned by the admin.</p>
            </div>
            <div class="lr-void-body">
                <div class="lr-void-details">
                    <div class="lr-void-row">
                        <span class="lr-void-row-label">Loan Reference</span>
                        <span class="lr-void-row-value" id="lr-void-loanref-text">—</span>
                    </div>
                    <div class="lr-void-row">
                        <span class="lr-void-row-label">Payment #</span>
                        <span class="lr-void-row-value" id="lr-void-paymentnum-text">—</span>
                    </div>
                    <div class="lr-void-row">
                        <span class="lr-void-row-label">Reference No.</span>
                        <span class="lr-void-row-value" id="lr-void-ref-text">—</span>
                    </div>
                    <div class="lr-void-row">
                        <span class="lr-void-row-label">Date</span>
                        <span class="lr-void-row-value" id="lr-void-date-text">—</span>
                    </div>
                    <div class="lr-void-row">
                        <span class="lr-void-row-label">Method</span>
                        <span class="lr-void-row-value" id="lr-void-method-text">—</span>
                    </div>
                </div>
                <div class="lr-void-label">Reason</div>
                <div class="lr-void-value" id="lr-void-reason-text"></div>
            </div>
            <div class="lr-void-footer">
                <button class="lr-btn-void-close" data-action="lrCloseVoidModal">Close</button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="status-alert" style="border-color:#dc3545;">
            <i class="fa fa-triangle-exclamation"></i>
            <div class="status-alert-text" style="color:#dc3545;">{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="status-alert"
            style="border-color:#dc3545; background:#fef2f2; position:fixed; top:20px; right:20px; z-index:999999; max-width:400px; padding:1rem 1.2rem; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.15);">
            <i class="fa fa-triangle-exclamation" style="color:#dc3545;"></i>
            <div style="color:#dc3545; font-size:13px; margin-top:4px;">
                @foreach($errors->all() as $error)
                    <p style="margin:2px 0;">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

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

    {{-- AOS animation link js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" data-error-mark="__aosFailed"></script>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;
            A.register('pick-schedule-row', function (e, el) { selectScheduleRow(el); closeModal('scheduleModal'); });
        })();

        (function () {
            const tbody = document.getElementById('ph-tbody');
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll('tr.ph-row'));
            if (rows.length === 0) return;

            const footerCount = document.getElementById('ph-footer-count');
            const pagination = document.getElementById('ph-pagination');
            const PAGE_SIZE = 10;
            const total = rows.length;
            const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
            let currentPage = 1;

            function renderPagination() {
                pagination.innerHTML = '';

                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'sc-page-btn nav';
                prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => { currentPage--; render(); });
                pagination.appendChild(prevBtn);

                for (let p = 1; p <= totalPages; p++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'sc-page-btn' + (p === currentPage ? ' active' : '');
                    btn.textContent = p;
                    btn.addEventListener('click', () => { currentPage = p; render(); });
                    pagination.appendChild(btn);
                }

                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'sc-page-btn nav';
                nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => { currentPage++; render(); });
                pagination.appendChild(nextBtn);
            }

            function render() {
                const startIdx = (currentPage - 1) * PAGE_SIZE;
                const endIdx = startIdx + PAGE_SIZE;

                rows.forEach(row => { row.style.display = 'none'; });
                rows.slice(startIdx, endIdx).forEach(row => { row.style.display = ''; });

                if (window.phSyncChildren) window.phSyncChildren();

                if (footerCount) {
                    const shownCount = Math.min(endIdx, total) - startIdx;
                    footerCount.textContent = `Showing ${shownCount} of ${total} payments`;
                }

                renderPagination();
            }

            render();
        })();

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

        function phEsc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        function phMoney(n) {
            return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function openPhGroupModal(el) {
            var d = el.dataset, items = [];
            try { items = JSON.parse(d.items || '[]'); } catch (err) { }

            document.getElementById('phg-sub').textContent = items.length + ' installments paid together';
            document.getElementById('phg-ref').textContent = d.ref || '—';
            document.getElementById('phg-date').textContent = d.date || '—';
            document.getElementById('phg-method').textContent = d.method || '—';
            document.getElementById('phg-total').textContent = '₱' + (d.total || '0.00');

            var st = document.getElementById('phg-status');
            if (d.status === 'voided') st.innerHTML = '<span class="badge-voided"><i class="fa fa-ban"></i> Returned</span>';
            else if (d.status === 'pending') st.innerHTML = '<span class="badge-pending"><i class="fa fa-hourglass-half"></i> Pending</span>';
            else st.innerHTML = '<span class="badge-paid">Paid</span>';

            document.getElementById('phg-list').innerHTML = items.map(function (it) {
                return '<div class="phg-item">' +
                    '<div class="phg-item-left"><div class="phg-num">' + phEsc(it.number) + '</div>' +
                    '<div><div style="font-weight:600;font-size:.85rem;">Installment #' + phEsc(it.number) + '</div>' +
                    '<div class="phg-due">Due ' + phEsc(it.due) + '</div></div></div>' +
                    '<div class="phg-amt">' + phMoney(it.amount) +
                    (it.penalty > 0 ? '<small>+ ' + phMoney(it.penalty) + ' late fee</small>' : '') +
                    '</div></div>';
            }).join('');

            document.getElementById('ph-group-overlay').classList.add('active');
        }
        function closePhGroupModal() {
            document.getElementById('ph-group-overlay').classList.remove('active');
        }

        document.addEventListener('click', function (e) {
            var g = e.target.closest('tr.ph-group-row, .ph-group-card');
            if (g) { openPhGroupModal(g); return; }

            if (e.target.id === 'ph-group-overlay' || e.target.id === 'phg-close-btn') closePhGroupModal();
        });
    </script>

    <script nonce="{{ csp_nonce() }}">
        function navigateToLoan(loanId) {
            if (!loanId) return;
            const url = new URL(window.location.href);
            url.searchParams.set('loan_id', loanId);
            window.location.href = url.toString();
        }

        // ══════════════════════════════════════════════════════════
        //  LOAN GRID — search / type / date filtering + pagination
        //  (only relevant on the "no loan selected" grid view)
        // ══════════════════════════════════════════════════════════
        const LOAN_GRID_PAGE_SIZE = 10;
        let loanGridPage = 1;

        function getLoanGridCards() {
            const grid = document.getElementById('loan-select-grid');
            return grid ? Array.from(grid.querySelectorAll('.loan-select-card')) : [];
        }

        let loanStatusFilter = 'all';

        function filterLoanStatusTab(status, btn) {
            document.querySelectorAll('.loan-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loanStatusFilter = status;
            applyLoanFilters();
        }

        function applyLoanFilters() {
            const cards = getLoanGridCards();
            if (cards.length === 0) return;

            const search = (document.getElementById('loan-search')?.value || '').toLowerCase().trim();
            const type = document.getElementById('loan-type-filter')?.value || '';
            const date = document.getElementById('loan-date-filter')?.value || '';

            cards.forEach(card => {
                const cardType = card.dataset.type || '';
                const cardRef = (card.dataset.ref || '').toLowerCase();
                const cardDate = card.dataset.date || '';
                const cardStatus = card.dataset.status || '';

                const matchesSearch = !search || cardRef.includes(search) || cardType.toLowerCase().includes(search);
                const matchesType = !type || cardType === type;
                const matchesDate = !date || cardDate === date;
                const matchesStatus = loanStatusFilter === 'all' || cardStatus === loanStatusFilter;

                card.dataset.filteredOut = (matchesSearch && matchesType && matchesDate && matchesStatus) ? 'false' : 'true';
            });

            loanGridPage = 1;
            paginateLoanGrid();
        }

        function paginateLoanGrid() {
            const cards = getLoanGridCards();
            if (cards.length === 0) return;

            const filtered = cards.filter(card => card.dataset.filteredOut !== 'true');
            const totalPages = Math.max(1, Math.ceil(filtered.length / LOAN_GRID_PAGE_SIZE));
            if (loanGridPage > totalPages) loanGridPage = totalPages;

            cards.forEach(card => {
                if (card.dataset.filteredOut === 'true') card.style.display = 'none';
            });

            filtered.forEach((card, i) => {
                const onPage = Math.floor(i / LOAN_GRID_PAGE_SIZE) + 1 === loanGridPage;
                card.style.display = onPage ? '' : 'none';
            });

            // ── Empty state ──
            const grid = document.getElementById('loan-select-grid');
            const emptyBlock = document.getElementById('loan-grid-empty');
            const emptyText = document.getElementById('loan-grid-empty-text');
            const paginationBar = document.querySelector('.loan-grid-pagination-bar');

            if (filtered.length === 0) {
                if (grid) grid.style.display = 'none';
                if (paginationBar) paginationBar.style.display = 'none';
                if (emptyBlock) emptyBlock.style.display = 'flex';

                const messages = {
                    all: 'You have no loans yet.',
                    active: 'You have no active loans.',
                    completed: 'You have no completed loans yet.',
                    overdue: 'You have no overdue loans. Great job staying on track!'
                };
                if (emptyText) emptyText.textContent = messages[loanStatusFilter] || 'No loans found.';
            } else {
                if (grid) grid.style.display = '';
                if (paginationBar) paginationBar.style.display = '';
                if (emptyBlock) emptyBlock.style.display = 'none';
            }

            renderLoanGridPagination(loanGridPage, totalPages, filtered.length);
        }

        function renderLoanGridPagination(page, totalPages, total) {
            const countEl = document.getElementById('loan-grid-count');

            if (countEl) {
                const shown = total === 0 ? 0 : Math.min(LOAN_GRID_PAGE_SIZE, total - (page - 1) * LOAN_GRID_PAGE_SIZE);
                countEl.textContent = `Showing ${shown} of ${total}`;
            }

            const container = document.getElementById('loan-grid-pg');
            if (!container) return;
            container.innerHTML = '';
            if (totalPages <= 1) return;

            const goTo = (targetPage) => {
                loanGridPage = targetPage;
                paginateLoanGrid();
            };

            // ‹ Previous
            const prevBtn = document.createElement('button');
            prevBtn.type = 'button';
            prevBtn.className = 'pg-btn';
            prevBtn.setAttribute('aria-label', 'Previous page');
            prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
            prevBtn.disabled = page <= 1;
            prevBtn.addEventListener('click', () => goTo(page - 1));

            // "1 of 2"
            const info = document.createElement('span');
            info.className = 'pg-info';
            info.textContent = page + ' of ' + totalPages;

            // Next ›
            const nextBtn = document.createElement('button');
            nextBtn.type = 'button';
            nextBtn.className = 'pg-btn';
            nextBtn.setAttribute('aria-label', 'Next page');
            nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
            nextBtn.disabled = page >= totalPages;
            nextBtn.addEventListener('click', () => goTo(page + 1));

            container.appendChild(prevBtn);
            container.appendChild(info);
            container.appendChild(nextBtn);
        }

        document.getElementById('loan-search')?.addEventListener('input', applyLoanFilters);
        document.getElementById('loan-type-filter')?.addEventListener('change', applyLoanFilters);
        document.getElementById('loan-date-filter')?.addEventListener('change', applyLoanFilters);

        document.addEventListener('DOMContentLoaded', applyLoanFilters);
    </script>

    <script nonce="{{ csp_nonce() }}">
        if (typeof AOS !== 'undefined') AOS.init();

        // Real values from controller
        const MONTHLY_AMOUNT = {{ $currentDueAmount ?? 0 }};
        const FULL_BALANCE = {{ $fullBalanceRemaining ?? 0 }};
        const PENALTY_PREVIEW = {{ $currentOverduePenalty ?? 0 }};

        // Tracks whichever Payment Schedule row the member last clicked
        let selectedRowStatus = null;
        let selectedRowNumber = null;
        let activeModalId = null;

        function updatePenaltyBreakdown(baseAmount, penalty, total) {
            const box = document.getElementById('penalty-breakdown');
            const text = document.getElementById('penalty-breakdown-text');
            if (!box || !text) return;
            if (penalty > 0) {
                text.innerHTML = '<strong>₱' + Number(baseAmount).toFixed(2) + '</strong> + <strong style="color:#b8860b;">₱' + Number(penalty).toFixed(2) + '</strong> (late fee) = <strong>₱' + Number(total).toFixed(2) + '</strong>';
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
                text.innerHTML = '';
            }
        }

        function renderMethodDetails(opt) {
            const box = document.getElementById('method-details');
            if (!box) return;
            const d = opt ? opt.dataset : {};
            const name = opt ? opt.value : '';
            let html = '';

            if (d.qr) {
                html += '<div style="background:linear-gradient(135deg,#f0f7ff 0%,#e8f4ff 100%);border:1.5px solid #c2deff;border-radius:12px;padding:1rem 1.2rem;text-align:center;">' +
                    '<p style="margin:0 0 10px;font-size:14px;font-weight:700;color:#0056b3;"><i class="fa-solid fa-mobile-screen-button"></i> Scan to Pay via ' + phEsc(name) + '</p>' +
                    '<img src="' + phEsc(d.qr) + '" alt="' + phEsc(name) + ' QR Code" loading="lazy" ' +
                    'style="width:320px;height:320px;max-width:100%;object-fit:contain;border-radius:10px;border:1px solid #c2deff;background:#fff;padding:14px;display:block;margin:0 auto;" ' +
                    'data-action="openQrLightbox" data-trigger="dblclick" data-arg=\'["|src|"]\'>' +
                    '<p style="margin:10px 0 0;font-size:11px;color:#5a8ac4;">Scan this using your ' + phEsc(name) + ' app, then upload your payment screenshot below.</p>' +
                    '<p style="margin:6px 0 0;font-size:11px;"><a href="#" class="js-open-qr-lightbox" data-qr-src="' + phEsc(d.qr) + '" style="color:#0056b3;font-weight:600;">' +
                    '<i class="fa fa-up-right-and-down-left-from-center"></i> View full-size QR</a></p></div>';
            }

            if (d.accountName || d.accountNumber) {
                html += '<div style="margin-top:' + (d.qr ? '12px' : '0') + ';background:#f8f9fa;border:1.5px solid #e5e7eb;border-radius:12px;padding:1rem 1.2rem;">' +
                    '<p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1a1a1a;"><i class="fa fa-building-columns"></i> Pay to this account</p>' +
                    (d.accountName
                        ? '<div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span style="color:#888;">Account Name</span><strong>' + phEsc(d.accountName) + '</strong></div>'
                        : '') +
                    (d.accountNumber
                        ? '<div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:4px 0;">' +
                        '<span style="color:#888;">Account Number</span>' +
                        '<span style="display:flex;align-items:center;gap:8px;">' +
                        '<strong>' + phEsc(d.accountNumber) + '</strong>' +
                        '<button type="button" class="js-copy-account" data-copy="' + phEsc(d.accountNumber) + '" ' +
                        'style="border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:6px;padding:2px 10px;font-size:11px;font-weight:600;cursor:pointer;">' +
                        '<i class="fa fa-copy"></i> Copy</button>' +
                        '</span></div>'
                        : '') +
                    '</div>';
            }

            if (d.instructions) {
                html += '<p style="margin:10px 0 0;font-size:12px;color:#6b7280;"><i class="fa fa-circle-info"></i> ' + phEsc(d.instructions) + '</p>';
            }

            if (!html) {
                html = '<div style="background:#fff3cd;border:1.5px solid #ffe08a;border-radius:12px;padding:1rem 1.2rem;"><p style="margin:0;font-size:13px;color:#856404;">' +
                    '<i class="fa fa-triangle-exclamation"></i> No payment details have been set up for ' + phEsc(name) + ' yet. Please contact the admin.</p></div>';
            }
            box.innerHTML = html;
        }

        function handleMethodChange() {
            const sel = document.getElementById('repay-method');
            const opt = sel.options[sel.selectedIndex];
            const needsProof = !!opt && opt.dataset.requiresProof === '1';
            const refInput = document.getElementById('repay-gcash-ref');

            document.getElementById('gcash-section').style.display = needsProof ? 'block' : 'none';
            document.getElementById('gcash-proof-input').required = needsProof;
            document.getElementById('repay-method-error').style.display = 'none';
            sel.style.borderColor = '#e0e0e0';

            document.getElementById('ref-label').innerHTML = phEsc(sel.value) + ' Reference Number <span style="color:red;">*</span>';
            const refLen = currentRefLength();
            if (refLen > 0) {
                refInput.maxLength = refLen;
                refInput.placeholder = refLen + '-character reference number';
            } else {
                refInput.removeAttribute('maxlength');
                refInput.placeholder = 'Reference / transaction number';
            }
            refInput.value = '';

            if (needsProof) renderMethodDetails(opt); else setRepayRefState(false);
        }

        function currentRefLength() {
            const sel = document.getElementById('repay-method');
            const opt = sel.options[sel.selectedIndex];
            return opt ? parseInt(opt.dataset.refLength || '0', 10) || 0 : 0;
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-copy-account');
            if (!btn) return;

            const text = btn.dataset.copy || '';
            const original = btn.innerHTML;

            const done = () => {
                btn.innerHTML = '<i class="fa fa-check"></i> Copied';
                setTimeout(() => { btn.innerHTML = original; }, 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                // Fallback for plain-HTTP hosts, where navigator.clipboard is unavailable
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(); } catch (err) { }
                ta.remove();
            }
        });

        document.getElementById('gcash-proof-input')?.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('gcash-proof-preview-img').src = e.target.result;
                    document.getElementById('gcash-proof-preview').style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // ── GCash reference duplicate check ─────────────────────────
        let repayRefUsed = false;
        const repayRefInput = document.getElementById('repay-gcash-ref');
        const repayRefUsedMsg = document.getElementById('repay-ref-used-msg');
        const repayConfirmBtn = document.getElementById('confirm-pay-btn');

        function setRepayRefState(used) {
            repayRefUsed = used;
            if (repayRefUsedMsg) repayRefUsedMsg.style.display = used ? 'block' : 'none';
            if (repayConfirmBtn) {
                repayConfirmBtn.disabled = used;
                repayConfirmBtn.style.opacity = used ? 0.55 : 1;
                repayConfirmBtn.style.cursor = used ? 'not-allowed' : 'pointer';
            }
        }

        let repayRefTimer = null;
        if (repayRefInput) {
            repayRefInput.addEventListener('input', function () {
                clearTimeout(repayRefTimer);
                const v = this.value.trim();
                const refLen = currentRefLength();
                const valid = refLen > 0 ? v.length === refLen : v.length >= 4;
                if (!valid) { setRepayRefState(false); return; }
                repayRefTimer = setTimeout(function () {
                    fetch('{{ route('reference.check') }}?ref=' + encodeURIComponent(v))
                        .then(r => r.json())
                        .then(d => setRepayRefState(!!d.used))
                        .catch(() => setRepayRefState(false));
                }, 300);
            });
        }

        // ── Installment checkboxes ─────────────────────────────────
        function instBoxes() {
            return Array.from(document.querySelectorAll('.inst-check'));
        }

        function refreshInstallments() {
            const boxes = instBoxes();
            let base = 0, penalty = 0, count = 0;

            boxes.forEach(b => {
                b.closest('.inst-card').classList.toggle('is-checked', b.checked);
                if (b.checked) {
                    base += parseFloat(b.dataset.amount || 0);
                    penalty += parseFloat(b.dataset.penalty || 0);
                    count++;
                }
            });

            const total = base + penalty;
            document.getElementById('repay-amount-input').value = total.toFixed(2);
            document.getElementById('cash-payment-type').value =
                (count > 0 && count === boxes.length) ? 'full' : 'monthly';
            updatePenaltyBreakdown(base, penalty, total);

            const summary = document.getElementById('inst-summary');
            if (summary) {
                summary.innerHTML = count
                    ? `<span>${count} installment${count === 1 ? '' : 's'} selected</span>
                       <strong>₱${total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>`
                    : '';
            }

            const allBtn = document.getElementById('inst-select-all');
            if (allBtn) allBtn.textContent = count === boxes.length ? 'Reset' : 'Select all';

            if (repayConfirmBtn && !repayRefUsed) {
                repayConfirmBtn.disabled = count === 0;
                repayConfirmBtn.style.opacity = count === 0 ? 0.55 : 1;
                repayConfirmBtn.style.cursor = count === 0 ? 'not-allowed' : 'pointer';
            }
        }

        // Keep selection consecutive: checking #N checks 1..N, unchecking #N unchecks N..end.
        function onInstallmentToggle(el) {
            const boxes = instBoxes();
            const idx = boxes.indexOf(el);

            if (el.checked) {
                boxes.forEach((b, i) => { b.checked = i <= idx; });
            } else {
                boxes.forEach((b, i) => { if (i >= idx) b.checked = false; });
            }

            if (!boxes.some(b => b.checked) && boxes[0]) boxes[0].checked = true;

            refreshInstallments();
        }

        // If a schedule row was clicked first, pre-select up to that installment.
        function presetInstallments() {
            const boxes = instBoxes();
            if (!boxes.length) { refreshInstallments(); return; }

            let upTo = 0;
            if (selectedRowNumber) {
                const i = boxes.findIndex(b => b.value === String(selectedRowNumber));
                if (i >= 0) upTo = i;
            }
            boxes.forEach((b, i) => { b.checked = i <= upTo; });
            refreshInstallments();
        }

        document.addEventListener('change', function (e) {
            if (e.target.classList && e.target.classList.contains('inst-check')) {
                onInstallmentToggle(e.target);
            }
        });

        document.getElementById('inst-select-all')?.addEventListener('click', function () {
            const boxes = instBoxes();
            const allChecked = boxes.every(b => b.checked);
            boxes.forEach((b, i) => { b.checked = allChecked ? i === 0 : true; });
            refreshInstallments();
        });

        // ── Repayment modal ────────────────────────────────────────
        function handleMakePaymentClick() {
            if (selectedRowStatus === 'paid') return;
            openRepayModal();
        }

        function openRepayModal() {
            activeModalId = 'repayModal';

            document.getElementById('repay-method').value = '';
            document.getElementById('repay-method-error').style.display = 'none';
            document.getElementById('repay-method').style.borderColor = '#e0e0e0';
            document.getElementById('gcash-section').style.display = 'none';
            document.getElementById('confirm-pay-btn').style.display = 'flex';
            document.getElementById('repay-gcash-ref').value = '';
            document.getElementById('method-details').innerHTML = '';
            setRepayRefState(false);
            document.getElementById('notes-section').style.display = 'block';
            document.querySelector('#notes-section textarea').value = '';

            presetInstallments();

            const modal = document.getElementById('repayModal');
            const backdrop = document.getElementById('repay-backdrop');

            modal.style.display = 'block';
            backdrop.style.display = 'block';
            document.body.classList.add('modal-open');

            void modal.offsetWidth;

            modal.classList.add('show');
            backdrop.classList.add('show');
        }

        function closeRepayModal() {
            const modal = document.getElementById('repayModal');
            const backdrop = document.getElementById('repay-backdrop');

            modal.classList.remove('show');
            backdrop.classList.remove('show');

            setTimeout(() => {
                modal.style.display = 'none';
                backdrop.style.display = 'none';
                document.body.classList.remove('modal-open');
            }, 250);

            activeModalId = null;
        }

        // ── Payment Schedule row click ─────────────────────────────
        function selectScheduleRow(el) {
            document.querySelectorAll('.schedule-row').forEach(r => r.classList.remove('row-selected'));
            el.classList.add('row-selected');

            const number = el.dataset.number;
            const date = el.dataset.date;
            const status = el.dataset.status;
            const amount = parseFloat(el.dataset.amount || 0);

            selectedRowStatus = status;
            selectedRowNumber = number;

            const monthlyDueEl = document.getElementById('monthly-due-value');
            if (monthlyDueEl) {
                monthlyDueEl.textContent = `₱${amount.toFixed(2)}`;
            }

            document.querySelectorAll('.payment-button button').forEach(payBtn => {
                if (FULL_BALANCE <= 0) return;
                const disable = status === 'paid';
                payBtn.disabled = disable;
                payBtn.style.opacity = disable ? '.5' : '';
                payBtn.style.cursor = disable ? 'not-allowed' : '';
            });

            const nextDueStat = document.getElementById('next-due-stat');
            if (nextDueStat) {
                const statusLabels = {
                    paid: 'Already paid',
                    pending: 'Awaiting admin verification',
                    overdue: 'Overdue',
                    active: 'Upcoming — due next',
                    upcoming: 'Upcoming'
                };

                const statSub = nextDueStat.querySelector('.stat-sub');
                statSub.textContent = `Installment #${number} · ${statusLabels[status] || ''}`;

                statSub.className = 'stat-sub';
                if (status === 'overdue') {
                    statSub.classList.add('badge-overdue');
                } else if (status === 'active' || status === 'upcoming') {
                    statSub.classList.add('badge-upcoming');
                }

                nextDueStat.querySelector('.stat-value').textContent = date;
            }

            const penaltyBox = document.getElementById('penalty-box');
            if (penaltyBox) {
                const penaltyValue = penaltyBox.querySelector('.penalty-value');
                const penaltyCaption = penaltyBox.querySelector('.penalty-caption');

                if (status === 'overdue') {
                    penaltyValue.textContent = `₱${PENALTY_PREVIEW.toFixed(2)}`;
                    penaltyValue.style.color = 'var(--coral)';
                    penaltyCaption.textContent = `Applies to installment #${number} — overdue`;
                } else if (status === 'paid') {
                    penaltyValue.textContent = '₱0.00';
                    penaltyValue.style.color = '';
                    penaltyCaption.textContent = `Installment #${number} — already paid, no penalty`;
                } else {
                    penaltyValue.textContent = '₱0.00';
                    penaltyValue.style.color = '';
                    penaltyCaption.textContent = `Installment #${number} — not yet due, no penalty`;
                }
            }

            document.getElementById('loan-hero-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ── Mobile schedule / charges modals ───────────────────────
        function openModal(modalId) {
            if (window.innerWidth > 880) return;

            const modal = document.getElementById(modalId);
            const backdrop = document.getElementById('repay-backdrop');
            if (!modal || !backdrop) return;

            activeModalId = modalId;

            modal.style.display = 'block';
            backdrop.style.display = 'block';
            document.body.classList.add('modal-open');

            void modal.offsetWidth;

            modal.classList.add('show');
            backdrop.classList.add('show');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const backdrop = document.getElementById('repay-backdrop');
            if (!modal || !backdrop) return;

            modal.classList.remove('show');
            backdrop.classList.remove('show');

            setTimeout(() => {
                modal.style.display = 'none';
                backdrop.style.display = 'none';
                document.body.classList.remove('modal-open');
            }, 250);

            activeModalId = null;
        }

        function openScheduleModal() { openModal('scheduleModal'); }
        function openChargesModal() { openModal('chargesModal'); }

        document.getElementById('scheduleModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeModal('scheduleModal');
        });
        document.getElementById('chargesModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeModal('chargesModal');
        });

        document.getElementById('repay-backdrop')?.addEventListener('click', function () {
            if (activeModalId === 'repayModal') {
                closeRepayModal();
            } else if (activeModalId) {
                closeModal(activeModalId);
            }
        });

        document.getElementById('repayModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeRepayModal();
        });

        document.getElementById('confirm-pay-btn')?.addEventListener('click', function () {
            const methodSelect = document.getElementById('repay-method');
            const methodError = document.getElementById('repay-method-error');

            // 1. Payment method is required
            if (!methodSelect.value) {
                methodError.style.display = 'block';
                methodSelect.style.borderColor = '#e53e3e';
                methodSelect.focus();
                return;
            }

            // 2. GCash needs proof + a valid, unused reference number
            const selOpt = methodSelect.options[methodSelect.selectedIndex];
            if (selOpt && selOpt.dataset.requiresProof === '1') {
                const proof = document.getElementById('gcash-proof-input');
                const ref = document.getElementById('repay-gcash-ref');
                const isGcash = methodSelect.value.toLowerCase() === 'gcash';

                if (!proof.files || !proof.files.length) {
                    proof.style.borderColor = '#e53e3e';
                    proof.focus();
                    return;
                }
                const refLen = currentRefLength();
                const refVal = ref.value.trim();
                const refOk = refLen > 0 ? refVal.length === refLen : refVal.length >= 4;
                if (!refOk) {
                    ref.style.borderColor = '#e53e3e';
                    ref.focus();
                    return;
                }
                ref.style.borderColor = '#e0e0e0';
            }

            if (repayRefUsed) {
                if (repayRefUsedMsg) repayRefUsedMsg.style.display = 'block';
                return;
            }

            document.getElementById('cash-repay-form').submit();
        });
    </script>

    <script nonce="{{ csp_nonce() }}">
        function phSyncChildren() {
            document.querySelectorAll('tr.ph-group-row').forEach(function (g) {
                var open = g.classList.contains('expanded') && g.style.display !== 'none';
                document.querySelectorAll('tr.ph-child[data-group="' + g.dataset.group + '"]').forEach(function (c) {
                    c.classList.toggle('show', open);
                });
            });
        }
        window.phSyncChildren = phSyncChildren;

        function togglePhGroup(el) {
            el.classList.toggle('expanded');
            phSyncChildren();
        }

        function togglePhGroupMobile(el) {
            var list = el.nextElementSibling;
            if (!list) return;
            var open = list.style.display === 'none';
            list.style.display = open ? 'block' : 'none';
            el.classList.toggle('expanded', open);
        }

        function loanCloseReceipt() {
            var overlay = document.getElementById('loan-receipt-overlay');
            if (overlay) overlay.remove();
        }

        /* ═══ SUCCESS TOAST ═══ */
        // (function () {
        //     const toast = document.getElementById('lr-toast');
        //     if (!toast) return;

        //     let hideTimer = null;

        //     function hideToast() {
        //         toast.classList.remove('show');
        //         clearTimeout(hideTimer);
        //         setTimeout(() => toast.remove(), 300);
        //     }

        //     // Small delay so the slide-in animation plays after page load
        //     setTimeout(() => {
        //         toast.classList.add('show');
        //         hideTimer = setTimeout(hideToast, 5000);
        //     }, 150);

        //     document.getElementById('lr-toast-close')?.addEventListener('click', hideToast);

        //     // Pause auto-hide while hovering
        //     toast.addEventListener('mouseenter', () => clearTimeout(hideTimer));
        //     toast.addEventListener('mouseleave', () => { hideTimer = setTimeout(hideToast, 2000); });
        // })();

        document.getElementById('loan-receipt-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) loanCloseReceipt();
        });

        function loanDownloadReceipt() {
            var d = document.getElementById('loan-receipt-data')?.dataset;
            if (!d) return;

            var wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:fixed;left:-9999px;top:0;width:400px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.15);font-family:-apple-system,BlinkMacSystemFont,sans-serif;';

            wrapper.innerHTML =
                '<div style="padding:24px 20px;text-align:center;border-bottom:1px solid #f0f0f0;">' +
                '<div style="width:50px;height:50px;border-radius:50%;background:#14825a;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">' +
                '<i class="fa-solid fa-check" style="color:#fff;font-size:22px;"></i>' +
                '</div>' +
                '<h2 style="margin:0 0 4px;font-size:18px;color:#1a1a1a;">Payment Submitted!</h2>' +
                '<p style="margin:0;font-size:12px;color:#6b7280;">Your payment is pending admin verification.</p>' +
                '</div>' +
                '<div style="padding:16px 20px;">' +
                lrRow('Organization', 'KMPCATS') +
                lrRow('Member', d.member) +
                lrRow('Loan Reference', d.lendingRef) +
                lrRow('Payment #', d.paymentNumber) +
                lrRow('Amount', '₱' + d.amount, true) +
                lrRow('Payment Method', d.method) +
                lrRow('Reference No.', d.ref) +
                lrRow('Date', d.date) +
                '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:none;">' +
                '<span style="font-size:12px;color:#6b7280;">Status</span>' +
                '<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#fef3c7;color:#b45309;">' +
                '<span style="width:7px;height:7px;border-radius:50%;background:currentColor;"></span> Pending Approval' +
                '</span>' +
                '</div>' +
                '</div>';

            document.body.appendChild(wrapper);

            if (typeof html2canvas !== 'undefined') {
                html2canvas(wrapper, { scale: 2, useCORS: true }).then(function (canvas) {
                    var link = document.createElement('a');
                    link.download = 'loan-repayment-receipt.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    wrapper.remove();
                });
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = function () {
                    html2canvas(wrapper, { scale: 2, useCORS: true }).then(function (canvas) {
                        var link = document.createElement('a');
                        link.download = 'loan-repayment-receipt.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        wrapper.remove();
                    });
                };
                document.head.appendChild(script);
            }
        }

        function lrRow(label, value, highlight) {
            return '<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">' +
                '<span style="font-size:12px;color:#6b7280;">' + label + '</span>' +
                '<span style="font-size:13px;font-weight:600;color:' + (highlight ? '#14825a' : '#1a1a1a') + ';text-align:right;max-width:55%;word-break:break-word;' + (highlight ? 'font-size:15px;' : '') + '">' + value + '</span>' +
                '</div>';
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

        function showLoanVoidReason(el) {
            var d = el.dataset;
            var reason = getVoidLabel(d.reason);
            document.getElementById('lr-void-reason-text').textContent = reason;
            // Returned payments always display ₱0.00 — the funds were reversed.
            document.getElementById('lr-void-loanref-text').textContent = d.loanref || '—';
            document.getElementById('lr-void-paymentnum-text').textContent = d.paymentnum || '—';
            document.getElementById('lr-void-ref-text').textContent = d.ref || '—';
            document.getElementById('lr-void-date-text').textContent = d.date || '—';
            document.getElementById('lr-void-method-text').textContent = d.method || '—';
            document.getElementById('lr-void-overlay').classList.add('active');
        }
        function lrCloseVoidModal() {
            document.getElementById('lr-void-overlay').classList.remove('active');
        }
        document.getElementById('lr-void-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) lrCloseVoidModal();
        });

        /* ═══ SCROLL ARROWS (alh-parent / loan-parent-box) ═══ */
        document.querySelectorAll('.scroll-wrap').forEach(function (wrap) {
            var scroller = wrap.querySelector('.alh-parent, .loan-parent-box');
            var left = wrap.querySelector('.scroll-arrow.left');
            var right = wrap.querySelector('.scroll-arrow.right');
            if (!scroller || !left || !right) return;

            function update() {
                var max = scroller.scrollWidth - scroller.clientWidth;
                var overflow = max > 4;
                left.classList.toggle('show', overflow && scroller.scrollLeft > 4);
                right.classList.toggle('show', overflow && scroller.scrollLeft < max - 4);
            }

            left.addEventListener('click', function () {
                scroller.scrollBy({ left: -scroller.clientWidth * 0.7, behavior: 'smooth' });
            });
            right.addEventListener('click', function () {
                scroller.scrollBy({ left: scroller.clientWidth * 0.7, behavior: 'smooth' });
            });

            scroller.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            window.addEventListener('load', update);
            update();
        });
    </script>


</body>

</html>