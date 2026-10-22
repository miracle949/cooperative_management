<div class="card">
    <div class="p-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-900">Patronage Refunds — {{ $year }}</h3>
        <p class="text-sm text-gray-500">{{ $patronageDistributions->total() }} members · Click a member name to view breakdown</p>
    </div>

    @if(isset($distribution) && $distribution)
    <div class="p-4 border-b border-gray-100 bg-primary-50/50">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Annual Distribution Formula</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500 block">Patronage Fund Rate</span>
                <p class="font-bold text-gray-900">{{ number_format($patronageFundPercentage, 2) }}%</p>
                <p class="text-xs text-gray-400">of remaining surplus</p>
            </div>
            <div>
                <span class="text-gray-500 block">Patronage Refund Pool</span>
                <p class="font-bold text-warning-700">₱{{ number_format($distribution->patronage_refund_pool, 2) }}</p>
                <p class="text-xs text-gray-400">remaining surplus × {{ number_format($patronageFundPercentage, 2) }}%</p>
            </div>
            <div>
                <span class="text-gray-500 block">Total Cooperative Patronage</span>
                <p class="font-bold text-gray-900">₱{{ number_format($totalSumPatronage, 2) }}</p>
                <p class="text-xs text-gray-400">sum of all member patronage</p>
            </div>
            <div>
                <span class="text-gray-500 block">Patronage Basis</span>
                <p class="font-bold text-gray-900">{{ $patronageBasis === 'net_repayment' ? 'Net Repayment' : 'Total Repayment' }}</p>
                <p class="text-xs text-gray-400">{{ $patronageBasis === 'net_repayment' ? 'excludes late fees' : 'includes late fees' }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Total Patronage</th>
                    <th>Member Share</th>
                    <th>Refund Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patronageDistributions as $record)
                <tr id="patronage-row-{{ $record->id }}">
                    <td class="text-sm text-gray-500">{{ $record->id }}</td>
                    <td>
                        <div class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 rounded-lg px-2 py-1 -mx-2 -my-1 transition-colors" data-action="openPatronageBreakdown" data-arg='[{{ $record->id }}]'>
                            <div class="w-8 h-8 rounded-full bg-warning-100 flex items-center justify-center">
                                <span class="text-xs text-warning-600 font-medium">
                                    {{ strtoupper(substr($record->user->first_name ?? 'U', 0, 1) . substr($record->user->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-900 font-medium">
                                    {{ $record->user->first_name ?? 'Unknown' }} {{ $record->user->last_name ?? '' }}
                                </span>
                                <p class="text-xs text-primary-600">View breakdown</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm font-medium text-gray-900">₱{{ number_format($record->total_patronage, 2) }}</td>
                    <td class="text-sm text-gray-900">{{ number_format($record->allocation_ratio * 100, 2) }}%</td>
                    <td>
                        @if($record->status === 'pending')
                            <div class="flex items-center gap-1">
                                <span class="text-sm text-gray-500">₱</span>
                                <input type="number" step="0.01" min="0"
                                    value="{{ $record->amount }}"
                                    class="w-28 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:border-primary-600 focus:ring-1 focus:ring-primary-600"
                                    id="patronage-amount-input-{{ $record->id }}"
                                    data-action="updatePatronageAmount" data-arg='[{{ $record->id }},"|value|"]'>
                            </div>
                        @else
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($record->amount, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($record->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($record->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($record->status === 'disbursed')
                            <span class="badge badge-primary">Disbursed</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            @if($record->status === 'pending')
                                <button data-action="approvePatronageRefund" data-arg='[{{ $record->id }}]'
                                    class="btn btn-sm btn-warning" title="Approve">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Approve
                                </button>
                            @elseif($record->status === 'approved')
                                <button data-action="disbursePatronageRefund" data-arg='[{{ $record->id }}]'
                                    class="btn btn-sm btn-success" title="Disburse">
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    Disburse
                                </button>
                            @elseif($record->status === 'disbursed')
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
                            <p>No patronage refund records for {{ $year }}</p>
                            <p class="text-xs text-gray-400 mt-1">Generate patronage refund allocations after calculating dividends</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($patronageDistributions->isNotEmpty())
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-4">
            <div>
                <span class="text-gray-500">Total Cooperative Patronage</span>
                <p class="font-bold text-gray-900">₱{{ number_format($totalSumPatronage, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Total Approved</span>
                <p class="font-bold text-warning-700">₱{{ number_format($totalSumPatronageApproved, 2) }}</p>
            </div>
            <div>
                <span class="text-gray-500">Members</span>
                <p class="font-bold text-gray-900">{{ $patronageDistributions->total() }}</p>
            </div>
        </div>

        @if($patronageDistributions->hasPages())
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Showing {{ $patronageDistributions->firstItem() }} to {{ $patronageDistributions->lastItem() }} of {{ $patronageDistributions->total() }} members
            </p>
            <div class="flex items-center gap-1">
                @if($patronageDistributions->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <button class="js-patronage-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $patronageDistributions->previousPageUrl() }}">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @endif

                @foreach($patronageDistributions->getUrlRange(max(1, $patronageDistributions->currentPage() - 2), min($patronageDistributions->lastPage(), $patronageDistributions->currentPage() + 2)) as $page => $url)
                    @if($page == $patronageDistributions->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <button class="js-patronage-page px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $url }}">{{ $page }}</button>
                    @endif
                @endforeach

                @if($patronageDistributions->hasMorePages())
                    <button class="js-patronage-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $patronageDistributions->nextPageUrl() }}">
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
