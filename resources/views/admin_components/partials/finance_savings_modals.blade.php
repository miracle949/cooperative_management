<!-- Hidden data for withdrawal breakdown JavaScript -->
<div id="bladeWithdrawalData" style="display:none;" data-withdrawal-json="{{ json_encode($monthlyWithdrawalData) }}"
    data-highest-month-json="{{ json_encode($highestWithdrawalMonth) }}"></div>

<!-- Interest Eligibility Modal -->
<div id="interestEligibilityModal" class="modal-overlay hidden">
    <div class="modal max-w-3xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                        <i data-lucide="percent" class="w-5 h-5 text-success-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Interest Eligibility</h2>
                        <p class="text-xs text-gray-500">{{ $sirSettings->frequency_label }} ·
                            {{ $sirSettings->annual_rate }}% p.a. · Min balance:
                            ₱{{ number_format($sirSettings->min_balance_for_interest, 2) }}
                        </p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["interestEligibilityModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <!-- Eligible Section -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-success-700 mb-3 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    ELIGIBLE ({{ $eligibleCount }})
                </h3>
                @if(count($eligibleAccounts) > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibleAccounts as $account)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-success-100 flex items-center justify-center">
                                                    <span class="text-xs text-success-600 font-medium">
                                                        {{ strtoupper(substr($account->user->first_name ?? 'U', 0, 1) . substr($account->user->last_name ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span
                                                    class="text-sm text-gray-900">{{ $account->user->first_name ?? 'Unknown' }}
                                                    {{ $account->user->last_name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-right text-sm font-semibold text-gray-900">
                                            ₱{{ number_format($account->balance, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No eligible accounts</p>
                @endif
            </div>

            <!-- Not Eligible Section -->
            <div>
                <h3 class="text-sm font-semibold text-danger-700 mb-3 flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    NOT ELIGIBLE ({{ $notEligibleCount }})
                </h3>
                @if(count($notEligibleAccounts) > 0)
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th class="text-right">Balance</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notEligibleAccounts as $item)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-danger-100 flex items-center justify-center">
                                                    <span class="text-xs text-danger-600 font-medium">
                                                        {{ strtoupper(substr($item['account']->user->first_name ?? 'U', 0, 1) . substr($item['account']->user->last_name ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span
                                                    class="text-sm text-gray-900">{{ $item['account']->user->first_name ?? 'Unknown' }}
                                                    {{ $item['account']->user->last_name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-right text-sm font-semibold text-gray-900">
                                            ₱{{ number_format($item['account']->balance, 2) }}</td>
                                        <td class="text-xs text-danger-600">{{ implode(', ', $item['reasons']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">All accounts are eligible</p>
                @endif
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button data-action="closeModal" data-arg='["interestEligibilityModal"]'
                class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Savings Balance Modal (static container, populated by JS) -->
<div id="savingsBalanceModal" class="modal-overlay hidden">
    <div class="modal max-w-2xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="piggy-bank" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Savings Balance</h2>
                        <p class="text-xs text-gray-500" id="savingsBalanceSubtitle"></p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["savingsBalanceModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[55vh] overflow-y-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-100">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Member</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Balance</th>
                    </tr>
                </thead>
                <tbody id="savingsBalanceBody"></tbody>
            </table>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end">
            <button data-action="closeModal" data-arg='["savingsBalanceModal"]'
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Close</button>
        </div>
    </div>
</div>

<!-- Manage Savings Modal -->
<div id="addContributionModal" class="modal-overlay hidden">
    <div class="modal max-w-lg" style="border-radius: 16px;">
        <!-- Header -->
        <div class="modal-header"
            style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="piggy-bank" class="w-5 h-5" style="color: #fff;"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Manage Savings</h2>
                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Add or withdraw
                            funds</p>
                    </div>
                </div>
                <button data-action="closeAddContributionModal"
                    style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                </button>
            </div>
        </div>

        <div style="padding: 1.25rem;">
            <form id="adminSavingsForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="member_id" id="memberSelect" class="select" style="width: 100%;"
                        data-action="updateMemberBalance">
                        <option value="">Select member</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Balance Pill -->
                <div
                    style="background: #f8f9f8; border-radius: 10px; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; border: 1px dashed #1E2A4A;">
                    <span style="font-size: 13px; color: #666;">Current Balance</span>
                    <span id="currentBalance" style="font-size: 14px; font-weight: 700; color: #1E2A4A;">₱0.00</span>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="savingsType" class="select" style="width: 100%;"
                        data-action="toggleSavingsPaymentFields">
                        <option value="">Select type...</option>
                        <option value="deposit">Deposit</option>
                        <option value="withdrawal">Withdrawal</option>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
                        <input type="number" name="amount" id="savingsAmount" class="input pl-10" placeholder="0.00"
                            style="width: 100%; padding-left: 2.5rem;">
                    </div>
                    <!-- Quick Amounts -->
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                        <button type="button" data-action="set-input-value" data-target="savingsAmount" data-value="500"
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱500</button>
                        <button type="button" data-action="set-input-value" data-target="savingsAmount"
                            data-value="1000"
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱1,000</button>
                        <button type="button" data-action="set-input-value" data-target="savingsAmount"
                            data-value="2000"
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱2,000</button>
                        <button type="button" data-action="set-input-value" data-target="savingsAmount"
                            data-value="5000"
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱5,000</button>
                    </div>
                </div>

                <!-- Payment Method (deposit only) -->
                <div id="savingsPaymentField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <input type="hidden" name="payment_method" id="savingsPaymentMethod" value="cash">
                    <input type="text" value="Cash" readonly
                        style="width: 100%; background: #f3f4f6; cursor: default; border: 1px solid #ddd; border-radius: 8px; padding: 8px; color: #555;">
                </div>

                <!-- Mobile Number (withdrawal only) -->
                <div id="savingsMobileField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">GCash Number</label>
                    <input type="text" id="savingsMobileDisplay" name="gcash_number" class="input"
                        placeholder="Enter GCash number" style="width: 100%;">
                </div>

                <!-- QR Code Display -->
                <div id="savingsQrCodeDisplay" class="hidden">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-center">
                        <p class="text-xs text-gray-500 mb-2 font-medium">Scan QR Code to Pay</p>
                        <img id="savingsQrCodeImg"
                            class="w-40 h-40 mx-auto rounded-lg object-cover border border-gray-200" src=""
                            alt="Payment QR Code">
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
                <button data-action="submitAdminSavings"
                    style="width: 100%; padding: 0.7rem; background: #1E2A4A; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Transaction
                </button>
                <button data-action="closeAddContributionModal"
                    style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Convert to Share Capital Modal -->
<div id="convertToSCModal" class="modal-overlay hidden">
    <div class="modal max-w-lg" style="border-radius: 16px;">
        <div class="modal-header"
            style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="refresh-cw" class="w-5 h-5" style="color: #fff;"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Convert to Share Capital</h2>
                        <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Transfer savings to
                            share capital</p>
                    </div>
                </div>
                <button data-action="closeConvertToSCModal"
                    style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                </button>
            </div>
        </div>

        <div style="padding: 1.25rem;">
            <form id="convertToSCForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="member_id" id="convertMemberSelect" class="select" style="width: 100%;"
                        data-action="updateConvertBalances">
                        <option value="">Select member</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div
                        style="background: #f0fdf4; border-radius: 10px; padding: 0.75rem 1rem; border: 1px dashed #22c55e;">
                        <span style="font-size: 12px; color: #666;">Savings Balance</span>
                        <div style="font-size: 16px; font-weight: 700; color: #16a34a; margin-top: 2px;">
                            ₱<span id="convertSavingsBalance">0.00</span>
                        </div>
                    </div>
                    <div
                        style="background: #eff6ff; border-radius: 10px; padding: 0.75rem 1rem; border: 1px dashed #3b82f6;">
                        <span style="font-size: 12px; color: #666;">Share Capital Balance</span>
                        <div style="font-size: 16px; font-weight: 700; color: #2563eb; margin-top: 2px;">
                            ₱<span id="convertSCBalance">0.00</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Convert</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
                        <input type="number" name="amount" id="convertAmount" class="input pl-10" placeholder="0.00"
                            style="width: 100%; padding-left: 2.5rem;">
                    </div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                        <button type="button" data-action="setConvertAmount" data-arg='[500]'
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱500</button>
                        <button type="button" data-action="setConvertAmount" data-arg='[200]'
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱200</button>
                        <button type="button" data-action="setConvertAmount" data-arg='[2000]'
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱2,000</button>
                        <button type="button" data-action="setConvertAmount" data-arg='[5000]'
                            style="padding: 4px 12px; border-radius: 16px; font-size: 11px; font-weight: 600; cursor: pointer; background: #fff; color: #555; border: 1px solid #ddd;">₱5,000</button>
                    </div>
                </div>

                <div
                    style="background: #f8f9f8; border-radius: 10px; padding: 0.75rem 1rem; border: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                        <span style="color: #666;">Share Price</span>
                        <span
                            style="font-weight: 600; color: #1E2A4A;">₱{{ number_format(\App\Http\Controllers\ShareCapital::PAR_VALUE, 2) }}
                            / share</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-top: 4px;">
                        <span style="color: #666;">Estimated Shares</span>
                        <span id="estimatedShares" style="font-weight: 700; color: #1E2A4A;">0</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-top: 4px;">
                        <span style="color: #666;">Remainder (not converted)</span>
                        <span id="convertRemainder" style="font-weight: 600; color: #dc2626;">₱0.00</span>
                    </div>
                </div>
            </form>

            <div style="margin-top: 1.25rem; display: flex; flex-direction: column; gap: 8px;">
                <button data-action="submitConvertToSC"
                    style="width: 100%; padding: 0.7rem; background: #1E2A4A; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Convert to Share Capital
                </button>
                <button data-action="closeConvertToSCModal"
                    style="width: 100%; padding: 0.65rem; background: #fff; color: #666; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Disburse Savings Withdrawal Modal -->
<div id="savingsDisburseModal" class="modal-overlay hidden">
    <div class="modal max-w-lg">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-5 h-5 text-success-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Disburse Savings Withdrawal</h2>
                        <p class="text-xs text-gray-500">Process the GCash disbursement to the member</p>
                    </div>
                </div>
                <button data-action="closeSavingsDisburseModal"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="disburseTxId">

            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Member</span>
                    <span id="disburseMemberName" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">GCash Number</span>
                    <span id="disburseContact" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Current Savings Balance</span>
                    <span id="disburseBalance" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Withdrawal Amount</span>
                    <span id="disburseAmount" class="text-lg font-bold text-red-600"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">GCash Disbursement Receipt (Proof)</label>
                <div class="flex items-center gap-3">
                    <label
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-primary-400 hover:bg-primary-50 transition-colors"
                        id="disburseReceiptLabel">
                        <i data-lucide="upload" class="w-5 h-5 text-gray-400"></i>
                        <span id="disburseReceiptText" class="text-sm text-gray-500">Upload receipt image</span>
                        <input type="file" id="disburseReceipt" accept="image/jpg,image/jpeg,image/png" class="hidden"
                            data-action="handleDisburseReceipt" data-arg='["|el|"]'>
                    </label>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button data-action="closeSavingsDisburseModal"
                class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
            <button id="disburseSubmitBtn" data-action="submitSavingsDisbursement" disabled
                class="px-5 py-2.5 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Mark as Disbursed
            </button>
        </div>
    </div>
</div>

<!-- Savings Deposit Detail Modal -->
<div id="depositDetailModal" class="modal-overlay hidden">
    <div class="modal max-w-lg">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="eye" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Savings Deposit Details</h2>
                        <p class="text-xs text-gray-500">Review and act on this deposit request</p>
                    </div>
                </div>
                <button data-action="closeDepositDetailModal"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="depositDetailTxId">
            <input type="hidden" id="depositDetailPaymentMethod">

            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Member</span>
                    <span id="depositDetailMemberName" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Contact</span>
                    <span id="depositDetailContact" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Current Savings Balance</span>
                    <span id="depositDetailBalance" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">GCash Ref No</span>
                    <span id="depositDetailGcashRef" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Deposit Amount</span>
                    <span id="depositDetailAmount" class="text-lg font-bold text-success-600"></span>
                </div>
            </div>

            <div id="depositDetailMemberProof" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Member's Payment Proof</label>
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <img id="depositDetailMemberProofImg" src="" alt="Member payment proof"
                        class="w-full max-h-64 object-contain bg-gray-50">
                    <p id="depositDetailMemberProofMissing" class="hidden text-sm text-gray-500 p-4 text-center">
                        Proof image file is missing on the server.
                    </p>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button id="depositDetailVoidBtn" data-action="openVoidConfirmModal"
                class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors flex items-center gap-2">
                <i data-lucide="undo-2" class="w-4 h-4"></i>
                Mark as Returned
            </button>
            <button id="depositDetailCompleteBtn" data-action="submitSavingsDepositCompletion"
                class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Mark as Complete
            </button>
        </div>
    </div>
</div>

<!-- Void Deposit Confirmation Modal -->
<div id="voidConfirmModal" class="modal-overlay hidden">
    <div class="modal max-w-md">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-danger-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Return Savings Deposit</h2>
                        <p class="text-xs text-gray-500">Are you sure you want to mark this deposit as returned?</p>
                    </div>
                </div>
                <button data-action="closeVoidConfirmModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="voidConfirmTxId">

            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Member</span>
                    <span id="voidConfirmMemberName" class="text-sm font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Deposit Amount</span>
                    <span id="voidConfirmAmount" class="text-lg font-bold text-danger-600"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Voiding</label>
                <select id="voidConfirmReason"
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
            <button data-action="goBackToDetailModal"
                class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Go
                Back</button>
            <button id="voidConfirmSubmitBtn" data-action="submitSavingsDepositVoid" disabled
                class="px-5 py-2.5 bg-danger-600 text-white font-medium rounded-lg hover:bg-danger-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                Confirm Void
            </button>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    var savingsAccountsData = @json($savingsAccounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);

    function openSavingsBalanceModal() {
        var accts = savingsAccountsData;
        var tot = parseFloat('{{ $totalSavingsBalance }}');
        var tbody = document.getElementById('savingsBalanceBody');
        tbody.innerHTML = '';

        if (accts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="py-12 text-center text-sm text-gray-400">No savings accounts found</td></tr>';
        } else {
            for (var i = 0; i < accts.length; i++) {
                var a = accts[i];
                var fn = (a.user && a.user.first_name) ? a.user.first_name : 'Unknown';
                var ln = (a.user && a.user.last_name) ? a.user.last_name : '';
                var ini = (fn.charAt(0) + ln.charAt(0)).toUpperCase();
                var bal = parseFloat(a.balance) || 0;
                var row = document.createElement('tr');
                row.className = 'border-b border-gray-50';
                row.innerHTML = '<td class="py-3 px-4">'
                    + '<div class="flex items-center gap-3">'
                    + '<div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center">'
                    + '<span class="text-xs font-semibold text-primary-600">' + ini + '</span>'
                    + '</div>'
                    + '<span class="text-sm font-medium text-gray-900">' + fn + ' ' + ln + '</span>'
                    + '</div></td>'
                    + '<td class="py-3 px-4 text-right text-sm font-semibold text-gray-900">₱' + bal.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + '</td>';
                tbody.appendChild(row);
            }
        }

        document.getElementById('savingsBalanceSubtitle').textContent =
            'Total: ₱' + tot.toLocaleString('en-PH', { minimumFractionDigits: 2 }) + ' · ' + accts.length + ' account(s)';

        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        openModal('savingsBalanceModal');
    }

    function openWithdrawalDisburseModal(id, name, balance, amount, contact) {
        document.getElementById('disburseTxId').value = id;
        document.getElementById('disburseMemberName').textContent = name;
        document.getElementById('disburseContact').textContent = contact;
        document.getElementById('disburseBalance').textContent = '₱' + balance;
        document.getElementById('disburseAmount').textContent = '₱' + amount;
        document.getElementById('disburseReceipt').value = '';
        document.getElementById('disburseReceiptText').textContent = 'Upload receipt image';
        document.getElementById('disburseSubmitBtn').disabled = true;
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        openModal('savingsDisburseModal');
    }

    function closeSavingsDisburseModal() {
        closeModal('savingsDisburseModal');
    }

    function handleDisburseReceipt(input) {
        var label = document.getElementById('disburseReceiptText');
        var btn = document.getElementById('disburseSubmitBtn');
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
            label.classList.replace('text-gray-500', 'text-success-600');
            btn.disabled = false;
        } else {
            label.textContent = 'Upload receipt image';
            label.classList.replace('text-success-600', 'text-gray-500');
            btn.disabled = true;
        }
    }

    function submitSavingsDisbursement() {
        var id = document.getElementById('disburseTxId').value;
        var fileInput = document.getElementById('disburseReceipt');
        if (!fileInput.files || !fileInput.files[0]) {
            showToast('Error', 'Please upload a receipt image first.');
            return;
        }
        var btn = document.getElementById('disburseSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }

        var formData = new FormData();
        formData.append('admin_receipt', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/savings/' + id + '/disburse', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeSavingsDisburseModal();
                    showToast('Success', data.message || 'Withdrawal disbursed successfully.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else {
                    showToast('Error', data.message || 'Disbursement failed.');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Mark as Disbursed';
                    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                }
            })
            .catch(function (error) {
                console.error('Disburse error:', error);
                showToast('Error', 'An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Mark as Disbursed';
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            });
    }

    function openDepositDetailModal(id, name, balance, amount, contact, paymentMethod, proofPath, gcashRefNo) {
        document.getElementById('depositDetailTxId').value = id;
        document.getElementById('depositDetailPaymentMethod').value = paymentMethod;
        document.getElementById('depositDetailMemberName').textContent = name;
        document.getElementById('depositDetailContact').textContent = contact;
        document.getElementById('depositDetailBalance').textContent = '₱' + balance;
        document.getElementById('depositDetailAmount').textContent = '₱' + amount;
        document.getElementById('depositDetailGcashRef').textContent = gcashRefNo || '—';

        var memberProofSection = document.getElementById('depositDetailMemberProof');
        var proofImg = document.getElementById('depositDetailMemberProofImg');
        var proofMissing = document.getElementById('depositDetailMemberProofMissing');
        
        if (proofPath) {
            var cleanPath = String(proofPath).replace(/^\/?(storage|public)\//, '');
            proofMissing.classList.add('hidden');
            proofImg.classList.remove('hidden');
            proofImg.onerror = function () {
                proofImg.classList.add('hidden');
                proofMissing.classList.remove('hidden');
            };
            proofImg.src = '/proof/' + cleanPath.split('/').map(encodeURIComponent).join('/');
            memberProofSection.classList.remove('hidden');
        } else {
            memberProofSection.classList.add('hidden');
        }

        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        openModal('depositDetailModal');
    }

    function closeDepositDetailModal() {
        closeModal('depositDetailModal');
    }

    document.addEventListener('click', function (e) {
        var row = e.target.closest('.js-deposit-detail');
        if (row) {
            openDepositDetailModal(
                row.dataset.depositId,
                row.dataset.depositMember,
                row.dataset.depositBalance,
                row.dataset.depositAmount,
                row.dataset.depositContact,
                row.dataset.depositMethod,
                row.dataset.depositProof,
                row.dataset.depositRef
            );
            return;
        }
        var disburseBtn = e.target.closest('.js-disburse-withdrawal');
        if (disburseBtn) {
            openWithdrawalDisburseModal(
                disburseBtn.dataset.withdrawalId,
                disburseBtn.dataset.withdrawalMember,
                disburseBtn.dataset.withdrawalBalance,
                disburseBtn.dataset.withdrawalAmount,
                disburseBtn.dataset.withdrawalContact
            );
        }
    });

    function openVoidConfirmModal() {
        var id = document.getElementById('depositDetailTxId').value;
        var name = document.getElementById('depositDetailMemberName').textContent;
        var amount = document.getElementById('depositDetailAmount').textContent;

        document.getElementById('voidConfirmTxId').value = id;
        document.getElementById('voidConfirmMemberName').textContent = name;
        document.getElementById('voidConfirmAmount').textContent = amount;
        document.getElementById('voidConfirmReason').value = '';
        document.getElementById('voidConfirmSubmitBtn').disabled = true;

        closeModal('depositDetailModal');

        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        openModal('voidConfirmModal');
    }

    function closeVoidConfirmModal() {
        closeModal('voidConfirmModal');
    }

    function goBackToDetailModal() {
        closeModal('voidConfirmModal');
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        openModal('depositDetailModal');
    }

    document.getElementById('voidConfirmReason').addEventListener('change', function () {
        document.getElementById('voidConfirmSubmitBtn').disabled = !this.value;
    });

    function submitSavingsDepositVoid() {
        var id = document.getElementById('voidConfirmTxId').value;
        var reason = document.getElementById('voidConfirmReason').value;

        if (!reason) {
            showToast('Error', 'Please select a reason for voiding.');
            return;
        }

        var btn = document.getElementById('voidConfirmSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('void_reason', reason);

        fetch('/savings/' + id + '/void-deposit', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeModal('voidConfirmModal');
                    showToast('Success', data.message || 'Deposit voided successfully.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else {
                    showToast('Error', data.message || 'Failed to void deposit.');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="x-circle" class="w-4 h-4"></i> Confirm Void';
                    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                }
            })
            .catch(function (error) {
                console.error('Void deposit error:', error);
                showToast('Error', 'An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="x-circle" class="w-4 h-4"></i> Confirm Void';
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            });
    }

    function submitSavingsDepositCompletion() {
        var id = document.getElementById('depositDetailTxId').value;

        var btn = document.getElementById('depositDetailCompleteBtn');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Processing...';
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/savings/' + id + '/complete-deposit', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeDepositDetailModal();
                    showToast('Success', data.message || 'Deposit confirmed successfully.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else {
                    showToast('Error', data.message || 'Failed to confirm deposit.');
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Mark as Complete';
                    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
                }
            })
            .catch(function (error) {
                console.error('Complete deposit error:', error);
                showToast('Error', 'An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Mark as Complete';
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            });
    }

    function openInterestEligibilityModal() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        openModal('interestEligibilityModal');
    }

    function openBalanceBreakdownModal() {
        try {
            const existing = document.getElementById('balanceBreakdownDynamic');
            if (existing) existing.remove();

            const currentBalance = {{ $currentBalance }};
            const totalDeposits = {{ $totalDeposits }};
            const totalWithdrawals = {{ $totalWithdrawals }};

            const modal = document.createElement('div');
            modal.id = 'balanceBreakdownDynamic';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;';
            modal.innerHTML = `
                <div style="background:white;border-radius:12px;max-width:32rem;width:90%;max-height:90vh;overflow:auto;">
                    <div style="padding:1.5rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <div style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#edf0f5;display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3a4e7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><path d="M12 1v6m0 6v6"/><path d="m4.22 4.22 4.24 4.24m2.12 9.64 4.24 4.24"/><path d="M1 12h6m6 0h6"/><path d="m4.22 19.78 4.24-4.24m2.12-9.64 4.24-4.24"/></svg>
                            </div>
                            <div>
                                <h2 style="font-size:1.25rem;font-weight:700;color:#111;">Balance Details</h2>
                                <p style="font-size:0.75rem;color:#6b7280;">Account balance breakdown</p>
                            </div>
                        </div>
                        <button data-action="remove-closest" data-sel="#balanceBreakdownDynamic" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
                    </div>
                    <div style="padding:1.5rem;max-height:60vh;overflow-y:auto;">
                        <div style="background:#edf0f5;border-radius:0.5rem;padding:1rem;border:1px solid #d0d6e4;text-align:center;margin-bottom:1.5rem;">
                            <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.5rem;">Current Balance</p>
                            <p style="font-size:1.875rem;font-weight:700;color:#3a4e7a;">₱${currentBalance.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                        </div>
                        
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#f0fdf4;border-radius:0.5rem;border-left:3px solid #3a4e7a;">
                                <div>
                                    <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Deposits</p>
                                    <p style="font-size:0.875rem;font-weight:600;color:#111;">₱${totalDeposits.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                                </div>
                                <i data-lucide="arrow-up" style="color:#3a4e7a;width:20px;height:20px;"></i>
                            </div>
                            
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#fef2f2;border-radius:0.5rem;border-left:3px solid #dc2626;">
                                <div>
                                    <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Withdrawals</p>
                                    <p style="font-size:0.875rem;font-weight:600;color:#111;">₱${totalWithdrawals.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                                </div>
                                <i data-lucide="arrow-down" style="color:#dc2626;width:20px;height:20px;"></i>
                            </div>
                            
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#f9fafb;border-radius:0.5rem;border:1px solid #e5e7eb;margin-top:0.5rem;">
                                <p style="font-size:0.75rem;color:#6b7280;">Balance Calculation</p>
                                <p style="font-size:0.875rem;font-weight:600;color:#111;">Deposits - Withdrawals</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } catch (error) {
            console.error('Error opening balance breakdown modal:', error);
        }
    }

    function openMonthlyBreakdownModal() {
        try {
            const existing = document.getElementById('monthlyBreakdownDynamic');
            if (existing) existing.remove();

            const rawData = @json($monthlyData);
            const monthlyData = rawData.map(m => ({
                name: m.name || '',
                year: m.year || new Date().getFullYear().toString(),
                amount: parseFloat(m.amount) || 0,
                bar_height: parseFloat(m.bar_height) || 0
            }));
            const monthlyAvg = {{ $monthlyAvg ?? 0 }};
            const highestMonthRaw = @json($highestMonth);
            const highestMonth = {
                name: highestMonthRaw.name || '',
                amount: parseFloat(highestMonthRaw.amount) || 0
            };

            let chartHtml = '';
            const maxAmount = Math.max(...monthlyData.map(m => m.amount), 1);
            monthlyData.forEach(month => {
                const height = (month.amount / maxAmount) * 150;
                const hasAmount = month.amount > 0;
                chartHtml += `
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:0.5rem;">
                    <div style="width:100%;${hasAmount ? 'background:#d0d6e4;cursor:pointer;' : 'background:#f3f4f6;border:2px dashed #d1d5db;'}border-radius:0.5rem;height:${Math.max(height, 5)}px;" title="₱${month.amount.toLocaleString()}"></div>
                    <span style="font-size:0.75rem;color:#6b7280;">${month.name.substring(0, 3)}</span>
                </div>
            `;
            });

            const modal = document.createElement('div');
            modal.id = 'monthlyBreakdownDynamic';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;';
            modal.innerHTML = `
            <div style="background:white;border-radius:12px;max-width:42rem;width:90%;max-height:90vh;overflow:auto;">
                <div style="padding:1.5rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#edf0f5;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3a4e7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        </div>
                        <div>
                            <h2 style="font-size:1.25rem;font-weight:700;color:#111;">Monthly Deposits</h2>
                            <p style="font-size:0.75rem;color:#6b7280;">Savings by month (Last 6 months)</p>
                        </div>
                    </div>
                    <button data-action="remove-closest" data-sel="#monthlyBreakdownDynamic" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
                </div>
                <div style="padding:1.5rem;max-height:60vh;overflow-y:auto;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                        <div style="background:#edf0f5;border-radius:0.5rem;padding:1rem;border:1px solid #d0d6e4;text-align:center;">
                            <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Average Monthly</p>
                            <p style="font-size:1.25rem;font-weight:700;color:#111;">₱${monthlyAvg.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                        </div>
                        <div style="background:#d0d6e4;border-radius:0.5rem;padding:1rem;border:1px solid #a8b3cc;text-align:center;">
                            <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Highest Month</p>
                            <p style="font-size:1.25rem;font-weight:700;color:#111;">₱${(highestMonth.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                            <p style="font-size:0.75rem;color:#6b7280;">${highestMonth.name || 'N/A'}</p>
                        </div>
                    </div>
                    <h4 style="font-weight:600;color:#111;margin-bottom:1rem;">Monthly Savings</h4>
                    <div style="height:12rem;display:flex;align-items:flex-end;justify-content:space-between;gap:0.5rem;padding:0 1rem;margin-bottom:1rem;">
                        ${chartHtml}
                    </div>
                    <div style="display:flex;flex-direction:column;gap:0.5rem;">
                        ${monthlyData.map(month => `
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#f9fafb;border-radius:0.5rem;margin-bottom:0.5rem;">
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <div style="width:2rem;height:2rem;background:#d0d6e4;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                                        <span style="font-size:0.75rem;font-weight:600;color:#3a4e7a;">${month.name.substring(0, 3)}</span>
                                    </div>
                                    <span style="font-size:0.875rem;font-weight:500;color:#111;">${month.name} ${month.year || new Date().getFullYear()}</span>
                                </div>
                                <span style="font-size:0.875rem;font-weight:600;${month.amount > 0 ? 'color:#111;' : 'color:#9ca3af;'}">₱${month.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                            </div>
                         `).join('')}
                     </div>
                 </div>
             </div>
         `;
            document.body.appendChild(modal);
        } catch (error) {
            console.error('Error opening monthly breakdown modal:', error);
        }
    }

    function openLastContributionModal() {
        const existing = document.getElementById('lastContributionDynamic');
        if (existing) existing.remove();

        const lastContribution = @json($lastContribution);

        const memberName = lastContribution?.savings_account?.user
            ? lastContribution.savings_account.user.first_name + ' ' + lastContribution.savings_account.user.last_name
            : 'Unknown';

        const modal = document.createElement('div');
        modal.id = 'lastContributionDynamic';
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;';
        modal.innerHTML = `
            <div style="background:white;border-radius:12px;max-width:28rem;width:90%;max-height:90vh;overflow:auto;">
                <div style="padding:1.5rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        </div>
                        <div>
                            <h2 style="font-size:1.25rem;font-weight:700;color:#111;">Deposits Today</h2>
                            <p style="font-size:0.75rem;color:#6b7280;">Most recent transaction details</p>
                        </div>
                    </div>
                    <button data-action="remove-closest" data-sel="#lastContributionDynamic" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
                </div>
                <div style="padding:1.5rem;max-height:60vh;overflow-y:auto;">
                    ${lastContribution ? `
                        <div style="background:linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);border-radius:0.75rem;padding:1.5rem;border:1px solid #fef9c3;margin-bottom:1.5rem;text-align:center;">
                            <p style="font-size:1.875rem;font-weight:700;color:#111;">₱${parseFloat(lastContribution.amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                            <span style="display:inline-block;margin-top:0.5rem;padding:0.25rem 0.75rem;background:#d0d6e4;color:#1E2A4A;font-size:0.875rem;font-weight:600;border-radius:9999px;text-transform:capitalize;">
                                ${lastContribution.type}
                            </span>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <div style="display:flex;justify-content:space-between;padding:0.75rem;border-bottom:1px solid #f3f4f6;">
                                <span style="font-size:0.875rem;color:#6b7280;">Member</span>
                                <span style="font-size:0.875rem;font-weight:500;color:#111;">${memberName}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:0.75rem;border-bottom:1px solid #f3f4f6;">
                                <span style="font-size:0.875rem;color:#6b7280;">Reference No.</span>
                                <span style="font-size:0.875rem;font-weight:500;color:#111;font-family:monospace;">${lastContribution.reference_no || 'N/A'}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:0.75rem;border-bottom:1px solid #f3f4f6;">
                                <span style="font-size:0.875rem;color:#6b7280;">Date & Time</span>
                                <span style="font-size:0.875rem;font-weight:500;color:#111;">${new Date(lastContribution.created_at).toLocaleString('en-PH')}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:0.75rem;border-bottom:1px solid #f3f4f6;">
                                <span style="font-size:0.875rem;color:#6b7280;">Payment Method</span>
                                <span style="font-size:0.875rem;font-weight:500;color:#111;">${lastContribution.payment_method || 'N/A'}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:0.75rem;">
                                <span style="font-size:0.875rem;color:#6b7280;">Status</span>
                                <span style="font-size:0.875rem;font-weight:500;color:#059670;">Completed</span>
                            </div>
                        </div>
                    ` : `
                        <div style="text-align:center;padding:3rem;color:#9ca3af;">
                            <p>No contributions found</p>
                        </div>
                    `}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function closeMonthlyBreakdownModal() {
        document.getElementById('monthlyBreakdownDynamic')?.remove();
        document.body.style.overflow = '';
    }

    function openWithdrawalBreakdownModal() {
        try {
            const existing = document.getElementById('withdrawalBreakdownDynamic');
            if (existing) existing.remove();

            const dataElement = document.getElementById('bladeWithdrawalData');
            const jsonStr = dataElement.getAttribute('data-withdrawal-json');
            const rawData = JSON.parse(jsonStr);
            const highestMonthStr = dataElement.getAttribute('data-highest-month-json');
            const highestMonthRaw = JSON.parse(highestMonthStr);

            const monthlyData = rawData.map(m => ({
                name: m.name || '',
                year: m.year || new Date().getFullYear().toString(),
                amount: parseFloat(m.amount) || 0,
                bar_height: parseFloat(m.bar_height) || 0
            }));
            const monthlyAvg = {{ $monthlyWithdrawalAvg ?? 0 }};
            const highestMonth = {
                name: highestMonthRaw.name || '',
                amount: parseFloat(highestMonthRaw.amount) || 0
            };

            let chartHtml = '';
            const maxAmount = Math.max(...monthlyData.map(m => m.amount), 1);
            monthlyData.forEach(month => {
                const height = (month.amount / maxAmount) * 150;
                const hasAmount = month.amount > 0;
                chartHtml += `
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:0.5rem;">
                    <div style="width:100%;${hasAmount ? 'background:#fee2e2;cursor:pointer;' : 'background:#f3f4f6;border:2px dashed #d1d5db;'}border-radius:0.5rem;height:${Math.max(height, 5)}px;" title="₱${month.amount.toLocaleString()}"></div>
                    <span style="font-size:0.75rem;color:#6b7280;">${month.name.substring(0, 3)}</span>
                </div>
            `;
            });

            const modal = document.createElement('div');
            modal.id = 'withdrawalBreakdownDynamic';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;';
            modal.innerHTML = `
            <div style="background:white;border-radius:12px;max-width:42rem;width:90%;max-height:90vh;overflow:auto;">
                <div style="padding:1.5rem;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#fee2e2;display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 17-5-5-4 4-3-3"/></svg>
                        </div>
                        <div>
                            <h2 style="font-size:1.25rem;font-weight:700;color:#111;">Monthly Withdrawals</h2>
                            <p style="font-size:0.75rem;color:#6b7280;">Withdrawals by month (Last 6 months)</p>
                        </div>
                    </div>
                    <button data-action="remove-closest" data-sel="#withdrawalBreakdownDynamic" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
                </div>
                <div style="padding:1.5rem;max-height:60vh;overflow-y:auto;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                        <div style="background:#fef2f2;border-radius:0.5rem;padding:1rem;border:1px solid #fee2e2;text-align:center;">
                            <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Average Monthly</p>
                            <p style="font-size:1.25rem;font-weight:700;color:#111;">₱${monthlyAvg.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                        </div>
                        <div style="background:#fee2e2;border-radius:0.5rem;padding:1rem;border:1px solid #fecaca;text-align:center;">
                            <p style="font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">Highest Month</p>
                            <p style="font-size:1.25rem;font-weight:700;color:#111;">₱${(highestMonth.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                            <p style="font-size:0.75rem;color:#6b7280;">${highestMonth.name || 'N/A'}</p>
                        </div>
                    </div>
                     <h4 style="font-weight:600;color:#111;margin-bottom:1rem;">Monthly Withdrawals</h4>
                     <div style="height:12rem;display:flex;align-items:flex-end;justify-content:space-between;gap:0.5rem;padding:0 1rem;margin-bottom:1rem;">
                         ${chartHtml}
                     </div>
                      <div style="display:flex;flex-direction:column;gap:0.5rem;">
                          ${monthlyData.map(month => `
                              <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;background:#f9fafb;border-radius:0.5rem;margin-bottom:0.5rem;">
                                  <div style="display:flex;align-items:center;gap:0.75rem;">
                                      <div style="width:2rem;height:2rem;background:#fee2e2;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                                          <span style="font-size:0.75rem;font-weight:600;color:#dc2626;">${month.name.substring(0, 3)}</span>
                                      </div>
                                      <span style="font-size:0.875rem;font-weight:500;color:#111;">${month.name} ${month.year || new Date().getFullYear()}</span>
                                  </div>
                                  <span style="font-size:0.875rem;font-weight:600;${month.amount > 0 ? 'color:#111;' : 'color:#9ca3af;'}">₱${month.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                               </div>
                           `).join('')}
                       </div>
                  </div>
              </div>
          `;
            document.body.appendChild(modal);
        } catch (error) {
            console.error('Error opening withdrawal breakdown modal:', error);
        }
    }

    function closeWithdrawalBreakdownModal() {
        document.getElementById('withdrawalBreakdownDynamic')?.remove();
        document.body.style.overflow = '';
    }

    function closeLastContributionModal() {
        document.getElementById('lastContributionDynamic')?.remove();
        document.body.style.overflow = '';
    }

    function closeAddContributionModal() {
        const modal = document.getElementById('addContributionModal');
        modal.classList.add('hidden');
        modal.style.cssText = '';
        document.body.style.overflow = '';
    }

    function showMonthDetail(monthName, amount) {
        const formattedAmount = amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        showToast(monthName + ' ' + new Date().getFullYear(), 'Savings: ₱' + formattedAmount, 'info');
    }

    // Initialize icons when modal opens
    document.getElementById('addContributionModal')?.addEventListener('transitionend', function () {
        if (!this.classList.contains('hidden') && typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // View Receipt Modal Functions (html2canvas — uniform with member pages)
    const VOID_LABELS = {
        wrong_amount: 'Wrong amount entered',
        duplicate_payment: 'Duplicate payment',
        fraudulent: 'Fraudulent / suspicious transaction',
        other_member: 'Sent by wrong member',
        technical_error: 'System / technical error',
        other: 'Other'
    };
    function getVoidLabel(key) { return VOID_LABELS[key] || key || 'No reason provided'; }

    let currentSavingsReceipt = null;

    window.showSavingsVoidReason = function (d) {
        d = d || {};
        const label = getVoidLabel(d.reason);
        const existingModal = document.getElementById('voidReasonModal');
        if (existingModal) { existingModal.remove(); }

        const amountFmt = Number(d.amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const balanceFmt = 'data-balance' in d && d.balance !== '' && d.balance !== undefined
            ? Number(d.balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })
            : null;

        function row(label2, value) {
            return '<div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #f0e2e2; font-size:0.85rem;">'
                + '<span style="color:#888; font-weight:500; text-transform:uppercase; font-size:0.7rem; letter-spacing:0.3px;">' + label2 + '</span>'
                + '<span style="color:#1a1a1a; font-weight:600; text-align:right; max-width:60%; word-break:break-word;">' + value + '</span>'
                + '</div>';
        }

        const modal = document.createElement('div');
        modal.id = 'voidReasonModal';
        modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';
        modal.innerHTML = `
            <div style="background:#fff; border-radius:20px; width:100%; max-width:400px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:voidModalIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                <style>
                    @keyframes voidModalIn {
                        from { opacity: 0; transform: translateY(28px) scale(0.97); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .sv-ghost-btn:hover { background:#f5f5f5; color:#333; }
                </style>
                <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                    <div style="width:60px;height:60px;background:#c0392b;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                    </div>
                    <h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">Transaction Returned</h2>
                    <p style="color:#6b7280; font-size:0.82rem; margin:0;">This transaction has been returned by the admin.</p>
                </div>
                <div style="padding:0.75rem 1.5rem 0;">
                    <div style="background:#fafafa; border-radius:12px; padding:0.25rem 1rem;">
                        ${row('Type', d.type || '—')}
                        ${row('Member', d.member || '—')}
                        ${row('Reference No.', d.ref || '—')}
                        ${row('Date & Time', d.date || '—')}
                        ${row('Method', d.method || '—')}
                        ${balanceFmt !== null ? row('Balance After', '&#8369;' + balanceFmt) : ''}
                    </div>
                </div>
                <div style="padding:1.25rem 1.5rem;">
                    <div style="font-size:0.78rem; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.4rem;">Reason</div>
                    <div style="font-size:1rem; font-weight:700; color:#c0392b; background:#fdecec; border:1px solid #f5c6c6; border-radius:10px; padding:0.75rem 1rem;">${label}</div>
                </div>
                <div style="padding:0 1.5rem 1.5rem;">
                    <button data-action="remove-element" data-target="voidReasonModal" class="sv-ghost-btn" style="width:100%; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    };

    window.openSavingsRow = function (e, row) {
        if (e && e.target && e.target.closest && e.target.closest('a, button')) return;
        const d = row.dataset;
        if (d.void === '1') {
            showSavingsVoidReason(d);
            return;
        }
        viewReceipt(d.ref, d.member, d.type, d.amount, d.method, d.date, d.balance, d.note);
    };

    const SV_NAVY = '#1E2A4A';
    const SV_NAVY_LIGHT = '#eef1f8';

    function svReceiptRow(label, value, options) {
        options = options || {};
        return `
            <div style="display:flex; align-items:center; justify-content:space-between; padding:0.6rem 0; border-bottom:1px dashed #e8e8e8; font-size:0.85rem;">
                <span style="color:#888; font-weight:500;">${label}</span>
                <span style="color:#1a1a1a; font-weight:${options.highlight ? '800' : '600'}; text-align:right; max-width:55%; word-break:break-word; font-size:${options.highlight ? '1rem' : '0.85rem'}; ${options.highlight ? 'color:' + SV_NAVY + ';' : ''}">${value}</span>
            </div>
        `;
    }

    function svRefBadge(ref) {
        return '<span style="display:inline-block; background:' + SV_NAVY_LIGHT + '; color:' + SV_NAVY + '; padding:0.2rem 0.6rem; border-radius:6px; letter-spacing:0.5px; font-family:monospace; font-size:0.78rem; font-weight:600;">' + ref + '</span>';
    }

    function svStatusBadge() {
        return '<span style="display:inline-flex; align-items:center; gap:6px; background:#d1fae5; border:1.5px solid #a7f3d0; color:#065f46; border-radius:20px; padding:0.2rem 0.75rem; font-size:0.75rem; font-weight:700;"><span style="width:7px; height:7px; background:#059669; border-radius:50%; flex-shrink:0;"></span> Completed</span>';
    }

    window.viewReceipt = function (referenceNo, member, type, amount, method, date, balance, note) {
        const existingModal = document.getElementById('receiptModal');
        if (existingModal) { existingModal.remove(); }

        currentSavingsReceipt = { referenceNo: referenceNo, member: member, type: type, amount: amount, method: method, date: date, balance: balance, note: note };

        const amountFmt = Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const balanceFmt = Number(balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const isDeposit = String(type).toLowerCase() === 'deposit';
        const isInterest = String(type).toLowerCase() === 'interest';

        const modal = document.createElement('div');
        modal.id = 'receiptModal';
        modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:999999; display:flex; align-items:flex-start; justify-content:center; padding:1.5rem 1rem; overflow-y:auto;';
        modal.innerHTML = `
            <div style="background:#fff; border-radius:20px; width:100%; max-width:420px; box-shadow:0 24px 60px rgba(0,0,0,0.18); margin:auto; animation:svRecIn 0.35s cubic-bezier(.22,1,.36,1) both;">
                <style>
                    @keyframes svRecIn {
                        from { opacity: 0; transform: translateY(28px) scale(0.97); }
                        to { opacity: 1; transform: translateY(0) scale(1); }
                    }
                    .sv-ghost-btn:hover { background:#f5f5f5; color:#333; }
                    .sv-receipt-dl:hover { opacity:0.88; }
                </style>
                <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e5e7eb;">
                    <div style="width:60px;height:60px;background:${SV_NAVY};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h2 style="color:#1a1a1a; font-size:1.25rem; font-weight:700; margin:0 0 0.25rem;">${isInterest ? 'Interest Credited Successful!' : (isDeposit ? 'Deposit Successful!' : 'Withdrawal Successful!')}</h2>
                    <p style="color:#6b7280; font-size:0.82rem; margin:0;">KMPCATS Cooperative -- Official Receipt</p>
                </div>
                <div style="padding:1.5rem;">
                    ${svReceiptRow('Organization', 'KMPCATS')}
                    ${svReceiptRow('Member', member)}
                    ${svReceiptRow('Transaction Type', '<strong>' + type + '</strong>')}
                    ${svReceiptRow('Amount', '&#8369;' + amountFmt, { highlight: true })}
                    ${svReceiptRow('Payment Method', method)}
                    ${svReceiptRow('Reference No.', svRefBadge(referenceNo))}
                    ${svReceiptRow('Date & Time', date)}
                    ${svReceiptRow('Balance After', '&#8369;' + balanceFmt)}
                    ${svReceiptRow('Note', note)}
                    ${svReceiptRow('Status', svStatusBadge())}
                </div>
                <div style="padding:0 1.5rem 1.5rem; display:flex; flex-direction:column; gap:0.6rem;">
                    <button data-action="downloadSavingsReceipt" id="receiptDownloadBtn" class="sv-receipt-dl" style="width:100%; padding:0.8rem; background:${SV_NAVY}; color:#fff; border:none; border-radius:12px; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:opacity .2s;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Receipt
                    </button>
                    <button data-action="remove-element" data-target="receiptModal" class="sv-ghost-btn" style="width:100%; padding:0.7rem; background:transparent; color:#888; border:1.5px solid #e8e8e8; border-radius:12px; font-size:0.88rem; font-weight:600; cursor:pointer; transition:background .2s,color .2s;">Close</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    };

    window.downloadSavingsReceipt = function () {
        const d = currentSavingsReceipt || {};
        const referenceNo = d.referenceNo || '';
        const member = d.member || '';
        const type = d.type || '';
        const amount = d.amount || '';
        const method = d.method || '';
        const date = d.date || '';
        const balance = d.balance || '';
        const note = d.note || 'N/A';

        const amountFmt = Number(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        const isDeposit = String(type).toLowerCase() === 'deposit';
        const isInterest = String(type).toLowerCase() === 'interest';

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position:fixed; left:-9999px; top:0; width:400px; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,0.15);';
        wrapper.innerHTML = `
            <div style="padding:1.5rem; text-align:center; border-bottom:1px solid #e8e8e8;">
                <div style="width:60px;height:60px;background:${SV_NAVY};border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div style="color:#1a1a1a;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">${isInterest ? 'Interest Credited Successful!' : (isDeposit ? 'Deposit Successful!' : 'Withdrawal Successful!')}</div>
                <div style="color:#6b7280;font-size:0.82rem;margin:0;">KMPCATS Cooperative -- Official Receipt</div>
            </div>
            <div style="padding:1.5rem;">
                ${svReceiptRow('Organization', 'KMPCATS')}
                ${svReceiptRow('Member', member)}
                ${svReceiptRow('Transaction Type', '<strong>' + type + '</strong>')}
                ${svReceiptRow('Amount', '&#8369;' + amountFmt, { highlight: true })}
                ${svReceiptRow('Payment Method', method)}
                ${svReceiptRow('Reference No.', svRefBadge(referenceNo))}
                ${svReceiptRow('Date & Time', date)}
                ${svReceiptRow('Status', svStatusBadge())}
            </div>
            <div style="padding:0.8rem 1.5rem 1.2rem;text-align:center;border-top:1px dashed #e8e8e8;">
                <div style="color:#aaa;font-size:0.72rem;">This receipt is system-generated and serves as official proof of your transaction.</div>
            </div>
        `;
        document.body.appendChild(wrapper);

        const doCapture = function () {
            html2canvas(wrapper, { scale: 2, useCORS: true, backgroundColor: null }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'KMPCATS_Savings_Receipt_' + referenceNo + '.png';
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

    // Get member balance via AJAX
    window.updateMemberBalance = function () {
        const memberId = document.getElementById('memberSelect').value;
        const balanceEl = document.getElementById('currentBalance');
        const mobileEl = document.getElementById('savingsMobileDisplay');

        if (!memberId) {
            balanceEl.textContent = '₱0.00';
            if (mobileEl) mobileEl.value = '';
            return;
        }

        fetch('/savings/admin/balance/' + memberId)
            .then(response => response.json())
            .then(data => {
                balanceEl.textContent = '₱' + parseFloat(data.balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (mobileEl) mobileEl.value = data.contact_no || '';
            })
            .catch(error => {
                console.error('Error fetching balance:', error);
                balanceEl.textContent = '₱0.00';
                if (mobileEl) mobileEl.value = '';
            });
    };

    // Toggle between Payment Method (deposit) and GCash Number (withdrawal)
    function toggleSavingsPaymentFields() {
        const type = document.getElementById('savingsType').value;
        const paymentField = document.getElementById('savingsPaymentField');
        const mobileField = document.getElementById('savingsMobileField');
        const qrDisplay = document.getElementById('savingsQrCodeDisplay');
        const paymentInput = document.getElementById('savingsPaymentMethod');
        if (!paymentField || !mobileField) return;

        if (type === 'withdrawal') {
            paymentField.classList.add('hidden');
            mobileField.classList.remove('hidden');
            if (qrDisplay) qrDisplay.classList.add('hidden');
            if (paymentInput) paymentInput.value = 'gcash';
        } else if (type === 'deposit') {
            paymentField.classList.remove('hidden');
            mobileField.classList.add('hidden');
            if (paymentInput) paymentInput.value = 'cash';
        } else {
            paymentField.classList.remove('hidden');
            mobileField.classList.add('hidden');
            if (qrDisplay) qrDisplay.classList.add('hidden');
            if (paymentInput) paymentInput.value = 'cash';
        }
    }

    function checkSavingsPaymentMethodQr() {
        const select = document.getElementById('savingsPaymentMethod');
        const qrDisplay = document.getElementById('savingsQrCodeDisplay');
        const qrImg = document.getElementById('savingsQrCodeImg');
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

    // Submit admin savings form
    window.submitAdminSavings = function () {
        const form = document.getElementById('adminSavingsForm');
        const formData = new FormData(form);

        if (!formData.get('member_id')) {
            showToast('Error', 'Please select a member');
            return;
        }
        if (!formData.get('amount') || parseFloat(formData.get('amount')) <= 0) {
            showToast('Error', 'Please enter a valid amount');
            return;
        }
        if (!formData.get('type')) {
            showToast('Error', 'Please select transaction type');
            return;
        }
        if (formData.get('type') === 'deposit' && !formData.get('payment_method')) {
            showToast('Error', 'Please select payment method');
            return;
        }

        fetch('/savings/admin/store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddContributionModal();
                    showToast('Success', data.message);
                    form.reset();
                    document.getElementById('currentBalance').textContent = '₱0.00';
                    document.getElementById('savingsMobileDisplay').value = '';
                    toggleSavingsPaymentFields();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message || 'Transaction failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
            });
    };

    // ============ Convert to Share Capital Functions ============
    window.openConvertToSCModal = function () {
        const modal = document.getElementById('convertToSCModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') {
            setTimeout(() => lucide.createIcons(), 50);
        }
        document.getElementById('convertMemberSelect').value = '';
        document.getElementById('convertSavingsBalance').textContent = '0.00';
        document.getElementById('convertSCBalance').textContent = '0.00';
        document.getElementById('convertAmount').value = '';
        document.getElementById('estimatedShares').textContent = '0';
        document.getElementById('convertRemainder').textContent = '₱0.00';
        convertIdempotencyKey = generateConvertKey();
    };

    window.closeConvertToSCModal = function () {
        document.getElementById('convertToSCModal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.updateConvertBalances = function () {
        const memberId = document.getElementById('convertMemberSelect').value;
        if (!memberId) {
            document.getElementById('convertSavingsBalance').textContent = '0.00';
            document.getElementById('convertSCBalance').textContent = '0.00';
            return;
        }
        fetch('/savings/admin/balance/' + memberId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('convertSavingsBalance').textContent = parseFloat(data.balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            });
        fetch('/savings/admin/sc-balance/' + memberId)
            .then(r => r.json())
            .then(data => {
                document.getElementById('convertSCBalance').textContent = parseFloat(data.balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            });
    };

    const scPerShareValue = {{ \App\Http\Controllers\ShareCapital::PAR_VALUE }};
    let convertIdempotencyKey = null;

    function generateConvertKey() {
        return Math.random().toString(36).slice(2, 10) + Math.random().toString(36).slice(2, 10);
    }

    window.setConvertAmount = function (val) {
        document.getElementById('convertAmount').value = val;
        updateConvertEstimate();
    };

    window.updateConvertEstimate = function () {
        const amount = parseFloat(document.getElementById('convertAmount').value) || 0;
        const shares = Math.floor(amount / scPerShareValue);
        const remainder = amount - (shares * scPerShareValue);
        document.getElementById('estimatedShares').textContent = shares;
        document.getElementById('convertRemainder').textContent = '₱' + remainder.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    document.getElementById('convertAmount')?.addEventListener('input', updateConvertEstimate);

    window.submitConvertToSC = function () {
        const form = document.getElementById('convertToSCForm');
        const formData = new FormData(form);
        const amount = parseFloat(formData.get('amount')) || 0;
        const shares = Math.floor(amount / scPerShareValue);

        if (!formData.get('member_id')) {
            showToast('Error', 'Please select a member');
            return;
        }
        if (amount <= 0) {
            showToast('Error', 'Please enter a valid amount');
            return;
        }
        if (shares < 1) {
            showToast('Error', 'Minimum conversion amount is ₱' + scPerShareValue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (1 share)');
            return;
        }

        formData.append('idempotency_key', convertIdempotencyKey);

        fetch('/savings/convert-to-share-capital', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeConvertToSCModal();
                    showToast('Success', data.message);
                    form.reset();
                    convertIdempotencyKey = null;
                    document.getElementById('convertSavingsBalance').textContent = '0.00';
                    document.getElementById('convertSCBalance').textContent = '0.00';
                    document.getElementById('estimatedShares').textContent = '0';
                    document.getElementById('convertRemainder').textContent = '₱0.00';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message || 'Conversion failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.');
            });
    };
</script>