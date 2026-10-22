<!-- Manage Share-capital Modal -->
<div id="scContributionModal" class="modal-overlay hidden">
    <div class="modal max-w-lg" style="border-radius: 16px;">
        <!-- Header -->
        <div class="modal-header"
            style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="coins" class="w-5 h-5" style="color: #fff;"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Manage Share-capital</h2>
                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Purchase or
                            withdraw shares</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["scContributionModal"]'
                    style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                </button>
            </div>
        </div>

        <div style="padding: 1.25rem;">
            <form id="adminShareCapitalForm" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="member_id" id="manage-share-member-select" class="select" style="width: 100%;"
                        required>
                        <option value="">Select member</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Balance Pill -->
                <div
                    style="background: #f8f9f8; border-radius: 10px; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; border: 1px dashed #1E2A4A;">
                    <span style="font-size: 13px; color: #666;">Current Shares</span>
                    <span id="currentSharesDisplay" style="font-size: 14px; font-weight: 700; color: #1E2A4A;">0 shares
                        · ₱0.00</span>
                </div>

                <!-- Full Withdrawal Warning (auto-resignation) -->
                <div id="adminFullWithdrawalWarning"
                    style="display:none; background:#fef2f2; border:1.5px solid #fecaca; border-radius:10px; padding:0.65rem 1rem; font-size:12px; color:#991b1b; line-height:1.5;">
                    <i data-lucide="alert-triangle" class="w-4 h-4" style="margin-right:6px;"></i>
                    <strong>Notice:</strong> Fully withdrawing this member's share capital is equivalent to resigning.
                    This will auto-submit a resignation request subject to the 60-day holding period.
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="shareTypeSelect" class="select" style="width: 100%;" required
                        data-action="sc-type-change">
                        <option value="">Select type...</option>
                        <option value="Deposit">Deposit</option>
                        <option value="Withdrawal">Withdrawal</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                    <input type="number" name="amount_input" id="adminAmountInput" value="{{ $perShareValue }}"
                        min="200" step="200"
                        style="width: 100%; text-align: center; font-size: 14px; font-weight: 600; color: #1E2A4A; border: 1px solid #ddd; border-radius: 8px; padding: 8px;"
                        data-action="updateFromAmount" data-trigger="input">
                    <p style="font-size: 12px; color: #888; margin: 4px 0 0;">Equivalent to <strong
                            id="adminSharesDisplay">1</strong> {{ Str::plural('share', 1) }} ·
                        ₱{{ number_format($perShareValue, 0) }}/share</p>
                </div>

                <!-- Calculated fields -->
                <input type="hidden" name="shares" id="adminSharesInput" value="1">
                <input type="hidden" name="amount" id="adminTotalAmount" value="{{ $perShareValue }}">

                <!-- Payment Method (deposit only) -->
                <div id="scPaymentField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <input type="hidden" name="payment_method" id="scPaymentMethod" value="cash">
                    <input type="text" value="Cash" readonly
                        style="width: 100%; background: #f3f4f6; cursor: default; border: 1px solid #ddd; border-radius: 8px; padding: 8px; color: #555;">
                </div>

                <!-- Mobile Number (withdrawal only, read-only) -->
                <div id="scMobileField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="tel" id="scMobileDisplay" class="input" readonly
                        placeholder="No mobile number on record"
                        style="width: 100%; background: #f3f4f6; cursor: default;">
                </div>

                <!-- QR Code Display -->
                <div id="scQrCodeDisplay" class="hidden">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-center">
                        <p class="text-xs text-gray-500 mb-2 font-medium">Scan QR Code to Pay</p>
                        <img id="scQrCodeImg" class="w-40 h-40 mx-auto rounded-lg object-cover border border-gray-200"
                            src="" alt="Payment QR Code">
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note <span
                            style="color: #999;">(optional)</span></label>
                    <textarea name="note" class="input" rows="2" placeholder="Add any notes..."
                        style="width: 100%;"></textarea>
                </div>
            </form>

            <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                <button data-action="submitAdminShareCapital"
                    style="width: 100%; padding: 0.7rem; background: #1E2A4A; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Transaction
                </button>
                <button data-action="closeModal" data-arg='["scContributionModal"]'
                    style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Share Capital Accounts Modal -->
