<div class="card">
    <div class="p-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-900">Member Dividends — {{ $year }}</h3>
        <p class="text-sm text-gray-500">{{ $dividends->total() }} members</p>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Share Capital</th>
                    <th>Recommended</th>
                    <th>Approved Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dividends as $dividend)
                <tr id="dividend-row-{{ $dividend->id }}">
                    <td class="text-sm text-gray-500">{{ $dividend->id }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-xs text-primary-600 font-medium">
                                    {{ strtoupper(substr($dividend->user->first_name ?? 'U', 0, 1) . substr($dividend->user->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-900">
                                {{ $dividend->user->first_name ?? 'Unknown' }} {{ $dividend->user->last_name ?? '' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-sm font-medium text-gray-900">₱{{ number_format($dividend->share_capital_amount, 2) }}</td>
                    <td class="text-sm text-gray-900">₱{{ number_format($dividend->recommended_amount, 2) }}</td>
                    <td>
                        @if($dividend->status === 'pending')
                            <div class="flex items-center gap-1">
                                <span class="text-sm text-gray-500">₱</span>
                                <input type="number" step="0.01" min="0"
                                    value="{{ $dividend->approved_amount }}"
                                    class="w-28 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                    id="amount-input-{{ $dividend->id }}"
                                    data-action="updateDividendAmount" data-arg='[{{ $dividend->id }},"|value|"]'>
                            </div>
                        @else
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($dividend->approved_amount, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($dividend->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($dividend->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($dividend->status === 'disbursed')
                            <span class="badge badge-primary">Disbursed</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            @if($dividend->status === 'pending')
                                <button data-action="approveDividend" data-arg='[{{ $dividend->id }}]'
                                    class="btn btn-sm btn-warning"
                                    title="Approve">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Approve
                                </button>
                            @elseif($dividend->status === 'approved')
                                <button data-action="disburseDividend" data-arg='[{{ $dividend->id }}]'
                                    class="btn btn-sm btn-success"
                                    title="Disburse">
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    Disburse
                                </button>
                            @elseif($dividend->status === 'disbursed')
                                <span class="badge badge-primary">Disbursed</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <div class="flex flex-col items-center text-gray-500">
                            <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-50"></i>
                            <p>No dividend records found for {{ $year }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dividends->isNotEmpty())
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
            <div>
                <span class="text-gray-500">Total Share Capital</span>
                <p class="font-bold text-gray-900">₱{{ number_format($totalSumShareCapital, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Total Recommended</span>
                <p class="font-bold text-gray-900">₱{{ number_format($totalSumRecommended, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Total Approved</span>
                <p class="font-bold text-primary-700">₱{{ number_format($totalSumApproved, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Members</span>
                <p class="font-bold text-gray-900">{{ $dividends->total() }}</p>
            </div>
        </div>

        @if($dividends->hasPages())
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Showing {{ $dividends->firstItem() }} to {{ $dividends->lastItem() }} of {{ $dividends->total() }} members
            </p>
            <div class="flex items-center gap-1">
                @if($dividends->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <button class="js-dividends-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $dividends->previousPageUrl() }}">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @endif

                @foreach($dividends->getUrlRange(max(1, $dividends->currentPage() - 2), min($dividends->lastPage(), $dividends->currentPage() + 2)) as $page => $url)
                    @if($page == $dividends->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <button class="js-dividends-page px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $url }}">{{ $page }}</button>
                    @endif
                @endforeach

                @if($dividends->hasMorePages())
                    <button class="js-dividends-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $dividends->nextPageUrl() }}">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @else
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
