@extends('layouts.admin')

@section('title', 'Officers & Committees - CoopAdmin')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <div class="mb-6">
        <nav class="text-sm text-gray-500">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600">
                        <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                    </a>
                </li>
                <li class="flex items-center">
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-400"></i>
                    <span class="text-gray-900 font-medium">Officers & Committees</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Officers & Committees</h1>
        <p class="text-sm text-gray-500">View cooperative officers and committee members</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
            <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
            <span class="text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <button data-action="openModal" data-arg='["addOfficerModal"]' class="btn btn-primary">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Add Officer
        </button>
        <button type="button" id="managePositionsBtn"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <i data-lucide="list-plus" class="w-4 h-4 text-gray-500"></i>
            Manage Positions
        </button>
    </div>

    <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Board of Officers</h3>
                <p class="text-sm text-gray-500">Current cooperative officers and their positions</p>
            </div>
            <span class="badge badge-primary">{{ $officers->count() }}
                Officer{{ $officers->count() !== 1 ? 's' : '' }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $gradients = ['primary', 'blue', 'emerald', 'amber', 'purple', 'rose', 'cyan', 'indigo', 'teal', 'orange'];
            @endphp
            @forelse($officers as $index => $officer)
                @php
                    $color = $gradients[$index % count($gradients)];
                    $name = trim(($officer->user->first_name ?? '') . ' ' . ($officer->user->last_name ?? ''));
                    $initials = strtoupper(substr($officer->user->first_name ?? 'U', 0, 1) . substr($officer->user->last_name ?? '', 0, 1));
                    $term = '';
                    if ($officer->term_start && $officer->term_end) {
                        $term = $officer->term_start->format('Y') . ' - ' . $officer->term_end->format('Y');
                    } elseif ($officer->term_start) {
                        $term = $officer->term_start->format('Y') . ' - Present';
                    }
                @endphp
                <div
                    class="relative p-5 bg-gradient-to-br from-{{ $color }}-50 to-{{ $color }}-100 rounded-xl border border-{{ $color }}-200">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-14 h-14 rounded-full bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-600 flex items-center justify-center shadow-md">
                                <span class="text-white text-xl font-bold">{{ $initials }}</span>
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">{{ $name }}</h4>
                                <span
                                    class="inline-block px-3 py-1 bg-{{ $color }}-600 text-white text-xs font-semibold rounded-full mt-1">{{ $officer->position }}</span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button class="js-edit-officer p-1.5 hover:bg-white rounded-lg transition-colors" title="Edit"
                                data-officer-id="{{ $officer->id }}" data-officer-user-id="{{ $officer->user_id }}"
                                data-officer-position="{{ $officer->position }}"
                                data-officer-term-start="{{ $officer->term_start?->format('Y-m-d') }}">
                                <i data-lucide="pencil" class="w-4 h-4 text-gray-500"></i>
                            </button>
                            <button class="p-1.5 hover:bg-white rounded-lg transition-colors" title="Remove"
                                data-action="deleteOfficer" data-arg='[{{ $officer->id }}]'>
                                <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4 space-y-1.5 text-sm text-gray-600">
                        @if($officer->user->email)
                            <div class="flex items-center gap-2">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>{{ $officer->user->email }}</span>
                            </div>
                        @endif
                        @if($term)
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>Term: {{ $term }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No officers added yet. Click "Add Officer" to get started.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Add Officer Modal -->
    <div id="addOfficerModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="user-plus" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 id="officerModalTitle" class="text-lg font-semibold" style="color: #fff; margin: 0;">Add New
                                Officer</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Assign a new
                                officer position</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["addOfficerModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <form id="addOfficerForm" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Member</label>
                    <select name="user_id" id="officerMemberSelect" class="select" style="width: 100%;" required>
                        <option value="">Select a member...</option>
                        @foreach($allMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <button type="button" id="openManagePositionsFromForm"
                            class="text-xs font-medium text-primary-600 hover:text-primary-700">
                            + Add new position
                        </button>
                    </div>
                    <select name="position" id="officerPositionSelect" class="select" style="width: 100%;" required>
                        <option value="">Select position...</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->name }}">{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term Start</label>
                    <input type="date" name="term_start" class="input" style="width: 100%;">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-action="closeModal" data-arg='["addOfficerModal"]'
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Save Officer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Positions Modal -->
    <div id="managePositionsModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="list-plus" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold" style="color: #fff; margin: 0;">Manage Positions</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Add or remove
                                officer position titles</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["managePositionsModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <form id="addPositionForm" class="flex items-center gap-2">
                    <input type="text" name="name" id="newPositionInput" placeholder="e.g. Vice Treasurer"
                        class="input flex-1" style="width: 100%;" required maxlength="255">
                    <button type="submit"
                        class="shrink-0 px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add
                    </button>
                </form>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Current Positions</p>
                    <ul id="positionsList" class="space-y-1.5 max-h-64 overflow-y-auto pr-1"></ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script nonce="{{ csp_nonce() }}">
        const csrfToken = document.querySelector('input[name="_token"]').value;
        let positions = @json($positions);

        document.addEventListener('DOMContentLoaded', function () {
            new TomSelect('#officerMemberSelect', { maxOptions: 200, placeholder: 'Search for a member...' });
            renderPositionsList();
        });

        function editOfficer(id, userId, position, termStart) {
            const form = document.getElementById('addOfficerForm');
            form.setAttribute('data-edit-id', id);

            const memberSelect = document.getElementById('officerMemberSelect');
            if (memberSelect.tomselect) memberSelect.tomselect.setValue(userId);

            form.querySelector('[name="position"]').value = position;
            form.querySelector('[name="term_start"]').value = termStart || '';

            document.getElementById('officerModalTitle').textContent = 'Edit Officer';
            openModal('addOfficerModal');
        }

        document.querySelectorAll('.js-edit-officer').forEach(function (btn) {
            btn.addEventListener('click', function () {
                editOfficer(
                    Number(this.dataset.officerId),
                    this.dataset.officerUserId,
                    this.dataset.officerPosition,
                    this.dataset.officerTermStart || ''
                );
            });
        });

        document.getElementById('addOfficerForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const editId = this.getAttribute('data-edit-id');
            const formData = new FormData(this);
            const url = editId ? '/officers/' + editId : '{{ route("officers.store") }}';
            const method = editId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                body: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeModal('addOfficerModal');
                        this.removeAttribute('data-edit-id');
                        document.getElementById('officerModalTitle').textContent = 'Add New Officer';
                        this.reset();
                        showToast('Success', data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast('Error', data.message || 'Failed');
                    }
                })
                .catch(() => showToast('Error', 'An error occurred'));
        });

        function deleteOfficer(id) {
            if (!confirm('Are you sure you want to remove this officer?')) return;
            fetch('/officers/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showToast('Error', data.message || 'Failed');
                    }
                })
                .catch(() => showToast('Error', 'An error occurred'));
        }

        // ─── Manage Positions ───────────────────────────────────────────────

        document.getElementById('managePositionsBtn').addEventListener('click', function () {
            openModal('managePositionsModal');
        });

        document.getElementById('openManagePositionsFromForm').addEventListener('click', function () {
            openModal('managePositionsModal');
        });

        function renderPositionOptions() {
            const select = document.getElementById('officerPositionSelect');
            const current = select.value;
            select.innerHTML = '<option value="">Select position...</option>' +
                positions.map(p => `<option value="${escapeHtml(p.name)}">${escapeHtml(p.name)}</option>`).join('');
            if (current) select.value = current;
        }

        function renderPositionsList() {
            const list = document.getElementById('positionsList');
            if (positions.length === 0) {
                list.innerHTML = '<li class="text-sm text-gray-400 text-center py-4">No positions yet.</li>';
                return;
            }
            list.innerHTML = positions.map(p => `
                    <li class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="text-sm text-gray-800">${escapeHtml(p.name)}</span>
                        <button type="button" data-position-id="${p.id}" class="js-delete-position p-1 hover:bg-red-50 rounded transition-colors" title="Remove">
                            <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                        </button>
                    </li>
                `).join('');
            if (window.lucide) lucide.createIcons();
            list.querySelectorAll('.js-delete-position').forEach(btn => {
                btn.addEventListener('click', () => deletePosition(Number(btn.dataset.positionId)));
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        document.getElementById('addPositionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const input = document.getElementById('newPositionInput');
            const name = input.value.trim();
            if (!name) return;

            fetch('{{ route("officer-positions.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        positions.push(data.position);
                        renderPositionsList();
                        renderPositionOptions();
                        input.value = '';
                        showToast('Success', data.message);
                    } else {
                        showToast('Error', data.message || 'Failed to add position');
                    }
                })
                .catch(() => showToast('Error', 'An error occurred'));
        });

        function deletePosition(id) {
            if (!confirm('Remove this position?')) return;
            fetch('/officer-positions/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        positions = positions.filter(p => p.id !== id);
                        renderPositionsList();
                        renderPositionOptions();
                        showToast('Success', data.message);
                    } else {
                        showToast('Error', data.message || 'Failed to delete position');
                    }
                })
                .catch(() => showToast('Error', 'An error occurred'));
        }
    </script>
@endsection