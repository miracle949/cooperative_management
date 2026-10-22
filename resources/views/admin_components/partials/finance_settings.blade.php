{{--
Finance Settings panel for the Settings page.
Moved from the Finance page (financial_activity.blade.php) so it lives under Settings.
--}}
<div id="tab-finance" class="space-y-6 tab-content hidden">

    <div class="card p-6" id="loan-interest-rates">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Loan Charges</h2>
                <p class="text-sm text-gray-500">Configure the interest and fee rates applied to <strong>all loan types</strong>. Fees are a percentage (%) of the principal; loan protection is a flat ₱ amount per month of term. Changes apply to new loan applications.</p>
            </div>
            <button type="button" data-action="openManageLoanTypeModal" class="btn btn-outline">Manage Loan Type</button>
        </div>

        @php
            $globalLoanSetting = $loanSettingsList->first() ?? (object) [
                'interest_rate' => 2, 'processing_fee_rate' => 2,
                'service_fee_rate' => 2, 'loan_protection_fee' => 2,
                'retention_paid_rate' => 3, 'retention_unpaid_rate' => 6,
            ];
        @endphp

        <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (% per month)</label>
                    <input type="number" name="global_interest" class="input" step="0.1" min="0" max="100" value="{{ $globalLoanSetting->interest_rate ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Processing Fee (%)</label>
                    <input type="number" name="global_processing" class="input" step="0.1" min="0" max="100" value="{{ $globalLoanSetting->processing_fee_rate ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service &amp; Legal Fee (%)</label>
                    <input type="number" name="global_service" class="input" step="0.1" min="0" max="100" value="{{ $globalLoanSetting->service_fee_rate ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loan Protection (₱ per month)</label>
                    <input type="number" name="global_protection" class="input" step="0.1" min="0" value="{{ $globalLoanSetting->loan_protection_fee ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Retention — Paid Up (%)</label>
                    <input type="number" name="global_retention_paid" class="input" step="0.1" min="0" max="100" value="{{ $globalLoanSetting->retention_paid_rate ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Retention — Not Paid Up (%)</label>
                    <input type="number" name="global_retention_unpaid" class="input" step="0.1" min="0" max="100" value="{{ $globalLoanSetting->retention_unpaid_rate ?? 0 }}">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Late Fee Penalty Settings</h3>
            <p class="text-sm text-gray-500">Configure penalty for overdue loans</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('loan.settings.update') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Late Fee (%)</label>
                    <input type="number" name="late_fee_percentage" step="0.01" min="0" max="100"
                        value="{{ $lateFeePercentage ?? 2.00 }}"
                        class="input" style="width: 120px;" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grace Period (months)</label>
                    <input type="number" name="grace_period_months" step="1" min="0" max="12"
                        value="{{ $gracePeriodMonths ?? 1 }}"
                        class="input" style="width: 120px;" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update
                </button>
            </form>
        </div>
    </div>

    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Dividend & Patronage Refund Percentages</h3>
            <p class="text-sm text-gray-500">Set how the remaining surplus is split between the Dividend Fund and the Patronage Refund Pool</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="dividend_year" class="select" style="width: 120px;">
                        @for($y = $currentYear; $y >= $currentYear - 10; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                        @foreach($years as $y)
                            @if($y < $currentYear - 10)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dividend Fund (%)</label>
                    <input type="number" name="dividend_fund_percentage" id="finDividendPct" step="0.01" min="1" max="99"
                        value="{{ $dividendFundPercentage ?? 60.00 }}"
                        class="input" style="width: 120px;" data-action="syncFinFundPcts" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patronage Refund Fund (%)</label>
                    <input type="number" name="patronage_fund_percentage" id="finPatronagePct" step="0.01" min="1" max="99"
                        value="{{ $patronageFundPercentage ?? 40.00 }}"
                        class="input" style="width: 120px;" data-action="syncFinFundPcts" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2">The two percentages must total 100%. Changing them updates the pool — regenerate patronage refunds afterwards to apply the new pool to member refunds.</p>
        </div>
    </div>

    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Statutory Fund Allocation Percentages</h3>
            <p class="text-sm text-gray-500">Sets how the annual net surplus is allocated to statutory and optional funds before the remaining surplus is distributed to the Dividend Fund and Patronage Refund Pool</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 items-end gap-4" id="statutoryForm" data-action="validateStatutoryAllocation">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 min-h-10">Year</label>
                    <select name="statutory_year" class="select h-10">
                        @for($y = $currentYear; $y >= $currentYear - 10; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                        @foreach($years as $y)
                            @if($y < $currentYear - 10)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 min-h-10">Reserve Fund (%)</label>
                    <input type="number" name="reserve_fund_percentage" id="statReserve" step="0.01" min="0" max="100"
                        value="{{ $reserveFundPercentage ?? 10.00 }}"
                        class="input h-10" data-action="updateStatutoryLive" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 min-h-10">CETF / Education Fund (%)</label>
                    <input type="number" name="cetf_percentage" id="statCETF" step="0.01" min="0" max="100"
                        value="{{ $cetfPercentage ?? 10.00 }}"
                        class="input h-10" data-action="updateStatutoryLive" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 min-h-10">Community Dev. Fund (%)</label>
                    <input type="number" name="cdf_percentage" id="statCDF" step="0.01" min="0" max="100"
                        value="{{ $cdfPercentage ?? 3.00 }}"
                        class="input h-10" data-action="updateStatutoryLive" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 min-h-10">Optional Fund (%)</label>
                    <input type="number" name="optional_fund_percentage" id="statOptional" step="0.01" min="0" max="100"
                        value="{{ $optionalFundPercentage ?? 7.00 }}"
                        class="input h-10" data-action="updateStatutoryLive" required>
                </div>
                <button type="submit" class="btn btn-primary h-10 w-full justify-center whitespace-nowrap" id="statutorySaveBtn">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update
                </button>
            </form>
            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
                <span class="text-gray-500">
                    Total Statutory Allocation:
                    <span id="statTotalDisplay" class="font-semibold text-gray-900">{{ $statutoryTotalPercentage ?? 30 }}%</span>
                </span>
                <span class="text-gray-500">
                    Remaining Surplus:
                    <span id="statRemainingDisplay" class="font-semibold text-success-700">{{ $remainingSurplusPercentage ?? 70 }}%</span>
                </span>
                <span id="statErrorText" class="hidden text-sm font-medium text-red-600">Statutory fund allocations cannot exceed 100% of net surplus.</span>
            </div>
            <p class="text-xs text-gray-400 mt-3">These settings apply to newly generated annual distributions. Existing distributions keep the percentages used when they were generated.</p>
        </div>
    </div>

    <!-- Card: Savings Interest Settings -->
    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Savings Interest Settings</h3>
            <p class="text-sm text-gray-500">Configure automatic interest release for Regular Savings</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Annual Interest Rate (%)</label>
                        <input type="number" name="annual_rate" step="0.01" min="0" max="100"
                            value="{{ old('annual_rate', $savingsInterestSettings->annual_rate) }}"
                            class="input h-10" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Release Frequency</label>
                        <select name="release_frequency" class="select h-10" required>
                            <option value="monthly" {{ $savingsInterestSettings->release_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ $savingsInterestSettings->release_frequency === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="semi-annual" {{ $savingsInterestSettings->release_frequency === 'semi-annual' ? 'selected' : '' }}>Semi-Annual</option>
                            <option value="annual" {{ $savingsInterestSettings->release_frequency === 'annual' ? 'selected' : '' }}>Annual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Balance for Interest (₱)</label>
                        <input type="number" name="min_balance_for_interest" step="0.01" min="0"
                            value="{{ old('min_balance_for_interest', $savingsInterestSettings->min_balance_for_interest) }}"
                            class="input h-10" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maintaining Balance (₱)</label>
                        <input type="number" name="maintaining_balance" step="0.01" min="0"
                            value="{{ old('maintaining_balance', $savingsInterestSettings->maintaining_balance) }}"
                            class="input h-10" required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary h-10 px-4">Save Settings</button>
                </div>
            </form>
            <p class="text-xs text-gray-400 mt-3">Interest is released automatically by the scheduler when each period ends. Accounts with balances below the minimum or maintaining balance are excluded.</p>
        </div>
    </div>

    <!-- Card: Loan Eligibility Settings -->
    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Loan Eligibility Settings</h3>
            <p class="text-sm text-gray-500">Configure savings requirement for loan applications</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="savings_to_loan_enabled" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="savings_to_loan_enabled" value="1"
                                {{ $loanEligibilitySettings->savings_to_loan_enabled ? 'checked' : '' }}
                                class="sr-only peer" data-action="toggle-ratio-field">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700">Enable Savings Holdback Requirement</span>
                    </div>
                    <div id="ratioField" class="{{ !$loanEligibilitySettings->savings_to_loan_enabled ? 'hidden' : '' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Savings Holdback (₱)</label>
                        <input type="number" name="savings_to_loan_ratio" step="0.01" min="0" max="100000"
                            value="{{ old('savings_to_loan_ratio', $loanEligibilitySettings->savings_to_loan_ratio) }}"
                            class="input h-10">
                        <p class="text-xs text-gray-400 mt-1">Max loan = savings − holdback. Savings must remain ≥ holdback after borrowing.</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary h-10 px-4">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card: Share Capital Eligibility Settings -->
    <div class="card p-6">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Share Capital Eligibility Settings</h3>
            <p class="text-sm text-gray-500">Set the minimum paid-up share capital members need to apply for loans</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('settings', ['tab' => 'finance']) }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Share Capital (shares)</label>
                        <input type="number" name="minimum_shares" step="0.01" min="0"
                            value="{{ old('minimum_shares', $loanEligibilitySettings->minimum_shares) }}"
                            class="input h-10">
                        <p class="text-xs text-gray-400 mt-1">Member needs this many paid-up shares to apply for a loan</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary h-10 px-4">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Methods Management -->
    <div id="payment-methods" class="card p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Payment Methods Management</h2>
                <p class="text-sm text-gray-500">Manage payment methods and QR codes for member payments</p>
            </div>
            <button type="button" data-action="openPaymentMethodModal" class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Payment Method
            </button>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Method Name</th>
                        <th>QR Code</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payment-methods-table-body">
                    @forelse($paymentMethods as $pm)
                    <tr id="pm-row-{{ $pm->id }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <i data-lucide="credit-card" class="w-4 h-4 text-primary-600"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $pm->method_name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($pm->has_qr_code && $pm->qr_code_image_path)
                                <img src="{{ url('/proof/' . ltrim($pm->qr_code_image_path, '/')) }}" alt="QR Code" class="w-10 h-10 rounded-lg border border-gray-200 object-cover">
                            @elseif(!empty($pm->account_number))
                                <div class="text-xs text-gray-600">
                                    <div class="font-medium">{{ $pm->account_name ?: 'Account' }}</div>
                                    <div>{{ $pm->account_number }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">No QR / account details</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" data-action="togglePaymentMethod" data-arg='[{{ $pm->id }}]' class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" {{ $pm->is_active ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                            </button>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button class="js-edit-payment-method px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors"
                                    data-pm-id="{{ $pm->id }}"
                                    data-pm-name="{{ $pm->method_name }}"
                                    data-pm-has-qr="{{ $pm->has_qr_code ? 'true' : 'false' }}"
                                    data-pm-account-name="{{ $pm->account_name }}"
                                    data-pm-account-number="{{ $pm->account_number }}"
                                    data-pm-instructions="{{ $pm->instructions }}">
                                    Edit
                                </button>
                                <button class="js-delete-payment-method px-3 py-1.5 text-xs font-medium text-danger-600 bg-danger-50 rounded-lg hover:bg-danger-100 transition-colors"
                                    data-pm-id="{{ $pm->id }}"
                                    data-pm-name="{{ $pm->method_name }}">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="pm-empty-row">
                        <td colspan="4" class="text-center py-6 text-gray-500">No payment methods configured</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Payment Method Modal -->
    <div id="paymentMethodModal" class="modal-overlay hidden" style="display:none">
        <div class="modal max-w-lg">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="credit-card" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 id="pmModalTitle" class="text-xl font-bold text-gray-900">Add Payment Method</h2>
                            <p class="text-xs text-gray-500">Configure a payment method for member transactions</p>
                        </div>
                    </div>
                    <button type="button" data-action="closePaymentMethodModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <form id="paymentMethodForm" enctype="multipart/form-data" class="p-6 flex-1 flex flex-col">
                @csrf
                <input type="hidden" id="pm_edit_id" value="">

                <div class="space-y-4 flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method Name <span class="text-red-500">*</span></label>
                        <input type="text" name="method_name" id="pm_method_name" class="input" placeholder="e.g. GCash, Cash, Bank Transfer" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requires QR Code?</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_qr_code" id="pm_has_qr" value="1" class="sr-only peer" data-action="toggleQrUpload">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Yes, this method requires a QR code</span>
                        </label>
                    </div>

                    <div id="pm-qr-upload" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">QR Code Image</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-400 transition-colors">
                            <input type="file" name="qr_code_image" id="pm_qr_image" accept="image/png,image/jpeg,image/jpg" class="hidden" data-action="previewQrImage" data-arg='["|el|"]'>
                            <label for="pm_qr_image" class="cursor-pointer">
                                <div id="pm-qr-preview" class="hidden mb-3">
                                    <img id="pm-qr-preview-img" class="w-32 h-32 mx-auto rounded-lg object-cover border border-gray-200">
                                </div>
                                <div id="pm-qr-placeholder">
                                    <i data-lucide="upload" class="w-10 h-10 mx-auto text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500">Click to upload QR code image</p>
                                    <p class="text-xs text-gray-400">PNG, JPG (max 2MB)</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div id="pm-account-details" class="space-y-4 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500">For methods without a QR code (e.g. bank transfer), enter the account members should pay to.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="account_name" id="pm_account_name" class="input" placeholder="e.g. KPMPCATS Cooperative">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="pm_account_number" class="input" placeholder="e.g. 1234 5678 9012">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instructions <span class="text-gray-400">(optional)</span></label>
                            <textarea name="instructions" id="pm_instructions" class="input" rows="2" placeholder="e.g. Send a screenshot after paying"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" data-action="closePaymentMethodModal" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span id="pmSubmitText">Save Payment Method</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Loan Type Modal -->
    <div id="manageLoanTypeModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="settings" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Manage Loan Type</h2>
                            <p class="text-xs text-gray-500">Add new loan types or remove existing ones</p>
                        </div>
                    </div>
                    <button type="button" data-action="closeManageLoanTypeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Existing Loan Types</label>
                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($loanSettingsList as $setting)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $setting->loan_type }}</span>
                            </div>
                            <button type="button" data-id="{{ $setting->id }}" data-name="{{ $setting->loan_type }}" data-action="deleteLoanType" data-arg='["|el|"]' class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50 transition-colors" title="Delete loan type">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">No loan types found</p>
                        @endforelse
                    </div>
                </div>

                <form method="POST" action="{{ route('loan.settings.create') }}">
                    @csrf
                    <div class="border-t border-gray-100 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Add New Loan Type</label>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Loan Type Name</label>
                                <input type="text" name="loan_type" class="input" placeholder="e.g. Housing Loan" required>
                            </div>
                            <p class="text-xs text-gray-400">New loan types use the global loan charges configured on the Loan Charges card.</p>
                        </div>
                        <div class="flex justify-end gap-3 mt-4">
                            <button type="button" data-action="closeManageLoanTypeModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                            <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i> Add Loan Type
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script nonce="{{ csp_nonce() }}">
    // Event-binding glue (CSP delegation hooks)
    (function () {
        var A = window.CSP_actions;
        if (!A) return;
        A.register('toggle-ratio-field', function (e, el) {
            document.getElementById('ratioField').classList.toggle('hidden', !el.checked);
        });
    })();

    // Payment Methods Management
    function openPaymentMethodModal() {
        document.getElementById('pmModalTitle').textContent = 'Add Payment Method';
        document.getElementById('pmSubmitText').textContent = 'Save Payment Method';
        document.getElementById('pm_edit_id').value = '';
        document.getElementById('pm_method_name').value = '';
        document.getElementById('pm_has_qr').checked = false;
        document.getElementById('pm_qr_image').value = '';
        document.getElementById('pm-qr-upload').classList.add('hidden');
        document.getElementById('pm-qr-preview').classList.add('hidden');
        document.getElementById('pm-qr-placeholder').classList.remove('hidden');
        document.getElementById('paymentMethodModal').classList.remove('hidden');
        document.getElementById('paymentMethodModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.getElementById('pm_account_name').value = '';
        document.getElementById('pm_account_number').value = '';
        document.getElementById('pm_instructions').value = '';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closePaymentMethodModal() {
        document.getElementById('paymentMethodModal').classList.add('hidden');
        document.getElementById('paymentMethodModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function toggleQrUpload() {
        const checked = document.getElementById('pm_has_qr').checked;
        document.getElementById('pm-qr-upload').classList.toggle('hidden', !checked);
    }

    function previewQrImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('pm-qr-preview-img').src = e.target.result;
                document.getElementById('pm-qr-preview').classList.remove('hidden');
                document.getElementById('pm-qr-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function editPaymentMethod(id, name, hasQr, accName, accNumber, instructions) {
        document.getElementById('pmModalTitle').textContent = 'Edit Payment Method';
        document.getElementById('pmSubmitText').textContent = 'Update Payment Method';
        document.getElementById('pm_edit_id').value = id;
        document.getElementById('pm_method_name').value = name;
        document.getElementById('pm_has_qr').checked = hasQr;
        document.getElementById('pm-qr-upload').classList.toggle('hidden', !hasQr);
        document.getElementById('pm-qr-preview').classList.add('hidden');
        document.getElementById('pm-qr-placeholder').classList.remove('hidden');
        document.getElementById('pm_qr_image').value = '';
        document.getElementById('pm_account_name').value = accName || '';
        document.getElementById('pm_account_number').value = accNumber || '';
        document.getElementById('pm_instructions').value = instructions || '';
        document.getElementById('paymentMethodModal').classList.remove('hidden');
        document.getElementById('paymentMethodModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    
    document.querySelectorAll('.js-edit-payment-method').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editPaymentMethod(
                Number(this.dataset.pmId), this.dataset.pmName, this.dataset.pmHasQr === 'true',
                this.dataset.pmAccountName, this.dataset.pmAccountNumber, this.dataset.pmInstructions
            );
        });
    });

    document.querySelectorAll('.js-edit-payment-method').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editPaymentMethod(Number(this.dataset.pmId), this.dataset.pmName, this.dataset.pmHasQr === 'true');
        });
    });

    document.querySelectorAll('.js-delete-payment-method').forEach(function(btn) {
        btn.addEventListener('click', function() {
            deletePaymentMethod(Number(this.dataset.pmId), this.dataset.pmName);
        });
    });

    document.getElementById('paymentMethodForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const editId = document.getElementById('pm_edit_id').value;
        const formData = new FormData(form);
        const url = editId ? '/admin/payment-methods/' + editId : '/admin/payment-methods';

        if (editId) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                closePaymentMethodModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', data.message || 'Failed to save payment method', 'error');
            }
        })
        .catch(() => showToast('Error', 'An error occurred', 'error'));
    });

    function togglePaymentMethod(id) {
        fetch('/admin/payment-methods/' + id + '/toggle', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(() => showToast('Error', 'Failed to update status', 'error'));
    }

    function deletePaymentMethod(id, name) {
        if (!confirm('Delete payment method "' + name + '"?')) return;

        fetch('/admin/payment-methods/' + id, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                const row = document.getElementById('pm-row-' + id);
                if (row) row.remove();
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(() => showToast('Error', 'Failed to delete payment method', 'error'));
    }

    // ─── Manage Loan Type Modal ─────────────────────────────────────────
    function openManageLoanTypeModal() {
        document.getElementById('manageLoanTypeModal').classList.remove('hidden');
        document.getElementById('manageLoanTypeModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeManageLoanTypeModal() {
        document.getElementById('manageLoanTypeModal').classList.add('hidden');
        document.getElementById('manageLoanTypeModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function deleteLoanType(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        if (!confirm('Delete the "' + name + '" loan type?')) return;

        const url = '{{ route("loan.settings.delete", "ltid") }}'.replace('ltid', id);
        fetch(url, {
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
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast('Error', data.message || 'Delete failed', 'error');
            }
        })
        .catch(() => {
            showToast('Error', 'Something went wrong. Please try again.', 'error');
        });
    }

    // Keep the Dividend/Patronage fund percentages summing to 100
    function syncFinFundPcts() {
        const dEl = document.getElementById('finDividendPct');
        const pEl = document.getElementById('finPatronagePct');
        if (!dEl || !pEl) return;
        const dVal = parseFloat(dEl.value);
        const pVal = parseFloat(pEl.value);
        if (!isNaN(dVal) && isNaN(pVal)) {
            pEl.value = Math.max(1, Math.min(99, Math.round((100 - dVal) * 100) / 100));
        } else if (isNaN(dVal) && !isNaN(pVal)) {
            dEl.value = Math.max(1, Math.min(99, Math.round((100 - pVal) * 100) / 100));
        }
    }

    // Live total + remaining surplus for the statutory allocation card
    function updateStatutoryLive() {
        const ids = ['statReserve', 'statCETF', 'statCDF', 'statOptional'];
        const totalEl = document.getElementById('statTotalDisplay');
        const remainingEl = document.getElementById('statRemainingDisplay');
        const errorEl = document.getElementById('statErrorText');
        const saveBtn = document.getElementById('statutorySaveBtn');
        if (!totalEl || !remainingEl || !errorEl || !saveBtn) return;

        let total = 0;
        let invalid = false;
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            const val = parseFloat(el.value);
            if (isNaN(val) || val < 0 || val > 100) invalid = true;
            else total += val;
        });

        total = Math.round(total * 100) / 100;
        totalEl.textContent = total + '%';
        remainingEl.textContent = Math.max(0, Math.round((100 - total) * 100) / 100) + '%';

        const over = total > 100 || invalid;
        errorEl.classList.toggle('hidden', !over);
        saveBtn.classList.toggle('opacity-50', over);
        saveBtn.classList.toggle('pointer-events-none', over);
    }

    function validateStatutoryAllocation() {
        const errorEl = document.getElementById('statErrorText');
        if (errorEl && !errorEl.classList.contains('hidden')) {
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateStatutoryLive();
    });
</script>
