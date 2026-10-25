@extends('layouts.admin')

@section('title', 'Settings - CoopAdmin')

@section('content')
    @php
        $settingsIsPrivileged = auth()->user() ? (auth()->user()->isMainAdmin() || auth()->user()->isGeneralManager()) : false;

        $faqRole = auth()->user() ? \App\Models\Role::where('slug', auth()->user()->role)->first() : null;
        $faqPerms = $faqRole?->sidebar_permissions;
        $canManageFaqs = $settingsIsPrivileged || is_null($faqPerms) || in_array('faqs', (array) $faqPerms, true);
    @endphp

    <!-- Breadcrumb -->
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
                    <span class="text-gray-900 font-medium">Settings</span>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-sm text-gray-500">Manage your account and application preferences</p>
        </div>
        <a href="{{ route('logout') }}" class="btn btn-danger flex-shrink-0" data-action="settings-logout">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
        </form>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 p-1 bg-gray-100 rounded-lg w-fit">
        <button
            class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
            data-tab="profile" data-action="switchSettingsTab" data-arg='["profile","|el|"]'>
            <i data-lucide="user" class="w-3.5 h-3.5"></i>
            Profile
        </button>
        <button
            class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
            data-tab="security" data-action="switchSettingsTab" data-arg='["security","|el|"]'>
            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
            Security
        </button>
        @if($settingsIsPrivileged)
            <button
                class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
                data-tab="company" data-action="switchSettingsTab" data-arg='["company","|el|"]'>
                <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                Company
            </button>
        @endif
        @if($settingsIsPrivileged)
            <button
                class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
                data-tab="finance" data-action="switchSettingsTab" data-arg='["finance","|el|"]'>
                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                Finance Settings
            </button>
        @endif
        @if($canManageFaqs)
            <button
                class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
                data-tab="faqs" data-action="switchSettingsTab" data-arg='["faqs","|el|"]'>
                <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                FAQs
            </button>
        @endif
        @if($settingsIsPrivileged)
            <button
                class="tab-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap"
                data-tab="backup" data-action="switchSettingsTab" data-arg='["backup","|el|"]'>
                <i data-lucide="database" class="w-3.5 h-3.5"></i>
                System Backup
            </button>
        @endif
    </div>

    <!-- Settings Content -->
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700">
                <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
                <i data-lucide="alert-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Settings -->
        <div id="tab-profile" class="card p-6 tab-content">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Profile Settings</h2>

            <div class="flex items-center gap-6 mb-6">
                <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center">
                    <span class="text-primary-600 text-2xl font-bold">
                        {{ strtoupper(substr($adminUser->first_name ?? 'A', 0, 1) . substr($adminUser->last_name ?? '', 0, 1)) }}
                    </span>
                </div>
                <div>
                    <button class="btn btn-primary mb-2">Change Photo</button>
                    <p class="text-xs text-gray-500">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
            </div>

            <form id="profileForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" name="first_name" class="input" required value="{{ $adminUser->first_name ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" class="input" required value="{{ $adminUser->last_name ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" class="input" required value="{{ $adminUser->email ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="contact_no" class="input" value="{{ $adminOther->contact_no ?? '' }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="present_address" class="input"
                        rows="2">{{ $adminOther->present_address ?? '' }}</textarea>
                </div>
            </form>

            <div class="mt-6 flex justify-end">
                <button type="button" id="saveProfileBtn" data-action="submitProfileUpdate" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </div>

        <!-- Security Settings -->
        <div id="tab-security" class="card p-6 tab-content hidden">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Security Settings</h2>

            <div class="space-y-6">
                <div>
                    <h3 class="font-medium text-gray-900 mb-4">Change Password</h3>
                    <form id="changePasswordForm" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" class="input"
                                placeholder="Enter current password" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" class="input"
                                placeholder="Enter new password (min 8 characters)" required minlength="8">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="input"
                                placeholder="Confirm new password" required minlength="8">
                        </div>
                        <div id="passwordError"
                            class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"></div>
                        <div id="passwordSuccess"
                            class="hidden p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700"></div>
                    </form>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" data-action="submitPasswordChange" id="changePasswordBtn" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </div>

        <!-- Company Settings -->
        @if($settingsIsPrivileged)
            <div id="tab-company" class="card p-6 tab-content hidden">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Company Settings</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" class="input" value="{{ $companySettings['company_name'] }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number</label>
                        <input type="text" class="input" value="{{ $companySettings['registration_number'] }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea class="input" rows="2">{{ $companySettings['company_address'] }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" class="input" value="{{ $companySettings['company_phone'] }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="input" value="{{ $companySettings['company_email'] }}">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" data-action="showToast" data-arg='["Saved","Company settings updated successfully"]'
                        class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        @endif


        @if($settingsIsPrivileged)
            @include('admin_components.partials.finance_settings')
        @endif

        @if($settingsIsPrivileged)
            <div id="tab-backup" class="card p-6 tab-content hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">System Backup</h2>
                        <p class="text-sm text-gray-500">Create and manage full database backups</p>
                    </div>
                    <button type="button" id="createBackupBtn" data-action="createBackup"
                        class="btn btn-primary flex items-center gap-2">
                        <i data-lucide="database-backup" class="w-4 h-4"></i>
                        Create Backup
                    </button>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">File Name</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Size</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Created</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupTableBody" class="divide-y divide-gray-100 bg-white">
                            @forelse($backups as $backup)
                                <tr data-filename="{{ $backup['name'] }}">
                                    <td class="px-4 py-2 text-gray-900">{{ $backup['name'] }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $backup['size'] }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $backup['created_at']->format('M d, Y g:i A') }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('admin.backup.download', $backup['name']) }}"
                                            class="text-primary-600 hover:text-primary-700 mr-3">
                                            <i data-lucide="download" class="w-4 h-4 inline-block"></i>
                                        </a>

                                        <button type="button" class="text-amber-600 hover:text-amber-700 mr-3" title="Restore"
                                            data-action="openRestoreModal" data-arg='["{{ $backup['name'] }}"]'>
                                            <i data-lucide="rotate-ccw" class="w-4 h-4 inline-block"></i>
                                        </button>
                                        @if(auth()->user()->isMainAdmin())
                                            <button type="button" class="text-danger-600 hover:text-danger-700"
                                                data-action="deleteBackup" data-arg='["{{ $backup['name'] }}"]'>
                                                <i data-lucide="trash-2" class="w-4 h-4 inline-block"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="noBackupsRow">
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No backups yet. Click "Create
                                        Backup" to make one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-400 mt-3">Backups are full <code>mysqldump</code> exports stored on the server.
                    Download regularly and keep at least one copy off-server.</p>
            </div>

            <div id="restoreModal"
                style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:14px; padding:24px; width:94%; max-width:440px;">
                    <h3 style="margin:0 0 6px; font-size:17px; font-weight:700; color:#b91c1c;">Restore Database</h3>
                    <p style="margin:0 0 6px; font-size:13px; color:#444;">
                        This will <strong>replace all current data</strong> with the backup
                        <strong id="restoreFileName"></strong>. A safety backup of the current data is created first.
                    </p>
                    <p style="margin:0 0 14px; font-size:13px; color:#444;">Enter your password to confirm.</p>
                    <input type="password" id="restorePassword" class="input" placeholder="Your password">
                    <div id="restoreError" style="display:none; margin-top:10px; font-size:13px; color:#b91c1c;"></div>
                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                        <button type="button" class="btn" data-action="closeRestoreModal">Cancel</button>
                        <button type="button" id="restoreConfirmBtn" class="btn btn-danger"
                            data-action="submitRestore">Restore</button>
                    </div>
                </div>
            </div>
        @endif

        <script nonce="{{ csp_nonce() }}">
            function submitPasswordChange() {
                const form = document.getElementById('changePasswordForm');
                const errorDiv = document.getElementById('passwordError');
                const successDiv = document.getElementById('passwordSuccess');
                const btn = document.getElementById('changePasswordBtn');

                errorDiv.classList.add('hidden');
                successDiv.classList.add('hidden');

                const formData = new FormData(form);
                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch('{{ route("admin.change-password") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                    .then(res => res.json().then(data => ({ status: res.status, data })))
                    .then(({ status, data }) => {
                        if (data.success) {
                            successDiv.textContent = data.message;
                            successDiv.classList.remove('hidden');
                            form.reset();
                            showToast('Success', data.message, 'success');
                        } else {
                            errorDiv.textContent = data.message || 'Failed to change password.';
                            errorDiv.classList.remove('hidden');
                        }
                    })
                    .catch(() => {
                        errorDiv.textContent = 'An error occurred. Please try again.';
                        errorDiv.classList.remove('hidden');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Save Changes';
                    });
            }
        </script>

        <script nonce="{{ csp_nonce() }}">
            function submitProfileUpdate() {
                const form = document.getElementById('profileForm');
                if (!form.reportValidity()) return;

                const btn = document.getElementById('saveProfileBtn');
                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch('{{ route("admin.update-profile") }}', {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            showToast('Saved', data.message, 'success');
                        } else {
                            const msg = data.errors
                                ? Object.values(data.errors).flat().join(' ')
                                : (data.message || 'Failed to update profile.');
                            showToast('Error', msg, 'error');
                        }
                    })
                    .catch(() => showToast('Error', 'An error occurred. Please try again.', 'error'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Save Changes';
                    });
            }
        </script>

        <script nonce="{{ csp_nonce() }}">
            function switchSettingsTab(tabId, btn) {
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                const panel = document.getElementById('tab-' + tabId);
                if (panel) panel.classList.remove('hidden');

                document.querySelectorAll('.tab-btn').forEach(el => {
                    el.classList.remove('bg-primary-600', 'text-white');
                    el.classList.add('text-gray-600', 'hover:bg-gray-200');
                });
                btn.classList.remove('text-gray-600', 'hover:bg-gray-200');
                btn.classList.add('bg-primary-600', 'text-white');

                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                history.replaceState({}, '', url);

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const params = new URLSearchParams(window.location.search);
                const tab = params.get('tab') || 'profile';
                const btn = document.querySelector('.tab-btn[data-tab="' + tab + '"]');
                if (btn) {
                    switchSettingsTab(tab, btn);
                } else {
                    const firstBtn = document.querySelector('.tab-btn');
                    if (firstBtn) {
                        switchSettingsTab(firstBtn.getAttribute('data-tab'), firstBtn);
                    }
                }
            });
        </script>

        <script nonce="{{ csp_nonce() }}">
            (function () {
                var A = window.CSP_actions;
                if (!A) return;
                A.register('settings-logout', function (e) {
                    e.preventDefault();
                    var form = document.getElementById('logout-form');
                    if (form) form.submit();
                });
            })();
        </script>

        @if($errors->any())
            <script nonce="{{ csp_nonce() }}">
                document.addEventListener('DOMContentLoaded', function () {
                    showToast('Validation Error', '{{ $errors->first() }}', 'error');
                });
            </script>
        @endif

        @if($settingsIsPrivileged)
            <script nonce="{{ csp_nonce() }}">
                const isMainAdminUser = @json(auth()->user()->isMainAdmin());

                function createBackup() {
                    const btn = document.getElementById('createBackupBtn');
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Creating...';
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    fetch('{{ route("admin.backup.create") }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(res => res.json().then(data => ({ status: res.status, data })))
                        .then(({ data }) => {
                            if (data.success) {
                                showToast('Success', data.message, 'success');

                                const emptyRow = document.getElementById('noBackupsRow');
                                if (emptyRow) emptyRow.remove();

                                const tbody = document.getElementById('backupTableBody');
                                const row = document.createElement('tr');
                                row.dataset.filename = data.backup.name;

                                const deleteBtn = isMainAdminUser
                                    ? `<button type="button" class="text-danger-600 hover:text-danger-700" data-action="deleteBackup" data-arg='["${data.backup.name}"]'>
                                                                                                                       <i data-lucide="trash-2" class="w-4 h-4 inline-block"></i>
                                                                                                                   </button>`
                                    : '';

                                row.innerHTML = `
                                                                                                                <td class="px-4 py-2 text-gray-900">${data.backup.name}</td>
                                                                                                                <td class="px-4 py-2 text-gray-600">${data.backup.size}</td>
                                                                                                                <td class="px-4 py-2 text-gray-600">${data.backup.created_at}</td>
                                                                                                                <td class="px-4 py-2 text-right">
                                                                                                                <button type="button" class="text-amber-600 hover:text-amber-700 mr-3" title="Restore" data-action="openRestoreModal" data-arg='["${data.backup.name}"]'>
                                                                                                                    <i data-lucide="rotate-ccw" class="w-4 h-4 inline-block"></i>
                                                                                                                </button>
                                                                                                                    <a href="/admin/backups/${data.backup.name}/download" class="text-primary-600 hover:text-primary-700 mr-3">
                                                                                                                        <i data-lucide="download" class="w-4 h-4 inline-block"></i>
                                                                                                                    </a>
                                                                                                                    ${deleteBtn}
                                                                                                                </td>
                                                                                                            `;
                                tbody.prepend(row);
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            } else {
                                showToast('Error', data.message || 'Failed to create backup.', 'error');
                            }
                        })
                        .catch(() => showToast('Error', 'An error occurred. Please try again.', 'error'))
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                }

                function deleteBackup(filename) {
                    if (!confirm('Delete backup "' + filename + '"? This cannot be undone.')) return;

                    fetch(`/admin/backups/${encodeURIComponent(filename)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(res => res.json().then(data => ({ status: res.status, data })))
                        .then(({ data }) => {
                            if (data.success) {
                                showToast('Success', data.message, 'success');
                                const row = document.querySelector(`tr[data-filename="${filename}"]`);
                                if (row) row.remove();
                            } else {
                                showToast('Error', data.message || 'Failed to delete backup.', 'error');
                            }
                        })
                        .catch(() => showToast('Error', 'An error occurred. Please try again.', 'error'));
                }

                let restoreTarget = null;

                function openRestoreModal(filename) {
                    restoreTarget = filename;
                    document.getElementById('restoreFileName').textContent = filename;
                    document.getElementById('restorePassword').value = '';
                    document.getElementById('restoreError').style.display = 'none';
                    document.getElementById('restoreModal').style.display = 'flex';
                }

                function closeRestoreModal() {
                    document.getElementById('restoreModal').style.display = 'none';
                    restoreTarget = null;
                }

                function submitRestore() {
                    const pw = document.getElementById('restorePassword').value;
                    const errBox = document.getElementById('restoreError');
                    const btn = document.getElementById('restoreConfirmBtn');

                    if (!pw) {
                        errBox.textContent = 'Please enter your password.';
                        errBox.style.display = 'block';
                        return;
                    }

                    btn.disabled = true;
                    btn.textContent = 'Restoring...';
                    errBox.style.display = 'none';

                    const fd = new FormData();
                    fd.append('password', pw);

                    fetch(`/admin/backups/${encodeURIComponent(restoreTarget)}/restore`, {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',

                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok && data.success) {
                                closeRestoreModal();
                                showToast('Success', data.message, 'success');
                                setTimeout(() => window.location.reload(), 1800);
                            } else {
                                errBox.textContent = data.message || 'Restore failed.';
                                errBox.style.display = 'block';
                            }
                        })
                        .catch(() => {
                            errBox.textContent = 'An error occurred. Please try again.';
                            errBox.style.display = 'block';
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.textContent = 'Restore';
                        });
                }
            </script>
        @endif

        @if($canManageFaqs)
        @include('admin_components.partials.faqs_tab')
        @endif

    </div>
@endsection