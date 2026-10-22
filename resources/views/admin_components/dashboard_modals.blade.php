<!-- Pending Loans Modal -->
<div id="pendingLoansModal" class="modal-overlay hidden" style="display:none">
    <div class="modal max-w-2xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5 text-warning-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Pending Loan Applications</h2>
                        <p class="text-xs text-gray-500">{{ $pendingLoansCount }} application(s) awaiting review</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["pendingLoansModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[55vh] overflow-y-auto">
            @forelse($pendingLoansList as $item)
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl mb-3 border border-gray-100">
                <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-semibold text-warning-600">{{ $item['initials'] }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                    <p class="text-xs text-gray-500">₱{{ number_format($item['amount'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item['time'] }}</p>
                </div>
                <span class="badge badge-warning text-xs">Pending</span>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                <p>No pending loan applications</p>
            </div>
            @endforelse
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">Showing up to 10 latest applications</span>
            <div class="flex gap-3">
                <button data-action="closeModal" data-arg='["pendingLoansModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('lendings', ['filter' => 'pending']) }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    Go to Full Management Page
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Resignations Modal -->
<div id="resignationsModal" class="modal-overlay hidden" style="display:none">
    <div class="modal max-w-2xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-danger-100 flex items-center justify-center">
                        <i data-lucide="log-out" class="w-5 h-5 text-danger-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Pending Resignation Requests</h2>
                        <p class="text-xs text-gray-500">{{ $pendingResignationsCount }} request(s) awaiting admin review</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["resignationsModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[55vh] overflow-y-auto">
            @forelse($pendingResignationsList as $item)
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl mb-3 border border-gray-100">
                <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-semibold text-danger-600">{{ strtoupper(substr($item['name'], 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                    <p class="text-xs text-gray-500">{{ $item['withdraw'] ? 'Withdrawing share capital' : 'Not withdrawing share capital' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item['time'] }}</p>
                </div>
                <span class="badge badge-warning text-xs">Pending</span>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                <p>No pending resignation requests</p>
            </div>
            @endforelse
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">Showing up to 10 latest requests</span>
            <div class="flex gap-3">
                <button data-action="closeModal" data-arg='["resignationsModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('dashboard.members') }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    Go to Full Management Page
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Seminars Modal -->
<div id="seminarsModal" class="modal-overlay hidden" style="display:none">
    <div class="modal max-w-2xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center">
                        <i data-lucide="graduation-cap" class="w-5 h-5 text-sky-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Upcoming Seminars</h2>
                        <p class="text-xs text-gray-500">{{ $upcomingSeminarsCount }} upcoming seminar(s) scheduled</p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["seminarsModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[55vh] overflow-y-auto">
            @forelse($upcomingSeminars as $item)
            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl mb-3 border border-gray-100">
                <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="calendar" class="w-5 h-5 text-sky-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $item['type'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['schedule'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ ucfirst($item['delivery']) }} &middot; {{ $item['venue'] }}
                    </p>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <i data-lucide="calendar" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                <p>No upcoming seminars scheduled</p>
            </div>
            @endforelse
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">Showing next 5 upcoming seminars</span>
            <div class="flex gap-3">
                <button data-action="closeModal" data-arg='["seminarsModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('seminars.index') }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    Go to Full Management Page
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    function openPendingLoansModal() { openModal('pendingLoansModal'); }
    function openResignationsModal() { openModal('resignationsModal'); }
    function openSeminarsModal() { openModal('seminarsModal'); }
</script>

<!-- Loan Type Details Modal -->
<div id="loanTypeModal" class="modal-overlay hidden" style="display:none">
    <div class="modal max-w-3xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900" id="loanTypeModalTitle">Loans</h2>
                        <p class="text-xs text-gray-500" id="loanTypeModalSubtitle"></p>
                    </div>
                </div>
                <button data-action="closeModal" data-arg='["loanTypeModal"]' class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[55vh] overflow-y-auto">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="loanTypeTableBody">
                        <tr><td colspan="6" class="text-center py-8 text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-400" id="loanTypePagerInfo"></span>
            <div class="flex items-center gap-2">
                <button type="button" data-action="changeLoanTypePage" data-arg='[-1]'
                    class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all">&lt;</button>
                <span id="loanTypePagerPage" class="text-sm text-gray-600"></span>
                <button type="button" data-action="changeLoanTypePage" data-arg='[1]'
                    class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all">&gt;</button>
                <button data-action="closeModal" data-arg='["loanTypeModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
                <a href="{{ route('lendings') }}" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    Go to Loans Page
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
    window.loanTypeData = @json($loansByTypeDetails ?? []);
    let currentLoanType = null;
    let currentLoanTypePage = 1;
    const LOAN_TYPE_PAGE_SIZE = 10;

    window.openLoanTypeModal = function(type) {
        currentLoanType = type;
        currentLoanTypePage = 1;
        document.getElementById('loanTypeModalTitle').textContent = type + ' Loans';
        const total = (window.loanTypeData[type] || []).length;
        document.getElementById('loanTypeModalSubtitle').textContent = total + ' transacted loan(s)';
        renderLoanTypeTable();
        openModal('loanTypeModal');
        if (typeof lucide !== 'undefined') { setTimeout(() => lucide.createIcons(), 50); }
    };

    function renderLoanTypeTable() {
        const loans = (window.loanTypeData[currentLoanType] || []);
        const total = loans.length;
        const pages = Math.max(1, Math.ceil(total / LOAN_TYPE_PAGE_SIZE));
        if (currentLoanTypePage > pages) currentLoanTypePage = pages;
        const start = (currentLoanTypePage - 1) * LOAN_TYPE_PAGE_SIZE;
        const pageItems = loans.slice(start, start + LOAN_TYPE_PAGE_SIZE);

        const tbody = document.getElementById('loanTypeTableBody');
        if (pageItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">No loans transacted for this type</td></tr>';
        } else {
            tbody.innerHTML = pageItems.map(loan => {
                const user = loan.user || {};
                const initials = ((user.first_name || 'U')[0] + (user.last_name || '')[0]).toUpperCase();
                let statusBadge = '<span class="badge badge-warning">' + (loan.status || 'Pending') + '</span>';
                if (loan.status === 'Approved') statusBadge = '<span class="badge badge-success">On-going</span>';
                else if (loan.status === 'Completed') statusBadge = '<span class="badge badge-primary">Completed</span>';
                else if (loan.status === 'Declined') statusBadge = '<span class="badge badge-danger">Declined</span>';
                const date = loan.created_at ? new Date(loan.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
                return `<tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-primary-600">${initials}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">${(user.first_name || 'Unknown') + ' ' + (user.last_name || '')}</p>
                                <p class="text-xs text-gray-500">MEM-${String(loan.user_id || 0).padStart(3, '0')}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-600">${loan.reference_no || 'N/A'}</td>
                    <td class="text-sm font-semibold text-gray-900">₱${parseFloat(loan.lending_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 0 })}</td>
                    <td class="text-sm text-gray-600">${loan.lending_type_term || 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td class="text-sm text-gray-600">${date}</td>
                </tr>`;
            }).join('');
        }

        document.getElementById('loanTypePagerInfo').textContent =
            (total === 0 ? 'No loans' : 'Showing ' + (start + 1) + '-' + Math.min(start + LOAN_TYPE_PAGE_SIZE, total) + ' of ' + total);
        document.getElementById('loanTypePagerPage').textContent = 'Page ' + currentLoanTypePage + ' of ' + pages;
    }

    window.changeLoanTypePage = function(dir) {
        const loans = (window.loanTypeData[currentLoanType] || []);
        const pages = Math.max(1, Math.ceil(loans.length / LOAN_TYPE_PAGE_SIZE));
        const next = currentLoanTypePage + dir;
        if (next < 1 || next > pages) return;
        currentLoanTypePage = next;
        renderLoanTypeTable();
    };

    document.addEventListener('click', function(e) {
        const row = e.target.closest('.js-open-loan-type');
        if (row && row.dataset.loanType) openLoanTypeModal(row.dataset.loanType);
    });
</script>
