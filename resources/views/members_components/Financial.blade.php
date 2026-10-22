<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Financial</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- Both source stylesheets are loaded together. They share the same
    design-token names (--teal, --border, --muted, etc.) with matching
    values, so there is no visual conflict loading both. --}}
    <link rel="stylesheet" href="css_folder/share_capital.css?=4">
    <link rel="stylesheet" href="css_folder/savings.css?=2">
    <link rel="stylesheet" href="css_folder/savings_modal.css">
    <link rel="stylesheet" href="css_folder/loading.css?=8">
    <link rel="stylesheet" href="css_folder/financial.css?=11">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style>
        /* ═══ WITHDRAWAL RECEIPT OVERLAY ═══ */
        #wv-receipt-overlay {
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

        #wv-receipt-overlay.active {
            display: flex;
        }

        #wv-receipt-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: wvModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes wvModalIn {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .wv-receipt-header {
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid var(--line, #e8e8e8);
        }

        .wv-receipt-header .wv-check-circle {
            width: 60px;
            height: 60px;
            background-color: var(--teal, #0d9488);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .wv-receipt-header .wv-check-circle i {
            color: #fff;
            font-size: 26px;
        }

        .wv-receipt-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .wv-receipt-header p {
            color: var(--muted, #888);
            font-size: 0.82rem;
            margin: 0;
        }

        .wv-receipt-body {
            padding: 1.5rem;
        }

        .wv-receipt-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #e8e8e8;
            font-size: 0.85rem;
        }

        .wv-receipt-row:last-child {
            border-bottom: none;
        }

        .wv-receipt-row .label {
            color: #888;
            font-weight: 500;
        }

        .wv-receipt-row .value {
            color: #1a1a1a;
            font-weight: 700;
            text-align: right;
        }

        .wv-receipt-row .value.highlight {
            color: #1a1a1a;
            font-size: 13.6px;
        }

        .wv-ref-badge {
            background: #f4f4f4;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            letter-spacing: 0.5px;
            color: #333;
            font-family: monospace;
            font-size: 0.82rem;
        }

        .wv-status-badge {
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

        .wv-status-badge .dot {
            width: 7px;
            height: 7px;
            background: #e6a817;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .wv-receipt-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            border-radius: 0 0 20px 20px;
            background: #fff;
        }

        .wv-btn-download {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--teal, #0d9488);
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

        .wv-btn-download:hover {
            opacity: 0.88;
        }

        .wv-btn-close-modal {
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

        .wv-btn-close-modal:hover {
            background: #f5f5f5;
            color: #333;
        }

        /* ═══ DEPOSIT RECEIPT OVERLAY ═══ */
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
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .sv-receipt-header {
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid var(--line, #e8e8e8);
        }

        .sv-receipt-header .sv-check-circle {
            width: 60px;
            height: 60px;
            background-color: var(--teal, #0d9488);
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
            color: var(--muted, #888);
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
            background-color: var(--teal, #0d9488);
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

        /* ═══════════════════════════════════════════════════════
        ROW-RECEIPT OVERLAYS (opened by clicking a table row)
        Self-contained so both SC and SV overlays render correctly.
        ═══════════════════════════════════════════════════════ */
        .hidden-receipt {
            display: none;
        }

        #sc-row-receipt-overlay.active,
        #sv-row-receipt-overlay.active {
            display: flex;
        }

        #sc-row-receipt-overlay,
        #sv-row-receipt-overlay {
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

        #sc-row-receipt-modal,
        #sv-row-receipt-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: svModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        /* SC inner styles (not present in Financial.blade.php otherwise) */
        .sc-receipt-header {
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid #e8e8e8;
        }

        .sc-receipt-header .check-circle {
            width: 60px;
            height: 60px;
            /* background-color: #1a4a3a; */
            background-color: var(--teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .sc-receipt-header .check-circle i {
            color: #fff;
            font-size: 26px;
        }

        .sc-receipt-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .sc-receipt-header p {
            color: #888;
            font-size: 0.82rem;
            margin: 0;
        }

        .sc-receipt-body {
            padding: 1.5rem;
        }

        .sc-receipt-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #e8e8e8;
            font-size: 0.85rem;
        }

        .sc-receipt-row:last-child {
            border-bottom: none;
        }

        .sc-receipt-row .label {
            color: #888;
            font-weight: 500;
        }

        .sc-receipt-row .value {
            color: #1a1a1a;
            font-weight: 700;
            text-align: right;
        }

        .sc-receipt-row .value.highlight {
            font-size: 13.6px;
        }

        .sc-ref-badge {
            background: #f4f4f4;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            letter-spacing: 0.5px;
            color: #333;
            font-family: monospace;
            font-size: 0.82rem;
        }

        .sc-receipt-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            border-radius: 0 0 20px 20px;
            background: #fff;
        }

        .sc-btn-download {
            width: 100%;
            padding: 0.8rem;
            /* background-color: #1a4a3a; */
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

        .sc-btn-download:hover {
            opacity: 0.88;
        }

        .sc-btn-close-modal {
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

        .sc-btn-close-modal:hover {
            background: #f5f5f5;
            color: #333;
        }

        .tx-voided-row {
            background: #fef2f2;
        }

        .tx-voided-row:hover {
            background: #fde8e8;
        }

        .status.voided {
            background: #fdecec;
            border: 1.5px solid #f5c6c6;
            color: #c0392b;
            border-radius: 10px;
            padding: 2px 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .sc-status-pill.voided {
            background: #fdecec;
            border: 1.5px solid #f5c6c6;
            color: #c0392b;
        }

        /* ═══ VOID REASON OVERLAY (Financial) ═══ */
        #fin-void-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 999999;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }

        #fin-void-overlay.active {
            display: flex;
        }

        #fin-void-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            margin: auto;
            animation: scModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        .fin-void-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--line);
        }

        .fin-void-header .fin-void-circle {
            width: 60px;
            height: 60px;
            background-color: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .fin-void-header .fin-void-circle i {
            color: #fff;
            font-size: 26px;
        }

        .fin-void-header h2 {
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .fin-void-header p {
            color: var(--muted);
            font-size: 0.82rem;
            margin: 0;
        }

        .fin-void-body {
            padding: 1.5rem 1.5rem 1.5rem 1.5rem;
            text-align: center;
        }

        .fin-void-body .fin-void-label {
            font-size: 0.78rem;
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .fin-void-body .fin-void-value {
            font-size: 1rem;
            font-weight: 700;
            color: #c0392b;
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .fin-void-body .fin-void-amount-wrap {
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 12px;
            padding: 0.9rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .fin-void-body .fin-void-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #c0392b;
            line-height: 1.2;
        }

        .fin-void-body .fin-void-details {
            text-align: left;
            background: #fafafa;
            border-radius: 12px;
            padding: 0.25rem 1rem;
            margin-bottom: 1rem;
        }

        .fin-void-body .fin-void-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #f0e2e2;
            font-size: 0.85rem;
        }

        .fin-void-body .fin-void-row:last-child {
            border-bottom: none;
        }

        .fin-void-body .fin-void-row-label {
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.3px;
        }

        .fin-void-body .fin-void-row-value {
            color: #1a1a1a;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }

        .fin-void-footer {
            padding: 0 1.5rem 1.5rem;
        }

        .fin-btn-void-close {
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

        .fin-btn-void-close:hover {
            background: #f5f5f5;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")

        {{-- ═══════════════════════════════════════
        RECEIPT MODAL (Share Capital — shown after success)
        ═══════════════════════════════════════ --}}
        @if($activeTab === 'share_capital' && session('success'))
            <div id="sc-receipt-overlay" class="active">
                <div id="sc-receipt-modal">
                    <div class="sc-receipt-header">
                        <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                        <h2>Request Submitted!</h2>
                        @if(session('sc_receipt_type') === 'Deposit')
                            @if(session('sc_receipt_status') === 'Completed')
                                <p>Your deposit has been recorded successfully.</p>
                            @else
                                <p>Your deposit request is pending for approval.</p>
                            @endif
                        @else
                            @if(session('sc_receipt_status') === 'Completed')
                                <p>Your withdrawal has been processed successfully.</p>
                            @else
                                <p>Your withdrawal request is pending for approval.</p>
                            @endif
                        @endif
                    </div>

                    <div class="sc-receipt-body" id="sc-receipt-printable">
                        <div class="sc-receipt-row">
                            <span class="label">Organization</span>
                            <span class="value">KMPCATS</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Member</span>
                            <span class="value">{{ session('sc_receipt_member', Auth::user()->name ?? 'Member') }}</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Transaction Type</span>
                            <span class="value">{{ session('sc_receipt_type', 'Deposit') }}</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Shares</span>
                            <span class="value highlight">{{ session('sc_receipt_shares', '—') }} shares</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Amount</span>
                            <span class="value highlight">₱{{ number_format(session('sc_receipt_amount', 0), 0) }}</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Payment Method</span>
                            <span class="value">{{ session('sc_receipt_method', '—') }}</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Reference No.</span>
                            <span class="value"><span
                                    class="sc-ref-badge">{{ session('sc_receipt_ref', '—') }}</span></span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Date</span>
                            <span class="value">{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</span>
                        </div>
                        <div class="sc-receipt-row">
                            <span class="label">Status</span>
                            <span class="value">
                                @if(session('sc_receipt_status') === 'Completed')
                                    <span class="sc-status-badge completed"><span class="dot"></span> Completed</span>
                                @else
                                    <span class="sc-status-badge"><span class="dot"></span> Pending Approval</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="sc-receipt-footer">
                        <button class="sc-btn-download" data-action="scDownloadReceipt">
                            <i class="fa-solid fa-download"></i> Download Receipt
                        </button>
                        <button class="sc-btn-close-modal" data-action="scCloseModal">Close</button>
                    </div>
                </div>
            </div>

            <div id="sc-receipt-data" data-member="{{ session('sc_receipt_member', Auth::user()->name ?? 'Member') }}"
                data-type="{{ session('sc_receipt_type', 'Deposit') }}"
                data-shares="{{ session('sc_receipt_shares', '—') }}"
                data-amount="{{ number_format(session('sc_receipt_amount', 0), 0) }}"
                data-method="{{ session('sc_receipt_method', '—') }}" data-ref="{{ session('sc_receipt_ref', '—') }}"
                data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}"
                data-status="{{ session('sc_receipt_status', 'Pending') }}" style="display:none;">
            </div>
        @endif

        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            <div class="main-sub-parent">
                <main>

                    {{-- ═══════════════════════════════════════
                    UNIFIED TRANSACTION MODAL (Share Capital + Savings, one entry point)
                    ═══════════════════════════════════════ --}}
                    <div class="modal fade" id="unifiedTxModal" tabindex="-1" aria-labelledby="unifiedTxModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content"
                                style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 24px 60px rgba(0,0,0,0.15);">

                                <div class="modal-header"
                                    style="background: #ffffff; border-bottom: 1px solid var(--line); padding: 1.25rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="width: 45px; height: 45px; background: var(--teal); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-right-left" style="color: #ffffff; font-size: 16px;"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title mb-0" id="unifiedTxModalLabel"
                                                style="color: #1a1a1a; font-size: 15px; font-weight: 600;">New
                                                Transaction</h5>
                                            <p style="margin: 0; color: var(--muted); font-size: 13.5px;">
                                                Deposit or withdraw from Share Capital or Savings</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-close-dark m-0" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body"
                                    style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                                    <form action="{{ route('share_capital.member.store') }}" method="POST"
                                        id="unified-tx-form" enctype="multipart/form-data"
                                        data-sc-route="{{ route('share_capital.member.store') }}"
                                        data-sv-deposit-route="{{ route('savings.deposit') }}"
                                        data-sv-withdraw-route="{{ route('savings.withdraw') }}">
                                        @csrf
                                        <input type="hidden" name="_form" id="tx-form-marker" value="share_capital">

                                        {{-- <div class="tx-dest-toggle">
                                            <button type="button" class="tx-dest-btn active" data-dest="share_capital">
                                                <i class="fa fa-coins"></i> Share Capital
                                            </button>
                                            <button type="button" class="tx-dest-btn" data-dest="savings">
                                                <i class="fa fa-piggy-bank"></i> Savings
                                            </button>
                                        </div> --}}

                                        <div
                                            style="border-radius: 10px; padding: 0.8rem 1.1rem; display: flex; align-items: center; border: 1px solid rgba(0, 0, 0, 0.1); margin: 0 0 1.1rem 0; justify-content: space-between;">
                                            <span style="font-size: 0.78rem; color: var(--muted);"
                                                id="tx-balance-label">Current
                                                Balance</span>
                                            <span id="tx-balance-value"
                                                style="font-size: 14.5px; font-weight: 600; color: #1a1a1a">
                                                ₱{{ number_format($currentBalance ?? 0, 0) }} ·
                                                {{ $currentShares ?? 0 }} shares
                                            </span>
                                        </div>

                                        <div class="sc-inline-error" id="tx-inline-error">
                                            <i class="fa fa-circle-exclamation"></i>
                                            <span id="tx-inline-error-text"></span>
                                        </div>

                                        <div style="margin-bottom: 1.1rem;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">Account</label>
                                            <div id="tx-dest-display"
                                                style="display: flex; align-items: center; gap: 10px; border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px; padding: 0 14px; font-size: 14px; color: #333; background: #f7f7f8;">
                                                <span id="tx-dest-display-icon">🪙</span>
                                                <span id="tx-dest-display-text">Share Capital</span>
                                                <i class="fa fa-lock"
                                                    style="margin-left: auto; color: #b0b0b0; font-size: 12px;"></i>
                                            </div>
                                        </div>

                                        <div id="tx-type-field" style="margin-bottom: 17.6px;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">Type</label>
                                            <select class="form-select" id="tx-type" required
                                                style="border-radius: 10px;border: 1.5px solid #e0e0e0;height: 46px;font-size: 14px;color: #333;">
                                                <option value="">Select type..</option>
                                                <option value="Deposit">Deposit</option>
                                                <option value="Withdrawal">Withdrawal</option>
                                            </select>
                                            <input type="hidden" name="type" id="tx-type-hidden" value="">
                                        </div>

                                        <div id="tx-sc-withdrawal-notice"
                                            style="display:none; background:#fff8e1; border:1.5px solid #ffe082; border-radius:10px; padding:0.65rem 1rem; margin-bottom:0.9rem; font-size:12px; color:#856404; line-height:1.5;">
                                            <i class="fa fa-circle-info" style="margin-right:6px;"></i>
                                            Withdrawal requests are subject to admin approval. Your current share
                                            balance will <strong>not</strong> be reduced until the request is
                                            approved.
                                        </div>

                                        <div id="tx-sc-full-withdrawal-warning"
                                            style="display:none; background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:0.65rem 1rem; margin-bottom:0.9rem; font-size:12px; color:#991b1b; line-height:1.5;">
                                            <i class="fa fa-circle-exclamation" style="margin-right:6px;"></i>
                                            <strong>Notice:</strong> Fully withdrawing your share capital is
                                            equivalent
                                            to resigning. This action will submit a resignation request and is
                                            subject
                                            to a 60-day holding period upon approval.
                                        </div>

                                        <input type="hidden" name="payment_method" id="tx-pay-hidden" value="">

                                        <div id="tx-pay-field" style="margin-bottom: 17.6px;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">Payment
                                                Method</label>
                                            <select class="form-select" id="tx-pay"
                                                style="border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px; font-size: 14px; color: #333;">
                                                <option value="" disabled selected>Select payment method...</option>
                                                @foreach($paymentMethods as $pm)
                                                    <option value="{{ strtolower($pm->method_name) }}">
                                                        {{ $pm->method_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="tx-gcash-number-field" style="display:none; margin-bottom: 17.6px;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">GCash
                                                Mobile Number</label>
                                            <input type="text" name="gcash_number" id="tx-gcash-number"
                                                placeholder="e.g. 09123456789"
                                                value="{{ old('gcash_number', Auth::user()->otherinfo->contact_no ?? '') }}"
                                                style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #e0e0e0; font-size: 14px; color: #333; box-sizing: border-box; height: 46px;">
                                        </div>

                                        <div id="tx-qr-box" style="display: none; margin-top: 0.8rem;">
                                            <div id="tx-qr-area" style="display:none;">
                                                <div
                                                    style="background: linear-gradient(135deg, #f0f7ff 0%, #e8f4ff 100%); border: 1.5px solid #c2deff; border-radius: 12px; padding: 1rem 1.2rem; text-align: center;">
                                                    <p id="tx-qr-title"
                                                        style="margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #0056b3;">
                                                        <i class="fa-solid fa-mobile-screen-button"></i> Scan to Pay
                                                        via
                                                    </p>
                                                    <img id="tx-qr-img" src="" alt="QR Code"
                                                        style="width: 220px; height: 220px; max-width: 100%; object-fit: contain; border-radius: 10px; border: 1px solid #c2deff; background: #fff; padding: 12px; display: block; margin: 0 auto;">
                                                    <p id="tx-qr-hint"
                                                        style="margin: 10px 0 0; font-size: 11px; color: #5a8ac4;">
                                                        Scan this using your app, then upload your payment
                                                        screenshot
                                                        below.
                                                    </p>
                                                    <p style="margin: 6px 0 0; font-size: 11px;">
                                                        <a href="#" id="tx-qr-view" data-action="open-sc-qr"
                                                            style="color: #0056b3; font-weight: 600;">
                                                            <i class="fa fa-up-right-and-down-left-from-center"></i>
                                                            View
                                                            full-size QR
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                            <div id="tx-qr-fallback"
                                                style="display:none; background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 12px; padding: 1rem 1.2rem;">
                                                <p style="margin: 0; font-size: 13px; color: #856404;">
                                                    <i class="fa fa-triangle-exclamation"></i> No <span
                                                        id="tx-qr-fallback-name">QR</span> code has been
                                                    set up yet. Please contact the admin.
                                                </p>
                                            </div>

                                            <div style="margin-top: 1rem;">
                                                <label
                                                    style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                    <span id="tx-qr-ref-label">Reference Number</span> <span
                                                        style="color: #e53e3e;">*</span>
                                                </label>
                                                <p id="tx-ref-used-msg"
                                                    style="display:none; margin:0 0 6px; color:#e53e3e; font-size:12px; font-weight:600;">
                                                    <i class="fa fa-circle-exclamation"></i> This reference number
                                                    has
                                                    already been used for a transaction.
                                                </p>
                                                <input type="text" name="gcash_reference_no" id="tx-gcash-ref-input"
                                                    maxlength="13" pattern="\d{13}" placeholder="e.g. 1234567890123"
                                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box; height: 46px;">
                                                <p style="margin: 4px 0 0; font-size: 11px; color: #888;">
                                                    <span id="tx-qr-ref-hint">Enter the reference number from your
                                                        transaction.</span>
                                                </p>
                                            </div>

                                            <div style="margin-top: 1rem;">
                                                <label
                                                    style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                    Upload Payment Screenshot <span
                                                        style="font-size: 11px; color: #bbb;"
                                                        id="tx-qr-proof-tag">(proof)</span>
                                                </label>
                                                <input type="file" name="gcash_proof" id="tx-gcash-proof-input"
                                                    accept="image/png,image/jpeg,image/jpg"
                                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box;"
                                                    class="form-control">
                                                <div id="tx-gcash-proof-preview" style="display:none; margin-top:10px;">
                                                    <img id="tx-gcash-proof-preview-img"
                                                        style="width:100%; height:180px; object-fit:cover; border-radius:8px; border:1px solid #e0e0e0;">
                                                </div>
                                            </div>
                                        </div>

                                        <div id="tx-deposit-fraud-warning"
                                            style="display: none; background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 10px; padding: 0.75rem 1rem; margin-top: 1rem;">
                                            <p style="margin: 0; font-size: 12px; color: #856404;">
                                                <i class="fa fa-triangle-exclamation"></i>
                                                Deposits paid via QR code are <strong>pending verification</strong>.
                                                Please enter the correct reference number and attach a screenshot of
                                                your payment. Submitting false or fraudulent entries will result in
                                                account suspension.
                                            </p>
                                        </div>

                                        {{-- SAVINGS AMOUNT FIELD (bottom) --}}
                                        <div id="tx-sv-amount-field" style="display:none; margin-top: 0.8rem;">
                                            <label class="sm-form-label" for="tx-amount"
                                                style="font-size: 13px; color: #666; display:block; margin-bottom:8px;">Amount</label>
                                            <div class="sm-amount-wrap" style="margin-bottom: 10px;">
                                                <span class="sm-amount-prefix">₱</span>
                                                <input class="sm-form-input" type="number" id="tx-amount" name="amount"
                                                    placeholder="0.00" min="1" step="0.01" disabled />
                                            </div>
                                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
                                                <button type="button" class="tx-amt-qbtn" data-v="500">₱500</button>
                                                <button type="button" class="tx-amt-qbtn" data-v="1000">₱1,000</button>
                                                <button type="button" class="tx-amt-qbtn" data-v="1500">₱1,500</button>
                                                <button type="button" class="tx-amt-qbtn" data-v="2000">₱2,000</button>
                                                <button type="button" class="tx-amt-qbtn" data-v="5000">₱5,000</button>
                                            </div>
                                        </div>

                                        {{-- SHARE CAPITAL FIELDS (bottom) --}}
                                        <div id="tx-sc-fields" style="display:none; margin-top: 0.8rem;">
                                            <label for="tx-sc-amount"
                                                style="font-size: 13px; color: #666; display: block; margin-bottom: 8px;">Amount</label>
                                            <div
                                                style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                                <span style="font-size: 16px; font-weight: 600; color: #666;">₱</span>
                                                <input type="number" id="tx-sc-amount" name="sc_amount" value="200"
                                                    min="1" step="0.01" placeholder="0.00"
                                                    style="width: 130px; font-size: 14.5px; font-weight: 600; color: var(--teal); border: 1.5px solid #ddd; border-radius: 10px; padding: 8px 10px;">
                                                <input type="number" name="shares" id="tx-shares" value="1" min="0.005"
                                                    step="any" style="display:none;">
                                            </div>

                                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
                                                <button type="button" class="tx-qbtn active" data-v="200"
                                                    style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">₱200</button>
                                                <button type="button" class="tx-qbtn" data-v="1250"
                                                    style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">₱1,250</button>
                                                <button type="button" class="tx-qbtn" data-v="2000"
                                                    style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">₱2,000</button>
                                                <button type="button" class="tx-qbtn" data-v="2400"
                                                    style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">₱2,400</button>
                                                <button type="button" class="tx-qbtn" data-v="5000"
                                                    style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">₱5,000</button>
                                            </div>

                                            <p style="font-size: 12px; color: #888888; margin-bottom: 1.2rem;">
                                                Cost: <strong id="tx-cost" style="color: var(--teal);">₱200</strong>
                                                ·
                                                <strong id="tx-sc-share-count">1</strong> shares · ₱200 per Share
                                                (Par
                                                Value)
                                            </p>
                                        </div>

                                        <div style="margin-top: 17.6px;">
                                            <label
                                                style="font-size: 12px;text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                Note <span style="font-size: 12px; color: #bbb;">(optional)</span>
                                            </label>
                                            <input type="text" name="note" id="tx-note"
                                                placeholder="e.g. Q2 CBU installment"
                                                style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; color: #333; box-sizing: border-box; height: 46px; ">
                                        </div>

                                    </form>

                                </div>

                                <div class="modal-footer"
                                    style="background: #f8f9fa; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 1rem 1.6rem;display: flex;justify-content: center;align-items: center; gap: 8px;">
                                    <button type="submit" form="unified-tx-form" id="tx-submit-btn"
                                        style="width: 100%; padding: 0.75rem; background: var(--teal); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fa fa-right-left"></i> Confirm Transaction
                                    </button>
                                    <button type="button" class="btn w-100 text-center" data-bs-dismiss="modal"
                                        style="border-radius: 8px;font-size: 14px; padding: 10px 18px; border: 1.5px solid #e0e0e0;color: var(--muted);">
                                        Cancel
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="main-parent">

                        {{-- ═══════════════════════════════════════
                        PAGE HEADER + TABS
                        ═══════════════════════════════════════ --}}
                        <div class="parent-main">
                            <div class="parent-text">
                                <h3>Financial</h3>
                                <p>Track your Share Capital contributions and Savings activity in one place.</p>
                            </div>
                            <div class="parent-download" style="">
                                <div class="share">
                                    <button data-bs-toggle="modal" data-bs-target="#unifiedTxModal">
                                        <i class="fa fa-right-left"></i>
                                        <span>New Transaction</span>
                                    </button>
                                </div>
                                @if($activeTab === 'savings')
                                    <div class="share">
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#convertScModal">
                                            <i class="fa fa-arrows-rotate"></i>
                                            <span>Convert to Share Capital</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="fin-tabs">
                            <a href="{{ route('Financial', ['tab' => 'savings']) }}"
                                class="fin-tab {{ $activeTab === 'savings' ? 'active' : '' }}">Savings</a>
                            <a href="{{ route('Financial', ['tab' => 'share_capital']) }}"
                                class="fin-tab {{ $activeTab === 'share_capital' ? 'active' : '' }}">Share
                                Capital</a>
                        </div>

                        {{-- ═══════════════════════════════════════════════════════════
                        TAB: SHARE CAPITAL
                        ═══════════════════════════════════════════════════════════ --}}
                        @if($activeTab === 'share_capital')

                            {{-- STAT CARDS --}}
                            <div class="sc-stat-grid" style="animation-delay:.11s">

                                <div class="sc-stat-card green" style="animation-delay:.08s">
                                    <div class="sc-stat-header">
                                        <div class="sc-stat-label green">Paid-up Capital</div>
                                        <div class="sc-stat-icon green"><i class="fa fa-peso-sign"></i></div>
                                    </div>
                                    <div class="sc-stat-value green main-stat">₱
                                        <b>{{ number_format($currentBalance, 0) }}</b>
                                    </div>

                                    <div class="sc-progress-track">
                                        <div class="sc-progress-fill" style="width:{{ $paidUpPercent }}%;"></div>
                                    </div>
                                    <div class="sc-stat-sub green">{{ $currentShares }} shares actually contributed</div>
                                </div>

                                <div class="sc-journey-sub-card">
                                    <div class="sc-stat-card" style="animation-delay:.05s">
                                        <div class="sc-stat-header">
                                            <div class="sc-stat-label">Subscribed Capital</div>
                                            <div class="sc-stat-icon blue"><i class="fa fa-bullseye"></i></div>
                                        </div>
                                        <div class="sc-stat-value">₱{{ number_format($targetAmount, 0) }}</div>
                                        <div class="sc-stat-sub">Target · {{ $targetShares }} shares ₱{{ $parValue }} per
                                            value
                                        </div>
                                    </div>

                                    <div class="sc-stat-card" style="animation-delay:.11s">
                                        <div class="sc-stat-header">
                                            <div class="sc-stat-label">CBU Progress</div>
                                            <div class="sc-stat-icon blue"><i class="fa fa-arrows-rotate"></i></div>
                                        </div>
                                        <div class="sc-stat-value blue">{{ $paidUpPercent }}%</div>
                                        <div class="sc-stat-sub {{ $remainingToTarget > 0 ? '' : 'unlocked-note' }}">
                                            @if($remainingToTarget > 0)
                                                ₱{{ number_format($remainingToTarget, 0) }} remaining to complete subscription
                                            @else
                                                Subscription complete
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sc-stat-card" style="animation-delay:.14s">
                                        <div class="sc-stat-header">
                                            <div class="sc-stat-label">Eligibility Status</div>
                                            <div class="sc-stat-icon {{ $certificateEligible ? 'gold' : 'locked' }}">
                                                <i class="fa {{ $certificateEligible ? 'fa-certificate' : 'fa-lock' }}"
                                                    style="color:{{ $certificateEligible ? '#C9A84C' : '#999' }};"></i>
                                            </div>
                                        </div>
                                        <div class="sc-stat-value {{ $certificateEligible ? 'gold' : '' }}">
                                            {{ $certificateEligible ? 'Eligible' : 'Not Yet Eligible' }}
                                        </div>
                                        <div
                                            class="sc-stat-sub {{ $certificateEligible ? 'unlocked-note' : 'locked-note' }}">
                                            Unlocks automatically at ₱{{ number_format($targetAmount, 0) }} paid-up
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- ═══════════════════════════════════════
                            DIVIDEND EARNINGS — 60/40 SPLIT
                            ═══════════════════════════════════════ --}}
                            <h3 class="sc-split-heading">
                                <!-- Dividend Earnings — The 60/40 Split
                                                                                                                                                                                                                                        <span class="sc-split-note">Distributed once a year, after audited annual surplus is
                                                                                                                                                                                                                                            approved by the General Assembly</span> -->
                                <div>
                                    <h4>Dividend Earnings — The 60/40 Split</h4>
                                    <p>Distributed once a year, after audited annual surplus is approved by the General
                                        Assembly</p>
                                </div>
                                {{-- <a href="{{ route('Financial', ['tab' => 'dividends']) }}"
                                    style="font-size:13px; font-weight:600; color:var(--teal, #1E2A4A); white-space:nowrap;">
                                    View full dividend history →
                                </a> --}}
                            </h3>
                            <div class="sc-split-grid">
                                <div class="sc-split-card isc">
                                    <div class="sc-split-tag">Capital-Based</div>
                                    <div class="sc-split-title">Interest on Share Capital (ISC)</div>
                                    <div class="sc-split-pct">
                                        {{ number_format(\App\Http\Controllers\ShareCapital::ISC_SPLIT * 100, 0) }}%
                                    </div>
                                    <div class="sc-split-desc">of allocated annual surplus. Computed on <strong>how much
                                            capital you hold</strong> — your Average Monthly Balance across the year,
                                        multiplied by the board-approved dividend rate. The more shares you build up,
                                        the
                                        larger your ISC share.</div>
                                    {{-- <div class="sc-split-est"><span>Est. ISC this
                                            cycle</span><strong>₱{{ number_format($iscAmount, 2) }}</strong></div> --}}
                                </div>

                                <div class="sc-split-card patronage">
                                    <div class="sc-split-tag">Usage-Based</div>
                                    <div class="sc-split-title">Patronage Refund</div>
                                    <div class="sc-split-pct">
                                        {{ number_format(\App\Http\Controllers\ShareCapital::PATRONAGE_SPLIT * 100, 0) }}%
                                    </div>
                                    <div class="sc-split-desc">of allocated annual surplus. Computed on <strong>how much
                                            you
                                            transacted with the cooperative</strong> — loan interest paid, service fees,
                                        and
                                        other patronage — regardless of how many shares you hold. Rewards active
                                        members.
                                    </div>
                                    {{-- <div class="sc-split-est"><span>Est. Patronage this
                                            cycle</span><strong>₱{{ number_format($patronageAmount, 2) }}</strong></div>
                                    --}}
                                </div>

                                {{-- <div class="sc-split-card transparent">
                                    <div class="sc-split-tag">Transparent Basis</div>
                                    <div class="sc-split-pct">₱{{ number_format($projectedNextDividend, 0) }}</div>
                                    <div class="sc-split-desc">Your Average Monthly Balance (AMB) — the running average
                                        of
                                        your paid-up capital across the 12-month period. This is the figure your ISC is
                                        computed from.</div>
                                    <div class="sc-split-formula">
                                        ISC = AMB × <strong>{{ $dividendRate }}% p.a.</strong> × 60%<br>
                                        Patronage = Loan interest paid × allocation rate × 40%
                                    </div>
                                </div> --}}
                            </div>

                            {{-- 2-YEAR CAPITAL BUILD-UP JOURNEY --}}
                            <div class="sc-journey-card">


                                {{-- NOTICES --}}
                                <div class="sc-notice-grid">
                                    <div class="sc-notice-box warn">
                                        <div class="sc-notice-icon"><i class="fa fa-lock"></i></div>
                                        <div>
                                            <strong class="title">Non-Withdrawable Fund<span class="tag">INVESTMENT /
                                                    SOSYO</span></strong><br>
                                            Share capital is your <strong>investment (sosyo)</strong> in the
                                            cooperative,
                                            not a
                                            savings account. It cannot be withdrawn on demand — it earns dividends
                                            annually
                                            and
                                            builds your ownership stake instead of sitting as liquid, on-call funds.
                                        </div>
                                    </div>
                                    <div class="sc-notice-box danger">
                                        <div class="sc-notice-icon"><i class="fa fa-triangle-exclamation"></i></div>
                                        <div>
                                            <strong class="title">Applied to Outstanding Loans on
                                                Resignation</strong><br>
                                            If you resign or your membership is terminated, your share capital balance
                                            will
                                            first be used to <strong>offset any outstanding loan balance</strong> before
                                            the
                                            remainder — if any — is refunded to you, per cooperative bylaws.
                                        </div>
                                    </div>
                                </div>

                                {{-- CONTRIBUTION HISTORY --}}
                                <div class="contribution-parent">
                                    <div class="contribution-text">
                                        <h3>Contribution History</h3>
                                        <p>View your contribution history breakdown</p>
                                    </div>
                                    <div class="contribution-header">
                                        <div class="contribution-search">
                                            <div class="sc-search-box">
                                                <i class="fa fa-search" style="color:#aaa; font-size:13px;"></i>
                                                <input type="text" id="sc-search-input"
                                                    placeholder="Search reference no., type...">
                                            </div>
                                        </div>
                                        <div class="sc-filter-bar">
                                            <div class="sc-filter-group">
                                                <input type="date" id="sc-filter-date" class="form-control">
                                            </div>
                                            <div class="sc-filter-stats">
                                                <div class="sc-filter-group">
                                                    <select id="sc-filter-type" class="form-select">
                                                        <option value="">All</option>
                                                        <option value="Deposit">Deposit</option>
                                                        <option value="Withdrawal">Withdrawal</option>
                                                    </select>
                                                </div>
                                                <div class="sc-filter-group">
                                                    <select id="sc-filter-status" class="form-select">
                                                        <option value="">All</option>
                                                        <option value="posted">Completed</option>
                                                        <option value="pending">Pending</option>
                                                        <option value="failed">Failed</option>
                                                    </select>
                                                </div>
                                                <button type="button" id="sc-filter-clear">Clear filter</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="parent-table">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Reference No.</th>
                                                    <th>Shares Purchased</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="sc-contribution-tbody">
                                                @forelse($contributions as $row)
                                                    @php
                                                        $method = strtolower($row->payment_method ?? '');
                                                        $channelClass = match (true) {
                                                            str_contains($method, 'gcash') => 'gcash',
                                                            str_contains($method, 'maya') => 'maya',
                                                            str_contains($method, 'otc') || str_contains($method, 'counter') => 'otc',
                                                            str_contains($method, 'transfer') => 'transfer',
                                                            default => 'cash',
                                                        };
                                                        $channelLabel = match ($channelClass) {
                                                            'gcash' => 'GCash',
                                                            'maya' => 'Maya',
                                                            'otc' => 'Over-the-Counter',
                                                            'transfer' => 'Transfer',
                                                            default => 'Cash',
                                                        };
                                                        $isPending = strtolower($row->status ?? '') === 'pending';
                                                        $statusKey = ($row->status === 'Completed' || $row->status === null)
                                                            ? 'posted'
                                                            : ($isPending ? 'pending' : 'failed');
                                                    @endphp
                                                    <tr data-date="{{ \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d') }}"
                                                        data-type="{{ $row->type }}" data-status="{{ $statusKey }}"
                                                        data-void="{{ strtolower($row->status ?? '') === 'voided' ? '1' : '0' }}"
                                                        data-reason="{{ $row->void_reason }}" data-row-source="sc"
                                                        class="{{ strtolower($row->status ?? '') === 'voided' ? 'tx-voided-row' : '' }}"
                                                        data-member="{{ Auth::user()->name ?? 'Member' }}"
                                                        data-amount="{{ number_format((float) $row->total_amount, 2) }}"
                                                        data-shares="{{ $row->shares ? rtrim(rtrim(number_format((float) $row->shares, 6, '.', ''), '0'), '.') : '0' }}"
                                                        data-method="{{ $row->payment_method ? \Illuminate\Support\Str::title($row->payment_method) : '—' }}"
                                                        data-ref="{{ $row->reference_no ?? '—' }}"
                                                        data-txdate="{{ \Carbon\Carbon::parse($row->transaction_date)->timezone('Asia/Manila')->format('M d, Y · h:i A') }}"
                                                        style="cursor:pointer;" data-action="handleTxnRowClick"
                                                        data-arg='["|event|","|el|","sc"]'>
                                                        <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('M d, Y') }}
                                                        </td>
                                                        <td>{{ $row->type }}</td>
                                                        <td>
                                                            @if($row->type === \App\Http\Controllers\ShareCapital::CONVERSION_TYPE)
                                                                <span style="color:#aaa;">—</span>
                                                            @else
                                                                <span
                                                                    class="sc-channel-badge {{ $channelClass }}">{{ $channelLabel }}</span>
                                                                <span class="sc-ref-text">
                                                                    {{ $isPending && empty($row->reference_no) ? 'Awaiting reference' : ('Ref# ' . ($row->reference_no ?? '—')) }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row->shares ? rtrim(rtrim(number_format((float) $row->shares, 6, '.', ''), '0'), '.') . ' shares' : '— shares' }}
                                                        </td>
                                                        <td>
                                                            @if(strtolower($row->status ?? '') === 'voided')
                                                                <span style="color:#aaa;">—</span>
                                                            @else
                                                                ₱{{ number_format($row->total_amount, 2) }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($row->status === 'Completed' || $row->status === null)
                                                                <span class="sc-status-pill posted">Completed</span>
                                                            @elseif($isPending)
                                                                <span class="sc-status-pill pending">Pending</span>
                                                            @elseif(strtolower($row->status ?? '') === 'voided')
                                                                <span class="sc-status-pill voided">Returned</span>
                                                            @else
                                                                <span class="sc-status-pill failed">{{ $row->status }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6"
                                                            style="text-align: center; color: #aaa; padding: 2rem; font-size: 13px;">
                                                            <i class="fa fa-inbox"
                                                                style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                                            No contributions yet.<br>
                                                            <span style="font-size: 11px;">Start by subscribing to share
                                                                capital.</span>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($contributions->isNotEmpty())
                                        <div class="sc-table-footer">
                                            <span id="sc-table-footer-count">Showing {{ $contributions->count() }} of
                                                {{ $contributions->count() }}
                                            </span>
                                            <div class="sc-pagination" id="sc-pagination"></div>
                                        </div>
                                    @endif
                                </div>

                        @endif

                            {{-- ═══════════════════════════════════════════════════════════
                            TAB: SAVINGS
                            ═══════════════════════════════════════════════════════════ --}}
                            @if($activeTab === 'savings')

                                <main class="main">
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
                                    </div>
                                </main>

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

                                            {{-- <div class="card-box tw:bg-white" style="cursor:pointer;"
                                                data-action="openContributionModal">
                                                <div class="card-header-icon">
                                                    <p>Last Contribution</p>
                                                    <div class="card-icon d-flex justify-content-center align-items-center">
                                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <h4>₱ {{ number_format($lastContributionAmount, 2) }}</h4>
                                                    <span>{{ $lastContributionDate }}</span>
                                                </div>
                                            </div> --}}

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
                                                Please subscribe to Share Capital first to unlock your savings stats — use
                                                the
                                                <a href="{{ route('Financial', ['tab' => 'share_capital']) }}">Share Capital
                                                    tab</a> above.
                                            </div>
                                        </div>
                                    @endif
                                </div>



                                {{-- <div class="ask-box">
                                    <div class="ask-body">
                                        <div class="ask-card">
                                            <div class="ask-card-text">
                                                <h3>Secure your savings with Time Deposit</h3>
                                                <p>Grow your money with higher returns and guaranteed earnings over a
                                                    fixed
                                                    term.
                                                </p>
                                            </div>
                                            <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                                                <div
                                                    class="ask-card-button {{ request()->routeIs('TimeDeposit') ? 'active' : '' }}">
                                                    <a href="{{ route(" TimeDeposit") }}">

                                                        Time Deposit
                                                        <i class="fa fa-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="{{ !$hasShareCapital ? 'gated' : '' }}">
                                    <div class="parent-panel">
                                        <div class="panel graph">
                                            <div class="panel-head"
                                                style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                                <div>
                                                    <h3>Savings Growth</h3>
                                                    <p>{{ $growthYear === now()->year ? 'Net deposits over the last 6
                                                        months' : "Net deposits for {$growthYear}" }}
                                                    </p>
                                                </div>
                                                <select class="sm-filter-select" id="growthYearSelect"
                                                    onchange="changeGrowthYear(this.value)">
                                                    @foreach($availableGrowthYears as $y)
                                                    <option value="{{ $y }}" {{ $growthYear==$y ? 'selected' : '' }}>
                                                        {{ $y }}
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
                                                                <div class="bar-tooltip-title">{{ $month['label'] }}
                                                                </div>
                                                                <div class="bar-tooltip-row">
                                                                    <span
                                                                        class="bar-tooltip-dot {{ $month['is_current'] ? 'dot-gold' : 'dot-blue' }}"></span>
                                                                    <span class="bar-tooltip-label">Net Savings:</span>
                                                                    <span class="bar-tooltip-value">
                                                                        {{ $month['net'] >= 0 ? '₱' : '-₱' }}{{
                                                                        number_format(abs($month['net']), 2) }}
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
                                            Please <a href="{{ route('ShareCapitalMember') }}">subscribe to Share
                                                Capital</a>
                                            first to unlock your breakdown and growth chart.
                                        </div>
                                    </div>
                                    @endif
                                </div> --}}

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
                                                <a href="{{ route('Financial', array_merge(request()->except('type', 'page'), ['tab' => 'savings', 'type' => 'all'])) }}"
                                                    class="sm-tab {{ $type === 'all' ? 'active' : '' }}">All</a>
                                                <a href="{{ route('Financial', array_merge(request()->except('type', 'page'), ['tab' => 'savings', 'type' => 'deposit'])) }}"
                                                    class="sm-tab {{ $type === 'deposit' ? 'active' : '' }}">Deposits</a>
                                                <a href="{{ route('Financial', array_merge(request()->except('type', 'page'), ['tab' => 'savings', 'type' => 'withdrawal'])) }}"
                                                    class="sm-tab {{ $type === 'withdrawal' ? 'active' : '' }}">Withdrawals</a>
                                                <a href="{{ route('Financial', array_merge(request()->except('type', 'page'), ['tab' => 'savings', 'type' => 'interest_credit'])) }}"
                                                    class="sm-tab {{ $type === 'interest_credit' ? 'active' : '' }}">Interest
                                                    Accrued</a>
                                            </div>

                                            <form method="GET" action="{{ route('Financial') }}" class="sm-tx-toolbar"
                                                id="sm-tx-filter-form">
                                                <input type="hidden" name="tab" value="savings">
                                                <input type="hidden" name="type" value="{{ $type }}">
                                                <div class="sm-search-box">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                    <input type="text" name="ref" value="{{ $ref }}"
                                                        placeholder="Search by reference no.">
                                                </div>
                                                <input type="date" class="sm-filter-select" name="date" value="{{ $date }}"
                                                    data-action="sm-submit-tx-filter">

                                                <select name="status" class="sm-filter-select" data-submit-on-change>
                                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All
                                                        Status
                                                    </option>
                                                    @foreach($availableStatuses as $s)
                                                        <option value="{{ strtolower($s) }}" {{ $status === strtolower($s) ? 'selected' : '' }}>{{ $s }}</option>
                                                    @endforeach
                                                </select>

                                                @if($ref !== '' || $date !== '' || $status !== 'all')
                                                    <a href="{{ route('Financial', ['tab' => 'savings', 'type' => $type]) }}"
                                                        class="sm-filter-clear">Clear filters</a>
                                                @endif
                                            </form>

                                            <div class="card-box-savings">
                                                <div class="overflow-x-auto">
                                                    <table class="table table-scroll m-0">
                                                        <thead>
                                                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.2);">
                                                                <th class="text-start">Type</th>
                                                                <th class="text-start">Reference No.</th>
                                                                <th class="text-start">Date</th>
                                                                <th class="text-start">Amount</th>
                                                                <th class="text-start">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($transactions as $tx)
                                                                @php
                                                                    $rowMethod = !empty($tx->payment_method)
                                                                        ? $tx->payment_method
                                                                        : ($tx->type === 'interest_credit'
                                                                            ? 'Auto-credited'
                                                                            : (!empty($tx->gcash_reference_no) ? 'GCash' : '—'));
                                                                    $rowDisplayStatus = $tx->status ?? 'completed';
                                                                    $rowRefPref = $tx->reference_no ?? '';
                                                                    if ($tx->type === 'deposit' && str_starts_with($rowRefPref, 'DISB')) {
                                                                        $rowTypeLabel = 'Loan Disbursement';
                                                                    } elseif ($tx->type === 'deposit' && str_starts_with($rowRefPref, 'PAT')) {
                                                                        $rowTypeLabel = 'Patronage Refund';
                                                                    } elseif ($tx->type === 'deposit' && str_starts_with($rowRefPref, 'DIV')) {
                                                                        $rowTypeLabel = 'Dividend Deposit';
                                                                    } elseif ($tx->type === 'interest_credit') {
                                                                        $rowTypeLabel = 'Interest Accrued';
                                                                    } elseif (str_starts_with($rowRefPref, 'CNV')) {
                                                                        $rowTypeLabel = 'Converted to Share Capital';

                                                                    } elseif ($tx->type === 'deposit') {
                                                                        $rowTypeLabel = 'Savings Deposit';
                                                                    } elseif (str_starts_with($rowRefPref, 'LNPAY')) {
                                                                        $rowTypeLabel = 'Loan Repay';
                                                                    } else {
                                                                        $rowTypeLabel = 'Withdrawal';
                                                                    }
                                                                @endphp
                                                                <tr data-row-source="sv"
                                                                    data-void="{{ strtolower($tx->status ?? '') === 'voided' ? '1' : '0' }}"
                                                                    data-reason="{{ $tx->void_reason }}"
                                                                    class="{{ strtolower($tx->status ?? '') === 'voided' ? 'tx-voided-row' : '' }}"
                                                                    data-member="{{ Auth::user()->name ?? 'Member' }}"
                                                                    data-type="{{ $rowTypeLabel }}"
                                                                    data-amount="{{ number_format((float) $tx->amount, 2) }}"
                                                                    data-method="{{ $rowMethod }}"
                                                                    data-ref="{{ $tx->reference_no ?? '—' }}"
                                                                    data-txdate="{{ \Carbon\Carbon::parse($tx->transaction_date)->timezone('Asia/Manila')->format('M d, Y · h:i A') }}"
                                                                    data-status="{{ $rowDisplayStatus }}"
                                                                    style="cursor:pointer;" data-action="handleTxnRowClick"
                                                                    data-arg='["|event|","|el|","sv"]'>
                                                                    <td class="text-start">
                                                                        @if($tx->type === 'deposit' && str_starts_with($tx->reference_no ?? '', 'DISB'))
                                                                            <div class="deposit">Loan Disbursement</div>
                                                                        @elseif($tx->type === 'deposit' && str_starts_with($tx->reference_no ?? '', 'PAT'))
                                                                            <div class="deposit">Patronage Refund</div>
                                                                        @elseif($tx->type === 'deposit' && str_starts_with($tx->reference_no ?? '', 'DIV'))
                                                                            <div class="deposit">Dividend Deposit</div>
                                                                        @elseif($tx->type === 'interest_credit')
                                                                            <div class="deposit">Interest Accrued</div>
                                                                        @elseif($tx->type === 'deposit')
                                                                            <div class="deposit">Savings Deposit</div>

                                                                        @elseif(str_starts_with($tx->reference_no ?? '', 'CNV'))
                                                                            <div class="withdraw">Converted to Share Capital
                                                                            </div>
                                                                        @elseif(str_starts_with($tx->reference_no ?? '', 'LNPAY'))
                                                                            <div class="withdraw">Loan Repay</div>
                                                                        @else
                                                                            <div class="withdraw">Withdrawal</div>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-start">
                                                                        @if ($tx->reference_no && !str_starts_with($tx->reference_no, 'CNV'))
                                                                            <span class="tx-ref">{{ $tx->reference_no }}</span>
                                                                        @else
                                                                            <span style="color:#aaa;">—</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-start">
                                                                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('m/d/Y') }}
                                                                    </td>
                                                                    <td class="text-start"
                                                                        style="font-weight:700; color:{{ strtolower($tx->status ?? '') === 'voided' ? 'var(--muted)' : ($tx->type === 'withdrawal' ? 'var(--red)' : 'var(--green)') }}">
                                                                        @if(strtolower($tx->status ?? '') === 'voided')
                                                                            <span style="color:#aaa; font-weight:400;">—</span>
                                                                        @else
                                                                            {{ $tx->type === 'withdrawal' ? '-' : '+' }}
                                                                            ₱{{ number_format($tx->amount, 2) }}
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
                                                                        @elseif ($displayStatus === 'voided')
                                                                            <span class="status voided">Returned</span>
                                                                        @elseif ($displayStatus === 'locked')
                                                                            <span class="status locked">Locked</span>
                                                                        @else
                                                                            <span
                                                                                class="status">{{ ucfirst($displayStatus) }}</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-5"
                                                                        style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                                                        <i class="fa-solid fa-folder-open fa-2x mb-3"
                                                                            style="color: var(--muted);"></i>
                                                                        <p
                                                                            style="color:var(--muted);margin-top:0.5rem; text-align: center;">
                                                                            No
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
                                                            <b>{{ $transactions->total() }} </b>
                                                        </div>

                                                        @if ($transactions->hasPages())
                                                            <div class="sm-pagination">
                                                                @if ($transactions->onFirstPage())
                                                                    <span class="sm-page-btn disabled" aria-label="Previous page"><i
                                                                            class="fa-solid fa-chevron-left"></i></span>
                                                                @else
                                                                    <a href="{{ $transactions->previousPageUrl() }}" class="sm-page-btn"
                                                                        aria-label="Previous page">
                                                                        <i class="fa-solid fa-chevron-left"></i>
                                                                    </a>
                                                                @endif

                                                                <span class="sm-page-info">{{ $transactions->currentPage() }} of
                                                                    {{ $transactions->lastPage() }}</span>

                                                                @if ($transactions->hasMorePages())
                                                                    <a href="{{ $transactions->nextPageUrl() }}" class="sm-page-btn"
                                                                        aria-label="Next page">
                                                                        <i class="fa-solid fa-chevron-right"></i>
                                                                    </a>
                                                                @else
                                                                    <span class="sm-page-btn disabled" aria-label="Next page"><i
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
                                                Please subscribe to Share Capital first to unlock your transaction history —
                                                use the <a href="{{ route('Financial', ['tab' => 'share_capital']) }}">Share
                                                    Capital tab</a> above.
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            @endif

                        </div>

                </main>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
        ERROR / WARNING TOASTS (shared)
        ═══════════════════════════════════════ --}}
        @if(session('error'))
            <div
                style="position: fixed; top: 1.2rem; right: 1.2rem; z-index: 9999;
                                                                                                                                                                                                                                                            background: #fff; border: 1.5px solid #f5c6c6; border-radius: 14px;
                                                                                                                                                                                                                                                            padding: 1rem 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                                                                                                                                                                                                                                                            display: flex; align-items: center; gap: 12px; max-width: 360px;">
                <div
                    style="width: 36px; height: 36px; background: #fef0f0; border-radius: 50%;
                                                                                                                                                                                                                                                                display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa fa-times" style="color: #e03131; font-size: 15px;"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 13px; font-weight: 700; color: #1a1a1a;">Error</p>
                    <p style="margin: 0; font-size: 12px; color: #888;">{{ session('error') }}</p>
                </div>
                <button data-action="remove-parent"
                    style="background: none; border: none; color: #bbb; font-size: 18px; cursor: pointer; margin-left: auto; line-height: 1;">×</button>
            </div>
        @endif

        @if(session('warning'))
            <div
                style="position: fixed; top: 1.2rem; right: 1.2rem; z-index: 9999;
                                                                                                                                                                                                                                                            background: #fff; border: 1.5px solid #ffe082; border-radius: 14px;
                                                                                                                                                                                                                                                            padding: 1rem 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                                                                                                                                                                                                                                                            display: flex; align-items: center; gap: 12px; max-width: 380px;">
                <div
                    style="width: 36px; height: 36px; background: #fff8e1; border-radius: 50%;
                                                                                                                                                                                                                                                                display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa fa-triangle-exclamation" style="color: #b8860b; font-size: 15px;"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 13px; font-weight: 700; color: #1a1a1a;">Notice</p>
                    <p style="margin: 0; font-size: 12px; color: #888;">{{ session('warning') }}</p>
                </div>
                <button data-action="remove-parent"
                    style="background: none; border: none; color: #bbb; font-size: 18px; cursor: pointer; margin-left: auto; line-height: 1;">×</button>
            </div>
        @endif

    </div><!-- end .container-fluid -->

    {{-- ═══════════════════════════════════════
    SAVINGS TAB — modals (success / TD history)
    ═══════════════════════════════════════ --}}
    @if($activeTab === 'savings')

        {{-- ============================================================
        RECEIPT OVERLAY — Deposit (matches share-capital/withdrawal style)
        ============================================================ --}}
        @if(session('deposit_success'))
            <div id="sv-receipt-overlay" class="active">
                <div id="sv-receipt-modal">
                    <div class="sv-receipt-header">
                        <div class="sv-check-circle"><i class="fa-solid fa-check"></i></div>
                        <h2>Request Submitted!</h2>
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
                                <span class="sv-status-badge"><span class="dot"></span> Pending Approval</span>
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
                data-type="Deposit" data-amount="{{ number_format(session('deposit_amount', 0), 2) }}"
                data-method="{{ session('deposit_method', '—') }}" data-ref="{{ session('deposit_reference', '—') }}"
                data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}" data-status="Pending"
                style="display:none;">
            </div>
        @endif

        {{-- ============================================================
        RECEIPT OVERLAY — Withdrawal (matches deposit/share-capital style)
        ============================================================ --}}
        @if(session('withdraw_success'))
            <div id="wv-receipt-overlay" class="active">
                <div id="wv-receipt-modal">
                    <div class="wv-receipt-header">
                        <div class="wv-check-circle"><i class="fa-solid fa-check"></i></div>
                        <h2>Request Submitted!</h2>
                        <p>Your withdrawal request is pending for approval.</p>
                    </div>

                    <div class="wv-receipt-body" id="wv-receipt-printable">
                        <div class="wv-receipt-row">
                            <span class="label">Organization</span>
                            <span class="value">KMPCATS</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Member</span>
                            <span class="value">{{ session('withdraw_member', Auth::user()->name ?? 'Member') }}</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Transaction Type</span>
                            <span class="value">Withdrawal</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Amount</span>
                            <span class="value highlight">₱{{ number_format(session('withdraw_amount', 0), 2) }}</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Payment Method</span>
                            <span class="value">{{ session('withdraw_method', '—') }}</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Reference No.</span>
                            <span class="value"><span
                                    class="wv-ref-badge">{{ session('withdraw_reference', '—') }}</span></span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Date</span>
                            <span class="value">{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</span>
                        </div>
                        <div class="wv-receipt-row">
                            <span class="label">Status</span>
                            <span class="value">
                                <span class="wv-status-badge"><span class="dot"></span> Pending Approval</span>
                            </span>
                        </div>
                    </div>

                    <div class="wv-receipt-footer">
                        <button class="wv-btn-download" data-action="wvDownloadReceipt">
                            <i class="fa-solid fa-download"></i> Download Receipt
                        </button>
                        <button class="wv-btn-close-modal" data-action="wvCloseModal">Close</button>
                    </div>
                </div>
            </div>

            <div id="wv-receipt-data" data-member="{{ session('withdraw_member', Auth::user()->name ?? 'Member') }}"
                data-type="Withdrawal" data-amount="{{ number_format(session('withdraw_amount', 0), 2) }}"
                data-method="{{ session('withdraw_method', '—') }}" data-ref="{{ session('withdraw_reference', '—') }}"
                data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}" data-status="Pending"
                style="display:none;">
            </div>
        @endif

        <div id="contribution-breakdown-overlay"
            style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:99999; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;">
            <div
                style="background:#fff; border-radius:20px; width:100%; max-width:480px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto;">
                <div
                    style="padding:1.5rem; border-bottom:1px solid #e8e8e8; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <h2 style="margin:0 0 4px; font-size:1.1rem; font-weight:700; color:#1a1a1a;">Contribution
                            Breakdown
                        </h2>
                        <p style="margin:0; font-size:0.8rem; color:var(--muted,#888);">Your savings contribution
                            history
                        </p>
                    </div>
                    <button type="button" data-action="closeContributionModal"
                        style="background:none;border:none;font-size:18px;color:#888;cursor:pointer;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div style="padding:1.25rem 1.5rem; display:flex; gap:0.75rem;">
                    <div style="flex:1; background:#f7f7f8; border-radius:12px; padding:0.9rem 1rem;">
                        <div
                            style="font-size:0.72rem; color:var(--muted,#888); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">
                            Total Contributions</div>
                        <div style="font-size:1.15rem; font-weight:700; color:var(--teal,#0d9488);">
                            ₱{{ number_format($totalContributions, 2) }}</div>
                    </div>
                    <div style="flex:1; background:#f7f7f8; border-radius:12px; padding:0.9rem 1rem;">
                        <div
                            style="font-size:0.72rem; color:var(--muted,#888); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px;">
                            Last Posted</div>
                        <div style="font-size:1.15rem; font-weight:700; color:#1a1a1a;">
                            ₱{{ number_format($lastContributionAmount, 2) }}</div>
                        <div style="font-size:0.72rem; color:var(--muted,#888); margin-top:2px;">
                            {{ $lastContributionDate }}
                        </div>
                    </div>
                </div>

                <div style="padding:0 1.5rem 1.5rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                        <span style="font-size:0.85rem; font-weight:600; color:#1a1a1a;">Monthly Breakdown</span>
                        <select id="contribution-year-select"
                            style="border-radius:8px; border:1.5px solid #e0e0e0; font-size:12.5px; padding:5px 8px; color:#333;">
                            @foreach($availableGrowthYears as $yr)
                                <option value="{{ $yr }}">{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="contribution-months-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px;">
                    </div>
                    <p style="margin:10px 0 0; font-size:11px; color:#aaa;">
                        <span
                            style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1a7f37;margin-right:4px;"></span>
                        Deposited
                        <span
                            style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ddd;margin:0 4px 0 12px;"></span>
                        Skipped
                    </p>
                </div>
            </div>
        </div>

        <script nonce="{{ csp_nonce() }}">
            const MONTHLY_BREAKDOWN = @json($monthlyBreakdownByYear);

            function renderContributionMonths(year) {
                const grid = document.getElementById('contribution-months-grid');
                if (!grid) return;
                const months = MONTHLY_BREAKDOWN[year] || [];
                grid.innerHTML = months.map(m => `
                                                                                                                                                                    <div style="text-align:center; padding:8px 4px; border-radius:10px;
                                                                                                                                                                        background:${m.paid ? '#e7f6ec' : '#f7f7f8'};
                                                                                                                                                                        border:1px solid ${m.paid ? '#b7e4c3' : '#eee'};">
                                                                                                                                                                        <div style="font-size:11px; font-weight:600; color:${m.paid ? '#1a7f37' : '#aaa'};">${m.label}</div>
                                                                                                                                                                        <div style="font-size:10px; margin-top:2px; color:${m.paid ? '#1a7f37' : '#bbb'};">
                                                                                                                                                                            ${m.paid ? '₱' + Number(m.amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '—'}
                                                                                                                                                                        </div>
                                                                                                                                                                    </div>
                                                                                                                                                                `).join('');
            }

            function openContributionModal() {
                const overlay = document.getElementById('contribution-breakdown-overlay');
                const yearSelect = document.getElementById('contribution-year-select');
                if (yearSelect) renderContributionMonths(yearSelect.value);
                if (overlay) overlay.style.display = 'flex';
            }

            function closeContributionModal() {
                const overlay = document.getElementById('contribution-breakdown-overlay');
                if (overlay) overlay.style.display = 'none';
            }

            document.getElementById('contribution-year-select')?.addEventListener('change', function () {
                renderContributionMonths(this.value);
            });

            document.getElementById('contribution-breakdown-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) closeContributionModal();
            });
        </script>

    @endif

    {{-- ═══ CONVERT SAVINGS → SHARE CAPITAL (member, own account) ═══ --}}
    <div class="modal fade" id="convertScModal" tabindex="-1" aria-labelledby="convertScModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 24px 60px rgba(0,0,0,0.15);">

                <div class="modal-header"
                    style="background:#fff; border-bottom:1px solid var(--line); padding:1.25rem 1.5rem;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div
                            style="width:45px; height:45px; background:var(--teal); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-arrows-rotate" style="color:#fff; font-size:16px;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="convertScModalLabel"
                                style="color:#1a1a1a; font-size:15px; font-weight:600;">Convert to Share Capital
                            </h5>
                            <p style="margin:0; color:var(--muted); font-size:13.5px;">Move money from your savings
                                into
                                share capital</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding:1.25rem 1.5rem;">

                    {{-- Form state --}}
                    <div id="cv-form-body">
                        <div style="display:flex; gap:12px; margin-bottom:1rem;">
                            <div
                                style="flex:1; background:#f0fbf4; border:1.5px dashed #86d9a3; border-radius:12px; padding:0.8rem 1rem;">
                                <div style="font-size:12px; color:#666;">Savings Balance</div>
                                <div id="cv-savings-balance" style="font-size:1.15rem; font-weight:700; color:#1a7f37;">
                                    ₱0.00
                                </div>
                            </div>
                            <div
                                style="flex:1; background:#eef4ff; border:1.5px dashed #9bbcf5; border-radius:12px; padding:0.8rem 1rem;">
                                <div style="font-size:12px; color:#666;">Share Capital Balance</div>
                                <div id="cv-sc-balance" style="font-size:1.15rem; font-weight:700; color:#1d4ed8;">
                                    ₱0.00
                                </div>
                            </div>
                        </div>

                        <div class="sc-inline-error" id="cv-error">
                            <i class="fa fa-circle-exclamation"></i>
                            <span id="cv-error-text"></span>
                        </div>

                        <label for="cv-amount"
                            style="font-size:12px; text-transform:uppercase; color:#888; font-weight:600; display:block; margin-bottom:6px;">Amount
                            to Convert</label>
                        <div class="sm-amount-wrap" style="margin-bottom:10px;">
                            <span class="sm-amount-prefix">₱</span>
                            <input class="sm-form-input" type="number" id="cv-amount" placeholder="0.00" min="200"
                                step="200" />
                        </div>

                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:15px;">
                            @foreach ([200, 500, 1000, 2000, 5000] as $q)
                                <button type="button" class="cv-qbtn" data-v="{{ $q }}"
                                    style="padding:5px 13px; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; background:#fff; color:#808080; border:1.5px solid #ddd;">₱{{ number_format($q) }}</button>
                            @endforeach
                        </div>

                        <div
                            style="background:#f7f7f8; border:1px solid #ececec; border-radius:12px; padding:0.9rem 1rem;">
                            <div style="display:flex; justify-content:space-between; font-size:13.5px; padding:4px 0;">
                                <span style="color:#666;">Share Price</span>
                                <strong>₱200.00 / share</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13.5px; padding:4px 0;">
                                <span style="color:#666;">Shares you will get</span>
                                <strong id="cv-shares">0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13.5px; padding:4px 0;">
                                <span style="color:#666;">Amount converted</span>
                                <strong id="cv-cost">₱0.00</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13.5px; padding:4px 0;">
                                <span style="color:#666;">Remainder (stays in savings)</span>
                                <strong id="cv-remainder" style="color:#c0392b;">₱0.00</strong>
                            </div>
                        </div>

                        <div
                            style="background:#fff8e1; border:1.5px solid #ffe082; border-radius:10px; padding:0.65rem 1rem; margin-top:1rem; font-size:12px; color:#856404; line-height:1.5;">
                            <i class="fa fa-circle-info" style="margin-right:6px;"></i>
                            Share capital is your investment in the cooperative. Once converted it
                            <strong>cannot be moved back to savings</strong>. Only whole shares are converted.
                        </div>
                    </div>

                    {{-- Confirm + password state --}}
                    <div id="cv-confirm" style="display:none;">
                        <div
                            style="background:#fff8e1; border:1.5px solid #ffe082; border-radius:12px; padding:0.9rem 1rem; margin-bottom:1rem; font-size:13.5px; color:#856404; line-height:1.5;">
                            <i class="fa fa-triangle-exclamation" style="margin-right:6px;"></i>
                            Are you sure you want to convert <strong id="cv-confirm-amount">₱0.00</strong>
                            (<strong id="cv-confirm-shares">0 shares</strong>) from your savings into share capital?
                            This cannot be moved back to savings.
                        </div>

                        <div class="sc-inline-error" id="cv-confirm-error">
                            <i class="fa fa-circle-exclamation"></i>
                            <span id="cv-confirm-error-text"></span>
                        </div>

                        <label for="cv-password"
                            style="font-size:12px; text-transform:uppercase; color:#888; font-weight:600; display:block; margin-bottom:6px;">
                            Enter your password if you are sure you wish to continue the conversion
                        </label>
                        <input type="password" id="cv-password" autocomplete="current-password"
                            placeholder="Your password"
                            style="width:100%; padding:8px 10px; border-radius:10px; border:1.5px solid #ddd; font-size:14px; box-sizing:border-box; height:46px;">
                    </div>

                    {{-- Success state --}}
                    <div id="cv-success" style="display:none; text-align:center; padding:1rem 0;">
                        <div
                            style="width:60px; height:60px; background:var(--teal); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 0.8rem;">
                            <i class="fa-solid fa-check" style="color:#fff; font-size:26px;"></i>
                        </div>
                        <h5 style="margin:0 0 4px; font-weight:700; color:#1a1a1a;">Conversion complete</h5>
                        <p id="cv-success-text" style="margin:0; color:#666; font-size:13.5px;"></p>
                    </div>
                </div>

                <div class="modal-footer"
                    style="background:#f8f9fa; border-top:1px solid rgba(0,0,0,0.1); padding:1rem 1.6rem; display:flex; flex-direction:column; gap:8px;">
                    <button type="button" id="cv-submit"
                        style="width:100%; padding:0.75rem; background:var(--teal); color:#fff; border:none; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fa fa-arrows-rotate"></i> <span id="cv-submit-label">Convert to Share
                            Capital</span>
                    </button>
                    <button type="button" id="cv-cancel" class="btn w-100 text-center" data-bs-dismiss="modal"
                        style="border-radius:8px; font-size:14px; padding:10px 18px; border:1.5px solid #e0e0e0; color:var(--muted);">
                        Cancel
                    </button>
                    <button type="button" id="cv-back" class="btn w-100 text-center"
                        style="display:none; border-radius:8px; font-size:14px; padding:10px 18px; border:1.5px solid #e0e0e0; color:var(--muted);">
                        Back
                    </button>
                    <button type="button" id="cv-done" class="btn w-100 text-center" data-bs-dismiss="modal"
                        style="display:none; border-radius:12px; font-size:14px; padding:10px 18px; background:var(--teal); color:#fff; font-weight:600;">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            const modalEl = document.getElementById('convertScModal');
            if (!modalEl) return;

            const PRICE = 200;
            const SV_BALANCE = {{ (float) $totalSavingsBalance }};
            const SC_BALANCE = {{ (float) $currentBalance }};
            const CONVERT_URL = "{{ route('savings.member.convert') }}";
            const CSRF = "{{ csrf_token() }}";

            const $ = (id) => document.getElementById(id);
            const amountEl = $('cv-amount');
            const sharesEl = $('cv-shares');
            const costEl = $('cv-cost');
            const remEl = $('cv-remainder');
            const errEl = $('cv-error');
            const errTextEl = $('cv-error-text');
            const submitBtn = $('cv-submit');
            const submitLabel = $('cv-submit-label');
            const cancelBtn = $('cv-cancel');
            const doneBtn = $('cv-done');
            const formBody = $('cv-form-body');
            const successBox = $('cv-success');

            const VERIFY_URL = "{{ route('savings.member.verify_password') }}";
            const confirmBox = $('cv-confirm');
            const pwEl = $('cv-password');
            const pwErr = $('cv-confirm-error');
            const pwErrText = $('cv-confirm-error-text');
            const backBtn = $('cv-back');
            let step = 'form';

            function showPwError(msg) { pwErrText.textContent = msg; pwErr.classList.add('show'); }
            function clearPwError() { pwErr.classList.remove('show'); pwErrText.textContent = ''; }

            function backToForm() {
                step = 'form';
                confirmBox.style.display = 'none';
                formBody.style.display = '';
                backBtn.style.display = 'none';
                cancelBtn.style.display = '';
                pwEl.value = '';
                clearPwError();
                submitBtn.disabled = false;
                submitLabel.textContent = 'Convert to Share Capital';
            }
            backBtn.addEventListener('click', backToForm);

            let converted = false;

            const fmt = (n) => '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function calc() {
                const amt = parseFloat(amountEl.value) || 0;
                const shares = Math.floor(amt / PRICE + 1e-9);
                const cost = shares * PRICE;
                return { amt, shares, cost, remainder: Math.max(0, amt - cost) };
            }

            function showError(msg) {
                errTextEl.textContent = msg;
                errEl.classList.add('show');
            }

            function clearError() {
                errEl.classList.remove('show');
                errTextEl.textContent = '';
            }

            function validate() {
                const c = calc();
                if (SV_BALANCE <= 0) return 'You have no savings to convert.';
                if (c.amt <= 0) return 'Enter the amount you want to convert.';
                if (c.shares < 1) return 'The minimum conversion is ₱' + PRICE + ' (1 share).';
                if (c.cost > SV_BALANCE) return 'That is more than your savings balance (' + fmt(SV_BALANCE) + ').';
                return '';
            }

            function refresh() {
                const c = calc();
                sharesEl.textContent = c.shares;
                costEl.textContent = fmt(c.cost);
                remEl.textContent = fmt(c.remainder);
                clearError();
            }

            amountEl.addEventListener('input', refresh);

            document.querySelectorAll('.cv-qbtn').forEach(function (b) {
                b.addEventListener('click', function () {
                    amountEl.value = b.dataset.v;
                    refresh();
                });
            });

            modalEl.addEventListener('show.bs.modal', function () {
                converted = false;
                backToForm();
                amountEl.value = '';
                $('cv-savings-balance').textContent = fmt(SV_BALANCE);
                $('cv-sc-balance').textContent = fmt(SC_BALANCE);
                formBody.style.display = '';
                successBox.style.display = 'none';
                submitBtn.style.display = 'flex';
                cancelBtn.style.display = '';
                doneBtn.style.display = 'none';
                submitBtn.disabled = false;
                submitLabel.textContent = 'Convert to Share Capital';
                refresh();
            });

            // After a successful conversion, refresh so every balance on the page is up to date.
            modalEl.addEventListener('hidden.bs.modal', function () {
                if (converted) window.location.href = "{{ route('Financial', ['tab' => 'savings']) }}";
            });

            submitBtn.addEventListener('click', async function () {
                // ── STEP 1: validate, then show the "are you sure" + password screen ──
                if (step === 'form') {
                    const problem = validate();
                    if (problem) { showError(problem); return; }

                    const c = calc();
                    $('cv-confirm-amount').textContent = fmt(c.cost);
                    $('cv-confirm-shares').textContent = c.shares + (c.shares === 1 ? ' share' : ' shares');

                    formBody.style.display = 'none';
                    confirmBox.style.display = 'block';
                    cancelBtn.style.display = 'none';
                    backBtn.style.display = '';
                    submitLabel.textContent = 'Confirm Conversion';
                    step = 'confirm';
                    pwEl.focus();
                    return;
                }

                // ── STEP 2: verify password, then convert ──
                const password = pwEl.value;
                if (!password) { showPwError('Please enter your password to continue.'); return; }

                const c = calc();
                submitBtn.disabled = true;
                backBtn.disabled = true;
                submitLabel.textContent = 'Converting...';
                clearPwError();

                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest'
                };

                try {
                    // verify password
                    const vRes = await fetch(VERIFY_URL, {
                        method: 'POST', headers, credentials: 'same-origin',
                        body: JSON.stringify({ password })
                    });
                    let vData = null;
                    try { vData = await vRes.json(); } catch (e) { }

                    if (!vRes.ok || !vData || vData.success === false) {
                        showPwError((vData && vData.message) || 'Incorrect password. Please try again.');
                        submitBtn.disabled = false;
                        backBtn.disabled = false;
                        submitLabel.textContent = 'Confirm Conversion';
                        pwEl.select();
                        return;
                    }

                    // convert
                    const res = await fetch(CONVERT_URL, {
                        method: 'POST', headers, credentials: 'same-origin',
                        body: JSON.stringify({ amount: c.cost })
                    });

                    let data = null;
                    try { data = await res.json(); } catch (e) { }

                    if (data === null) { window.location.reload(); return; }

                    if (res.ok && data.success !== false) {
                        converted = true;
                        $('cv-success-text').textContent =
                            fmt(c.cost) + ' was converted into ' + c.shares + (c.shares === 1 ? ' share.' : ' shares.');
                        confirmBox.style.display = 'none';
                        successBox.style.display = 'block';
                        submitBtn.style.display = 'none';
                        backBtn.style.display = 'none';
                        cancelBtn.style.display = 'none';
                        doneBtn.style.display = '';
                    } else {
                        const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                        showPwError(firstError || data.message || 'The conversion could not be completed.');
                        submitBtn.disabled = false;
                        submitLabel.textContent = 'Confirm Conversion';
                    }
                } catch (err) {
                    console.error('Convert to share capital failed:', err);
                    showPwError('Network error. Please try again.');
                    submitBtn.disabled = false;
                    submitLabel.textContent = 'Confirm Conversion';
                } finally {
                    backBtn.disabled = false;
                }
            });
        })();
    </script>
    {{-- ═══ END CONVERT SAVINGS → SHARE CAPITAL ═══ --}}

    {{-- ═══════════════════════════════════════════════════════════
    RECEIPT OVERLAY — Share Capital (opened by clicking a table row)
    ═══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'share_capital' || $activeTab === 'savings')
        <div id="sc-row-receipt-overlay" class="sc-receipt-overlay hidden-receipt">
            <div id="sc-row-receipt-modal" class="sc-receipt-modal">
                <div class="sc-receipt-header">
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                    <h2>Transaction Receipt</h2>
                    <p id="sc-row-receipt-sub">Share capital transaction details.</p>
                </div>

                <div class="sc-receipt-body" id="sc-row-receipt-printable">
                    <div class="sc-receipt-row">
                        <span class="label">Organization</span>
                        <span class="value">KMPCATS</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Member</span>
                        <span class="value" id="sc-row-receipt-member">—</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Transaction Type</span>
                        <span class="value" id="sc-row-receipt-type">—</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Shares</span>
                        <span class="value highlight" id="sc-row-receipt-shares">— shares</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Amount</span>
                        <span class="value highlight" id="sc-row-receipt-amount">—</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Payment Method</span>
                        <span class="value" id="sc-row-receipt-method">—</span>
                    </div>
                    <div class="sc-receipt-row" id="sc-row-receipt-ref-row">
                        <span class="label">Reference No.</span>
                        <span class="value"><span class="sc-ref-badge" id="sc-row-receipt-ref">—</span></span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Date</span>
                        <span class="value" id="sc-row-receipt-date">—</span>
                    </div>
                    <div class="sc-receipt-row">
                        <span class="label">Status</span>
                        <span class="value" id="sc-row-receipt-status">—</span>
                    </div>
                </div>

                <div class="sc-receipt-footer">
                    <button class="sc-btn-download" data-action="scRowDownloadReceipt">
                        <i class="fa-solid fa-download"></i> Download Receipt
                    </button>
                    <button class="sc-btn-close-modal" data-action="closeRowReceipt" data-arg='["sc"]'>Close</button>
                </div>
            </div>
        </div>
        <div id="sc-row-receipt-data" style="display:none;" data-member="" data-type="" data-shares="" data-amount=""
            data-method="" data-ref="" data-date="" data-status=""></div>

        {{-- ═══════════════════════════════════════════════════════════
        RECEIPT OVERLAY — Savings (opened by clicking a table row)
        ═══════════════════════════════════════════════════════════ --}}
        <div id="sv-row-receipt-overlay" class="sv-receipt-overlay hidden-receipt">
            <div id="sv-row-receipt-modal" class="sv-receipt-modal">
                <div class="sv-receipt-header">
                    <div class="sv-check-circle"><i class="fa-solid fa-check"></i></div>
                    <h2>Transaction Receipt</h2>
                    <p id="sv-row-receipt-sub">Savings transaction details.</p>
                </div>

                <div class="sv-receipt-body" id="sv-row-receipt-printable">
                    <div class="sv-receipt-row">
                        <span class="label">Organization</span>
                        <span class="value">KMPCATS</span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Member</span>
                        <span class="value" id="sv-row-receipt-member">—</span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Transaction Type</span>
                        <span class="value" id="sv-row-receipt-type">—</span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Amount</span>
                        <span class="value highlight" id="sv-row-receipt-amount">—</span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Payment Method</span>
                        <span class="value" id="sv-row-receipt-method">—</span>
                    </div>
                    <div class="sv-receipt-row" id="sv-row-receipt-ref-row">
                        <span class="label">Reference No.</span>
                        <span class="value"><span class="sv-ref-badge" id="sv-row-receipt-ref">—</span></span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Date</span>
                        <span class="value" id="sv-row-receipt-date">—</span>
                    </div>
                    <div class="sv-receipt-row">
                        <span class="label">Status</span>
                        <span class="value" id="sv-row-receipt-status">—</span>
                    </div>
                </div>

                <div class="sv-receipt-footer">
                    <button class="sv-btn-download" data-action="svRowDownloadReceipt">
                        <i class="fa-solid fa-download"></i> Download Receipt
                    </button>
                    <button class="sv-btn-close-modal" data-action="closeRowReceipt" data-arg='["sv"]'>Close</button>
                </div>
            </div>
        </div>
        <div id="sv-row-receipt-data" style="display:none;" data-member="" data-type="" data-amount="" data-method=""
            data-ref="" data-date="" data-status=""></div>
    @endif

    {{-- ═══════════════════════════════════════
    VOID REASON MODAL (Financial — savings & share capital)
    ═══════════════════════════════════════ --}}
    <div id="fin-void-overlay">
        <div id="fin-void-modal">
            <div class="fin-void-header">
                <div class="fin-void-circle"><i class="fa-solid fa-ban"></i></div>
                <h2>Transaction Returned</h2>
                <p>This transaction has been returned and cannot be viewed as a receipt.</p>
            </div>
            <div class="fin-void-body">
                <div class="fin-void-details">
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Type</span>
                        <span class="fin-void-row-value" id="fin-void-type-text">—</span>
                    </div>
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Member</span>
                        <span class="fin-void-row-value" id="fin-void-member-text">—</span>
                    </div>
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Shares</span>
                        <span class="fin-void-row-value" id="fin-void-shares-text">—</span>
                    </div>
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Reference No.</span>
                        <span class="fin-void-row-value" id="fin-void-ref-text">—</span>
                    </div>
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Date &amp; Time</span>
                        <span class="fin-void-row-value" id="fin-void-date-text">—</span>
                    </div>
                    <div class="fin-void-row">
                        <span class="fin-void-row-label">Method</span>
                        <span class="fin-void-row-value" id="fin-void-method-text">—</span>
                    </div>
                </div>
                <div class="fin-void-label">Returned Reason</div>
                <div class="fin-void-value" id="fin-void-reason-text">—</div>
            </div>
            <div class="fin-void-footer">
                <button class="fin-btn-void-close" data-action="finCloseVoidModal">Close</button>
            </div>
        </div>
    </div>

    <button id="triggerUnifiedTx" data-bs-toggle="modal" data-bs-target="#unifiedTxModal"
        style="display:none;"></button>

    {{-- QR Lightbox (shared by both tabs) --}}
    <div id="qr-lightbox-overlay"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:100000; align-items:center; justify-content:center;">
        <button type="button" data-action="closeQrLightbox"
            style="position:absolute; top:20px; right:24px; background:#fff; border:none; width:40px; height:40px; border-radius:50%; font-size:20px; color:#333; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa fa-times"></i>
        </button>
        <img id="qr-lightbox-img" src="" alt="GCash QR Code"
            style="max-width:90%; max-height:85vh; border-radius:12px;">
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;
            A.register('open-sc-qr', function (e, el) { openQrLightbox(el.dataset.src); return false; });
            A.register('sm-submit-tx-filter', function (e, el) { document.getElementById('sm-tx-filter-form').submit(); });
        })();

        AOS.init();

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
    </script>

    {{-- ═══════════════════════════════════════
    UNIFIED TRANSACTION MODAL — script (always loaded, both tabs supply the
    balance figures needed since the Financial controller computes both
    Share Capital and Savings data on every request)
    ═══════════════════════════════════════ --}}
    <script nonce="{{ csp_nonce() }}">
        (function () {
            const form = document.getElementById('unified-tx-form');
            if (!form) return;

            const PRICE = 200;
            const SC_BALANCE = {{ $currentBalance ?? 0 }};
            const SC_SHARES = {{ $currentShares ?? 0 }};
            const SV_BALANCE = {{ $totalSavingsBalance ?? 0 }};

            const HAS_SHARE_CAPITAL = {{ $hasShareCapital ? 'true' : 'false' }};
            const DEFAULT_DEST = "{{ $activeTab }}";

            const PAY_METHODS = {
                @foreach($paymentMethods as $pm)
                                                                                                                                                                                                        "{{ strtolower($pm->method_name) }}": {
                        name: "{{ $pm->method_name }}",
                        hasQr: {{ $pm->has_qr_code ? 'true' : 'false' }},
                        qrPath: "{{ $pm->qr_code_image_path ? url('/proof/' . ltrim($pm->qr_code_image_path, '/')) : '' }}"
                    },
                @endforeach
            };

        function getPayMethod(v) {
            v = String(v).toLowerCase();
            return PAY_METHODS[v] || null;
        }

        const destDisplay = document.getElementById('tx-dest-display');
        const destDisplayIcon = document.getElementById('tx-dest-display-icon');
        const destDisplayText = document.getElementById('tx-dest-display-text');
        const scFields = document.getElementById('tx-sc-fields');
        const svFields = document.getElementById('tx-sv-amount-field');
        const typeEl = document.getElementById('tx-type');
        const typeField = document.getElementById('tx-type-field');
        const typeHidden = document.getElementById('tx-type-hidden');
        const pay = document.getElementById('tx-pay');
        const qrBox = document.getElementById('tx-qr-box');
        const balanceLabel = document.getElementById('tx-balance-label');
        const balanceValue = document.getElementById('tx-balance-value');
        const sharesInput = document.getElementById('tx-shares');
        const scAmountInput = document.getElementById('tx-sc-amount');
        const shareCountEl = document.getElementById('tx-sc-share-count');
        const amountInput = document.getElementById('tx-amount');
        const costEl = document.getElementById('tx-cost');
        const submitBtn = document.getElementById('tx-submit-btn');
        const withdrawalNotice = document.getElementById('tx-sc-withdrawal-notice');
        const fullWithdrawalWarning = document.getElementById('tx-sc-full-withdrawal-warning');
        const inlineError = document.getElementById('tx-inline-error');
        const inlineErrorText = document.getElementById('tx-inline-error-text');
        const formMarker = document.getElementById('tx-form-marker');
        const payHidden = document.getElementById('tx-pay-hidden');
        const payField = document.getElementById('tx-pay-field');
        const gcashNumberField = document.getElementById('tx-gcash-number-field');
        const gcashNumberInput = document.getElementById('tx-gcash-number');
        const defaultContactNo = '{{ Auth::user()->otherinfo->contact_no ?? '' }}';

        let dest = '';
        let txRefUsed = false;
        const txRefInput = document.getElementById('tx-gcash-ref-input');
        const txRefUsedMsg = document.getElementById('tx-ref-used-msg');

        function setTxRefState(used) {
            txRefUsed = used;
            if (txRefUsedMsg) txRefUsedMsg.style.display = used ? 'block' : 'none';
            submitBtn.disabled = used;
            submitBtn.style.opacity = used ? 0.55 : 1;
            submitBtn.style.cursor = used ? 'not-allowed' : 'pointer';
        }

        let txRefTimer = null;
        if (txRefInput) {
            txRefInput.addEventListener('input', function () {
                clearTimeout(txRefTimer);
                const v = this.value.trim();
                if (v.length !== 13 || !/^\d{13}$/.test(v)) {
                    setTxRefState(false);
                    return;
                }
                txRefTimer = setTimeout(function () {
                    fetch('{{ route('reference.check') }}?ref=' + encodeURIComponent(v))
                        .then(function (r) { return r.json(); })
                        .then(function (d) { setTxRefState(!!d.used); })
                        .catch(function () { setTxRefState(false); });
                }, 300);
            });
        }


        function showInlineError(msg) {
            inlineErrorText.textContent = msg;
            inlineError.classList.add('show');
            inlineError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function clearInlineError() {
            inlineError.classList.remove('show');
            inlineErrorText.textContent = '';
        }

        function updateDestDisplay() {
            if (dest === 'share_capital') {
                destDisplayIcon.textContent = '🪙';
                destDisplayText.textContent = 'Share Capital';
            } else if (dest === 'savings') {
                destDisplayIcon.textContent = '🐷';
                destDisplayText.textContent = 'Savings';
            } else {
                destDisplayIcon.textContent = '—';
                destDisplayText.textContent = 'Select an account';
            }
        }

        function renderQrBox(method) {
            var qrArea = document.getElementById('tx-qr-area');
            var qrFallback = document.getElementById('tx-qr-fallback');
            if (!method) {
                qrArea.style.display = 'none';
                qrFallback.style.display = 'none';
                return;
            }
            var name = method.name || 'QR';
            document.getElementById('tx-qr-title').textContent = 'Scan to Pay via ' + name;
            document.getElementById('tx-qr-fallback-name').textContent = name;
            document.getElementById('tx-qr-ref-label').textContent = name + ' Reference Number';
            document.getElementById('tx-qr-ref-hint').textContent = 'Enter the reference number from your ' + name + ' transaction.';
            document.getElementById('tx-qr-proof-tag').textContent = '(' + name + ' proof)';
            var viewLink = document.getElementById('tx-qr-view');
            if (method.qrPath) {
                document.getElementById('tx-qr-img').src = method.qrPath;
                viewLink.dataset.src = method.qrPath;
                qrArea.style.display = 'block';
                qrFallback.style.display = 'none';
            } else {
                qrArea.style.display = 'none';
                qrFallback.style.display = 'block';
            }
        }

        function updatePaymentFields() {
            var txGcashRefInput = document.getElementById('tx-gcash-ref-input');
            var depositFraudWarning = document.getElementById('tx-deposit-fraud-warning');
            var proofInput = document.getElementById('tx-gcash-proof-input');
            var method = getPayMethod(pay.value);
            var hasQr = !!(method && method.hasQr);

            if (dest === 'savings' && typeEl.value === 'Withdrawal') {
                payField.style.display = 'none';
                gcashNumberField.style.display = '';
                gcashNumberInput.value = defaultContactNo;
                payHidden.value = 'gcash';
                qrBox.style.display = 'none';
                if (proofInput) proofInput.required = false;
                if (txGcashRefInput) txGcashRefInput.required = false;
                if (depositFraudWarning) depositFraudWarning.style.display = 'none';
            } else {
                payField.style.display = '';
                gcashNumberField.style.display = 'none';
                payHidden.value = pay.value || '';
                var isDeposit = dest === 'savings' || typeEl.value === 'Deposit';
                var showQr = hasQr && isDeposit;
                if (hasQr) {
                    renderQrBox(method);
                    qrBox.style.display = '';
                } else {
                    qrBox.style.display = 'none';
                }
                if (txGcashRefInput) txGcashRefInput.required = showQr;
                if (proofInput) proofInput.required = showQr;
                if (depositFraudWarning) depositFraudWarning.style.display = showQr ? '' : 'none';
            }
        }

        function updateFormAction() {
            if (dest === 'share_capital') {
                form.action = form.dataset.scRoute;
                formMarker.value = 'share_capital';
            } else if (typeEl.value === 'Withdrawal') {
                form.action = form.dataset.svWithdrawRoute;
                formMarker.value = 'withdraw';
            } else {
                form.action = form.dataset.svDepositRoute;
                formMarker.value = 'deposit';
            }
        }

        function toggleFullWithdrawalWarning(cost) {
            fullWithdrawalWarning.style.display = (SC_BALANCE > 0 && cost >= SC_BALANCE) ? 'block' : 'none';
        }

        function validateScWithdrawal(cost) {
            if (SC_BALANCE <= 0) {
                showInlineError('You cannot withdraw because your current share capital balance is ₱0.');
                return false;
            }
            if (cost > SC_BALANCE) {
                showInlineError(
                    'Withdrawal amount (₱' + cost.toLocaleString() +
                    ') exceeds your current share capital balance (₱' + SC_BALANCE.toLocaleString() + ').'
                );
                return false;
            }
            clearInlineError();
            toggleFullWithdrawalWarning(cost);
            return true;
        }

        function validateSvWithdrawal(amount) {
            if (SV_BALANCE <= 0) {
                showInlineError('You cannot withdraw because your current savings balance is ₱0.');
                return false;
            }
            if (amount > SV_BALANCE) {
                showInlineError(
                    'Withdrawal amount (₱' + amount.toLocaleString() +
                    ') exceeds your current savings balance (₱' + SV_BALANCE.toLocaleString() + ').'
                );
                return false;
            }
            clearInlineError();
            return true;
        }

        function setDest(d) {
            if (d === 'savings' && !HAS_SHARE_CAPITAL) {
                dest = '';
                updateDestDisplay();
                showInlineError('Savings is locked. You need an active Share Capital subscription before you can deposit or withdraw from Savings.');
                scFields.style.display = 'none';
                svFields.style.display = 'none';
                balanceLabel.textContent = 'Current Balance';
                balanceValue.textContent = 'Select an account';
                updateFormAction();
                return;
            }

            dest = d;
            updateDestDisplay();
            clearInlineError();
            withdrawalNotice.style.display = 'none';
            fullWithdrawalWarning.style.display = 'none';

            if (d === 'share_capital') {
                scFields.style.display = '';
                svFields.style.display = 'none';
                sharesInput.disabled = false;
                amountInput.disabled = true;
                typeField.style.display = '';
                typeEl.disabled = true;
                typeEl.value = 'Deposit';
                typeHidden.value = 'Deposit';
                balanceLabel.textContent = 'Current Balance';
                balanceValue.textContent = '₱' + SC_BALANCE.toLocaleString() + ' · ' + SC_SHARES + ' shares';
                if (typeEl.value === 'Withdrawal') {
                    withdrawalNotice.style.display = 'block';
                    validateScWithdrawal(+sharesInput.value * PRICE);
                }
            } else if (d === 'savings') {
                scFields.style.display = 'none';
                svFields.style.display = '';
                sharesInput.disabled = true;
                amountInput.disabled = false;
                typeField.style.display = '';
                typeEl.disabled = false;
                balanceLabel.textContent = 'My Savings Balance';
                balanceValue.textContent = '₱' + SV_BALANCE.toLocaleString(undefined, { minimumFractionDigits: 2 });
                if (typeEl.value === 'Withdrawal') {
                    validateSvWithdrawal(parseFloat(amountInput.value || 0));
                }
            } else {
                // Nothing selected yet
                scFields.style.display = 'none';
                svFields.style.display = 'none';
                typeField.style.display = 'none';
                typeEl.disabled = false;
                balanceLabel.textContent = 'Current Balance';
                balanceValue.textContent = 'Select an account';
            }
            updatePaymentFields();
            updateFormAction();
        }

        function pesoToShares(p) {
            p = parseFloat(p);
            return (!isNaN(p) && p > 0) ? +(p / PRICE).toFixed(6) : 0;
        }

        function updateSharesFromPeso(p) {
            p = parseFloat(p);
            if (isNaN(p) || p < 0) return;
            var shares = pesoToShares(p);
            sharesInput.value = shares;
            costEl.textContent = '₱' + p.toLocaleString();
            shareCountEl.textContent = shares;
            document.querySelectorAll('.tx-qbtn').forEach(b => {
                b.classList.toggle('active', parseFloat(b.dataset.v) === p);
            });
            if (dest === 'share_capital' && typeEl.value === 'Withdrawal') {
                validateScWithdrawal(p);
            }
        }

        scAmountInput.addEventListener('input', () => updateSharesFromPeso(scAmountInput.value));
        scAmountInput.addEventListener('blur', () => {
            const p = parseFloat(scAmountInput.value);
            if (isNaN(p) || p <= 0) {
                scAmountInput.value = 200;
                updateSharesFromPeso(200);
            } else {
                updateSharesFromPeso(p);
            }
        });
        document.querySelectorAll('.tx-qbtn').forEach(b => b.onclick = () => {
            scAmountInput.value = b.dataset.v;
            updateSharesFromPeso(+b.dataset.v);
        });

        document.querySelectorAll('.tx-amt-qbtn').forEach(b => b.onclick = () => {
            amountInput.value = b.dataset.v;
            if (typeEl.value === 'Withdrawal') validateSvWithdrawal(parseFloat(amountInput.value || 0));
        });

        typeEl.onchange = function () {
            typeHidden.value = this.value;
            clearInlineError();
            fullWithdrawalWarning.style.display = 'none';

            if (dest === 'share_capital') {
                withdrawalNotice.style.display = (this.value === 'Withdrawal') ? 'block' : 'none';
                if (this.value === 'Withdrawal') validateScWithdrawal(+sharesInput.value * PRICE);
            } else if (dest === 'savings') {
                withdrawalNotice.style.display = 'none';
                if (this.value === 'Withdrawal') validateSvWithdrawal(parseFloat(amountInput.value || 0));
            }
            updatePaymentFields();
            updateFormAction();
        };

        pay.onchange = function () {
            payHidden.value = this.value;
            updatePaymentFields();
            submitBtn.style.display = 'flex';
        };

        document.getElementById('tx-gcash-proof-input')?.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('tx-gcash-proof-preview-img').src = e.target.result;
                    document.getElementById('tx-gcash-proof-preview').style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        document.getElementById('unifiedTxModal').addEventListener('show.bs.modal', () => {
            amountInput.value = '';
            pay.value = '';
            payHidden.value = '';
            typeEl.value = '';
            typeEl.disabled = false;
            typeHidden.value = '';
            typeField.style.display = 'none';
            qrBox.style.display = 'none';
            document.getElementById('tx-qr-area').style.display = 'none';
            document.getElementById('tx-qr-fallback').style.display = 'none';
            payField.style.display = '';
            gcashNumberField.style.display = 'none';
            gcashNumberInput.value = defaultContactNo;
            submitBtn.style.display = 'flex';
            withdrawalNotice.style.display = 'none';
            fullWithdrawalWarning.style.display = 'none';
            document.getElementById('tx-note').value = '';
            document.getElementById('tx-gcash-proof-input').required = false;
            var refResets = document.getElementById('tx-gcash-ref-input');
            if (refResets) { refResets.value = ''; refResets.required = false; }
            setTxRefState(false);
            var depositFraudWarning = document.getElementById('tx-deposit-fraud-warning');
            if (depositFraudWarning) depositFraudWarning.style.display = 'none';
            clearInlineError();
            scAmountInput.value = 200;
            updateSharesFromPeso(200);
            setDest(DEFAULT_DEST);
        });

        form.addEventListener('submit', function (e) {
            if (txRefUsed) {
                e.preventDefault();
                if (txRefUsedMsg) txRefUsedMsg.style.display = 'block';
                return;
            }
            if (!dest) {
                e.preventDefault();
                showInlineError('Please select an account (Share Capital or Savings).');
                return;
            }
            if (typeEl.value === 'Withdrawal') {
                if (dest === 'share_capital' && !validateScWithdrawal(+sharesInput.value * PRICE)) {
                    e.preventDefault();
                    return;
                }
                if (dest === 'savings' && !validateSvWithdrawal(parseFloat(amountInput.value || 0))) {
                    e.preventDefault();
                    return;
                }
            }
            updateFormAction();
        });

        updateFormAction();

        @if ($errors->any() && in_array(old('_form'), ['share_capital', 'deposit', 'withdraw']))
            document.getElementById('triggerUnifiedTx').click();
        @endif
        }) ();
    </script>

    {{-- RECEIPT MODAL script (Share Capital) --}}
    @if($activeTab === 'share_capital')
        <script nonce="{{ csp_nonce() }}">
            function scCloseModal() {
                const overlay = document.getElementById('sc-receipt-overlay');
                if (overlay) overlay.remove();
            }

            document.getElementById('sc-receipt-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) scCloseModal();
            });

            function scDownloadReceipt() {
                const d = document.getElementById('sc-receipt-data')?.dataset;
                if (!d) return;

                const isCompleted = d.status === 'Completed';

                const wrapper = document.createElement('div');
                wrapper.style.cssText = `
                                                                                                                                                                                                                                                        position: fixed; left: -9999px; top: 0;
                                                                                                                                                                                                                                                        width: 400px; background: #fff;
                                                                                                                                                                                                                                                        border-radius: 20px; overflow: hidden;
                                                                                                                                                                                                                                                        box-shadow: 0 8px 40px rgba(0,0,0,0.15);
                                                                                                                                                                                                                                                    `;

                wrapper.innerHTML = `
                                                                                                                                                                                                                                                        <div style="background:linear-gradient(135deg,#1a4a3a,#2d6a4f);padding:2rem 1.5rem 1.2rem;text-align:center;">
                                                                                                                                                                                                                                                            <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                            <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Request Submitted!</div>
                                                                                                                                                                                                                                                            <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">
                                                                                                                                                                                                                                                                ${d.type === 'Deposit'
                        ? (isCompleted
                            ? 'Your deposit has been recorded successfully.'
                            : 'Your deposit request is pending for approval.')
                        : (isCompleted
                            ? 'Your withdrawal has been processed successfully.'
                            : 'Your withdrawal request is pending for approval.')}
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                        <div style="padding:1.2rem 1.5rem;">
                                                                                                                                                                                                                                                            <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                                                                                                                                                                                                                                                                ${scReceiptRow('Organization', 'KMPCATS')}
                                                                                                                                                                                                                                                                ${scReceiptRow('Member', d.member)}
                                                                                                                                                                                                                                                                ${scReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                                                                                                                                                                                                                                                                ${scReceiptRow('Shares', '<strong style="color:#1a4a3a">' + d.shares + ' shares</strong>')}
                                                                                                                                                                                                                                                                ${scReceiptRow('Amount', '<strong style="color:#1a4a3a">&#8369;' + d.amount + '</strong>')}
                                                                                                                                                                                                                                                                ${scReceiptRow('Payment Method', d.method)}
                                                                                                                                                                                                                                                                ${scReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                                                                                                                                                                                                                                                                ${scReceiptRow('Date & Time', d.date)}
                                                                                                                                                                                                                                                                ${scReceiptRow('Status', isCompleted
                                ? '<span style="color:#2e7d32;font-weight:700;font-size:0.72rem;">✓ Completed</span>'
                                : '<span style="color:#b8860b;font-weight:700;font-size:0.72rem;">• Pending Approval</span>')}
                                                                                                                                                                                                                                                            </table>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                        <div style="padding:0.8rem 1.5rem 1.2rem;text-align:center;border-top:1px dashed #e8e8e8;">
                                                                                                                                                                                                                                                            <div style="color:#aaa;font-size:0.72rem;">This is an official transaction receipt from KMPCATS.</div>
                                                                                                                                                                                                                                                            <div style="color:#bbb;font-size:0.68rem;margin-top:2px;">Keep this for your records.</div>
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                    `;

                document.body.appendChild(wrapper);

                html2canvas(wrapper, { scale: 2, useCORS: true, backgroundColor: null }).then(canvas => {
                    document.body.removeChild(wrapper);
                    const link = document.createElement('a');
                    link.download = `KMPCATS-Receipt-${d.ref}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            }

            function scReceiptRow(label, value) {
                return `
                                                                                                                                                                                                                                                        <tr style="border-bottom:1px dashed #ebebeb;">
                                                                                                                                                                                                                                                            <td style="color:#888;font-weight:500;padding:0.55rem 0.5rem 0.55rem 0;vertical-align:middle;white-space:nowrap;">${label}</td>
                                                                                                                                                                                                                                                            <td style="color:#1a1a1a;font-weight:600;text-align:right;padding:0.55rem 0 0.55rem 0.5rem;vertical-align:middle;">${value}</td>
                                                                                                                                                                                                                                                        </tr>
                                                                                                                                                                                                                                                    `;
            }

            /* CONTRIBUTION HISTORY — SEARCH + FILTERS + PAGINATION */
            (function () {
                const searchInp = document.getElementById('sc-search-input');
                const dateInp = document.getElementById('sc-filter-date');
                const typeInp = document.getElementById('sc-filter-type');
                const statusInp = document.getElementById('sc-filter-status');
                const clearBtn = document.getElementById('sc-filter-clear');
                if (!dateInp) return;

                const tbody = document.getElementById('sc-contribution-tbody');
                const rows = Array.from(tbody.querySelectorAll('tr[data-date]'));
                const footerCount = document.getElementById('sc-table-footer-count');
                const pagination = document.getElementById('sc-pagination');
                const PAGE_SIZE = 10;
                let emptyRow = null;
                let currentPage = 1;

                function getFilteredRows() {
                    const search = (searchInp?.value || '').trim().toLowerCase().replace(/,/g, '');
                    const date = dateInp.value;
                    const type = typeInp?.value || '';
                    const status = statusInp?.value || '';

                    return rows.filter(row => {
                        const matchesDate = !date || row.dataset.date === date;
                        const matchesType = !type || row.dataset.type === type;
                        const matchesStatus = !status || row.dataset.status === status;
                        const rowText = row.textContent.toLowerCase().replace(/,/g, '');
                        const matchesSearch = !search || rowText.includes(search);
                        return matchesDate && matchesType && matchesStatus && matchesSearch;
                    });
                }

                function renderPagination(filteredCount, totalPages) {
                    if (!pagination) return;
                    pagination.innerHTML = '';
                    if (totalPages <= 1) return;

                    const prevBtn = document.createElement('button');
                    prevBtn.type = 'button';
                    prevBtn.className = 'sc-page-btn nav';
                    prevBtn.setAttribute('aria-label', 'Previous page');
                    prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
                    prevBtn.disabled = currentPage === 1;
                    prevBtn.addEventListener('click', () => { currentPage--; applyFilter(); });

                    const info = document.createElement('span');
                    info.className = 'sc-page-info';
                    info.textContent = currentPage + ' of ' + totalPages;

                    const nextBtn = document.createElement('button');
                    nextBtn.type = 'button';
                    nextBtn.className = 'sc-page-btn nav';
                    nextBtn.setAttribute('aria-label', 'Next page');
                    nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
                    nextBtn.disabled = currentPage === totalPages;
                    nextBtn.addEventListener('click', () => { currentPage++; applyFilter(); });

                    pagination.appendChild(prevBtn);
                    pagination.appendChild(info);
                    pagination.appendChild(nextBtn);
                }

                function applyFilter() {
                    const filtered = getFilteredRows();
                    const filteredCount = filtered.length;
                    const totalPages = Math.max(1, Math.ceil(filteredCount / PAGE_SIZE));

                    if (currentPage > totalPages) currentPage = totalPages;
                    if (currentPage < 1) currentPage = 1;

                    const startIdx = (currentPage - 1) * PAGE_SIZE;
                    const endIdx = startIdx + PAGE_SIZE;

                    rows.forEach(row => { row.style.display = 'none'; });
                    filtered.slice(startIdx, endIdx).forEach(row => { row.style.display = ''; });

                    if (footerCount) {
                        const shownCount = Math.min(endIdx, filteredCount) - startIdx;
                        footerCount.textContent = `Showing ${Math.max(shownCount, 0)} of ${filteredCount}`;
                    }

                    if (filteredCount === 0) {
                        if (!emptyRow) {
                            emptyRow = document.createElement('tr');
                            emptyRow.id = 'sc-filter-empty-row';
                            emptyRow.innerHTML = `<td colspan="6" style="text-align:center;color:#aaa;padding:2rem;font-size:13px;">
                                                                                                                                                                                                                                                        <i class="fa fa-filter-circle-xmark" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                                                                                                                                                                                                                                        No contributions match your filters.
                                                                                                                                                                                                                                                    </td>`;
                            tbody.appendChild(emptyRow);
                        }
                        emptyRow.style.display = '';
                    } else if (emptyRow) {
                        emptyRow.style.display = 'none';
                    }

                    renderPagination(filteredCount, totalPages);
                }

                searchInp?.addEventListener('input', () => { currentPage = 1; applyFilter(); });
                dateInp.addEventListener('change', () => { currentPage = 1; applyFilter(); });
                typeInp?.addEventListener('change', () => { currentPage = 1; applyFilter(); });
                statusInp?.addEventListener('change', () => { currentPage = 1; applyFilter(); });
                clearBtn.addEventListener('click', () => {
                    if (searchInp) searchInp.value = '';
                    dateInp.value = '';
                    if (typeInp) typeInp.value = '';
                    if (statusInp) statusInp.value = '';
                    currentPage = 1;
                    applyFilter();
                });

                applyFilter();
            })();
        </script>
    @endif

    {{-- ═══════════════════════════════════════
    SAVINGS TAB — scripts
    ═══════════════════════════════════════ --}}
    @if($activeTab === 'savings')
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
        </script>

        <script nonce="{{ csp_nonce() }}">
            function copyRef(elementId) {
                const text = document.getElementById(elementId).textContent.trim();
                navigator.clipboard.writeText(text).then(() => {
                    const btn = event.currentTarget;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                    setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1500);
                });
            }

            /* ═══ DEPOSIT RECEIPT OVERLAY ═══ */
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
                                                                                                                                                                                                        <div style="background-color:var(--teal, #0d9488);padding:2rem 1.5rem 1.2rem;text-align:center;">
                                                                                                                                                                                                            <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                            <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Request Submitted!</div>
                                                                                                                                                                                                            <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">Your deposit request is pending for approval.</div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                        <div style="padding:1.2rem 1.5rem;">
                                                                                                                                                                                                            ${svReceiptRow('Organization', 'KMPCATS')}
                                                                                                                                                                                                            ${svReceiptRow('Member', d.member)}
                                                                                                                                                                                                            ${svReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                                                                                                                                                                                                            ${svReceiptRow('Amount', '<strong style="color:var(--teal, #0d9488)">&#8369;' + d.amount + '</strong>')}
                                                                                                                                                                                                            ${svReceiptRow('Payment Method', d.method)}
                                                                                                                                                                                                            ${svReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                                                                                                                                                                                                            ${svReceiptRow('Date & Time', d.date)}
                                                                                                                                                                                                            ${svReceiptRow('Status', '<span style="color:#b8860b;font-weight:700;font-size:0.72rem;">&#9203; Pending Approval</span>')}
                                                                                                                                                                                                            <div style="text-align:center;margin-top:12px;color:#aaa;font-size:0.72rem;">KMPCATS Savings Receipt</div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    `;

                document.body.appendChild(wrapper);

                if (typeof html2canvas !== 'undefined') {
                    html2canvas(wrapper, { scale: 2, useCORS: true }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = `KMPCATS_Deposit_Receipt_${d.ref}.png`;
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

            /* ═══ WITHDRAWAL RECEIPT OVERLAY ═══ */
            function wvCloseModal() {
                const overlay = document.getElementById('wv-receipt-overlay');
                if (overlay) overlay.remove();
            }

            document.getElementById('wv-receipt-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) wvCloseModal();
            });

            function wvReceiptRow(label, value) {
                return `<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e8e8e8;font-size:0.84rem;">
                                                                                                                                                                                                        <span style="color:#888;">${label}</span>
                                                                                                                                                                                                        <span style="color:#1a1a1a;font-weight:700;">${value}</span>
                                                                                                                                                                                                    </div>`;
            }

            function wvDownloadReceipt() {
                const d = document.getElementById('wv-receipt-data')?.dataset;
                if (!d) return;

                const wrapper = document.createElement('div');
                wrapper.style.cssText = `
                                                                                                                                                                                                        position: fixed; left: -9999px; top: 0;
                                                                                                                                                                                                        width: 400px; background: #fff;
                                                                                                                                                                                                        border-radius: 20px; overflow: hidden;
                                                                                                                                                                                                        box-shadow: 0 8px 40px rgba(0,0,0,0.15);
                                                                                                                                                                                                    `;

                wrapper.innerHTML = `
                                                                                                                                                                                                        <div style="background-color:var(--teal, #0d9488);padding:2rem 1.5rem 1.2rem;text-align:center;">
                                                                                                                                                                                                            <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                                                                                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                            <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Request Submitted!</div>
                                                                                                                                                                                                            <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">Your withdrawal request is pending for approval.</div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                        <div style="padding:1.2rem 1.5rem;">
                                                                                                                                                                                                            ${wvReceiptRow('Organization', 'KMPCATS')}
                                                                                                                                                                                                            ${wvReceiptRow('Member', d.member)}
                                                                                                                                                                                                            ${wvReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                                                                                                                                                                                                            ${wvReceiptRow('Amount', '<strong style="color:var(--teal, #0d9488)">&#8369;' + d.amount + '</strong>')}
                                                                                                                                                                                                            ${wvReceiptRow('Payment Method', d.method)}
                                                                                                                                                                                                            ${wvReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                                                                                                                                                                                                            ${wvReceiptRow('Date & Time', d.date)}
                                                                                                                                                                                                            ${wvReceiptRow('Status', '<span style="color:#b8860b;font-weight:700;font-size:0.72rem;">&#9203; Pending Approval</span>')}
                                                                                                                                                                                                            <div style="text-align:center;margin-top:12px;color:#aaa;font-size:0.72rem;">KMPCATS Savings Receipt</div>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    `;

                document.body.appendChild(wrapper);

                if (typeof html2canvas !== 'undefined') {
                    html2canvas(wrapper, { scale: 2, useCORS: true }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = `KMPCATS_Withdrawal_Receipt_${d.ref}.png`;
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

            document.querySelectorAll('.parent-panel .panel-body').forEach(el => el.scrollTop = 0);
        </script>
    @endif

    {{-- ═══════════════════════════════════════════════════════
    ROW-RECEIPT SCRIPTS (both tabs) — click a table row to view
    ═══════════════════════════════════════════════════════ --}}
    @if($activeTab === 'share_capital' || $activeTab === 'savings')
        <script nonce="{{ csp_nonce() }}">
            function scRowReceiptRow(label, value) {
                return `<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e8e8e8;font-size:0.84rem;">
                                                                                                                                                                                                                <span style="color:#888;">${label}</span>
                                                                                                                                                                                                                <span style="color:#1a1a1a;font-weight:700;text-align:right;">${value}</span>
                                                                                                                                                                                                            </div>`;
            }

            function svRowReceiptRow(label, value) {
                return `<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e8e8e8;font-size:0.84rem;">
                                                                                                                                                                                                                <span style="color:#888;">${label}</span>
                                                                                                                                                                                                                <span style="color:#1a1a1a;font-weight:700;text-align:right;">${value}</span>
                                                                                                                                                                                                            </div>`;
            }

            function scStatusBadge(status) {
                const key = String(status || '').toLowerCase();
                if (key === 'posted' || key === 'completed' || key === 'approved') {
                    return '<span class="sc-status-badge completed" style="background:#e7f6ec;border:1px solid #b7e4c3;color:#1a7f37;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:700;"><span class="dot" style="width:7px;height:7px;background:#1a7f37;border-radius:50%;flex-shrink:0;"></span> Completed</span>';
                }
                if (key === 'pending' || key === 'awaiting') {
                    return '<span class="sc-status-badge" style="background:#fff8e1;border:1.5px solid #ffe082;color:#b8860b;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:700;"><span class="dot" style="width:7px;height:7px;background:#e6a817;border-radius:50%;flex-shrink:0;"></span> Pending</span>';
                }
                return '<span class="sc-status-badge" style="background:#fdecec;border:1.5px solid #f5c6c6;color:#c0392b;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:700;"><span class="dot" style="width:7px;height:7px;background:#c0392b;border-radius:50%;flex-shrink:0;"></span> ' + (status || 'Failed') + '</span>';
            }

            function svStatusBadge(status) {
                const key = String(status || '').toLowerCase();
                const pendingKeys = ['pending', 'awaiting', 'released', 'deducted'];
                const badKeys = ['rejected', 'failed', 'cancelled', 'voided'];
                if (key === 'completed' || key === 'approved' || key === 'credited') {
                    return '<span style="background:#e7f6ec;border:1px solid #b7e4c3;color:#1a7f37;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.76rem;font-weight:700;text-transform:capitalize;"><span style="width:7px;height:7px;background:#1a7f37;border-radius:50%;"></span> ' + status + '</span>';
                }
                if (pendingKeys.includes(key)) {
                    return '<span style="background:#fff8e1;border:1.5px solid #ffe082;color:#b8860b;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.76rem;font-weight:700;text-transform:capitalize;"><span style="width:7px;height:7px;background:#e6a817;border-radius:50%;"></span> ' + status + '</span>';
                }
                if (badKeys.includes(key)) {
                    return '<span style="background:#fdecec;border:1.5px solid #f5c6c6;color:#c0392b;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.76rem;font-weight:700;text-transform:capitalize;"><span style="width:7px;height:7px;background:#c0392b;border-radius:50%;"></span> ' + status + '</span>';
                }
                return '<span style="background:#eef2f6;border:1px solid #d5dee6;color:#556;display:inline-flex;align-items:center;gap:6px;padding:0.2rem 0.75rem;border-radius:20px;font-size:0.76rem;font-weight:700;text-transform:capitalize;">' + status + '</span>';
            }

            const VOID_LABELS = {
                wrong_amount: 'Wrong amount entered',
                duplicate_payment: 'Duplicate payment',
                fraudulent: 'Fraudulent / suspicious transaction',
                other_member: 'Sent by wrong member',
                technical_error: 'System / technical error',
                other: 'Other'
            };
            function getVoidLabel(key) {
                return VOID_LABELS[key] || key || 'No reason provided';
            }

            function handleTxnRowClick(e, row, which) {
                if (row.dataset.void === '1') {
                    const d = row.dataset;
                    // Returned transactions always display ₱0.00 — the funds were
                    // reversed, so nothing is owed regardless of the original amount.

                    document.getElementById('fin-void-reason-text').textContent = getVoidLabel(d.reason);

                    document.getElementById('fin-void-type-text').textContent = d.type || '—';
                    document.getElementById('fin-void-member-text').textContent = d.member || '—';
                    document.getElementById('fin-void-ref-text').textContent = d.ref || '—';
                    document.getElementById('fin-void-date-text').textContent = d.txdate || d.date || '—';
                    document.getElementById('fin-void-method-text').textContent = d.method || '—';
                    const sharesEl = document.getElementById('fin-void-shares-text');
                    if (d.shares) {
                        sharesEl.textContent = (d.shares !== '0' ? d.shares : '—') + ' shares';
                        sharesEl.parentElement.style.display = '';
                    } else {
                        sharesEl.textContent = '—';
                        sharesEl.parentElement.style.display = 'none';
                    }
                    document.getElementById('fin-void-overlay').classList.add('active');
                    return;
                }
                openReceipt(e, row, which);
            }

            function finCloseVoidModal() {
                const overlay = document.getElementById('fin-void-overlay');
                if (overlay) overlay.classList.remove('active');
            }
            document.getElementById('fin-void-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) finCloseVoidModal();
            });

            function openReceipt(e, row, which) {
                if (e && e.target && e.target.closest && e.target.closest('a')) return;
                if (row.dataset.void === '1') return;
                const d = row.dataset;
                if (which === 'sc') {
                    const overlay = document.getElementById('sc-row-receipt-overlay');
                    const dataEl = document.getElementById('sc-row-receipt-data');
                    if (!overlay) return;
                    document.getElementById('sc-row-receipt-member').textContent = d.member || '—';
                    document.getElementById('sc-row-receipt-type').textContent = d.type || '—';
                    const shares = d.shares && d.shares !== '0' ? d.shares + ' shares' : '— shares';
                    document.getElementById('sc-row-receipt-shares').textContent = shares;
                    document.getElementById('sc-row-receipt-amount').textContent = '₱' + d.amount;
                    document.getElementById('sc-row-receipt-method').textContent = d.method || '—';
                    document.getElementById('sc-row-receipt-ref').textContent = d.ref || '—';


                    const scRefRow = document.getElementById('sc-row-receipt-ref-row');
                    if (scRefRow) scRefRow.style.display = (d.type === 'Savings to Share Capital Conversion') ? 'none' : '';
                    document.getElementById('sc-row-receipt-date').textContent = d.txdate || d.date || '—';
                    document.getElementById('sc-row-receipt-status').innerHTML = scStatusBadge(d.status);
                    document.getElementById('sc-row-receipt-sub').textContent = 'Share capital transaction details.';
                    if (dataEl) {
                        dataEl.setAttribute('data-member', d.member || '');
                        dataEl.setAttribute('data-type', d.type || '');
                        dataEl.setAttribute('data-shares', shares);
                        dataEl.setAttribute('data-amount', d.amount || '');
                        dataEl.setAttribute('data-method', d.method || '');
                        dataEl.setAttribute('data-ref', d.ref || '');
                        dataEl.setAttribute('data-date', d.txdate || d.date || '');
                        dataEl.setAttribute('data-status', d.status || '');
                    }
                    overlay.classList.add('active');
                } else {
                    const overlay = document.getElementById('sv-row-receipt-overlay');
                    const dataEl = document.getElementById('sv-row-receipt-data');
                    if (!overlay) return;
                    document.getElementById('sv-row-receipt-member').textContent = d.member || '—';
                    document.getElementById('sv-row-receipt-type').textContent = d.type || '—';
                    document.getElementById('sv-row-receipt-amount').textContent = '₱' + d.amount;
                    document.getElementById('sv-row-receipt-method').textContent = d.method || '—';
                    document.getElementById('sv-row-receipt-ref').textContent = d.ref || '—';
                    const svRefRow = document.getElementById('sv-row-receipt-ref-row');
                    if (svRefRow) svRefRow.style.display = (d.type === 'Converted to Share Capital') ? 'none' : '';
                    document.getElementById('sv-row-receipt-date').textContent = d.txdate || d.date || '—';
                    document.getElementById('sv-row-receipt-status').innerHTML = svStatusBadge(d.status);
                    document.getElementById('sv-row-receipt-sub').textContent = 'Savings transaction details.';
                    if (dataEl) {
                        dataEl.setAttribute('data-member', d.member || '');
                        dataEl.setAttribute('data-type', d.type || '');
                        dataEl.setAttribute('data-amount', d.amount || '');
                        dataEl.setAttribute('data-method', d.method || '');
                        dataEl.setAttribute('data-ref', d.ref || '');
                        dataEl.setAttribute('data-date', d.txdate || d.date || '');
                        dataEl.setAttribute('data-status', d.status || '');
                    }
                    overlay.classList.add('active');
                }
            }

            function closeRowReceipt(which) {
                const overlay = document.getElementById(which + '-row-receipt-overlay');
                if (overlay) overlay.classList.remove('active');
            }

            document.getElementById('sc-row-receipt-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) closeRowReceipt('sc');
            });
            document.getElementById('sv-row-receipt-overlay')?.addEventListener('click', function (e) {
                if (e.target === this) closeRowReceipt('sv');
            });

            function scRowDownloadReceipt() {
                const d = document.getElementById('sc-row-receipt-data')?.dataset;
                if (!d || !d.ref) return;
                const statusBadge = scStatusBadge(d.status);
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'position:fixed;left:-9999px;top:0;width:400px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.15);';
                wrapper.innerHTML = `
                                                                                                                                                                                                                <div style="background:linear-gradient(135deg,#1a4a3a,#2d6a4f);padding:2rem 1.5rem 1.2rem;text-align:center;">
                                                                                                                                                                                                                    <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                                                                                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                    <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Transaction Receipt</div>
                                                                                                                                                                                                                    <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">KMPCATS Share Capital</div>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                <div style="padding:1.2rem 1.5rem;">
                                                                                                                                                                                                                    ${scRowReceiptRow('Organization', 'KMPCATS')}
                                                                                                                                                                                                                    ${scRowReceiptRow('Member', d.member)}
                                                                                                                                                                                                                    ${scRowReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                                                                                                                                                                                                                    ${scRowReceiptRow('Shares', '<strong>' + d.shares + '</strong>')}
                                                                                                                                                                                                                    ${scRowReceiptRow('Amount', '<strong style="color:#1a4a3a">&#8369;' + d.amount + '</strong>')}
                                                                                                                                                                                                                    ${scRowReceiptRow('Payment Method', d.method)}
                                                                                                                                                                                                                    ${d.type === 'Savings to Share Capital Conversion' ? '' : scRowReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                                                                                                                                                                                                                    ${scRowReceiptRow('Date & Time', d.date)}
                                                                                                                                                                                                                    ${scRowReceiptRow('Status', statusBadge)}
                                                                                                                                                                                                                    <div style="text-align:center;margin-top:12px;color:#aaa;font-size:0.72rem;">This is an official transaction receipt from KMPCATS.</div>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                            `;
                document.body.appendChild(wrapper);
                if (typeof html2canvas !== 'undefined') {
                    html2canvas(wrapper, { scale: 2, useCORS: true }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'KMPCATS_ShareCapital_Receipt_' + d.ref + '.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        wrapper.remove();
                    });
                } else {
                    wrapper.style.left = '0'; wrapper.style.top = '50%'; wrapper.style.transform = 'translateY(-50%)'; wrapper.style.zIndex = '999999';
                    alert('Screenshot library loading — press Ctrl+P to save as PDF, then close this receipt.');
                }
            }

            function svRowDownloadReceipt() {
                const d = document.getElementById('sv-row-receipt-data')?.dataset;
                if (!d || !d.ref) return;
                const statusBadge = svStatusBadge(d.status);
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'position:fixed;left:-9999px;top:0;width:400px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.15);';
                wrapper.innerHTML = `
                                                                                                                                                                                                                <div style="background-color:var(--teal,#0d9488);padding:2rem 1.5rem 1.2rem;text-align:center;">
                                                                                                                                                                                                                    <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                                                                                                                                                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                    <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Transaction Receipt</div>
                                                                                                                                                                                                                    <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">KMPCATS Savings</div>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                <div style="padding:1.2rem 1.5rem;">
                                                                                                                                                                                                                    ${svRowReceiptRow('Organization', 'KMPCATS')}
                                                                                                                                                                                                                    ${svRowReceiptRow('Member', d.member)}
                                                                                                                                                                                                                    ${svRowReceiptRow('Transaction Type', '<strong>' + d.type + '</strong>')}
                                                                                                                                                                                                                    ${svRowReceiptRow('Amount', '<strong style="color:var(--teal,#0d9488)">&#8369;' + d.amount + '</strong>')}
                                                                                                                                                                                                                    ${svRowReceiptRow('Payment Method', d.method)}
                                                                                                                                                                                                                    ${d.type === 'Converted to Share Capital' ? '' : svRowReceiptRow('Reference No.', '<span style="font-size:0.76rem;">' + d.ref + '</span>')}
                                                                                                                                                                                                                    ${svRowReceiptRow('Date & Time', d.date)}
                                                                                                                                                                                                                    ${svRowReceiptRow('Status', statusBadge)}
                                                                                                                                                                                                                    <div style="text-align:center;margin-top:12px;color:#aaa;font-size:0.72rem;">This is an official transaction receipt from KMPCATS.</div>
                                                                                                                                                                                                                </div>
                                                                                                                                                                                                            `;
                document.body.appendChild(wrapper);
                if (typeof html2canvas !== 'undefined') {
                    html2canvas(wrapper, { scale: 2, useCORS: true }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'KMPCATS_Savings_Receipt_' + d.ref + '.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        wrapper.remove();
                    });
                } else {
                    wrapper.style.left = '0'; wrapper.style.top = '50%'; wrapper.style.transform = 'translateY(-50%)'; wrapper.style.zIndex = '999999';
                    alert('Screenshot library loading — press Ctrl+P to save as PDF, then close this receipt.');
                }
            }
        </script>
    @endif

</body>

</html>