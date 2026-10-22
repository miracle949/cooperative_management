<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Share Capital</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css_folder/share_capital_form.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('font-awesome-icon/css/all.min.css') }}">

    <style>
        /* ── Success Modal Overlay ── */
        #success-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }

        #success-modal-overlay.active {
            display: flex;
        }

        /* ── Modal Card ── */
        #success-modal {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            overflow: visible;
            margin: auto;
            animation: modalIn 0.35s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(28px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Modal Header (receipt-style) ── */
        .modal-receipt-header {
            background: linear-gradient(135deg, #1a4a3a 0%, #2d6a4f 100%);
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            border-radius: 20px 20px 0 0;
        }

        .modal-receipt-header .check-circle {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border: 3px solid rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
        }

        .modal-receipt-header .check-circle i {
            color: #fff;
            font-size: 26px;
        }

        .modal-receipt-header h2 {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
        }

        .modal-receipt-header p {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.82rem;
            margin: 0;
        }

        /* Zigzag tear effect */
        .modal-tear {
            background: linear-gradient(135deg, #1a4a3a 0%, #2d6a4f 100%);
            height: 20px;
            position: relative;
            margin-bottom: -1px;
        }

        .modal-tear::after {
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
        }

        /* ── Modal Body ── */
        .modal-receipt-body {
            padding: 1.5rem;
        }

        .receipt-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #e8e8e8;
            font-size: 0.85rem;
        }

        .receipt-row:last-child {
            border-bottom: none;
        }

        .receipt-row .label {
            color: #888;
            font-weight: 500;
        }

        .receipt-row .value {
            color: #1a1a1a;
            font-weight: 700;
            text-align: right;
        }

        .receipt-row .value.highlight {
            color: #1a4a3a;
            font-size: 14.5px;
        }

        .ref-badge {
            background: #f4f4f4;
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            color: #333;
        }

        /* Status badge */
        .status-badge {
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

        .status-badge .dot {
            width: 7px;
            height: 7px;
            background: #e6a817;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Modal Footer ── */
        .modal-receipt-footer {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            border-radius: 0 0 20px 20px;
            background: #fff;
        }

        .btn-download-receipt {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #1a4a3a, #2d6a4f);
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

        .btn-download-receipt:hover {
            opacity: 0.88;
        }

        .btn-close-modal {
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

        .btn-close-modal:hover {
            background: #f5f5f5;
            color: #333;
        }

        /* ── Error Flash (kept for non-success errors) ── */
        .flash-error {
            background: #fef0f0;
            border: 1.5px solid #f5c6c6;
            border-radius: 12px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Hidden receipt canvas container ── */
        #receipt-canvas-container {
            display: none;
        }
    </style>
</head>

<body>

    {{-- ══════════════════════════════════════
    SUCCESS MODAL
    ══════════════════════════════════════ --}}
    @if(session('success'))
        <div id="success-modal-overlay" class="active">
            <div id="success-modal">

                {{-- Header --}}
                <div class="modal-receipt-header">
                    <div class="check-circle">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h2>Payment Successful!</h2>
                    <p>Your share capital request has been submitted.</p>
                </div>
                <div class="modal-tear"></div>

                {{-- Receipt Body --}}
                <div class="modal-receipt-body" id="receipt-printable">
                    <div class="receipt-row">
                        <span class="label">Organization</span>
                        <span class="value">KMPCATS</span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Member</span>
                        <span class="value">
                            {{ session('sc_receipt_member', Auth::user()->name ?? 'Member') }}
                        </span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Shares Added</span>
                        <span class="value highlight">
                            {{ session('sc_receipt_shares', '—') }} shares
                        </span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Total Amount</span>
                        <span class="value highlight">
                            ₱{{ number_format(session('sc_receipt_amount', 0), 0) }}
                        </span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Payment Method</span>
                        <span class="value">{{ session('sc_receipt_method', '—') }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Reference No.</span>
                        <span class="value">
                            <span class="ref-badge">{{ session('sc_receipt_ref', '—') }}</span>
                        </span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Date</span>
                        <span class="value">{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="status-badge">
                                <span class="dot"></span> Pending Approval
                            </span>
                        </span>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="modal-receipt-footer">
                    <button class="btn-download-receipt" data-action="downloadReceipt">
                        <i class="fa-solid fa-download"></i> Download Receipt
                    </button>
                    <button class="btn-close-modal" data-action="closeModal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- Hidden data for JS receipt generation --}}
    <div id="receipt-data" data-member="{{ session('sc_receipt_member', Auth::user()->name ?? 'Member') }}"
        data-shares="{{ session('sc_receipt_shares', '—') }}"
        data-amount="{{ number_format(session('sc_receipt_amount', 0), 0) }}"
        data-method="{{ session('sc_receipt_method', '—') }}" data-ref="{{ session('sc_receipt_ref', '—') }}"
        data-date="{{ now()->timezone('Asia/Manila')->format('M d, Y · h:i A') }}" style="display:none;">
    </div>

    <div class="container-fluid">
        <nav>
            <div class="nav-logo">
                <img src="../images/logo2.png" alt="">
                <div class="nav-text">
                    <h2 class="fw-bold">Membership Share Capital Form</h2>
                    <p>Ready to become part of something special? We can't wait to welcome you.</p>
                </div>
            </div>
        </nav>

        <main>
            <div class="card-share-container">
                <div class="card-share-header">
                    <div class="card-logo">
                        <i class="fa fa-coins"></i>
                    </div>
                    <p>KMPCATS</p>
</div> 

                @if(($currentShares ?? 0) >= 10)
                <div class="card-share-sub-header">
                    <h2>My Share Capital</h2>
                    <p>Your share capital account summary</p>
                </div>
                @else
                <div class="card-share-sub-header">
                    <h2>Share Capital Request Form</h2>
                    <p>You're almost there! To fully activate your account, please complete your share capital
                        contribution.</p>
                </div>
                @endif

                <hr>

                {{-- Error Alert only (success is now a modal) --}}
                @if(session('error'))
                    <div class="flash-error">
                        <i class="fa-solid fa-circle-xmark" style="color: #e03131; font-size: 16px;"></i>
                        <p style="margin: 0; font-size: 14px; color: #e03131; font-weight: 600;">{{ session('error') }}</p>
                    </div>
                @endif

                {{-- CASH form --}}
                @if(($currentShares ?? 0) >= 10)

                    {{-- ── Already has share capital: show summary ── --}}
                    <div style="text-align:center; padding: 1.5rem 1rem 0.5rem; share-capital-success">

                        <div
                            style="width:70px;height:70px;background:linear-gradient(135deg,#e8f5ee,#d0ede0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                            <i class="fa-solid fa-circle-check" style="color:#1a4a3a;font-size:2rem;"></i>
                        </div>

                        <h3 style="font-size:1.1rem;font-weight:700;color:#1a1a1a;margin:0 0 0.4rem;">Share Capital Active</h3>
                        <p style="font-size:0.85rem;color:#888;margin:0 0 1.5rem;line-height:1.5;">
                            Your share capital contribution is complete.<br>
                            You can now login to access your account.
                        </p>

                        <div
                            style="background:#f9f9f9;border:1.5px solid #e8e8e8;border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.5rem;text-align:left;">
                            <div
                                style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;border-bottom:1px dashed #ececec;font-size:0.84rem;">
                                <span style="color:#888;font-weight:500;">Total Shares</span>
                                <span style="color:#1a4a3a;font-weight:700;">{{ $currentShares }} shares</span>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;align-items:center;padding:0.4rem 0;font-size:0.84rem;">
                                <span style="color:#888;font-weight:500;">Total Balance</span>
                                <span style="color:#1a4a3a;font-weight:700;">₱{{ number_format($currentBalance, 0) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('LoginPage') }}"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:0.85rem;background:linear-gradient(135deg,#1a4a3a,#2d6a4f);color:#fff;border:none;border-radius:12px;font-size:0.95rem;font-weight:700;text-decoration:none;box-sizing:border-box;margin-bottom:1.5rem;">
                            <i class="fa-solid fa-right-to-bracket"></i> Go to Login
                        </a>

                    </div>

                @else

                    <form action="{{ route('share_capital.member.store') }}" method="POST" id="cash-form">
                        @csrf

                        <div class="card-share-body">
                            <div class="balance-card">
                                <span>Current Balance</span>
                                <p>₱{{ number_format($currentBalance ?? 0, 0) }} · {{ $currentShares ?? 0 }} shares</p>
                            </div>

                            <p class="number">Number of Shares to Add</p>

                            <div class="shares-counter">
                                <button type="button" class="counter-btn" id="decreaseBtn">−</button>
                                <input type="number" name="shares" id="sharesInput" class="shares-input" value="1" min="1"
                                    readonly>
                                <button type="button" class="counter-btn" id="increaseBtn">+</button>
                            </div>

                            <div class="quick-select">
                                <button type="button" class="quick-btn active" data-value="1">1 share</button>
                                <button type="button" class="quick-btn" data-value="5">5 shares</button>
                                <button type="button" class="quick-btn" data-value="10">10 shares</button>
                                <button type="button" class="quick-btn" data-value="25">25 shares</button>
                            </div>

                            <p class="cost-display">Cost: <strong id="totalCost">₱1,000</strong> · ₱1,000 per share</p>

                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <div class="select-wrapper">
                                    <select class="form-select" disabled>
                                        <option value="Subscription" selected>Subscription</option>
                                    </select>
                                    <i class="fa fa-chevron-down select-arrow"></i>
                                </div>
                                <input type="hidden" name="type" value="Subscription">
                            </div>

                            <div class="form-group">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <div class="select-wrapper">
                                    <select name="payment_method" id="paymentMethod" class="form-select" required>
                                        <option value="" disabled selected>Select payment method...</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                    <i class="fa fa-chevron-down select-arrow"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="noteInput" class="form-label">
                                    Note <span class="optional-tag">(optional)</span>
                                </label>
                                <input type="text" name="note" id="noteInput" class="form-input"
                                    placeholder="e.g. Monthly contribution">
                            </div>

                        </div>

                        <div class="card-share-footer">
                            <button type="submit" id="confirm-btn" class="btn-confirm">
                                <i class="fa fa-coins"></i> Add Share Capital
                            </button>
                        </div>

                    </form>

                @endif

            </div>
        </main>
    </div>

    {{-- html2canvas CDN for receipt download --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script nonce="{{ csp_nonce() }}">
        /* ── Form Logic (only runs when form is present) ── */
        const sharesInput = document.getElementById('sharesInput');
        const totalCostEl = document.getElementById('totalCost');
        const PRICE_PER_SHARE = 1000;

        if (sharesInput) {
            function updateCost() {
                const shares = parseInt(sharesInput.value) || 1;
                totalCostEl.textContent = '₱' + (shares * PRICE_PER_SHARE).toLocaleString();
            }

            function setShares(value) {
                if (value < 1) return;
                sharesInput.value = value;
                updateCost();
                document.querySelectorAll('.quick-btn').forEach(btn => {
                    btn.classList.toggle('active', parseInt(btn.dataset.value) === value);
                });
            }

            document.getElementById('decreaseBtn').addEventListener('click', () => {
                setShares((parseInt(sharesInput.value) || 1) - 1);
            });
            document.getElementById('increaseBtn').addEventListener('click', () => {
                setShares((parseInt(sharesInput.value) || 1) + 1);
            });
            document.querySelectorAll('.quick-btn').forEach(btn => {
                btn.addEventListener('click', () => setShares(parseInt(btn.dataset.value)));
            });
        }

        /* ── Modal Logic ── */
        function closeModal() {
            // Redirect to same page — clears the session flash and shows the correct state cleanly
            window.location.href = window.location.pathname;
        }

        // Close on overlay click
        document.getElementById('success-modal-overlay')?.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        /* ── Download Receipt as Image ── */
        function downloadReceipt() {
            const d = document.getElementById('receipt-data').dataset;

            // Build a clean off-screen receipt card
            const wrapper = document.createElement('div');
            wrapper.style.cssText = `
                position: fixed; left: -9999px; top: 0;
                width: 400px; background: #fff;
                overflow: hidden;
                box-shadow: 0 8px 40px rgba(0,0,0,0.15);
            `;

            wrapper.innerHTML = `
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #1a4a3a, #2d6a4f); padding: 2rem 1.5rem 1.2rem; text-align: center;">
                    <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div style="color:#fff;font-size:1.2rem;font-weight:800;margin-bottom:4px;">Payment Successful!</div>
                    <div style="color:rgba(255,255,255,0.75);font-size:0.8rem;">Your share capital request has been submitted.</div>
                </div>

                <!-- Zigzag -->
                <div style="height:16px;background:linear-gradient(135deg,#1a4a3a,#2d6a4f);position:relative;">
                    <svg viewBox="0 0 400 16" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:16px;">
                        <polygon fill="#ffffff" points="0,16 10,0 20,16 30,0 40,16 50,0 60,16 70,0 80,16 90,0 100,16 110,0 120,16 130,0 140,16 150,0 160,16 170,0 180,16 190,0 200,16 210,0 220,16 230,0 240,16 250,0 260,16 270,0 280,16 290,0 300,16 310,0 320,16 330,0 340,16 350,0 360,16 370,0 380,16 390,0 400,16"/>
                    </svg>
                </div>

                <!-- Body -->
                <div style="padding: 1.2rem 1.5rem;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                        ${receiptRow('Organization', 'KMPCATS')}
                        ${receiptRow('Member', d.member)}
                        ${receiptRow('Shares Added', `<strong style="color:#1a4a3a">${d.shares} shares</strong>`)}
                        ${receiptRow('Total Amount', `<strong style="color:#1a4a3a">&#8369;${d.amount}</strong>`)}
                        ${receiptRow('Payment Method', d.method)}
                        ${receiptRow('Reference No.', `<span style="padding:2px 0px;border-radius:5px;font-size:0.76rem;">${d.ref}</span>`)}
                        ${receiptRow('Date & Time', d.date)}
                        ${receiptRow('Status', `<span style="color:#b8860b;border-radius:20px;padding:3px 0px;font-size:0.72rem;font-weight:700;">• Pending Approval</span>`)}
                    </table>
                </div>

                <!-- Watermark footer -->
                <div style="padding: 0.8rem 1.5rem 1.2rem; text-align:center; border-top: 1px dashed #e8e8e8;">
                    <div style="color:#aaa; font-size:0.72rem;">This is an official transaction receipt from KMPCATS.</div>
                    <div style="color:#bbb; font-size:0.68rem; margin-top:2px;">Keep this for your records.</div>
                </div>
            `;

            document.body.appendChild(wrapper);

            html2canvas(wrapper, {
                scale: 2,
                useCORS: true,
                backgroundColor: null,
                borderRadius: 20,
            }).then(canvas => {
                document.body.removeChild(wrapper);
                const link = document.createElement('a');
                link.download = `KMPCATS-Receipt-${d.ref}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }

        function receiptRow(label, value) {
            return `
                <tr style="border-bottom:1px dashed #ebebeb;">
                    <td style="color:#888;font-weight:500;padding:0.55rem 0.5rem 0.55rem 0;vertical-align:middle;white-space:nowrap;">${label}</td>
                    <td style="color:#1a1a1a;font-weight:600;text-align:right;padding:0.55rem 0 0.55rem 0.5rem;vertical-align:middle;">${value}</td>
                </tr>
            `;
        }
    </script>

</body>

</html>