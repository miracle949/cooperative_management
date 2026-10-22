@extends('layouts.admin')

@section('title', 'Allied Workers - CoopAdmin')

@section('content')
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
                    <span class="text-gray-900 font-medium">Allied Workers</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Allied Workers</h1>
        <p class="text-sm text-gray-500">Promote existing members to staff roles on the same account (no duplicate accounts).</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700">
            <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
            <i data-lucide="alert-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Current Allied Workers -->
    <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Current Allied Workers</h2>
                <p class="text-sm text-gray-500">Members currently holding an Allied Worker role.</p>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Mode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alliedWorkers as $aw)
                    <tr>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ $aw->first_name }} {{ $aw->last_name }}</span>
                            <span class="text-xs text-gray-400 block">{{ $aw->email }}</span>
                        </td>
                        <td>
                            @php $awRole = $systemRoles->merge($activeRoles)->firstWhere('slug', $aw->role); @endphp
                            <span class="badge badge-info">{{ $awRole?->name ?? $aw->role }}</span>
                        </td>
                        <td>
                            <span class="text-xs font-medium text-primary-700 bg-primary-50 px-2 py-1 rounded-full">Member / Allied Worker</span>
                        </td>
                        <td>
<button class="js-open-revoke px-3 py-1.5 text-xs font-medium text-danger-600 bg-danger-50 rounded-lg hover:bg-danger-100 transition-colors"
                data-aw-id="{{ $aw->id }}"
                data-aw-name="{{ $aw->first_name }} {{ $aw->last_name }}">
                Revoke
            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-500">No Allied Workers yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Promote form -->
    <div class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Promote a Member</h2>
        <p class="text-sm text-gray-500 mb-4">Choose an existing member and assign a custom staff role. The member keeps their existing account and member portal; they can switch to Allied Worker mode with their own password.</p>
        <form action="{{ route('allied-workers.promote') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member <span class="text-red-500">*</span></label>
                    <select name="member_id" class="input" required>
                        <option value="">Select member</option>
                        @foreach($candidates as $m)
                        <option value="{{ $m->id }}">{{ $m->first_name }} {{ $m->last_name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Allied Worker Role <span class="text-red-500">*</span></label>
                    <select name="role" class="input" required>
                        <option value="">Select role</option>
                        @foreach($activeRoles as $r)
                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Document <span class="text-red-500">*</span></label>
                    <input type="file" name="id_document" accept=".jpg,.jpeg,.png,.pdf" class="input" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <input type="text" name="reason" class="input" placeholder="Optional note for the promotion">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Promote to Allied Worker
                </button>
            </div>
        </form>
    </div>

    <!-- History -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Assignment History</h2>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role Change</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td>
                            <span class="text-sm font-medium text-gray-900">{{ $h->user?->first_name }} {{ $h->user?->last_name }}</span>
                        </td>
                        <td>
                            <span class="text-xs text-gray-500">{{ $h->previous_role }} → <strong>{{ $h->new_role }}</strong></span>
                        </td>
                        <td>
                            @if($h->status === 'active')
                            <span class="badge badge-primary">Active</span>
                            @else
                            <span class="badge badge-danger">Revoked</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm text-gray-500">{{ $h->assigner?->first_name }} {{ $h->assigner?->last_name }}</span>
                        </td>
                        <td>
                            <span class="text-sm text-gray-500">{{ $h->created_at?->format('M d, Y') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">No assignment history.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manage Roles -->
    <div class="card p-6 mt-6 mb-6">
        @php
            $permissionCategories = [
                'core' => [
                    'label' => 'Core',
                    'icon' => 'layout-grid',
                    'items' => [
                        'dashboard'    => ['label' => 'Dashboard',       'icon' => 'layout-dashboard', 'desc' => 'Main dashboard with key metrics and overview.'],
                        'reports'      => ['label' => 'Reports & Analytics', 'icon' => 'bar-chart-3', 'desc' => 'View reports and analytics dashboards.'],
                    ],
                ],
                'operations' => [
                    'label' => 'Operations',
                    'icon' => 'briefcase',
                    'items' => [
                        'members'             => ['label' => 'Accounts / Members',     'icon' => 'users', 'desc' => 'View and manage member accounts and roles.'],
                        'officers-committees' => ['label' => 'Officers & Committees',  'icon' => 'users-round', 'desc' => 'Manage officers and committee listings.'],
                        'seminars'            => ['label' => 'Seminars',               'icon' => 'graduation-cap', 'desc' => 'Manage seminars, training and attendance.'],
                    ],
                ],
                'financial' => [
                    'label' => 'Financial',
                    'icon' => 'banknote',
                    'items' => [
                        'lendings'    => ['label' => 'Loans & Credit',   'icon' => 'banknote', 'desc' => 'Loan applications, approvals and disbursements.'],
                        'payments'    => ['label' => 'Payments & Collections', 'icon' => 'credit-card', 'desc' => 'Process payments and manage collections.'],
                        'finance'     => ['label' => 'Finance',          'icon' => 'wallet', 'desc' => 'Financial activity and transaction controls.', 'sensitive' => true],
                        'savings'     => ['label' => 'Savings',          'icon' => 'piggy-bank', 'desc' => 'Savings accounts and balances. Sidebar module not yet live.', 'submodule' => 'finance', 'future' => true],
                        'sharecapitals' => ['label' => 'Share Capital',  'icon' => 'landmark', 'desc' => 'Share capital accounts and records. Sidebar module not yet live.', 'submodule' => 'finance', 'future' => true],
                    ],
                ],
            ];

            // Flat lookup of permission key → label/icon/category for table chips.
            $permInfo = [];
            foreach ($permissionCategories as $catKey => $cat) {
                foreach ($cat['items'] as $key => $item) {
                    $permInfo[$key] = array_merge(['cat' => $catKey], $item);
                }
            }
            $chipColors = [
                'core'       => 'bg-slate-100 text-slate-600 border-slate-200',
                'operations' => 'bg-blue-50 text-blue-700 border-blue-200',
                'financial'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];

            // Client-only presets that prefill the create form (never submit).
            $rolePresets = [
                'secretary'         => ['name' => 'Secretary',         'description' => 'Handles correspondence, records and meeting coordination.', 'permissions' => ['dashboard', 'members', 'reports', 'seminars', 'officers-committees']],
                'treasurer'         => ['name' => 'Treasurer',         'description' => 'Oversees collections, payments and financial reporting.', 'permissions' => ['dashboard', 'members', 'payments', 'finance', 'reports']],
                'loan-officer'      => ['name' => 'Loan Officer',      'description' => 'Processes loan applications, approvals and collections.', 'permissions' => ['dashboard', 'members', 'lendings', 'payments', 'reports']],
                'audit-inspector'   => ['name' => 'Audit Inspector',   'description' => 'Reviews financial records, reports and compliance.', 'permissions' => ['dashboard', 'members', 'payments', 'finance', 'reports']],
                'collection-officer'=> ['name' => 'Collection Officer','description' => 'Handles payments, collections and follow-ups.', 'permissions' => ['dashboard', 'lendings', 'payments', 'reports']],
            ];

            $quotaMax = \App\Models\Role::MAX_CUSTOM_ROLES;
            $quotaPct = min(100, (int) round(($customRoleCount / max(1, $quotaMax)) * 100));
            $quotaBarColor = $customRoleCount >= $quotaMax ? 'bg-red-500'
                : ($customRoleCount >= $quotaMax - 1 ? 'bg-amber-500' : 'bg-emerald-500');
            $quotaTextColor = $customRoleCount >= $quotaMax ? 'text-red-600'
                : ($customRoleCount >= $quotaMax - 1 ? 'text-amber-600' : 'text-slate-700');
        @endphp

        <div class="flex flex-wrap items-start justify-between gap-4 mb-2">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Manage Roles</h2>
                <p class="text-sm text-slate-500 mt-1">System roles are protected and hold permanent full access. Custom roles define the delegated sidebar access granted to staff and Allied Workers.</p>
            </div>
            <div class="w-full sm:w-56">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-medium text-slate-600">Custom roles used</span>
                    <span class="font-semibold {{ $quotaTextColor }}">{{ $customRoleCount }} / {{ $quotaMax }}</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300 {{ $quotaBarColor }}" style="width: {{ $quotaPct }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Maximum {{ $quotaMax }} custom roles allowed</p>
            </div>
        </div>

        @if($customRoleCount >= $quotaMax)
        <div class="mt-4 p-3 rounded-lg bg-amber-50 text-sm text-amber-800 border border-amber-200">
            The maximum of {{ $quotaMax }} custom roles has been reached. Delete an existing custom role before creating or duplicating another.
        </div>
        @endif

        <script type="application/json" id="role-presets-data">
            {!! json_encode($rolePresets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}
        </script>

        <!-- View toggle -->
        <div class="mt-5 inline-flex rounded-lg border border-slate-200 bg-slate-100 p-0.5">
            <button type="button" class="js-view-toggle inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors bg-primary-600 text-white" data-view="table">
                <i data-lucide="table" class="w-3.5 h-3.5"></i>Roles Table
            </button>
            <button type="button" class="js-view-toggle inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-md transition-colors text-slate-600 hover:text-slate-800" data-view="matrix">
                <i data-lucide="grid" class="w-3.5 h-3.5"></i>Permission Matrix
            </button>
        </div>

        <!-- ===================== ROLES TABLE VIEW ===================== -->
        <div id="roleView-table" class="mt-4">

            <!-- Create role form -->
            <div class="rounded-xl border border-slate-200 bg-white overflow-hidden mb-6">
                <form id="createRoleForm" aria-label="Create a new role">
                    @csrf
                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">Create New Role</h3>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" class="js-select-all-perms font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Select All</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" class="js-clear-all-perms font-medium text-slate-400 hover:text-slate-600 transition-colors">Clear</button>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="role_name_input" class="input" placeholder="e.g. Loan Officer" required data-action="generateRoleSlug" data-trigger="input" data-arg='["|value|"]'>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">System Identifier (Slug)</label>
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="slug" id="role_slug_hidden">
                                    <span class="flex-1 inline-flex items-center gap-1 bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg min-h-[38px]">
                                        <span class="text-xs font-semibold text-slate-400">@</span>
                                        <code id="role_slug_preview" class="text-xs font-mono text-slate-500 flex-1 truncate">loan-officer</code>
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Auto-generated. Used to identify the role in the system.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
                                <input type="text" name="description" id="role_description_input" class="input" placeholder="Describe this role's responsibilities" required>
                            </div>
                        </div>

                        <!-- Quick presets -->
                        <div class="mt-5">
                            <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide mb-2">Quick presets</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($rolePresets as $pKey => $preset)
                                <button type="button" class="js-apply-preset inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 transition-colors" data-preset="{{ $pKey }}">
                                    <i data-lucide="wand" class="w-3.5 h-3.5"></i>{{ $preset['name'] }}
                                </button>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">Presets only prefill the form fields and permissions — they do not save anything until you submit.</p>
                        </div>

                        <!-- Permission selector -->
                        <div class="mt-5">
                            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">Sidebar Access Permissions</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Grant this role access to specific sidebar modules. &ldquo;Sensitive&rdquo; badges indicate organizational controls; &ldquo;Future&rdquo; badges are reserved modules not yet wired to a sidebar item. Savings and Share Capital are managed inside Finance.</p>
                                </div>
                            </div>

                            @include('admin_components.partials.role_permission_selector')

                            <div class="js-sensitive-warning hidden mt-4 flex items-start gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0 text-amber-600"></i>
                                <p class="text-xs leading-relaxed text-amber-800">You are delegating <strong>sensitive organizational controls</strong> (Finance). Grant these only to roles with clear responsibility and trust.</p>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="submit" class="btn btn-primary" {{ $customRoleCount >= $quotaMax ? 'disabled' : '' }}>
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Create Role
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search + filter -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" id="roleSearch" class="input pl-9" placeholder="Search roles...">
                </div>
                <div class="inline-flex rounded-lg border border-slate-200 bg-slate-100 p-0.5">
                    <button type="button" class="js-role-filter px-3 py-1.5 text-xs font-semibold rounded-md transition-colors bg-primary-600 text-white" data-type="all">All Roles</button>
                    <button type="button" class="js-role-filter px-3 py-1.5 text-xs font-semibold rounded-md transition-colors text-slate-600 hover:text-slate-800" data-type="system">System</button>
                    <button type="button" class="js-role-filter px-3 py-1.5 text-xs font-semibold rounded-md transition-colors text-slate-600 hover:text-slate-800" data-type="custom">Custom</button>
                </div>
            </div>

            <!-- Roles table -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Sidebar Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTableBody">
                        @forelse($roles as $role)
                        @php
                            $permList = $role->sidebar_permissions ?? [];
                            $visiblePerms = array_slice($permList, 0, 3);
                            $extraPerms = array_slice($permList, 3);
                            $roleUserCount = $roleCounts[$role->slug] ?? 0;
                        @endphp
                        <tr data-role-type="{{ $role->is_system ? 'system' : 'custom' }}" data-role-search="{{ strtolower($role->name.' '.$role->slug.' '.($role->description ?? '')) }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-primary-800 text-[11px] font-bold">{{ strtoupper(substr($role->name, 0, 2)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-900">{{ $role->name }}</span>
                                            @if($roleUserCount > 0)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-1.5 py-0.5" title="{{ $roleUserCount }} active user(s)">
                                                <i data-lucide="user" class="w-3 h-3"></i>{{ $roleUserCount }}
                                            </span>
                                            @endif
                                        </div>
                                        <code class="text-[11px] font-mono text-slate-400">&commat;{{ $role->slug }}</code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="block text-sm text-slate-600 max-w-xs truncate" title="{{ $role->description ?? '' }}">{{ $role->description ?? '—' }}</span>
                            </td>
                            <td>
                                @if($role->is_system)
                                <span class="badge badge-primary">System</span>
                                @else
                                <span class="badge badge-info">Custom</span>
                                @endif
                            </td>
                            <td>
                                @if(is_null($role->sidebar_permissions))
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>Full Access
                                </span>
                                @elseif(empty($role->sidebar_permissions))
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200 rounded-full px-2 py-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>No Sidebar Access
                                </span>
                                @else
                                <div class="js-perm-chips flex flex-wrap items-center gap-1 max-w-[300px]">
                                    @foreach($visiblePerms as $perm)
                                    @php
                                        $pi = $permInfo[$perm] ?? ['label' => ucwords(str_replace('-', ' ', $perm)), 'icon' => 'circle', 'cat' => 'core'];
                                        $chipCls = $chipColors[$pi['cat']] ?? $chipColors['core'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium border rounded-md px-1.5 py-0.5 {{ $chipCls }}">
                                        <i data-lucide="{{ $pi['icon'] }}" class="w-3 h-3"></i>{{ $pi['label'] }}
                                    </span>
                                    @endforeach
                                    @if(count($extraPerms) > 0)
                                    <span class="js-perm-rest hidden">
                                        @foreach($extraPerms as $perm)
                                        @php
                                            $pi = $permInfo[$perm] ?? ['label' => ucwords(str_replace('-', ' ', $perm)), 'icon' => 'circle', 'cat' => 'core'];
                                            $chipCls = $chipColors[$pi['cat']] ?? $chipColors['core'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium border rounded-md px-1.5 py-0.5 {{ $chipCls }}">
                                            <i data-lucide="{{ $pi['icon'] }}" class="w-3 h-3"></i>{{ $pi['label'] }}
                                        </span>
                                        @endforeach
                                    </span>
                                    <button type="button" class="js-perm-toggle text-[11px] font-semibold text-indigo-600 hover:text-indigo-800" data-more-text="+{{ count($extraPerms) }} more">+{{ count($extraPerms) }} more</button>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td>
                                @if(!$role->is_system)
                                <div class="flex items-center gap-1.5">
                                    <button class="js-edit-role inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors"
                                        data-role-id="{{ $role->id }}"
                                        data-role-name="{{ $role->name }}"
                                        data-role-description="{{ $role->description ?? '' }}"
                                        data-role-perms="{{ json_encode($role->sidebar_permissions ?? []) }}">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>Edit
                                    </button>
                                    <button class="js-delete-role inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-danger-600 bg-danger-50 rounded-lg hover:bg-danger-100 transition-colors"
                                        data-role-id="{{ $role->id }}"
                                        data-role-name="{{ $role->name }}"
                                        data-role-users="{{ $roleUserCount }}">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>Delete
                                    </button>
                                </div>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>Protected
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500">No roles found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== PERMISSION MATRIX VIEW ===================== -->
        <div id="roleView-matrix" class="mt-4 hidden">
            <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-900">Permission Matrix</h3>
                    <p class="text-xs text-slate-500 mt-0.5">A quick visual audit of which modules each role can access. Rows are sidebar modules grouped by category; columns are roles. System roles are shaded and immutable.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="min-w-[220px]">Module</th>
                                @foreach($roles as $role)
                                <th class="text-center px-3 {{ $role->is_system ? 'bg-slate-50' : '' }}">
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-xs font-semibold {{ $role->is_system ? 'text-primary-700' : 'text-slate-700' }}">{{ $role->name }}</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-normal {{ $role->is_system ? 'text-primary-500' : 'text-slate-400' }}">
                                            <code class="font-mono">&commat;{{ $role->slug }}</code>
                                        </span>
                                    </div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionCategories as $catKey => $cat)
                            <tr>
                                <td colspan="{{ 1 + $roles->count() }}" class="bg-slate-50 px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $cat['label'] }}</td>
                            </tr>
                            @foreach($cat['items'] as $key => $item)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <span class="flex items-center gap-2 text-sm {{ ($item['sensitive'] ?? false) ? 'text-amber-700 font-medium' : 'text-slate-700' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 text-slate-400"></i>{{ $item['label'] }}
                                        @if($item['sensitive'] ?? false)
                                        <span class="badge badge-warning">Sensitive</span>
                                        @elseif($item['future'] ?? false)
                                        <span class="badge badge-gray">Future</span>
                                        @endif
                                    </span>
                                </td>
                                @foreach($roles as $role)
                                @php
                                    $full = is_null($role->sidebar_permissions);
                                    $granted = $full || in_array($key, $role->sidebar_permissions ?? [], true);
                                @endphp
                                <td class="px-3 py-2.5 text-center {{ $granted ? 'bg-emerald-50/40' : '' }}">
                                    @if($granted)
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 mx-auto"></i>
                                    @else
                                    <span class="inline-block w-4 h-4 rounded-full border border-slate-200 bg-slate-50"></span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit role modal -->
    <div id="editRoleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white px-6 pt-5 pb-4 border-b border-slate-200 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Edit Role</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Update the role's name, description and sidebar access permissions.</p>
                </div>
                <button type="button" data-action="closeEditRole" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="editRoleForm" class="p-6">
                @csrf
                <input type="hidden" name="id" id="edit_role_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_role_name_input" class="input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" id="edit_role_description_input" class="input" required>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900">Sidebar Access Permissions</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Permissions are inherited by every user assigned this role.</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" class="js-select-all-perms font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Select All</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" class="js-clear-all-perms font-medium text-slate-400 hover:text-slate-600 transition-colors">Clear</button>
                        </div>
                    </div>

                    @include('admin_components.partials.role_permission_selector')

                    <div class="js-sensitive-warning hidden mt-4 flex items-start gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                        <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0 text-amber-600"></i>
                        <p class="text-xs leading-relaxed text-amber-800">You are delegating <strong>sensitive organizational controls</strong> (Finance). Grant these only to roles with clear responsibility and trust.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-action="closeEditRole" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete role modal -->
    <div id="deleteRoleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl w-full max-w-md overflow-hidden">
            <div class="px-6 pt-5 pb-4">
                <div class="w-10 h-10 rounded-full bg-danger-50 flex items-center justify-center mb-3">
                    <i data-lucide="trash-2" class="w-5 h-5 text-danger-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Delete Role</h3>
                <p class="text-sm text-slate-500 mt-1">You are about to delete <strong id="deleteRoleName"></strong>. This cannot be undone.</p>
                <div id="deleteRoleWarning" class="hidden mt-3 flex items-start gap-2.5 p-3 rounded-lg bg-amber-50 border border-amber-200">
                    <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5 text-amber-600"></i>
                    <p class="text-xs leading-relaxed text-amber-800" id="deleteRoleWarningText"></p>
                </div>
                <p class="text-xs text-slate-400 mt-3">The system enforces deletions server-side. General Manager and other system roles can never be deleted.</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                <button type="button" data-action="closeDeleteRole" class="btn btn-outline">Cancel</button>
                <button type="button" id="confirmDeleteRoleBtn" class="btn btn-danger">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete Role
                </button>
            </div>
        </div>
    </div>

    <!-- Revoke modal -->
    <div id="revokeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Revoke Allied Worker</h3>
            <p class="text-sm text-gray-500 mb-4">Revoke <strong id="revokeName"></strong> back to a regular member? Their account will be preserved.</p>
            <form action="{{ route('allied-workers.revoke') }}" method="POST">
                @csrf
                <input type="hidden" name="member_id" id="revokeMemberId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <input type="text" name="reason" class="input" placeholder="Optional reason">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-action="closeRevoke" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke</button>
                </div>
            </form>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function openRevoke(id, name) {
            document.getElementById('revokeMemberId').value = id;
            document.getElementById('revokeName').textContent = name;
            document.getElementById('revokeModal').classList.remove('hidden');
            document.getElementById('revokeModal').classList.add('flex');
        }
        function closeRevoke() {
            document.getElementById('revokeModal').classList.add('hidden');
            document.getElementById('revokeModal').classList.remove('flex');
        }

        document.querySelectorAll('.js-open-revoke').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openRevoke(Number(this.dataset.awId), this.dataset.awName);
            });
        });

        // ==================== Role Management ====================

        var rolePresets = {};
        try {
            rolePresets = JSON.parse(document.getElementById('role-presets-data').textContent || '{}');
        } catch (err) {
            rolePresets = {};
        }

        function generateRoleSlug(name) {
            const slug = name.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
            document.getElementById('role_slug_hidden').value = slug;
            document.getElementById('role_slug_preview').textContent = slug || '—';
        }

        function updateSensitiveWarning(form) {
            const warn = form ? form.querySelector('.js-sensitive-warning') : null;
            if (!warn) return;
            const hasSensitive = Array.prototype.some.call(form.querySelectorAll('.role-perm-checkbox'), (cb) =>
                cb.dataset.sensitive === '1' && cb.checked
            );
            warn.classList.toggle('hidden', !hasSensitive);
        }

        function setPermsForForm(form, perms) {
            const list = Array.isArray(perms) ? perms : [];
            form.querySelectorAll('.role-perm-checkbox').forEach((cb) => {
                cb.checked = list.indexOf(cb.value) !== -1;
            });
            updateSensitiveWarning(form);
        }

        // Keep the per-form sensitive warning in sync on any checkbox change.
        document.querySelectorAll('.role-perm-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateSensitiveWarning(this.closest('form'));
            });
        });

        // Category-level batch controls.
        document.querySelectorAll('.js-perm-cat-select').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const card = this.closest('.js-perm-category');
                if (card) card.querySelectorAll('.role-perm-checkbox').forEach((cb) => { cb.checked = true; });
                updateSensitiveWarning(form);
            });
        });
        document.querySelectorAll('.js-perm-cat-clear').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const card = this.closest('.js-perm-category');
                if (card) card.querySelectorAll('.role-perm-checkbox').forEach((cb) => { cb.checked = false; });
                updateSensitiveWarning(form);
            });
        });

        // Global batch controls (Select All / Clear).
        document.querySelectorAll('.js-select-all-perms').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('.role-perm-checkbox').forEach((cb) => { cb.checked = true; });
                updateSensitiveWarning(form);
            });
        });
        document.querySelectorAll('.js-clear-all-perms').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('.role-perm-checkbox').forEach((cb) => { cb.checked = false; });
                updateSensitiveWarning(form);
            });
        });

        // Presets: prefill name, description and permissions (never submits).
        document.querySelectorAll('.js-apply-preset').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const preset = rolePresets[this.dataset.preset];
                if (!preset) return;
                const nameInput = document.getElementById('role_name_input');
                nameInput.value = preset.name || '';
                generateRoleSlug(nameInput.value);
                document.getElementById('role_description_input').value = preset.description || '';
                setPermsForForm(this.closest('form'), preset.permissions || []);
            });
        });

        // Create role form (unchanged request format / validation).
        document.getElementById('createRoleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const nameInput = document.getElementById('role_name_input');
            const slugHidden = document.getElementById('role_slug_hidden');
            if (!slugHidden.value && nameInput.value) {
                generateRoleSlug(nameInput.value);
            }
            if (!slugHidden.value) {
                showToast('Error', 'Please enter a role name.', 'error');
                return;
            }
            const formData = new FormData(this);

            fetch('{{ route('roles.store') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(err => showToast('Error', 'Failed to create role', 'error'));
        });

        // Delete role — confirmation safety modal (backend stays authoritative).
        let deleteRoleId = null;
        function openDeleteRole(id, name, users) {
            deleteRoleId = id;
            document.getElementById('deleteRoleName').textContent = name;
            const warning = document.getElementById('deleteRoleWarning');
            const warningText = document.getElementById('deleteRoleWarningText');
            if (users > 0) {
                warning.classList.remove('hidden');
                warningText.textContent = users + ' user(s) are currently assigned this role. Reassign them to another role before this role can be deleted.';
            } else {
                warning.classList.add('hidden');
            }
            document.getElementById('deleteRoleModal').classList.remove('hidden');
            document.getElementById('deleteRoleModal').classList.add('flex');
        }
        function closeDeleteRole() {
            document.getElementById('deleteRoleModal').classList.add('hidden');
            document.getElementById('deleteRoleModal').classList.remove('flex');
        }

        document.querySelectorAll('.js-delete-role').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openDeleteRole(Number(this.dataset.roleId), this.dataset.roleName, Number(this.dataset.roleUsers || 0));
            });
        });
        document.getElementById('confirmDeleteRoleBtn').addEventListener('click', function() {
            if (deleteRoleId === null) return;
            const formData = new FormData();
            formData.append('id', deleteRoleId);

            fetch('{{ route('roles.delete') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(() => showToast('Error', 'Failed to delete role', 'error'));
        });

        // Edit role (same request format, categorized permission UI).
        function openEditRole(id, name, desc, perms) {
            document.getElementById('edit_role_id').value = id;
            document.getElementById('edit_role_name_input').value = name;
            document.getElementById('edit_role_description_input').value = desc || '';
            setPermsForForm(document.getElementById('editRoleForm'), perms);
            document.getElementById('editRoleModal').classList.remove('hidden');
            document.getElementById('editRoleModal').classList.add('flex');
        }

        function closeEditRole() {
            document.getElementById('editRoleModal').classList.add('hidden');
            document.getElementById('editRoleModal').classList.remove('flex');
        }

        document.querySelectorAll('.js-edit-role').forEach(function(btn) {
            btn.addEventListener('click', function() {
                let perms = [];
                try {
                    perms = JSON.parse(this.dataset.rolePerms || '[]');
                } catch (err) {
                    perms = [];
                }
                openEditRole(Number(this.dataset.roleId), this.dataset.roleName, this.dataset.roleDescription, perms);
            });
        });

        const editRoleForm = document.getElementById('editRoleForm');
        if (editRoleForm) {
            editRoleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('{{ route('roles.update') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(() => showToast('Error', 'Failed to update role', 'error'));
            });
        }

        // Expandable permission chips in the roles table.
        document.querySelectorAll('.js-perm-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const chips = this.closest('.js-perm-chips');
                const rest = chips ? chips.querySelector('.js-perm-rest') : null;
                if (!rest) return;
                const expanded = rest.classList.toggle('hidden');
                this.textContent = expanded ? this.dataset.moreText : 'less';
            });
        });

        // Search + type filter (client-side; all data already rendered).
        let activeRoleFilter = 'all';
        function applyRoleFilters() {
            const searchEl = document.getElementById('roleSearch');
            const query = (searchEl ? searchEl.value : '').toLowerCase().trim();
            document.querySelectorAll('#rolesTableBody tr[data-role-type]').forEach(function(row) {
                const typeOk = activeRoleFilter === 'all' || row.dataset.roleType === activeRoleFilter;
                const searchOk = !query || (row.dataset.roleSearch || '').indexOf(query) !== -1;
                row.classList.toggle('hidden', !(typeOk && searchOk));
            });
        }
        const roleSearchInput = document.getElementById('roleSearch');
        if (roleSearchInput) {
            roleSearchInput.addEventListener('input', applyRoleFilters);
        }
        document.querySelectorAll('.js-role-filter').forEach(function(btn) {
            btn.addEventListener('click', function() {
                activeRoleFilter = this.dataset.type;
                document.querySelectorAll('.js-role-filter').forEach(function(b) {
                    const isActive = (b === btn);
                    if (isActive) {
                        b.classList.add('bg-primary-600', 'text-white');
                        b.classList.remove('text-slate-600', 'hover:text-slate-800');
                    } else {
                        b.classList.remove('bg-primary-600', 'text-white');
                        b.classList.add('text-slate-600', 'hover:text-slate-800');
                    }
                });
                applyRoleFilters();
            });
        });

        // Roles Table / Permission Matrix view toggle.
        document.querySelectorAll('.js-view-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                document.getElementById('roleView-table').classList.toggle('hidden', view !== 'table');
                document.getElementById('roleView-matrix').classList.toggle('hidden', view !== 'matrix');
                document.querySelectorAll('.js-view-toggle').forEach(function(b) {
                    const isActive = (b === btn);
                    if (isActive) {
                        b.classList.add('bg-primary-600', 'text-white');
                        b.classList.remove('text-slate-600', 'hover:text-slate-800');
                    } else {
                        b.classList.remove('bg-primary-600', 'text-white');
                        b.classList.add('text-slate-600', 'hover:text-slate-800');
                    }
                });
            });
        });
    </script>
@endsection