<div id="shareCapitalsModal" class="modal-overlay hidden">
    <div class="modal max-w-3xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="coins" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 id="shareCapitalsModalTitle" class="text-xl font-bold text-gray-900">Share Capital Accounts
                        </h2>
                        <p id="shareCapitalsModalSubtitle" class="text-xs text-gray-500">All member accounts</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["shareCapitalsModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th class="text-right">Shares</th>
                            <th class="text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shareCapitalAccounts as $account)
                            <tr class="share-capital-row" data-shares="{{ $account->paid_up_shares }}"
                                data-amount="{{ $account->paid_up_amount }}">
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-xs text-primary-600 font-medium">
                                                {{ strtoupper(substr($account->user->first_name ?? 'U', 0, 1) . substr($account->user->last_name ?? '', 0, 1)) }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-900">{{ $account->user->first_name ?? 'Unknown' }}
                                            {{ $account->user->last_name ?? '' }}</span>
                                    </div>
                                </td>
                                <td class="text-right text-sm text-gray-900">
                                    {{ number_format($account->paid_up_shares, 2) }}</td>
                                <td class="text-right text-sm font-semibold text-gray-900">
                                    ₱{{ number_format($account->paid_up_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-12 text-gray-500">No share capital accounts found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button data-action="closeModal" data-arg='["shareCapitalsModal"]'
                class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
            <a href="{{ route('financial.activity', ['tab' => 'share-capitals']) }}"
                class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4"></i>
                View All Transactions
            </a>
        </div>
    </div>
</div>

<!-- Sell Shares Modal -->
<div id="sellSharesModal" class="modal-overlay hidden">
    <div class="modal max-w-lg" style="border-radius: 16px;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="arrow-left-right" class="w-5 h-5" style="color: #fff;"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Sell Shares</h2>
                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Transfer shares
                            between members</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["sellSharesModal"]'
                    style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                </button>
            </div>
        </div>

        <div style="padding: 1.25rem;">
            <form id="sellSharesForm">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seller (from)</label>
                    <select name="seller_id" id="sell-shares-seller-select" class="select" style="width: 100%;"
                        required>
                        <option value="">Select seller...</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div
                    style="background: #f8f9f8; border-radius: 10px; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; border: 1px dashed #1E2A4A; margin-bottom: 1rem;">
                    <span style="font-size: 13px; color: #666;">Seller's Current Shares</span>
                    <span id="sellerSharesDisplay" style="font-size: 14px; font-weight: 700; color: #1E2A4A;">Select a
                        seller</span>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyer (to)</label>
                    <select name="buyer_id" id="sell-shares-buyer-select" class="select" style="width: 100%;" required>
                        <option value="">Select buyer...</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                    <input type="number" name="amount_input" id="sellAmountInput"
                        placeholder="Enter amount (minimum ₱1,000)" min="1000" step="100"
                        style="width: 100%; text-align: center; font-size: 14px; font-weight: 600; color: #1E2A4A; border: 1px solid #ddd; border-radius: 8px; padding: 8px;"
                        data-action="updateSellSharesFromAmount" data-trigger="input" required>
                    <p style="font-size: 12px; color: #888; margin: 4px 0 0;">Equivalent to <strong
                            id="sellSharesDisplay">1</strong> {{ Str::plural('share', 1) }} ·
                        ₱{{ number_format($perShareValue, 0) }}/share</p>
                    <p style="font-size: 12px; color: #d32f2f; margin: 4px 0 0;">Minimum transfer amount is ₱1,000.</p>
                </div>

                <input type="hidden" name="shares" id="sellSharesInput">
                <input type="hidden" name="amount" id="sellTotalAmount">
            </form>

            <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                <button data-action="submitSellShares"
                    style="width: 100%; padding: 0.7rem; background: #1E2A4A; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i> Transfer Shares
                </button>
                <button data-action="closeModal" data-arg='["sellSharesModal"]'
                    style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    (function () {
        var A = window.CSP_actions;
        if (!A) return;
        A.register('sc-close-modal', function () {
            var m = document.getElementById('scTransactionModal');
            if (m && typeof m.remove === 'function') m.remove();
            document.body.style.overflow = 'auto';
        });
        A.register('sc-type-change', function () {
            toggleAdminFullWithdrawalWarning();
            toggleScPaymentFields();
            syncAdminAmountForType();
        });
    })();

    function openShareCapitalsModal(eligibleOnly) {
        const eligible = !!eligibleOnly;
        const title = document.getElementById('shareCapitalsModalTitle');
        const subtitle = document.getElementById('shareCapitalsModalSubtitle');
        if (title) title.textContent = eligible ? 'Eligible Accounts' : 'Contributions';
        if (subtitle) subtitle.textContent = eligible ? 'Accounts with 10+ paid-up shares' : 'All member share capital accounts';
        document.querySelectorAll('.share-capital-row').forEach(row => {
            const shares = parseFloat(row.dataset.shares) || 0;
            row.style.display = (eligible && shares < 10) ? 'none' : '';
        });
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        openModal('shareCapitalsModal');
    }

    // Share counter functionality
    document.getElementById('scContributionModal')?.addEventListener('transitionend', function () {
        if (!this.classList.contains('hidden')) {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    });

    // Show/hide full-withdrawal warning
    function toggleAdminFullWithdrawalWarning() {
        const type = document.getElementById('shareTypeSelect')?.value;
        const warning = document.getElementById('adminFullWithdrawalWarning');
        warning.style.display = (type === 'Withdrawal') ? 'block' : 'none';
    }

    // Auto-fill amount with the member's full share capital on Withdrawal; default on Deposit
    let currentMemberShareAmount = 0;
    function syncAdminAmountForType() {
        const type = document.getElementById('shareTypeSelect')?.value;
        const amountInput = document.getElementById('adminAmountInput');
        amountInput.value = (type === 'Withdrawal') ? currentMemberShareAmount : {{ $perShareValue }};
        updateFromAmount();
    }

    // Update shares display when amount changes
    function updateFromAmount() {
        const amount = parseFloat(document.getElementById('adminAmountInput')?.value) || 0;
        const perShare = {{ $perShareValue }};
        const shares = Math.round((amount / perShare) * 100) / 100;
        document.getElementById('adminSharesInput').value = shares;
        document.getElementById('adminTotalAmount').value = amount;
        document.getElementById('adminSharesDisplay').textContent = shares;
        toggleAdminFullWithdrawalWarning();
    }

    // Update member shares display
    window.updateMemberShares = function () {
        const memberId = document.getElementById('manage-share-member-select').value;
        const display = document.getElementById('currentSharesDisplay');
        const mobileEl = document.getElementById('scMobileDisplay');

        if (!memberId) {
            display.textContent = '0 shares · ₱0.00';
            currentMemberShareAmount = 0;
            if (mobileEl) mobileEl.value = '';
            toggleAdminFullWithdrawalWarning();
            return;
        }

        fetch('/sharecapital/member/' + memberId + '/balance')
            .then(response => response.json())
            .then(data => {
                const shares = data.total_shares || 0;
                const amount = data.total_amount || 0;
                currentMemberShareAmount = amount;
                display.textContent = shares + ' shares · ₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (mobileEl) mobileEl.value = data.contact_no || '';
                syncAdminAmountForType();
                toggleAdminFullWithdrawalWarning();
            })
            .catch(error => {
                console.error('Error:', error);
                display.textContent = '0 shares · ₱0.00';
                currentMemberShareAmount = 0;
                if (mobileEl) mobileEl.value = '';
                toggleAdminFullWithdrawalWarning();
            });
    };

    // Toggle between Payment Method (Deposit) and Mobile Number (Withdrawal)
    function toggleScPaymentFields() {
        const type = document.getElementById('shareTypeSelect').value;
        const paymentField = document.getElementById('scPaymentField');
        const mobileField = document.getElementById('scMobileField');
        const qrDisplay = document.getElementById('scQrCodeDisplay');
        if (!paymentField || !mobileField) return;

        if (type === 'Withdrawal') {
            paymentField.classList.add('hidden');
            mobileField.classList.remove('hidden');
            if (qrDisplay) qrDisplay.classList.add('hidden');
        } else if (type === 'Deposit') {
            paymentField.classList.remove('hidden');
            mobileField.classList.add('hidden');
        } else {
            paymentField.classList.remove('hidden');
            mobileField.classList.add('hidden');
            if (qrDisplay) qrDisplay.classList.add('hidden');
        }
    }

    // Submit admin share capital form
    window.submitAdminShareCapital = function () {
        const form = document.getElementById('adminShareCapitalForm');
        const formData = new FormData(form);

        if (!formData.get('member_id')) {
            showToast('Error', 'Please select a member');
            return;
        }
        if (!formData.get('amount_input') || parseFloat(formData.get('amount_input')) <= 0) {
            showToast('Error', 'Please enter a valid amount');
            return;
        }
        if (!formData.get('type')) {
            showToast('Error', 'Please select transaction type');
            return;
        }
        if (formData.get('type') === 'Deposit' && !formData.get('payment_method')) {
            showToast('Error', 'Please select payment method');
            return;
        }

        fetch('/sharecapital/admin/store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('scContributionModal');
                    showToast(data.warning ? 'Warning' : 'Success', data.message);
                    form.reset();
                    document.getElementById('currentSharesDisplay').textContent = '0 shares · ₱0.00';
                    document.getElementById('adminAmountInput').value = {{ $perShareValue }};
                    document.getElementById('adminTotalAmount').value = {{ $perShareValue }};
                    document.getElementById('adminSharesInput').value = 1;
                    document.getElementById('adminSharesDisplay').textContent = 1;
                    document.getElementById('scMobileDisplay').value = '';
                    toggleScPaymentFields();
                    toggleAdminFullWithdrawalWarning();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Error', data.message || 'Transaction failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
            });
    };

    // Transaction Detail Modal Functions
    let currentTransactionId = null;
    let currentSCReceipt = null;

    const SC_NAVY = '#1E2A4A';
    const SC_NAVY_LIGHT = '#eef1f8';

    function scRefBadge(ref) {
        return '<span style="display:inline-block; background:' + SC_NAVY_LIGHT + '; color:' + SC_NAVY + '; padding:0.2rem 0.6rem; border-radius:6px; letter-spacing:0.5px; font-family:monospace; font-size:0.78rem; font-weight:600;">' + ref + '</span>';
    }

    function scStatusBadge() {
        return '<span style="display:inline-flex; align-items:center; gap:6px; background:#d1fae5; border:1.5px solid #a7f3d0; color:#065f46; border-radius:20px; padding:0.2rem 0.75rem; font-size:0.75rem; font-weight:700;"><span style="width:7px; height:7px; background:#059669; border-radius:50%; flex-shrink:0;"></span> Completed</span>';
    }

    window.viewShareCapitalDetail = function (id, type, status, memberName, shares, amount, paymentMethod, referenceNo, transactionDate) {
        const existingModal = document.getElementById('scTransactionModal');
        if (existingModal) {
            existingModal.remove();
        }

        currentTransactionId = id;
        currentSCReceipt = { memberName: memberName, type: type, shares: shares, amount: amount, paymentMethod: paymentMethod, referenceNo: referenceNo, transactionDate: transactionDate };
        const isWithdrawal = type === 'Withdrawal';
        const isPending = status === 'Pending';
        const isCompleted = status === 'Completed';

        const modal = document.createElement('div');
        modal.id = 'scTransactionModal';
        modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';

        let bodyHtml = '';
        let actionsHtml = '';

        if (isCompleted) {
            const amountFmt = Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
            bodyHtml = scReceiptRows(memberName, type, shares, amountFmt, paymentMethod, referenceNo, transactionDate, isWithdrawal);
            actionsHtml = '<div style="padding:0 1.5rem 1.5rem; display:flex; flex-direction:column; gap:0.6rem;">'
                + '<button data-action="downloadSCReceipt" class="sc-rec-dl" style="width:100%; padding:0.8rem; background:' + SC_NAVY + '; color:#fff; border:none; border-radius:12px; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:opacity .2s;">'
                + '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>'
                + 'Download Receipt</button>'
                + '<button data-action="sc-close-modal" class="sc-ghost-close" style="width:100%; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>'
                + '</div>';
        } else {
            let badge = '';
            if (status === 'Approved') badge = '<span style="background:#dcfce7;color:#166534;padding:4px 8px;border-radius:4px;font-size:12px;">Approved</span>';
            else if (status === 'Pending') badge = '<span style="background:#fef3c7;color:#92400e;padding:4px 8px;border-radius:4px;font-size:12px;">Pending</span>';
            else if (status === 'Rejected') badge = '<span style="background:#fee2e2;color:#991b1b;padding:4px 8px;border-radius:4px;font-size:12px;">Rejected</span>';
            else badge = '<span style="background:#f3f4f6;color:#374151;padding:4px 8px;border-radius:4px;font-size:12px;">' + status + '</span>';

            bodyHtml = ''
                + '<div style="display:grid;gap:12px;">'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Member</span><span style="color:#111827;font-size:14px;font-weight:500;">' + memberName + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Type</span><span style="color:' + (isWithdrawal ? '#dc2626' : '#16a34a') + ';font-size:14px;font-weight:500;">' + type + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Shares</span><span style="color:#111827;font-size:14px;font-weight:500;">' + shares + ' shares</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Amount</span><span style="color:#111827;font-size:14px;font-weight:700;">₱' + parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Payment</span><span style="color:#111827;font-size:14px;">' + paymentMethod + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Reference</span><span style="color:#111827;font-size:14px;font-family:monospace;">' + referenceNo + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;font-size:14px;">Date</span><span style="color:#111827;font-size:14px;">' + transactionDate + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1px solid #e5e7eb;"><span style="color:#6b7280;font-size:14px;">Status</span>' + badge + '</div>'
                + '</div>';

            if (isWithdrawal && isPending) {
                actionsHtml = '<div style="display:flex;gap:12px;margin-top:24px;"><button data-action="processWithdrawalSC" data-arg=\'["accept"]\' style="flex:1;padding:10px;background:#16a34a;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500;">Accept</button><button data-action="processWithdrawalSC" data-arg=\'["reject"]\' style="flex:1;padding:10px;background:#dc2626;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:500;">Reject</button></div>';
            } else {
                actionsHtml = '<div style="margin-top:24px;"><button data-action="sc-close-modal" style="width:100%;padding:10px;background:#e5e7eb;color:#374151;border:none;border-radius:8px;cursor:pointer;font-weight:500;">Close</button></div>';
            }
        }

        modal.innerHTML = `
            <div style="background:#fff; border-radius:20px; width:100%; max-width:420px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:scRecIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                <style>
                    @keyframes scRecIn {
                        from { opacity: 0; transform: translateY(28px) scale(0.97); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .sc-rec-dl:hover { opacity:0.88; }
                    .sc-ghost-close:hover { background:#f5f5f5; color:#333; }
                </style>
                ${isCompleted ? ('<div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">'
                + '<div style="width:60px;height:60px;background:' + SC_NAVY + ';border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">'
                + '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'
                + '</div>'
                + '<h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">' + (isWithdrawal ? 'Withdrawal Successful!' : 'Deposit Successful!') + '</h2>'
                + '<p style="color:#6b7280; font-size:0.82rem; margin:0;">KMPCATS Cooperative -- Official Receipt</p>'
                + '</div>'
                + '<div style="padding:0.6rem 1.5rem 0;">' + bodyHtml + '</div>'
                + actionsHtml)
                : ('<div style="padding:1.5rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">'
                    + '<h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#1a1a1a;">Transaction Details</h3>'
                    + '<button data-action="sc-close-modal" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666; line-height:1;">&times;</button>'
                    + '</div>'
                    + '<div style="padding:1.25rem 1.5rem;">' + bodyHtml + actionsHtml + '</div>')}
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
    };

    function scReceiptRows(memberName, type, shares, amountFmt, paymentMethod, referenceNo, transactionDate, isWithdrawal) {
        return ''
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Organization</span><span style="color:#1a1a1a; font-weight:600; text-align:right;">KMPCATS</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Member</span><span style="color:#1a1a1a; font-weight:600; text-align:right; max-width:55%; word-break:break-word;">' + memberName + '</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Transaction Type</span><span style="color:#1a1a1a; font-weight:600; text-align:right;"><strong>' + type + '</strong></span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Shares</span><span style="color:#1a1a1a; font-weight:800; text-align:right;">' + shares + ' shares</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Amount</span><span style="color:' + SC_NAVY + '; font-weight:800; text-align:right; font-size:1rem;">&#8369;' + amountFmt + '</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Payment Method</span><span style="color:#1a1a1a; font-weight:600; text-align:right;">' + paymentMethod + '</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Reference No.</span><span style="text-align:right;">' + scRefBadge(referenceNo) + '</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Date & Time</span><span style="color:#1a1a1a; font-weight:600; text-align:right; max-width:55%; word-break:break-word;">' + transactionDate + '</span></div>'
            + '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; font-size:0.85rem;"><span style="color:#888; font-weight:500;">Status</span><span style="text-align:right;">' + scStatusBadge() + '</span></div>';
    }

    // Void reason display for share capital
    const SC_VOID_LABELS = {
        wrong_amount: 'Wrong amount entered',
        duplicate_payment: 'Duplicate payment',
        fraudulent: 'Fraudulent / suspicious transaction',
        other_member: 'Sent by wrong member',
        technical_error: 'System / technical error',
        other: 'Other'
    };
    function scGetVoidLabel(key) { return SC_VOID_LABELS[key] || key || 'No reason provided'; }

    window.showSCVoidReason = function (d) {
        d = d || {};
        const label = scGetVoidLabel(d.reason);
        const existingModal = document.getElementById('scVoidReasonModal');
        if (existingModal) { existingModal.remove(); }

        const amountFmt = Number(d.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const sharesFmt = (d.shares !== undefined && d.shares !== '' && d.shares !== '0')
            ? Number(d.shares || 0).toLocaleString('en-PH', { maximumFractionDigits: 0 }) + ' shares'
            : '— shares';
        const isWithdrawal = String(d.type || '').toLowerCase() === 'withdrawal';

        function row(label2, value) {
            return '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #f0e2e2; font-size:0.85rem;">'
                + '<span style="color:#888; font-weight:500; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.3px;">' + label2 + '</span>'
                + '<span style="color:#1a1a1a; font-weight:600; text-align:right; max-width:60%; word-break:break-word;">' + value + '</span>'
                + '</div>';
        }

        const modal = document.createElement('div');
        modal.id = 'scVoidReasonModal';
        modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';
        modal.innerHTML = `
            <div style="background:#fff; border-radius:20px; width:100%; max-width:400px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:scVoidIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                <style>
                    @keyframes scVoidIn {
                        from { opacity: 0; transform: translateY(28px) scale(0.97); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .sc-ghost-close:hover { background:#f5f5f5; color:#333; }
                </style>
                <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                    <div style="width:60px;height:60px;background:#c0392b;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                    </div>
                    <h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">$${isWithdrawal ? 'Withdrawal Returned' : 'Transaction Returned'}</h2>
                    <p style="color:#6b7280; font-size:0.82rem; margin:0;">This share capital transaction has been voided by the admin.</p>
                </div>
                <div style="padding:0.75rem 1.5rem 0;">
                    <div style="background:#fafafa; border-radius:12px; padding:0.25rem 1rem;">
                        ${row('Type', d.type || '—')}
                        ${row('Member', d.member || '—')}
                        ${row('Shares', sharesFmt)}
                        ${row('Reference No.', d.ref || '—')}
                        ${row('Date & Time', d.date || '—')}
                        ${row('Method', d.method || '—')}
                    </div>
                </div>
                <div style="padding:1.25rem 1.5rem;">
                    <div style="font-size:0.78rem; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.4rem;">Reason</div>
                    <div style="font-size:1rem; font-weight:700; color:#c0392b; background:#fdecec; border:1px solid #f5c6c6; border-radius:10px; padding:0.75rem 1rem;">${label}</div>
                </div>
                <div style="padding:0 1.5rem 1.5rem;">
                    <button data-action="remove-element" data-target="scVoidReasonModal" class="sc-ghost-close" style="width:100%; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    };

    window.downloadSCReceipt = function () {
        const d = currentSCReceipt || {};
        const memberName = d.memberName || '';
        const type = d.type || '';
        const shares = d.shares || '';
        const amount = d.amount || '';
        const paymentMethod = d.paymentMethod || '';
        const referenceNo = d.referenceNo || '';
        const transactionDate = d.transactionDate || '';

        const amountFmt = Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const isWithdrawal = type === 'Withdrawal';

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position:fixed; left:-9999px; top:0; width:400px; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.15);';
        wrapper.innerHTML = `
            <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e8e8e8;">
                <div style="width:60px;height:60px;background:${SC_NAVY};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div style="color:#1a1a1a;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">${isWithdrawal ? 'Withdrawal Successful!' : 'Deposit Successful!'}</div>
                <div style="color:#6b7280;font-size:0.82rem;margin:0;">KMPCATS Cooperative -- Official Receipt</div>
            </div>
            <div style="padding:0.6rem 1.5rem;">
                ${scReceiptRows(memberName, type, shares, amountFmt, paymentMethod, referenceNo, transactionDate, isWithdrawal)}
            </div>
            <div style="padding:0.8rem 1.5rem 1.2rem;text-align:center;border-top:1px dashed #e8e8e8;">
                <div style="color:#aaa;font-size:0.72rem;">This receipt is system-generated and serves as official proof of your transaction.</div>
            </div>
        `;
        document.body.appendChild(wrapper);

        const doCapture = function () {
            html2canvas(wrapper, { scale: 2, useCORS: true, backgroundColor: null }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'KMPCATS-Receipt-' + referenceNo + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                wrapper.remove();
            });
        };

        if (typeof html2canvas !== 'undefined') {
            doCapture();
        } else {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = doCapture;
            document.head.appendChild(script);
        }
    };

    window.openSCRow = function (e, row) {
        if (e && e.target && e.target.closest && e.target.closest('a, button')) return;
        const d = row.dataset;
        if (d.void === '1') {
            showSCVoidReason(d);
            return;
        }
        viewShareCapitalDetail(d.id, d.type, d.status, d.member, d.shares, d.amount, d.method, d.ref, d.date);
    };

    window.processWithdrawalSC = function (action) {
        if (!currentTransactionId) return;

        fetch('/sharecapital/withdrawal/' + currentTransactionId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ action: action })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    document.getElementById('scTransactionModal').remove();
                    document.body.style.overflow = 'auto';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message || 'Failed to process withdrawal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
            });
    };
    // ── Sell Shares ────────────────────────────────────────────────
    window.updateSellSharesFromAmount = function () {
        const amount = parseFloat(document.getElementById('sellAmountInput')?.value) || 0;
        const perShare = {{ $perShareValue }};
        const shares = Math.round((amount / perShare) * 100) / 100;
        document.getElementById('sellSharesInput').value = shares;
        document.getElementById('sellTotalAmount').value = amount;
        document.getElementById('sellSharesDisplay').textContent = shares;
    };

    window.fetchSellerBalance = function (sellerId) {
        const display = document.getElementById('sellerSharesDisplay');
        if (!sellerId) {
            display.textContent = 'Select a seller';
            return;
        }
        fetch('/sharecapital/member/' + sellerId + '/balance')
            .then(response => response.json())
            .then(data => {
                const shares = data.total_shares || 0;
                const amount = data.total_amount || 0;
                display.textContent = shares + ' shares · ₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            })
            .catch(error => {
                console.error('Error:', error);
                display.textContent = '0 shares · ₱0.00';
            });
    };

    window.submitSellShares = function () {
        const form = document.getElementById('sellSharesForm');
        const formData = new FormData(form);

        const sellerId = formData.get('seller_id');
        const buyerId = formData.get('buyer_id');
        const amountInput = parseFloat(formData.get('amount_input')) || 0;
        const shares = parseFloat(formData.get('shares')) || 0;

        if (!sellerId) {
            showToast('Error', 'Please select a seller');
            return;
        }
        if (!buyerId) {
            showToast('Error', 'Please select a buyer');
            return;
        }
        if (sellerId === buyerId) {
            showToast('Error', 'Seller and buyer must be different');
            return;
        }
        if (amountInput < 1000 || shares <= 0) {
            showToast('Error', 'Please enter a valid amount (minimum ₱1,000)');
            return;
        }

        fetch('/sharecapital/sell', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('sellSharesModal');
                    showToast('Success', data.message);
                    form.reset();
                    document.getElementById('sellerSharesDisplay').textContent = 'Select a seller';
                    document.getElementById('sellAmountInput').value = 1000;
                    document.getElementById('sellTotalAmount').value = 1000;
                    document.getElementById('sellSharesInput').value = 1;
                    document.getElementById('sellSharesDisplay').textContent = 1;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Error', data.message || 'Transfer failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
            });
    };

    function checkScPaymentMethodQr() {
        const select = document.getElementById('scPaymentMethod');
        const qrDisplay = document.getElementById('scQrCodeDisplay');
        const qrImg = document.getElementById('scQrCodeImg');
        const selected = select.options[select.selectedIndex];
        const pmId = selected ? selected.dataset.id : null;

        if (!pmId) { qrDisplay.classList.add('hidden'); return; }

        fetch('/admin/payment-methods/' + pmId + '/qr')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.has_qr) {
                    qrImg.src = data.qr_url;
                    qrDisplay.classList.remove('hidden');
                } else {
                    qrDisplay.classList.add('hidden');
                }
            })
            .catch(() => { qrDisplay.classList.add('hidden'); });
    }

    // ── Tom Select Initialization ────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect !== 'undefined') {
            // Manage Share Capital member select
            new TomSelect('#manage-share-member-select', {
                maxOptions: 200,
                placeholder: 'Search for a member...',
                onChange: function (value) {
                    updateMemberShares();
                }
            });

            // Sell Shares seller select
            new TomSelect('#sell-shares-seller-select', {
                maxOptions: 200,
                placeholder: 'Search for a seller...',
                onChange: function (value) {
                    fetchSellerBalance(value);
                }
            });

            // Sell Shares buyer select
            new TomSelect('#sell-shares-buyer-select', {
                maxOptions: 200,
                placeholder: 'Search for a buyer...',
            });
        }
    });
