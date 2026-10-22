<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Share Capital</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- css links --}}
    <link rel="stylesheet" href="css_folder/share_capital.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style>
        /* ── Receipt Modal Overlay ── */
        #sc-receipt-overlay {
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

        #sc-receipt-overlay.active {
            display: flex;
        }

        #sc-receipt-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: scModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes scModalIn {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .sc-receipt-header {
            /* background: linear-gradient(135deg, #1a4a3a 0%, #2d6a4f 100%); */
            background-color: #ffffff;
            padding: 1.5rem;
            text-align: center;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid var(--line);
        }

        .sc-receipt-header .check-circle {
            width: 60px;
            height: 60px;
            /* background: rgba(255, 255, 255, 0.15); */
            background-color: var(--teal);
            /* border: 3px solid rgba(255, 255, 255, 0.6); */
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
            /* color: #fff; */
            color: #1a1a1a;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .sc-receipt-header p {
            /* color: rgba(255, 255, 255, 0.75); */
            color: var(--muted);
            font-size: 0.82rem;
            margin: 0;
        }

        /* .sc-receipt-tear {
            background: linear-gradient(135deg, #1a4a3a 0%, #2d6a4f 100%);
            height: 20px;
            position: relative;
            margin-bottom: -1px;
        } */

        /* .sc-receipt-tear::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: #fff;
            clip-path: polygon(0% 100%, 2.5% 0%, 5% 100%, 7.5% 0%, 10% 100%,
                    12.5% 0%, 15% 100%, 17.5% 0%, 20% 100%,
                    22.5% 0%, 25% 100%, 27.5% 0%, 30% 100%,
                    32.5% 0%, 35% 100%, 37.5% 0%, 40% 100%,
                    42.5% 0%, 45% 100%, 47.5% 0%, 50% 100%,
                    52.5% 0%, 55% 100%, 57.5% 0%, 60% 100%,
                    62.5% 0%, 65% 100%, 67.5% 0%, 70% 100%,
                    72.5% 0%, 75% 100%, 77.5% 0%, 80% 100%,
                    82.5% 0%, 85% 100%, 87.5% 0%, 90% 100%,
                    92.5% 0%, 95% 100%, 97.5% 0%, 100% 100%);
        } */

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
            /* color: #1a4a3a; */
            color: #1a1a1a;
            font-size: 13.6px;
        }

        .sc-ref-badge {
            background: #f4f4f4;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            letter-spacing: 0.5px;
            color: #333;
        }

        .sc-status-badge {
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

        .sc-status-badge.completed {
            background: #e8f5e9;
            border-color: #a5d6a7;
            color: #2e7d32;
        }

        .sc-status-badge .dot {
            width: 7px;
            height: 7px;
            background: #e6a817;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .sc-status-badge.completed .dot {
            background: #2e7d32;
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
            /* background: linear-gradient(135deg, #1a4a3a, #2d6a4f); */
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

        .sc-inline-error {
            display: none;
            align-items: flex-start;
            gap: 8px;
            background: #fef2f2;
            border: 1.5px solid #fca5a5;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            font-size: 12px;
            color: #b91c1c;
            margin-bottom: 0.85rem;
            line-height: 1.5;
        }

        .sc-inline-error.show {
            display: flex;
        }

        .sc-inline-error i {
            margin-top: 1px;
            flex-shrink: 0;
        }

        .tx-voided-row { background: #fef2f2; }
        .tx-voided-row:hover { background: #fde8e8; }
        .sc-status-pill.voided {
            background: #fdecec;
            border: 1.5px solid #f5c6c6;
            color: #c0392b;
        }

        /* ═══ VOID REASON OVERLAY ═══ */
        #sc-void-overlay {
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
        #sc-void-overlay.active { display: flex; }
        #sc-void-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            margin: auto;
            animation: scModalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }
        .sc-void-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--line);
        }
        .sc-void-header .sc-void-circle {
            width: 60px;
            height: 60px;
            background-color: #c0392b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }
        .sc-void-header .sc-void-circle i { color: #fff; font-size: 26px; }
        .sc-void-header h2 { color: #1a1a1a; font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem; }
        .sc-void-header p { color: var(--muted); font-size: 0.82rem; margin: 0; }
        .sc-void-body {
            padding: 1.5rem;
            text-align: center;
        }
        .sc-void-body .sc-void-label {
            font-size: 0.78rem;
            color: #888;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .sc-void-body .sc-void-value {
            font-size: 1rem;
            font-weight: 700;
            color: #c0392b;
            background: #fdecec;
            border: 1px solid #f5c6c6;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .sc-void-footer {
            padding: 0 1.5rem 1.5rem;
        }
        .sc-btn-void-close {
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
        .sc-btn-void-close:hover { background: #f5f5f5; color: #333; }
    </style>
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")

        {{-- ═══════════════════════════════════════
        RECEIPT MODAL (shown after success)
        ═══════════════════════════════════════ --}}
        @if(session('success'))
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

        {{-- ============================================================
        VOID REASON OVERLAY — Share Capital
        ============================================================ --}}
        <div id="sc-void-overlay">
            <div id="sc-void-modal">
                <div class="sc-void-header">
                    <div class="sc-void-circle"><i class="fa-solid fa-ban"></i></div>
                    <h2>Transaction Voided</h2>
                    <p>This transaction has been voided by the admin.</p>
                </div>
                <div class="sc-void-body">
                    <div class="sc-void-label">Reason</div>
                    <div class="sc-void-value" id="sc-void-reason-text"></div>
                </div>
                <div class="sc-void-footer">
                    <button class="sc-btn-void-close" data-action="scCloseVoidModal">Close</button>
                </div>
            </div>
        </div>

        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")

            <div class="main-sub-parent">
                <main>

                    {{-- ═══════════════════════════════════════
                    ADD / MANAGE SHARE CAPITAL MODAL
                    ═══════════════════════════════════════ --}}
                    <div class="modal fade" id="shareCapital" tabindex="-1" aria-labelledby="shareCapitalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content"
                                style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 24px 60px rgba(0,0,0,0.15);">

                                <div class="modal-header"
                                    style="background: #ffffff; border-bottom: 1px solid var(--line); padding: 1.25rem 1.5rem;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="width: 45px; height: 45px; background: var(--teal); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-coins" style="color: #ffffff; font-size: 16px;"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title mb-0" id="shareCapitalLabel"
                                                style="color: #1a1a1a; font-size: 15px; font-weight: 600;">Manage Share
                                                Capital</h5>
                                            <p style="margin: 0; color: var(--muted); font-size: 13.5px;">
                                                Purchase additional shares</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close btn-close-dark m-0" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body"
                                    style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">

                                    <form action="{{ route('share_capital.member.store') }}" method="POST" id="modal-sc-form"
                                        enctype="multipart/form-data">
                                        @csrf

                                        <div
                                            style="border-radius: 10px; padding: 0.8rem 1.1rem; display: flex; align-items: center; border: 1px solid rgba(0, 0, 0, 0.1); margin: 1.3rem 0 1.3rem 0; justify-content: space-between;">
                                            <span style="font-size: 0.78rem;; color: var(--muted);">Current
                                                Balance</span>
                                            <span style="font-size: 14.5px; font-weight: 600; color: #1a1a1a">
                                                ₱{{ number_format($currentBalance ?? 0, 0) }} ·
                                                {{ $currentShares ?? 0 }} shares
                                            </span>
                                        </div>

                                        <div class="sc-inline-error" id="modal-inline-error">
                                            <i class="fa fa-circle-exclamation"></i>
                                            <span id="modal-inline-error-text"></span>
                                        </div>

                                        <p style="margin: 0 0 8px; font-size: 13px; color: #666;">Number of shares</p>
                                        <div
                                            style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                            <button type="button" id="modal-dec"
                                                style="width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #ddd; background: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #333;">−</button>
                                            <input type="number" name="shares" id="modal-shares" value="1" min="1"
                                                readonly
                                                style="width: 60px; text-align: center; font-size: 14.5px; font-weight: 600; color: var(--teal); border: 1.5px solid #ddd; border-radius: 10px; padding: 6px;">
                                            <button type="button" id="modal-inc"
                                                style="width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #ddd; background: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #333;">+</button>
                                        </div>

                                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
                                            <button type="button" class="modal-qbtn active" data-v="1"
                                                style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: var(--blue); color: #fff; border: 1.5px solid rgb(221, 221, 221);;">1
                                                share</button>
                                            <button type="button" class="modal-qbtn" data-v="6"
                                                style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">6.25
                                                shares</button>
                                            <button type="button" class="modal-qbtn" data-v="12"
                                                style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">12
                                                shares</button>
                                            <button type="button" class="modal-qbtn" data-v="25"
                                                style="padding: 5px 13px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; color: #808080; border: 1.5px solid #ddd;">25
                                                shares</button>
                                        </div>

                                        <p style="font-size: 12px; color: #888888; margin-bottom: 1.2rem;">
                                            Cost: <strong id="modal-cost" style="color: var(--teal);">₱200</strong> ·
                                            ₱200
                                            per Share (Par Value)
                                        </p>

                                        <!-- <hr style="border-color: #eee; margin: 0 0 1rem;"> -->

                                        <div style="margin-bottom: 17.6px;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">Type</label>
                                            <select name="type" class="form-select" id="modal-type" required
                                                style="    border-radius: 10px;border: 1.5px solid #e0e0e0;height: 46px;font-size: 14px;color: #333;">
                                                <option value="">Select type..</option>
                                                <option value="Deposit">Deposit</option>
                                                <option value="Withdrawal">Withdrawal</option>
                                            </select>
                                        </div>

                                        <div id="modal-withdrawal-notice"
                                            style="display:none; background:#fff8e1; border:1.5px solid #ffe082; border-radius:10px; padding:0.65rem 1rem; margin-bottom:0.9rem; font-size:12px; color:#856404; line-height:1.5;">
                                            <i class="fa fa-circle-info" style="margin-right:6px;"></i>
                                            Withdrawal requests are subject to admin approval. Your current share
                                            balance will <strong>not</strong> be reduced until the request is approved.
                                        </div>

                                        <div id="modal-full-withdrawal-warning"
                                            style="display:none; background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:0.65rem 1rem; margin-bottom:0.9rem; font-size:12px; color:#991b1b; line-height:1.5;">
                                            <i class="fa fa-circle-exclamation" style="margin-right:6px;"></i>
                                            <strong>Notice:</strong> Fully withdrawing your share capital is equivalent
                                            to resigning. This action will submit a resignation request and is subject
                                            to a 60-day holding period upon approval.
                                        </div>

                                        <div style="margin-bottom: 17.6px;">
                                            <label
                                                style="font-size: 12px; text-transform: uppercase; color: #888888; font-weight: 600; display: block; margin-bottom: 6px;">Payment
                                                Method</label>
                                            <select name="payment_method" class="form-select" id="modal-pay" required
                                                style="border-radius: 10px; border: 1.5px solid #e0e0e0; height: 46px; font-size: 14px; color: #333;">
                                                <option value="" disabled selected>Select payment method...</option>
                                                @foreach($paymentMethods as $pm)
                                                    <option value="{{ strtolower($pm->method_name) }}">{{ $pm->method_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="modal-gcash-box" style="display: none; margin-top: 0.8rem;">
                                            @if($gcashPaymentMethod && $gcashPaymentMethod->has_qr_code && $gcashPaymentMethod->qr_code_image_path)
                                                <div
                                                    style="background: linear-gradient(135deg, #f0f7ff 0%, #e8f4ff 100%); border: 1.5px solid #c2deff; border-radius: 12px; padding: 1rem 1.2rem; text-align: center;">
                                                    <p
                                                        style="margin: 0 0 10px; font-size: 14px; font-weight: 700; color: #0056b3;">
                                                        <i class="fa-solid fa-mobile-screen-button"></i> Scan to Pay via
                                                        GCash
                                                    </p>
                                                    <img src="{{ asset('storage/' . $gcashPaymentMethod->qr_code_image_path) }}"
                                                        alt="GCash QR Code"
                                                        style="width: 220px; height: 220px; max-width: 100%; object-fit: contain; border-radius: 10px; border: 1px solid #c2deff; background: #fff; padding: 12px; display: block; margin: 0 auto;">
                                                    <p style="margin: 10px 0 0; font-size: 11px; color: #5a8ac4;">
                                                        Scan this using your GCash app, then upload your payment screenshot
                                                        below.
                                                    </p>
                                                    <p style="margin: 6px 0 0; font-size: 11px;">
                                                        <a href="#"
                                                            class="js-open-qr-lightbox"
                                                            data-qr-src="{{ asset('storage/' . $gcashPaymentMethod->qr_code_image_path) }}"
                                                            style="color: #0056b3; font-weight: 600;">
                                                            <i class="fa fa-up-right-and-down-left-from-center"></i> View
                                                            full-size QR
                                                        </a>
                                                    </p>
                                                </div>
                                            @else
                                                <div
                                                    style="background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 12px; padding: 1rem 1.2rem;">
                                                    <p style="margin: 0; font-size: 13px; color: #856404;">
                                                        <i class="fa fa-triangle-exclamation"></i> No GCash QR code has been
                                                        set up yet. Please contact the admin.
                                                    </p>
                                                </div>
                                            @endif

                                            <div style="margin-top: 1rem;">
                                                <label
                                                    style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                    GCash Reference Number <span style="color: #e53e3e;">*</span>
                                                </label>
                                                <p id="modal-ref-used-msg"
                                                    style="display:none; margin:0 0 6px; color:#e53e3e; font-size:12px; font-weight:600;">
                                                    <i class="fa fa-circle-exclamation"></i> This reference number has already been used for a transaction.
                                                </p>
                                                <input type="text" name="gcash_reference_no" id="modal-gcash-ref-input"
                                                    maxlength="13" pattern="\d{13}" placeholder="e.g. 1234567890123"
                                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box; height: 46px;">
                                                <p style="margin: 4px 0 0; font-size: 11px; color: #888;">
                                                    Enter the 13-digit reference number from your GCash transaction.
                                                </p>
                                            </div>

                                            <div style="margin-top: 1rem;">
                                                <label
                                                    style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                    Upload Payment Screenshot <span
                                                        style="font-size: 11px; color: #bbb;">(GCash proof)</span>
                                                </label>
                                                <input type="file" name="gcash_proof" id="modal-gcash-proof-input"
                                                    accept="image/png,image/jpeg,image/jpg"
                                                    style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; box-sizing: border-box;"
                                                    class="form-control">
                                                <div id="modal-gcash-proof-preview"
                                                    style="display:none; margin-top:10px;">
                                                    <img id="modal-gcash-proof-preview-img"
                                                        style="width:100%; height:180px; object-fit:cover; border-radius:8px; border:1px solid #e0e0e0;">
                                                </div>
                                            </div>

                                            <div id="modal-deposit-fraud-warning" style="display: none; background: #fff3cd; border: 1.5px solid #ffe08a; border-radius: 10px; padding: 0.75rem 1rem; margin-top: 1rem;">
                                                <p style="margin: 0; font-size: 12px; color: #856404;">
                                                    <i class="fa fa-triangle-exclamation"></i>
                                                    Deposits via GCash are <strong>pending verification</strong>. Please enter the correct GCash reference number and attach a screenshot of your payment. Submitting false or fraudulent entries will result in account suspension.
                                                </p>
                                            </div>
                                        </div>

                                        <div style="margin-top: 17.6px;">
                                            <label
                                                style="font-size: 12px;text-transform: uppercase; font-weight: 600; color: #888888; display: block; margin-bottom: 6px;">
                                                Note <span style="font-size: 12px; color: #bbb;">(optional)</span>
                                            </label>
                                            <input type="text" name="note" id="modal-note"
                                                placeholder="e.g. Q2 CBU installment"
                                                style="width: 100%; padding: 8px 10px; border-radius: 10px; border: 1.5px solid #ddd; font-size: 14px; color: #333; box-sizing: border-box; height: 46px; ">
                                        </div>

                                    </form>



                                </div>

                                <div class="modal-footer"
                                    style="background: #f8f9fa; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 1rem 1.6rem;display: flex;justify-content: center;align-items: center; gap: 8px;">
                                    <button type="submit" form="modal-sc-form" id="modal-submit-btn"
                                        style="width: 100%; padding: 0.75rem; background: var(--teal); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <i class="fa fa-coins"></i> Confirm Transaction
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
                        PAGE ACTION BUTTONS
                        ═══════════════════════════════════════ --}}
                        <div class="parent-main">
                            <div class="parent-text">
                                <h3>Share Capital Overview</h3>
                                <p>Track your Capital Build-Up progress, dividend earnings, and contribution history</p>
                            </div>
                            <div class="parent-download">
                                <div class="share">
                                    <button data-bs-toggle="modal" data-bs-target="#shareCapital">
                                        <i class="fa fa-coins"></i>
                                        <span>Manage Share Capital</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                        STAT CARDS
                        ═══════════════════════════════════════ --}}
                        <div class="sc-stat-grid">

                            <div class="sc-stat-card" style="animation-delay:.05s">
                                <div class="sc-stat-header">
                                    <div class="sc-stat-label">Subscribed Capital</div>
                                    <div class="sc-stat-icon blue"><i class="fa fa-bullseye"></i></div>
                                </div>
                                <div class="sc-stat-value green">₱{{ number_format($targetAmount, 0) }}</div>
                                <div class="sc-stat-sub">Target · {{ $targetShares }} shares @ ₱{{ $parValue }} par
                                    value</div>
                            </div>

                            <div class="sc-stat-card" style="animation-delay:.08s">
                                <div class="sc-stat-header">
                                    <div class="sc-stat-label">Paid-up Capital</div>
                                    <div class="sc-stat-icon green"><i class="fa fa-peso-sign"></i></div>
                                </div>
                                <div class="sc-stat-value green">₱{{ number_format($currentBalance, 0) }}</div>
                                <div class="sc-stat-sub">{{ $currentShares }} shares actually contributed</div>
                                <div class="sc-progress-track">
                                    <div class="sc-progress-fill" style="width:{{ $paidUpPercent }}%;"></div>
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
                                    <div class="sc-stat-label">Share Certificate</div>
                                    <div class="sc-stat-icon {{ $certificateEligible ? 'gold' : 'locked' }}">
                                        <i class="fa {{ $certificateEligible ? 'fa-certificate' : 'fa-lock' }}"
                                            style="color:{{ $certificateEligible ? '#C9A84C' : '#999' }};"></i>
                                    </div>
                                </div>
                                <div class="sc-stat-value {{ $certificateEligible ? 'gold' : '' }}">
                                    {{ $certificateEligible ? 'Eligible' : 'Not Yet Eligible' }}
                                </div>
                                <div class="sc-stat-sub {{ $certificateEligible ? 'unlocked-note' : 'locked-note' }}">
                                    Unlocks automatically at ₱{{ number_format($targetAmount, 0) }} paid-up
                                </div>
                            </div>

                        </div>

                        {{-- ═══════════════════════════════════════
                        2-YEAR CAPITAL BUILD-UP JOURNEY
                        ═══════════════════════════════════════ --}}
                        <div class="sc-journey-card">
                            <div class="sc-journey-header">
                                <div>
                                    <p class="sc-journey-title">
                                        2-Year Capital Build-Up Journey ·
                                        <span class="accent">
                                            @if($nextDueSlot)
                                                {{ $nextDueSlot['sublabel'] }}, {{ $nextDueSlot['label'] }}
                                                {{ $nextDueSlot['status'] === 'due' ? 'due now' : 'upcoming' }}
                                            @else
                                                All installments complete
                                            @endif
                                        </span>
                                    </p>
                                    <p class="sc-journey-sub">Every member subscribes
                                        ₱{{ number_format($targetAmount, 0) }} in share capital, payable over 8 quarters
                                        (2 years). Miss a quarter and it simply shifts your certificate date — no
                                        penalty applies.</p>
                                </div>
                                <span class="sc-journey-target-chip"><i class="fa fa-bullseye"
                                        style="margin-right:5px;"></i>Target ₱{{ number_format($targetAmount, 0) }} by
                                    Q4 · Year 2</span>
                            </div>

                            <div class="sc-timeline-row">
                                @php
                                    $paidCount = collect($installmentTimeline)->where('status', 'paid')->count();
                                    $progressPct = round(($paidCount / max(1, count($installmentTimeline) - 1)) * 92);
                                @endphp
                                <div class="sc-timeline-progress" style="width: {{ $progressPct }}%;"></div>
                                @foreach($installmentTimeline as $slot)
                                    <div class="sc-tl-slot">
                                        <div class="sc-tl-dot {{ $slot['status'] }}"></div>
                                        <div class="sc-tl-year">{{ $slot['sublabel'] }}</div>
                                        <div class="sc-tl-q">{{ $slot['label'] }}</div>
                                        <div class="sc-tl-amount {{ $slot['status'] === 'due' ? 'due' : '' }}">
                                            @if($slot['status'] === 'paid')
                                                ₱{{ number_format($slot['amount'], 0) }} paid
                                            @elseif($slot['status'] === 'due')
                                                Due now
                                            @else
                                                Upcoming
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="sc-journey-footer">
                                <div class="sc-journey-eligibility">
                                    <div class="journey-icon">
                                        <i class="fa fa-award"></i>
                                    </div>
                                    <div>
                                        <strong>Share Certificate Eligibility</strong>
                                        Automatically issued once Paid-up Capital reaches
                                        ₱{{ number_format($targetAmount, 0) }}
                                        @if(!$certificateEligible && $nextDueSlot)
                                            — at your current pace, expected around
                                            {{ optional($nextDueSlot['date'])->format('M d, Y') }}.
                                        @endif
                                    </div>
                                </div>
                                <button type="button" class="sc-btn-pay-installment" data-bs-toggle="modal"
                                    data-bs-target="#shareCapital">
                                    <i class="fa fa-coins" style="margin-right:6px;"></i>
                                    @if($nextDueSlot)
                                        Pay {{ $nextDueSlot['label'] }} {{ $nextDueSlot['sublabel'] }} Installment
                                    @else
                                        Fully Subscribed
                                    @endif
                                </button>
                            </div>

                            @if($advancePayment > 0)
                                <div class="sc-advance-note">
                                    <i class="fa fa-circle-plus" style="margin-right:6px;"></i>
                                    <strong>₱{{ number_format($advancePayment, 0) }}</strong>
                                    in additional contributions beyond your 2-year plan window has been recorded and
                                    is included in your Paid-up Capital above.
                                </div>
                            @endif
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
                            <a href="{{ route('Financial', ['tab' => 'dividends']) }}"
                                style="font-size:13px; font-weight:600; color:var(--teal, #1E2A4A); white-space:nowrap;">
                                View full dividend history →
                            </a>
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
                                    multiplied by the board-approved dividend rate. The more shares you build up, the
                                    larger your ISC share.</div>
                                <div class="sc-split-est"><span>Est. ISC this
                                        cycle</span><strong>₱{{ number_format($iscAmount, 2) }}</strong></div>
                            </div>

                            <div class="sc-split-card patronage">
                                <div class="sc-split-tag">Usage-Based</div>
                                <div class="sc-split-title">Patronage Refund</div>
                                <div class="sc-split-pct">
                                    {{ number_format(\App\Http\Controllers\ShareCapital::PATRONAGE_SPLIT * 100, 0) }}%
                                </div>
                                <div class="sc-split-desc">of allocated annual surplus. Computed on <strong>how much you
                                        transacted with the cooperative</strong> — loan interest paid, service fees, and
                                    other patronage — regardless of how many shares you hold. Rewards active members.
                                </div>
                                <div class="sc-split-est"><span>Est. Patronage this
                                        cycle</span><strong>₱{{ number_format($patronageAmount, 2) }}</strong></div>
                            </div>

                            <div class="sc-split-card transparent">
                                <div class="sc-split-tag">Transparent Basis</div>
                                <div class="sc-split-pct">₱{{ number_format($projectedNextDividend, 0) }}</div>
                                <div class="sc-split-desc">Your Average Monthly Balance (AMB) — the running average of
                                    your paid-up capital across the 12-month period. This is the figure your ISC is
                                    computed from.</div>
                                <div class="sc-split-formula">
                                    ISC = AMB × <strong>{{ $dividendRate }}% p.a.</strong> × 60%<br>
                                    Patronage = Loan interest paid × allocation rate × 40%
                                </div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                        NOTICES
                        ═══════════════════════════════════════ --}}
                        <div class="sc-notice-grid">
                            <div class="sc-notice-box warn">
                                <div class="sc-notice-icon"><i class="fa fa-lock"></i></div>
                                <div>
                                    <strong class="title">Non-Withdrawable Fund<span class="tag">INVESTMENT /
                                            SOSYO</span></strong><br>
                                    Share capital is your <strong>investment (sosyo)</strong> in the cooperative, not a
                                    savings account. It cannot be withdrawn on demand — it earns dividends annually and
                                    builds your ownership stake instead of sitting as liquid, on-call funds.
                                </div>
                            </div>
                            <div class="sc-notice-box danger">
                                <div class="sc-notice-icon"><i class="fa fa-triangle-exclamation"></i></div>
                                <div>
                                    <strong class="title">Applied to Outstanding Loans on Resignation</strong><br>
                                    If you resign or your membership is terminated, your share capital balance will
                                    first be used to <strong>offset any outstanding loan balance</strong> before the
                                    remainder — if any — is refunded to you, per cooperative bylaws.
                                </div>
                            </div>
                        </div>

                        {{-- ═══════════════════════════════════════
                        CONTRIBUTION HISTORY
                        ═══════════════════════════════════════ --}}
                        <div class="contribution-parent">
                            <div class="contribution-text">
                                <h3>Contribution History</h3>
                                <p>View your contribution history breakdown</p>
                            </div>
                            <div class="contribution-header">
                                <div class="contribution-search">
                                    <div class="sc-search-box" style="">
                                        <i class="fa fa-search" style="color:#aaa; font-size:13px;"></i>
                                        <input type="text" id="sc-search-input"
                                            placeholder="Search reference no., type..." style="">
                                    </div>
                                </div>
                                <!-- <span style="font-size:12px; color:#999;">All deposits toward your
                                    ₱{{ number_format($targetAmount, 0) }} subscription goal</span> -->
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

                                <!-- @if($contributions->isNotEmpty())
                                    <div class="sc-filter-bar">
                                        <div class="sc-filter-group">
                                            <input type="date" id="sc-filter-date">
                                        </div>
                                        <div class="sc-filter-group">
                                            <select id="sc-filter-type">
                                                <option value="">All</option>
                                                <option value="Deposit">Deposit</option>
                                                <option value="Withdrawal">Withdrawal</option>
                                            </select>
                                        </div>
                                        <div class="sc-filter-group">
                                            <select id="sc-filter-status">
                                                <option value="">All</option>
                                                <option value="posted">Posted</option>
                                                <option value="pending">Pending</option>
                                                <option value="failed">Failed</option>
                                            </select>
                                        </div>
                                        <button type="button" id="sc-filter-clear">Clear filter</button>
                                    </div>
                                @endif -->
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
                                                @if(strtolower($row->status ?? '') === 'voided')
                                                    class="tx-voided-row" style="cursor:pointer;"
                                                    data-reason="{{ $row->void_reason }}"
                                                    data-action="showScVoidReason"
                                                    data-arg='["|el|"]'
                                                @endif
                                            >
                                                <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('M d, Y') }}
                                                </td>
                                                <td>{{ $row->type }}</td>
                                                <td>
                                                    <span
                                                        class="sc-channel-badge {{ $channelClass }}">{{ $channelLabel }}</span>
                                                    <span class="sc-ref-text">
                                                        {{ $isPending && empty($row->reference_no) ? 'Awaiting reference' : ('Ref# ' . ($row->reference_no ?? '—')) }}
                                                    </span>
                                                </td>
                                                <td>{{ $row->shares ? number_format((float) $row->shares, 2) . ' shares' : '— shares' }}
                                                </td>
                                                <td>₱{{ number_format($row->total_amount, 2) }}</td>
                                                <td>
                                                    @if($row->status === 'Completed' || $row->status === null)
                                                        <span class="sc-status-pill posted">Completed</span>
                                                    @elseif($isPending)
                                                        <span class="sc-status-pill pending">Pending</span>
                                                    @elseif(strtolower($row->status ?? '') === 'voided')
                                                        <span class="sc-status-pill voided">Voided</span>
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
                                        contributions</span>
                                    <div class="sc-pagination" id="sc-pagination"></div>
                                </div>
                            @endif
                        </div>

                    </div>

                </main>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
        ERROR TOAST
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script nonce="{{ csp_nonce() }}">
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
        /* ══════════════════════════════════════
            MANAGE SHARE CAPITAL MODAL LOGIC
        ══════════════════════════════════════ */
        (function () {
            const PRICE = 200; // par value per share
            const CURRENT_BALANCE = {{ $currentBalance ?? 0 }};

            const inp = document.getElementById('modal-shares');
            const costEl = document.getElementById('modal-cost');
            const pay = document.getElementById('modal-pay');
            const typeEl = document.getElementById('modal-type');
            const gcashBox = document.getElementById('modal-gcash-box');
            const submitBtn = document.getElementById('modal-submit-btn');
            const withdrawalNotice = document.getElementById('modal-withdrawal-notice');
            const inlineError = document.getElementById('modal-inline-error');
            const inlineErrorText = document.getElementById('modal-inline-error-text');

            if (!inp) return;

            function showInlineError(msg) {
                inlineErrorText.textContent = msg;
                inlineError.classList.add('show');
                inlineError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function clearInlineError() {
                inlineError.classList.remove('show');
                inlineErrorText.textContent = '';
            }

            var fullWithdrawalWarning = document.getElementById('modal-full-withdrawal-warning');

            function toggleFullWithdrawalWarning(cost) {
                if (CURRENT_BALANCE > 0 && cost >= CURRENT_BALANCE) {
                    fullWithdrawalWarning.style.display = 'block';
                } else {
                    fullWithdrawalWarning.style.display = 'none';
                }
            }

            function validateWithdrawal(cost) {
                if (CURRENT_BALANCE <= 0) {
                    showInlineError('You cannot withdraw because your current balance is ₱0.');
                    return false;
                }
                if (cost > CURRENT_BALANCE) {
                    showInlineError(
                        'Withdrawal amount (₱' + cost.toLocaleString() +
                        ') exceeds your current balance (₱' + CURRENT_BALANCE.toLocaleString() + ').'
                    );
                    return false;
                }
                clearInlineError();
                toggleFullWithdrawalWarning(cost);
                return true;
            }

            function setShares(v) {
                if (v < 1) return;
                inp.value = v;
                costEl.textContent = '₱' + (v * PRICE).toLocaleString();
                document.querySelectorAll('.modal-qbtn').forEach(b => {
                    const on = parseInt(b.dataset.v) === v;
                    b.style.background = on ? 'var(--teal)' : '#ffffff';
                    b.style.color = on ? 'var(--bg)' : 'var(--muted)';
                    b.style.borderColor = on ? 'var(--line)' : 'var(--line)';
                });
                if (typeEl.value === 'Withdrawal') {
                    validateWithdrawal(v * PRICE);
                } else {
                    toggleFullWithdrawalWarning(0);
                }
            }

            document.getElementById('modal-dec').onclick = () => setShares(+inp.value - 1);
            document.getElementById('modal-inc').onclick = () => setShares(+inp.value + 1);
            document.querySelectorAll('.modal-qbtn').forEach(b => b.onclick = () => setShares(+b.dataset.v));

            typeEl.onchange = function () {
                clearInlineError();
                fullWithdrawalWarning.style.display = 'none';
                withdrawalNotice.style.display = (this.value === 'Withdrawal') ? 'block' : 'none';
                if (this.value === 'Withdrawal') {
                    validateWithdrawal(+inp.value * PRICE);
                }
                var isGcash = pay.value === 'gcash';
                var isDeposit = this.value === 'Deposit';
                var txGcashRefInput = document.getElementById('modal-gcash-ref-input');
                var depositFraudWarning = document.getElementById('modal-deposit-fraud-warning');
                if (txGcashRefInput) txGcashRefInput.required = isGcash && isDeposit;
                document.getElementById('modal-gcash-proof-input').required = isGcash && isDeposit;
                if (depositFraudWarning) depositFraudWarning.style.display = (isGcash && isDeposit) ? '' : 'none';
            };

            pay.onchange = function () {
                const isGcash = this.value === 'gcash';
                gcashBox.style.display = isGcash ? 'block' : 'none';
                var isDeposit = typeEl.value === 'Deposit';
                var txGcashRefInput = document.getElementById('modal-gcash-ref-input');
                var depositFraudWarning = document.getElementById('modal-deposit-fraud-warning');
                if (txGcashRefInput) txGcashRefInput.required = isGcash && isDeposit;
                document.getElementById('modal-gcash-proof-input').required = isGcash && isDeposit;
                if (depositFraudWarning) depositFraudWarning.style.display = (isGcash && isDeposit) ? '' : 'none';
                submitBtn.style.display = 'flex';
            };

            document.getElementById('modal-gcash-proof-input')?.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('modal-gcash-proof-preview-img').src = e.target.result;
                        document.getElementById('modal-gcash-proof-preview').style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            document.getElementById('shareCapital').addEventListener('show.bs.modal', () => {
                setShares(1);
                pay.value = '';
                typeEl.value = '';
                gcashBox.style.display = 'none';
                submitBtn.style.display = 'flex';
                setModalRefState(false);
                withdrawalNotice.style.display = 'none';
                fullWithdrawalWarning.style.display = 'none';
                document.getElementById('modal-note').value = '';
                clearInlineError();
            });

            let modalRefUsed = false;
            const modalRefInput = document.getElementById('modal-gcash-ref-input');
            const modalRefUsedMsg = document.getElementById('modal-ref-used-msg');

            function setModalRefState(used) {
                modalRefUsed = used;
                if (modalRefUsedMsg) modalRefUsedMsg.style.display = used ? 'block' : 'none';
                submitBtn.disabled = used;
                submitBtn.style.opacity = used ? 0.55 : 1;
                submitBtn.style.cursor = used ? 'not-allowed' : 'pointer';
            }

            let modalRefTimer = null;
            if (modalRefInput) {
                modalRefInput.addEventListener('input', function () {
                    clearTimeout(modalRefTimer);
                    const v = this.value.trim();
                    if (v.length !== 13 || !/^\d{13}$/.test(v)) {
                        setModalRefState(false);
                        return;
                    }
                    modalRefTimer = setTimeout(function () {
                        fetch('{{ route('reference.check') }}?ref=' + encodeURIComponent(v))
                            .then(function (r) { return r.json(); })
                            .then(function (d) { setModalRefState(!!d.used); })
                            .catch(function () { setModalRefState(false); });
                    }, 300);
                });
            }

            document.getElementById('modal-sc-form').addEventListener('submit', function (e) {
                if (modalRefUsed) {
                    e.preventDefault();
                    if (modalRefUsedMsg) modalRefUsedMsg.style.display = 'block';
                    return;
                }
                if (typeEl.value === 'Withdrawal') {
                    if (!validateWithdrawal(+inp.value * PRICE)) {
                        e.preventDefault();
                    }
                }
            });

        })();

        /* ══════════════════════════════════════
            RECEIPT MODAL
        ══════════════════════════════════════ */
        function scCloseModal() {
            const overlay = document.getElementById('sc-receipt-overlay');
            if (overlay) overlay.remove();
        }

        document.getElementById('sc-receipt-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) scCloseModal();
        });

        /* ══════════════════════════════════════
            RECEIPT DOWNLOAD (html2canvas)
        ══════════════════════════════════════ */
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
                ? (isCompleted ? 'Your deposit has been recorded successfully.' : 'Your deposit request is pending for approval.')
                : (isCompleted ? 'Your withdrawal has been processed successfully.' : 'Your withdrawal request is pending for approval.')}
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

        /* ══════════════════════════════════════
    VOID REASON MODAL
══════════════════════════════════════ */
        const VOID_LABELS = {
            wrong_amount: 'Wrong amount entered',
            duplicate_payment: 'Duplicate payment',
            fraudulent: 'Fraudulent / suspicious transaction',
            other_member: 'Sent by wrong member',
            technical_error: 'System / technical error',
            other: 'Other'
        };
        function getVoidLabel(key) { return VOID_LABELS[key] || key || 'No reason provided'; }

        function showScVoidReason(el) {
            var reason = getVoidLabel(el.dataset.reason);
            document.getElementById('sc-void-reason-text').textContent = reason;
            document.getElementById('sc-void-overlay').classList.add('active');
        }
        function scCloseVoidModal() {
            document.getElementById('sc-void-overlay').classList.remove('active');
        }
        document.getElementById('sc-void-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) scCloseVoidModal();
        });

        /* ══════════════════════════════════════
    CONTRIBUTION HISTORY — SEARCH + FILTERS + PAGINATION
══════════════════════════════════════ */
        (function () {
            const searchInp = document.getElementById('sc-search-input');
            const dateInp = document.getElementById('sc-filter-date');
            const typeInp = document.getElementById('sc-filter-type');
            const statusInp = document.getElementById('sc-filter-status');
            const clearBtn = document.getElementById('sc-filter-clear');
            if (!dateInp) return; // no filter bar rendered (empty state)

            const tbody = document.getElementById('sc-contribution-tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-date]'));
            const footerCount = document.getElementById('sc-table-footer-count');
            const pagination = document.getElementById('sc-pagination');
            const total = rows.length;
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
                // (removed: if (totalPages <= 1) return;)

                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'sc-page-btn nav';
                prevBtn.innerHTML = '<i class="fa fa-chevron-left"></i>';
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => { currentPage--; applyFilter(); });
                pagination.appendChild(prevBtn);

                for (let p = 1; p <= totalPages; p++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'sc-page-btn' + (p === currentPage ? ' active' : '');
                    btn.textContent = p;
                    btn.addEventListener('click', () => { currentPage = p; applyFilter(); });
                    pagination.appendChild(btn);
                }

                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'sc-page-btn nav';
                nextBtn.innerHTML = '<i class="fa fa-chevron-right"></i>';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => { currentPage++; applyFilter(); });
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
                    footerCount.textContent = `Showing ${Math.max(shownCount, 0)} of ${filteredCount} contributions`;
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
</body>

</html>