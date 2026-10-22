<div class="card">
    <div class="p-4 border-b border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Additional Patronage Records — {{ $year }}</h3>
                <p class="text-sm text-gray-500">{{ $recordCount }} record(s) · Total: ₱{{ number_format($totalAmount, 2) }}</p>
            </div>
            <button data-action="openAddPatronageRecordModal" class="btn btn-primary btn-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Record
            </button>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Source</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Recorded By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr id="patronage-record-row-{{ $record->id }}">
                    <td class="text-sm text-gray-500">{{ $record->id }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-warning-100 flex items-center justify-center">
                                <span class="text-xs text-warning-600 font-medium">
                                    {{ strtoupper(substr($record->user->first_name ?? 'U', 0, 1) . substr($record->user->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-900">
                                {{ $record->user->first_name ?? 'Unknown' }} {{ $record->user->last_name ?? '' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-sm text-gray-900">{{ $record->source }}</td>
                    <td class="text-sm text-gray-500">{{ $record->description ?: '—' }}</td>
                    <td class="text-sm font-semibold text-gray-900">₱{{ number_format($record->amount, 2) }}</td>
                    <td class="text-sm text-gray-500">{{ $record->recorder->first_name ?? 'System' }} {{ $record->recorder->last_name ?? '' }}</td>
                    <td class="text-sm text-gray-500">{{ $record->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button class="js-edit-patronage-record p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Edit"
                                data-record-id="{{ $record->id }}"
                                data-record-source="{{ $record->source }}"
                                data-record-description="{{ $record->description ?? '' }}"
                                data-record-amount="{{ $record->amount }}">
                                <i data-lucide="pencil" class="w-4 h-4 text-gray-500"></i>
                            </button>
                            <button data-action="deletePatronageRecord" data-arg='[{{ $record->id }}]'
                                class="p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-8">
                        <div class="flex flex-col items-center text-gray-500">
                            <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-50"></i>
                            <p>No patronage records found for {{ $year }}</p>
                            <p class="text-xs text-gray-400 mt-1">Add records for services outside the lending system (gas, rice, oil, etc.)</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} records
            </p>
            <div class="flex items-center gap-1">
                @if($records->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <button class="js-patronage-records-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $records->previousPageUrl() }}">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @endif

                @foreach($records->getUrlRange(max(1, $records->currentPage() - 2), min($records->lastPage(), $records->currentPage() + 2)) as $page => $url)
                    @if($page == $records->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <button class="js-patronage-records-page px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $url }}">{{ $page }}</button>
                    @endif
                @endforeach

                @if($records->hasMorePages())
                    <button class="js-patronage-records-page p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" data-page-url="{{ $records->nextPageUrl() }}">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @else
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Add/Edit Patronage Record Modal -->
<div id="patronageRecordModal" class="modal-overlay hidden">
    <div class="modal max-w-lg">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center">
                        <i data-lucide="file-plus" class="w-5 h-5 text-warning-600"></i>
                    </div>
                    <div>
                        <h2 id="patronageModalTitle" class="text-xl font-bold text-gray-900">Add Patronage Record</h2>
                        <p class="text-xs text-gray-500">Record patronage from services outside the system</p>
                    </div>
                </div>
                <button data-action="closePatronageRecordModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
        </div>
        <form id="patronageRecordForm" class="p-6">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="record_id" id="patronageRecordId" value="">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                    <select name="user_id" id="patronageRecordUser" class="select" required>
                        <option value="">Select member</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                    <input type="text" name="source" id="patronageRecordSource" class="input"
                        placeholder="e.g. Gas, Rice, Oil, Transportation" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea name="description" id="patronageRecordDescription" rows="2" class="input"
                        placeholder="Additional details about this patronage record"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
                        <input type="number" name="amount" id="patronageRecordAmount" step="0.01" min="0.01"
                            class="input pl-10" placeholder="0.00" required>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" data-action="closePatronageRecordModal" class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="patronageRecordSubmitBtn" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Record
                </button>
            </div>
        </form>
    </div>
</div>