</script>

<!-- SC Deposit Detail Modal -->
<div id="scDepositDetailModal" class="modal-overlay hidden">
    <div class="modal max-w-lg">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="eye" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Share Capital Deposit Details</h2>
                        <p class="text-xs text-gray-500">Review and act on this deposit request</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["scDepositDetailModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Member</span>
                    <span id="scDetailMember" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Shares</span>
                    <span id="scDetailShares" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Payment Method</span>
                    <span id="scDetailMethod" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">GCash Number</span>
                    <span id="scDetailGcashNumber" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">GCash Ref No.</span>
                    <span id="scDetailGcashRef" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Reference No.</span>
                    <span id="scDetailRef" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Date</span>
                    <span id="scDetailDate" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div id="scDetailNoteRow" class="hidden justify-between items-center">
                    <span class="text-sm text-gray-500">Note</span>
                    <span id="scDetailNote" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Deposit Amount</span>
                    <span id="scDetailAmount" class="text-lg font-bold text-success-600">—</span>
                </div>
            </div>

            <div id="scDetailProofSection" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Member's Payment Proof</label>
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <img id="scDetailProofImg" src="" alt="Member payment proof"
                        class="w-full max-h-64 object-contain bg-gray-50">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button data-action="openScVoidConfirm" id="scVoidBtn"
                class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors flex items-center gap-2">
                <i data-lucide="undo-2" class="w-4 h-4"></i>
                Mark as Returned
            </button>
            <button data-action="confirmCompleteSCDeposit" id="scCompleteBtn"
                class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Mark as Complete
            </button>
        </div>
    </div>
</div>

<!-- SC Void Confirm Modal -->
<div id="scVoidConfirmModal" class="modal-overlay hidden">
    <div class="modal max-w-md">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-danger-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Return Share Capital Deposit</h2>
                        <p class="text-xs text-gray-500">Are you sure you want to mark this deposit as returned?</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["scVoidConfirmModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Member</span>
                    <span id="scVoidMember" class="text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Deposit Amount</span>
                    <span id="scVoidAmount" class="text-lg font-bold text-danger-600">—</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Voiding</label>
                <select id="scVoidReason"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-danger-500 focus:border-danger-500">
                    <option value="" disabled selected>Select a reason...</option>
                    <option value="wrong_amount">Wrong amount entered</option>
                    <option value="duplicate_payment">Duplicate payment</option>
                    <option value="fraudulent">Fraudulent / suspicious transaction</option>
                    <option value="other_member">Sent by wrong member</option>
                    <option value="technical_error">System / technical error</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button data-action="openScDetailFromVoid"
                class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Go
                Back</button>
            <button id="scVoidConfirmBtn" data-action="submitSCVoid" disabled
                class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                Confirm Void
            </button>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    let currentSCDepositId = null;

    document.querySelectorAll('.sc-deposit-row').forEach(row => {
        row.addEventListener('click', function () {
            currentSCDepositId = this.dataset.id;
            document.getElementById('scDetailMember').textContent = this.dataset.member || '—';
            document.getElementById('scDetailShares').textContent = this.dataset.shares + ' shares';
            document.getElementById('scDetailAmount').textContent = '₱' + this.dataset.amount;
            document.getElementById('scDetailMethod').textContent = this.dataset.method || '—';
            document.getElementById('scDetailGcashNumber').textContent = this.dataset.gcashNumber || '—';
            document.getElementById('scDetailGcashRef').textContent = this.dataset.gcashRef || '—';
            document.getElementById('scDetailRef').textContent = this.dataset.ref || '—';
            document.getElementById('scDetailDate').textContent = this.dataset.date || '—';
            var noteRow = document.getElementById('scDetailNoteRow');
            if (this.dataset.note) {
                document.getElementById('scDetailNote').textContent = this.dataset.note;
                noteRow.classList.remove('hidden');
                noteRow.classList.add('flex');
            } else {
                noteRow.classList.add('hidden');
                noteRow.classList.remove('flex');
            }
            var proofSection = document.getElementById('scDetailProofSection');
            var proofImg = document.getElementById('scDetailProofImg');
            if (this.dataset.proof) {
                proofImg.src = this.dataset.proof;
                proofSection.classList.remove('hidden');
            } else {
                proofSection.classList.add('hidden');
            }
            openModal('scDepositDetailModal');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    });

    document.getElementById('scVoidReason').addEventListener('change', function () {
        document.getElementById('scVoidConfirmBtn').disabled = !this.value;
    });

    function openScVoidConfirm() {
        var row = document.querySelector('.sc-deposit-row[data-id="' + currentSCDepositId + '"]');
        if (row) {
            document.getElementById('scVoidAmount').textContent = '₱' + row.dataset.amount;
            document.getElementById('scVoidMember').textContent = row.dataset.member;
        }
        document.getElementById('scVoidReason').value = '';
        document.getElementById('scVoidConfirmBtn').disabled = true;
        closeModal('scDepositDetailModal');
        openModal('scVoidConfirmModal');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openScDetailFromVoid() {
        closeModal('scVoidConfirmModal');
        openModal('scDepositDetailModal');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function submitSCVoid() {
        var reason = document.getElementById('scVoidReason').value;
        if (!reason) {
            showToast('Error', 'Please select a reason for voiding.');
            return;
        }
        document.getElementById('scVoidConfirmBtn').disabled = true;
        fetch('/sharecapital/' + currentSCDepositId + '/void-deposit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ void_reason: reason })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    closeModal('scVoidConfirmModal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message || 'Failed to void deposit');
                    document.getElementById('scVoidConfirmBtn').disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
                document.getElementById('scVoidConfirmBtn').disabled = false;
            });
    }

    function confirmCompleteSCDeposit() {
        if (!currentSCDepositId) return;
        document.getElementById('scCompleteBtn').disabled = true;
        fetch('/sharecapital/' + currentSCDepositId + '/complete-deposit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    closeModal('scDepositDetailModal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message || 'Failed to complete deposit');
                    document.getElementById('scCompleteBtn').disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
                document.getElementById('scCompleteBtn').disabled = false;
            });
    }
</script>