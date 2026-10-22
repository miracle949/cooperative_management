@extends('layouts.admin')

@section('title', 'Member Reports')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Member Reports</h1>
        <p class="text-gray-500 mt-1">Problems and appeals submitted by members through "Report a Problem".</p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-sm text-gray-500">Total reports</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['all'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">New</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['open'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Ongoing</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['in_progress'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Closed</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['resolved'] }}</p>
        </div>
    </div>

    <div class="card">
        {{-- Filters --}}
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach (['all' => 'All', 'open' => 'New', 'in_progress' => 'Ongoing', 'resolved' => 'Closed'] as $key => $label)
                    <a href="{{ route('admin.support-reports.index', ['status' => $key, 'search' => $search]) }}"
                        class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline' }}">
                        {{ $label }} ({{ $counts[$key] }})
                    </a>
                @endforeach
            </div>
            <form method="GET" action="{{ route('admin.support-reports.index') }}" class="flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" value="{{ $search }}" class="input md:w-72"
                    placeholder="Search member, subject or category">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $r)
                        @php
                            $memberName = $r->user ? trim($r->user->first_name . ' ' . $r->user->last_name) : 'Deleted member';
                            $badge = match ($r->status) {
                                'resolved' => 'badge-success',
                                'in_progress' => 'badge-info',
                                default => 'badge-warning',
                            };
                            $statusLabel = match ($r->status) {
                                'in_progress' => 'Ongoing',
                                'resolved' => 'Closed',
                                default => 'New',
                            };
                            $payload = [
                                'id' => $r->id,
                                'member' => $memberName,
                                'email' => $r->user->email ?? '',
                                'category' => $r->category,
                                'subject' => $r->subject,
                                'message' => $r->message,
                                'status' => $r->status ?: 'open',
                                'admin_reply' => $r->admin_reply,
                                'proof' => $r->proof_path ? asset('storage/' . $r->proof_path) : null,
                                'created' => $r->created_at ? $r->created_at->format('M d, Y g:i A') : '',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <p class="font-medium text-gray-900">{{ $memberName }}</p>
                                <p class="text-xs text-gray-500">{{ $r->user->email ?? '' }}</p>
                            </td>
                            <td>{{ $r->category }}</td>
                            <td class="max-w-xs">
                                <p class="font-medium text-gray-900 truncate">{{ $r->subject }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ \Illuminate\Support\Str::limit($r->message, 70) }}
                                </p>
                            </td>
                            <td><span class="badge {{ $badge }}">{{ $statusLabel }}</span></td>
                            <td class="text-gray-500">{{ $r->created_at ? $r->created_at->diffForHumans() : '—' }}</td>
                            <td class="text-right">
                                <button type="button" class="btn btn-outline btn-sm"
                                    data-view-report="{{ json_encode($payload) }}">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-10">
                                No reports found{{ $search !== '' ? ' for "' . $search . '"' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $reports->links() }}
        </div>
    </div>

    {{-- Review modal --}}
    <div id="reportModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Review report</h2>
                <button type="button" data-close-modal="reportModal" class="p-1 rounded hover:bg-gray-100">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500">From</p>
                    <p class="font-medium text-gray-900" id="rm-member"></p>
                    <p class="text-xs text-gray-500" id="rm-meta"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Subject</p>
                    <p class="font-medium text-gray-900" id="rm-subject"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Message</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 rounded-lg p-3" id="rm-message"></p>
                </div>
                <div id="rm-proof-wrap" class="hidden">
                    <p class="text-xs text-gray-500 mb-1">Attachment</p>
                    <a id="rm-proof-link" href="#" target="_blank" rel="noopener">
                        <img id="rm-proof-img" src="" alt="Attachment" class="max-h-48 rounded-lg border border-gray-200">
                    </a>
                </div>

                <hr class="border-gray-100">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="rm-status">Status</label>
                    <select id="rm-status" class="select">
                        <option value="open">New</option>
                        <option value="in_progress">Ongoing</option>
                        <option value="resolved">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="rm-reply">Reply to member</label>
                    <textarea id="rm-reply" rows="4" maxlength="2000" class="input"
                        placeholder="Optional. The member is notified when you send a reply."></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" data-close-modal="reportModal" class="btn btn-outline">Cancel</button>
                <button type="button" id="rm-save" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ csp_nonce() }}">
        (function () {
            const token = '{{ csrf_token() }}';
            const updateUrlTemplate = "{{ route('admin.support-reports.update', '__ID__') }}";
            let currentId = null;

            const $ = (id) => document.getElementById(id);

            document.addEventListener('click', function (e) {
                const closeBtn = e.target.closest('[data-close-modal]');
                if (closeBtn) {
                    closeModal(closeBtn.dataset.closeModal);
                    return;
                }

                const viewBtn = e.target.closest('[data-view-report]');
                if (!viewBtn) return;

                const r = JSON.parse(viewBtn.dataset.viewReport);
                currentId = r.id;

                $('rm-member').textContent = r.member;
                $('rm-meta').textContent = [r.email, r.created].filter(Boolean).join(' • ');
                $('rm-subject').textContent = '[' + r.category + '] ' + r.subject;
                $('rm-message').textContent = r.message;
                $('rm-status').value = r.status;
                $('rm-reply').value = r.admin_reply || '';

                if (r.proof) {
                    $('rm-proof-img').src = r.proof;
                    $('rm-proof-link').href = r.proof;
                    $('rm-proof-wrap').classList.remove('hidden');
                } else {
                    $('rm-proof-wrap').classList.add('hidden');
                }

                openModal('reportModal');
            });

            $('rm-save').addEventListener('click', async function () {
                if (!currentId) return;
                const btn = this;
                btn.disabled = true;

                try {
                    const res = await fetch(updateUrlTemplate.replace('__ID__', currentId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            status: $('rm-status').value,
                            admin_reply: $('rm-reply').value,
                        }),
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        showToast('Saved', data.message, 'success');
                        setTimeout(() => window.location.reload(), 700);
                    } else {
                        const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                        showToast('Could not save', firstError || data.message || 'Something went wrong.', 'error');
                        btn.disabled = false;
                    }
                } catch (err) {
                    showToast('Could not save', 'Network error. Please try again.', 'error');
                    btn.disabled = false;
                }
            });
        })();
    </script>
@endsection