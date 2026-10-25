@extends('layouts.admin')

@section('title', 'Members - CoopAdmin')

@section('content')
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
                    <span class="text-gray-900 font-medium">Members</span>
                </li>
            </ol>
        </nav>
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

    @if(session('import_warning'))
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                <span class="text-amber-800 font-medium">{{ session('import_warning') }}</span>
            </div>
        </div>
    @endif
 
    @if(session('import_errors'))
        <div class="mb-4 p-4 bg-white border border-amber-200 rounded-lg">
            <p class="text-sm font-semibold text-gray-900 mb-2">Rows that need attention:</p>
            <ul class="text-xs text-gray-700 space-y-1 max-h-48 overflow-y-auto list-disc pl-5">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('import_credentials'))
    @php
        $csv = "Name,Email,Username,Password\n";
        foreach (session('import_credentials') as $c) {
            $csv .= '"' . $c['name'] . '","' . $c['email'] . '","' . $c['username'] . '","' . $c['password'] . "\"\n";
        }
    @endphp
    <div class="mb-4 p-4 bg-white border border-green-200 rounded-lg">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold text-gray-900">New member logins (shown once)</p>
            <a href="data:text/csv;charset=utf-8,{{ rawurlencode($csv) }}" download="imported-members-logins.csv"
                class="btn btn-outline btn-xs">Download CSV</a>
        </div>
        <ul class="text-xs text-gray-700 space-y-1 max-h-48 overflow-y-auto list-disc pl-5">
            @foreach(session('import_credentials') as $c)
                <li>{{ $c['name'] }} — {{ $c['email'] }} — <span class="font-mono">{{ $c['password'] }}</span></li>
            @endforeach
        </ul>
    </div>
@endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Members</h1>
            <p class="text-sm text-gray-500">Manage your cooperative members and staff</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->isMainAdmin() || \App\Services\SoDGuard::isGeneralManager())
                <button data-action="openModal" data-arg='["importMembersModal"]' class="btn btn-outline">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Import Members
                </button>
            @endif
            <button data-action="openModal" data-arg='["addMemberModal"]' class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Member
            </button>
        </div>
    </div>

    <!-- Filters -->
    @php
        $mfCategories = [
            'Investor Associate' => 'Investor Associate',
            'Operator' => 'Operator',
            'Driver' => 'Driver',
            'Dispatcher' => 'Dispatcher',
            'Driver-Operator' => 'Driver-Operator',
            'Transport Entrepreneur' => 'Transport Entrepreneur',
            'Allied Worker' => 'Allied Worker',
            'staff' => 'Allied Workers (Staff / Admins)',
        ];
        $mfRoles = ['member' => 'Member'];
        if (auth()->user()?->isMainAdmin()) {
            foreach ($roles as $r) {
                if (!in_array($r->slug, ['member', 'pending', 'inactive'])) {
                    $mfRoles[$r->slug] = $r->name;
                }
            }
        }
        $mfSexes = ['' => 'All', 'Male' => 'Male', 'Female' => 'Female', 'unspecified' => 'Not set'];
        $mfAges = [
            'under-18' => 'Below 18',
            '18-25' => '18 – 25 yrs',
            '26-35' => '26 – 35 yrs',
            '36-45' => '36 – 45 yrs',
            '46-59' => '46 – 59 yrs',
            '60+' => '60+ (Senior)',
            'unknown' => 'No birthdate',
        ];
        $mfActive = [];
        if (request('category'))
            $mfActive['category'] = ['Category', $mfCategories[request('category')] ?? request('category')];
        if (request('role'))
            $mfActive['role'] = ['Role', $mfRoles[request('role')] ?? ucwords(str_replace('-', ' ', request('role')))];
        if (request('sex'))
            $mfActive['sex'] = ['Sex', $mfSexes[request('sex')] ?? request('sex')];
        if (request('age_group'))
            $mfActive['age_group'] = ['Age', $mfAges[request('age_group')] ?? request('age_group')];
        if (request('address'))
            $mfActive['address'] = ['Address', request('address')];
        $mfKeep = fn(array $extra = [], array $drop = []) => route('dashboard.members', array_merge(request()->except(array_merge(['page'], $drop)), $extra));
    @endphp

    <style>
        .mf-summary::-webkit-details-marker {
            display: none;
        }

        .mf-panel[open] .mf-chevron {
            transform: rotate(180deg);
        }

        .mf-pill input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .mf-pill span {
            display: block;
            padding: .45rem .9rem;
            font-size: .8125rem;
            font-weight: 500;
            color: #4b5563;
            border-radius: .375rem;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }

        .mf-pill span:hover {
            color: #111827;
        }

        .mf-pill input:checked+span {
            background: #fff;
            color: #111827;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        }

        .mf-pill input:focus-visible+span {
            outline: 2px solid #6366f1;
            outline-offset: 1px;
        }
    </style>

    <form action="{{ route('dashboard.members') }}" method="GET"
        class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">

        <!-- Row 1: search + status tabs -->
        <div class="p-4 flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 w-full">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" placeholder="Search by name, ID, or email..."
                        class="input pl-10 w-full" value="{{ request('search') }}">
                </div>
            </div>
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                <a href="{{ $mfKeep(['filter' => 'all']) }}"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ request('filter', 'all') === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    All
                </a>
                <a href="{{ $mfKeep(['filter' => 'active']) }}"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ request('filter') === 'active' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Active
                </a>
                <a href="{{ $mfKeep(['filter' => 'pending']) }}"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ request('filter') === 'pending' ? 'bg-white text-yellow-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Pending
                    @if($pendingRequests->count() > 0)
                        <span
                            class="ml-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">{{ $pendingRequests->count() }}</span>
                    @endif
                </a>
                <a href="{{ $mfKeep(['filter' => 'inactive']) }}"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ request('filter') === 'inactive' ? 'bg-white text-gray-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Inactive
                </a>
            </div>
        </div>

        <!-- Row 2: advanced filters (collapsible) -->
        <details class="mf-panel border-t border-gray-100" {{ ($activeFilterCount ?? 0) > 0 ? 'open' : '' }}>
            <summary
                class="mf-summary list-none px-4 py-3 flex items-center justify-between cursor-pointer select-none hover:bg-gray-50 transition-colors">
                <span class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-gray-500"></i>
                    Advanced Filters
                    @if(($activeFilterCount ?? 0) > 0)
                        <span class="px-2 py-0.5 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full">
                            {{ $activeFilterCount }} active
                        </span>
                    @endif
                </span>
                <span class="flex items-center gap-2 text-xs text-gray-400">
                    <span class="hidden sm:inline">Category · Role · Sex · Age · Address</span>
                    <i data-lucide="chevron-down" class="mf-chevron w-4 h-4 transition-transform"></i>
                </span>
            </summary>

            <div class="px-4 pb-4 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Membership category -->
                    <div>
                        <label
                            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                            <i data-lucide="id-card" class="w-3.5 h-3.5"></i> Membership Category
                        </label>
                        <select name="category" class="input w-full">
                            <option value="">All categories</option>
                            @foreach($mfCategories as $value => $label)
                                <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role -->
                    <div>
                        <label
                            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Role
                        </label>
                        <select name="role" class="input w-full">
                            <option value="">All roles</option>
                            @foreach($mfRoles as $value => $label)
                                <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Age group -->
                    <div>
                        <label
                            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                            <i data-lucide="cake" class="w-3.5 h-3.5"></i> Age Group
                        </label>
                        <select name="age_group" class="input w-full">
                            <option value="">All ages</option>
                            @foreach($mfAges as $value => $label)
                                <option value="{{ $value }}" {{ request('age_group') === $value ? 'selected' : '' }}>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Address -->
                    <div>
                        <label
                            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Address
                        </label>
                        <input type="text" name="address" list="mf-address-options" class="input w-full"
                            placeholder="Barangay, town or city..." value="{{ request('address') }}" autocomplete="off">
                        <datalist id="mf-address-options">
                            @foreach(($addressOptions ?? []) as $addr)
                                <option value="{{ $addr }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <!-- Sex -->
                    <div>
                        <label
                            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i> Sex
                        </label>
                        <div class="inline-flex gap-1 bg-gray-100 p-1 rounded-lg">
                            @foreach($mfSexes as $value => $label)
                                <label class="mf-pill relative">
                                    <input type="radio" name="sex" value="{{ $value }}" {{ (string) request('sex', '') === (string) $value ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard.members', array_filter(['filter' => request('filter'), 'search' => request('search')])) }}"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                            Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </details>

        <!-- Active filter chips -->
        @if(count($mfActive) > 0)
            <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap items-center gap-2">
                <span class="text-xs text-gray-500 mr-1">
                    {{ $members->total() }} {{ \Illuminate\Support\Str::plural('result', $members->total()) }} for:
                </span>
                @foreach($mfActive as $key => $chip)
                    <a href="{{ $mfKeep([], [$key]) }}" title="Remove this filter"
                        class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-primary-50 text-primary-700 text-xs font-medium rounded-full border border-primary-100 hover:bg-primary-100 transition-colors">
                        <span class="text-primary-400">{{ $chip[0] }}:</span> {{ $chip[1] }}
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </a>
                @endforeach
                <a href="{{ route('dashboard.members', array_filter(['filter' => request('filter'), 'search' => request('search')])) }}"
                    class="text-xs font-medium text-gray-500 hover:text-red-600 ml-1">Clear all</a>
            </div>
        @endif
    </form>

    <!-- Category Cards -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="stat-card cursor-pointer hover:shadow-lg hover:border-primary-200 transition-all group flex items-center justify-center w-full"
            data-action="open-member-category-modal">
            <div class="flex items-center justify-between w-full">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Members</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $memberCategoryCounts->sum() }}</p>
                    <!-- <p class="text-xs text-primary-500 mt-1 flex items-center">
                        <i data-lucide="users" class="w-3 h-3 mr-1"></i>
                        Click to view breakdown
                    </p> -->
                </div>
                <div
                    class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                    <i data-lucide="users" class="w-6 h-6 text-primary-600"></i>
                </div>
            </div>
        </div>

        <!-- Filtered Results card (count follows the active filters) -->
        @php
            $rs = $resultStats ?? ['total' => $members->total(), 'male' => 0, 'female' => 0, 'unspecified' => 0];
            $rsHasFilters = count($mfActive ?? []) > 0 || request('search') || request('filter', 'all') !== 'all';
            $rsStatusLabels = ['active' => 'Active', 'pending' => 'Pending', 'inactive' => 'Inactive'];
            $rsParts = [];
            if (isset($rsStatusLabels[request('filter')]))
                $rsParts[] = $rsStatusLabels[request('filter')];
            foreach (($mfActive ?? []) as $chip)
                $rsParts[] = $chip[1];
            if (request('search'))
                $rsParts[] = '"' . request('search') . '"';
            $rsPct = fn($n) => $rs['total'] > 0 ? round($n / $rs['total'] * 100) : 0;
            $rsSexUrl = fn($sex) => route('dashboard.members', array_merge(request()->except(['page', 'sex']), $sex !== '' ? ['sex' => $sex] : []));
        @endphp
        <div class="stat-card {{ $rsHasFilters ? 'border-primary-200' : '' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 mb-1">{{ $rsHasFilters ? 'Filtered Results' : 'All Accounts' }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($rs['total']) }}</p>
                    <p class="text-xs mt-1 flex items-center {{ $rsHasFilters ? 'text-primary-600' : 'text-gray-400' }}">
                        <i data-lucide="filter" class="w-3 h-3 mr-1 shrink-0"></i>
                        <span class="truncate">{{ $rsHasFilters ? implode(' · ', $rsParts) : 'No filters applied' }}</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="list-filter" class="w-6 h-6 text-primary-600"></i>
                </div>
            </div>

            <!-- Sex breakdown of the current results -->
            <div class="mt-4">
                <div class="flex h-1.5 rounded-full overflow-hidden bg-gray-100">
                    <div style="width: {{ $rsPct($rs['male']) }}%; background:#3b82f6"></div>
                    <div style="width: {{ $rsPct($rs['female']) }}%; background:#ec4899"></div>
                    <div style="width: {{ $rsPct($rs['unspecified']) }}%; background:#cbd5e1"></div>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    <a href="{{ $rsSexUrl('Male') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-gray-900">
                        <span class="w-2 h-2 rounded-full" style="background:#3b82f6"></span>
                        Male <strong class="text-gray-900">{{ $rs['male'] }}</strong>
                    </a>
                    <a href="{{ $rsSexUrl('Female') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-gray-900">
                        <span class="w-2 h-2 rounded-full" style="background:#ec4899"></span>
                        Female <strong class="text-gray-900">{{ $rs['female'] }}</strong>
                    </a>
                    <a href="{{ $rsSexUrl('unspecified') }}"
                        class="flex items-center gap-1.5 text-gray-600 hover:text-gray-900">
                        <span class="w-2 h-2 rounded-full" style="background:#cbd5e1"></span>
                        Not set <strong class="text-gray-900">{{ $rs['unspecified'] }}</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>



    <!-- Members Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        @php
                            $isStaff = !in_array(strtolower($member->role ?? ''), ['member', 'pending', 'inactive']);
                            $roleName = $isStaff ? ($roles->firstWhere('slug', $member->role)?->name ?? ucfirst($member->role)) : null;
                            $adminArr = $adminList->firstWhere('id', $member->id);
                        @endphp
                        <tr class="cursor-pointer hover:bg-gray-50 transition-colors"
                            data-action="{{ strtolower($member->role ?? '') === 'pending' ? 'openMemberReviewModal' : 'openMemberDetailModal' }}"
                            data-arg='[{{ $member->id }}]'>
                            <td class="text-sm font-medium text-gray-900">
                                {{ $isStaff ? 'ADM-' : 'MEM-' }}{{ str_pad($member->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full {{ $member->role === 'pending' ? 'bg-gradient-to-br from-yellow-400 to-orange-400' : 'bg-gradient-to-br from-primary-400 to-primary-600' }} flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ $member->first_name }}
                                            {{ $member->last_name }}</span>
                                        @if($isStaff)
                                            <p class="text-xs text-gray-400">{{ $member->username }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600">{{ $member->email }}</td>
                            <td class="text-sm text-gray-600">
                                {{ $isStaff ? 'Allied Workers' : ($member->membership_category ?? 'Investor Associate') }}
                            </td>
                            <td>
                                @if($isStaff)
                                    <span class="badge badge-info">{{ $roleName }}</span>
                                @elseif($member->role === 'pending' || $member->role === 'Pending')
                                    <span class="text-sm text-gray-600">Pending</span>
                                @elseif($member->role === 'inactive' || $member->status === 'inactive')
                                    <span class="text-sm text-gray-600">Inactive</span>
                                @else
                                    <span class="text-sm text-gray-600">Member</span>
                                @endif
                            </td>
                            <td>
                                @if($member->status === 'reactivation_pending')
                                    <span class="badge badge-warning">Reactivation Pending</span>
                                @elseif($member->status === 'awaiting_release')
                                    <span
                                        class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase rounded-full border border-blue-100">Awaiting
                                        Release</span>
                                @elseif($member->status === 'resigned')
                                    <span class="badge badge-gray">Resigned</span>
                                @elseif($isStaff)
                                    <span
                                        class="badge {{ ($member->status ?? 'active') === 'active' ? 'badge-success' : 'badge-gray' }}">{{ ucfirst($member->status ?? 'Active') }}</span>
                                @elseif($member->status === 'inactive' || $member->role === 'inactive')
                                    <span class="badge badge-gray">Inactive</span>
                                @elseif($member->role === 'member' || $member->role === 'active' || $member->role === 'Member')
                                    <span class="badge badge-success">Active</span>
                                @elseif($member->role === 'pending' || $member->role === 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-gray">{{ ucfirst($member->role) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="users" class="w-8 h-8 text-gray-300"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">No members found</h3>
                                <p class="text-sm text-gray-500">Try adjusting your search or filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($members->hasPages())
        <div class="flex items-center justify-between mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">
                Showing {{ $members->firstItem() ?? 1 }} to {{ $members->lastItem() ?? $members->count() }} of
                {{ $members->total() }} accounts
            </p>
            <div class="flex items-center gap-1">
                @if($members->onFirstPage())
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                @else
                    <a href="{{ $members->previousPageUrl() }}"
                        class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                @endif

                @foreach($members->getUrlRange(max(1, $members->currentPage() - 2), min($members->lastPage(), $members->currentPage() + 2)) as $page => $url)
                    @if($page == $members->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-primary-600 text-white font-medium">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($members->hasMorePages())
                    <a href="{{ $members->nextPageUrl() }}"
                        class="p-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                @else
                    <button class="p-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed" disabled>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Edit Admin Modal -->
    @if(auth()->user()?->isMainAdmin())
        <div id="editAdminModal" class="modal-overlay hidden" style="display:none">
            <div class="modal max-w-2xl">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <i data-lucide="user-cog" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Edit Admin Account</h2>
                                <p class="text-xs text-gray-500">Update role, permissions, and account details</p>
                            </div>
                        </div>
                        <button data-action="closeModal" data-arg='["editAdminModal"]'
                            class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                        </button>
                    </div>
                </div>
                <form id="editAdminForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="edit_admin_id">
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" id="edit_first_name" class="input" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" id="edit_last_name" class="input" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="edit_email" class="input" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select name="role" id="edit_role" class="input" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-800 mb-1">Sidebar Access Permissions</h3>
                            <p class="text-xs text-gray-500">Sidebar permissions are inherited from the selected role. Edit the
                                role to change permissions.</p>
                            <div class="mt-2 flex flex-wrap gap-1" id="edit_role_perms_display"></div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" data-action="closeModal" data-arg='["editAdminModal"]'
                            class="btn btn-outline">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <script type="application/json" id="admin-list-data">
                                                                                                                                                                                                                                {!! json_encode($adminList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}
                                                                                                                                                                                                                            </script>
        <script type="application/json" id="roles-data">
                                                                                                                                                                                                                                {!! json_encode($roles->map(fn($r) => ['slug' => $r->slug, 'name' => $r->name, 'sidebar_permissions' => $r->sidebar_permissions]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}
                                                                                                                                                                                                                            </script>
    @endif

    <!-- Resignation Requests -->
    @if($resignationRequests->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="log-out" class="w-5 h-5 text-orange-500"></i>
                Resignation Requests
                <span
                    class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">{{ $resignationRequests->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Requested</th>
                                <th>Withdraw Share Capital</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resignationRequests as $rr)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-red-400 flex items-center justify-center">
                                                <span
                                                    class="text-white font-bold text-sm">{{ strtoupper(substr($rr->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($rr->user->last_name ?? '', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $rr->user->first_name ?? '' }}
                                                {{ $rr->user->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">{{ $rr->created_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        @if($rr->withdraw_share_capital)
                                            <span class="badge badge-warning">Yes (withdraw)</span>
                                        @else
                                            <span class="badge badge-info">No (leave Share Capital)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('resignation.approve', $rr->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs px-2 py-1">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                    Approve
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-xs px-2 py-1 js-reject-resign-btn"
                                                data-resign-id="{{ $rr->id }}">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div id="rejectResignModal" class="modal-overlay hidden z-[9999]" style="display:none">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Reject Resignation Request</h2>
                            <p class="text-xs text-gray-500">Please give a reason — the member will see this.</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["rejectResignModal"]'
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <form id="rejectResignForm">
                @csrf
                <input type="hidden" name="id" id="reject_resign_id">
                <div class="pe-6 pt-6 ps-6 pb-0">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection <span
                            class="text-red-500">*</span></label>

                    <select id="reject_resign_select" class="input">
                        <option value="">Select a reason...</option>
                        <option value="Outstanding loan balance must be settled first">Outstanding loan balance must be
                            settled first</option>
                        <option value="Member is a guarantor or co-maker on an active loan">Member is a guarantor or
                            co-maker on an active loan</option>
                        <option value="Incomplete or missing requirements">Incomplete or missing requirements</option>
                        <option value="Unpaid contributions or dues">Unpaid contributions or dues</option>
                        <option value="Other">Other</option>
                    </select>

                    <div id="reject_resign_other_wrap" class="hidden mt-3">
                        <textarea name="rejection_reason" id="reject_resign_reason" class="input" rows="3" maxlength="500"
                            placeholder="Please specify the reason..."></textarea>
                    </div>

                    <p id="reject_resign_error" class="text-xs text-red-600 mt-1 hidden"></p>
                </div>
                <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" data-action="closeModal" data-arg='["rejectResignModal"]'
                        class="btn btn-outline">Cancel</button>
                    <button type="submit" id="rejectResignSubmitBtn" class="btn btn-danger">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- In-Process Resignations -->
    @if($inProcessResignations->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="clock" class="w-5 h-5 text-blue-500"></i>
                In-Process Resignations
                <span
                    class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">{{ $inProcessResignations->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Approved</th>
                                <th>Release Date</th>
                                <th>Days Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inProcessResignations as $rr)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-400 flex items-center justify-center">
                                                <span
                                                    class="text-white font-bold text-sm">{{ strtoupper(substr($rr->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($rr->user->last_name ?? '', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $rr->user->first_name ?? '' }}
                                                {{ $rr->user->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $rr->approved_at ? $rr->approved_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $rr->release_date ? $rr->release_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $daysLeft = $rr->release_date ? now()->startOfDay()->diffInDays($rr->release_date, false) : 0;
                                        @endphp
                                        @if($daysLeft > 0)
                                            <span class="badge badge-warning">{{ $daysLeft }} days</span>
                                        @elseif($daysLeft === 0)
                                            <span class="badge badge-success">Ready for release</span>
                                        @else
                                            <span class="badge badge-success">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Reactivation Requests -->
    @php $reactivationRequests = $reactivationRequests ?? collect(); @endphp
    @if($reactivationRequests->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-5 h-5 text-amber-500"></i>
                Reactivation Requests
                <span
                    class="ml-2 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">{{ $reactivationRequests->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Requested</th>
                                <th>Share Capital</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reactivationRequests as $reqUser)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-400 flex items-center justify-center">
                                                <span
                                                    class="text-white font-bold text-sm">{{ strtoupper(substr($reqUser->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($reqUser->last_name ?? '', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $reqUser->first_name ?? '' }}
                                                {{ $reqUser->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        @if($reqUser->updated_at)
                                            {{ $reqUser->updated_at->format('M d, Y g:i A') }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        @php
                                            $reqScRow = \App\Models\share_capital_account_tbl::where('user_id', $reqUser->id)->first();
                                            $reqScStats = $reqScRow ? \App\Http\Controllers\ShareCapital::paidUpForAccount($reqScRow->id) : null;
                                        @endphp
                                        @if($reqScStats && $reqScStats['amount'] > 0)
                                            ₱{{ number_format($reqScStats['amount'], 2) }} ({{ $reqScStats['shares'] }} shares)
                                        @else
                                            <span class="text-gray-400">No remaining share capital</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('member.reactivation.approve', $reqUser->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs px-2 py-1">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('member.reactivation.reject', $reqUser->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-xs px-2 py-1"
                                                    data-action="stop-propagation" data-confirm="Reject this reactivation request?">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Resignees -->
    @if($resignees->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="user-x" class="w-5 h-5 text-gray-500"></i>
                Resignees
                <span
                    class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">{{ $resignees->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Processed</th>
                                <th>Share Capital</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resignees as $rr)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center">
                                                <span
                                                    class="text-white font-bold text-sm">{{ strtoupper(substr($rr->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($rr->user->last_name ?? '', 0, 1)) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $rr->user->first_name ?? '' }}
                                                {{ $rr->user->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $rr->updated_at ? $rr->updated_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @if($rr->withdraw_share_capital)
                                            @if($rr->is_released)
                                                <span class="badge badge-gray">Released</span>
                                            @else
                                                <span class="badge badge-warning">Pending Release</span>
                                            @endif
                                        @else
                                            <span class="badge badge-info">Retained</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rr->is_released || ($rr->status === 'approved' && !$rr->withdraw_share_capital))
                                            <span class="badge badge-gray">Resigned</span>
                                        @else
                                            <span class="badge badge-warning">{{ ucfirst($rr->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Member Category Modal -->
    <div id="memberCategoryModal" class="modal-overlay hidden" style="display:none">
        <div class="modal max-w-md">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <i data-lucide="users" class="w-5 h-5 text-primary-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Member Categories</h2>
                            <p class="text-xs text-gray-500">Category breakdown</p>
                        </div>
                    </div>
                    <button data-action="hide-modal" data-target="memberCategoryModal"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Investor Associate</span>
                    <span
                        class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Investor Associate'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Driver</span>
                    <span class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Driver'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Dispatcher</span>
                    <span class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Dispatcher'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Driver-Operator</span>
                    <span
                        class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Driver-Operator'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Allied Worker</span>
                    <span
                        class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Allied Worker'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Transport Entrepreneur</span>
                    <span
                        class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Transport Entrepreneur'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Operator</span>
                    <span class="text-sm font-bold text-primary-600">{{ $memberCategoryCounts['Operator'] ?? 0 }}</span>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end">
                <button data-action="hide-modal" data-target="memberCategoryModal"
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
            </div>
        </div>
    </div>

    @include('admin_components._admin_category_breakdown_modal')

    <!-- Import Members Modal -->
    <div id="importMembersModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Import Existing Members</h2>
                        <p class="text-xs text-gray-500">Members, share capital, savings and active loans</p>
                    </div>
                </div>
                <button type="button" data-action="closeModal" data-arg='["importMembersModal"]'
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>
 
            <form method="POST" action="{{ route('members.import') }}" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excel file (.xlsx) <span
                            class="text-red-500">*</span></label>
                    <input type="file" name="import_file" accept=".xlsx,.xls" class="input" required>
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                    Members log in with the <strong>Password</strong> column in your Excel file (min 8 characters).
                    If a Password cell is blank, the password is set to <strong>FirstName@123456</strong> (e.g. Alfredo@123456).
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    Existing emails are not duplicated. Balances already imported for a member are skipped if you
                    upload the same file twice. Back up your database before a large import.
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" data-action="closeModal" data-arg='["importMembersModal"]'
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="modal-overlay hidden">
        <div class="modal max-w-4xl">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="user-plus" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Add New Member</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Create a new
                                cooperative member</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["addMemberModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('member.store') }}" class="p-6 space-y-6">
                @csrf
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i data-lucide="user" class="w-4 h-4 text-primary-500"></i>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="first_name" class="input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="last_name" class="input" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="username" class="input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="email" class="input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span
                                    class="text-red-500">*</span></label>
                            <input type="password" name="password" class="input" required>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i data-lucide="info" class="w-4 h-4 text-primary-500"></i>
                        Additional Details
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Membership Category <span
                                    class="text-red-500">*</span></label>
                            <select name="membership_category" class="input" required>
                                <option value="">Select...</option>
                                <option value="Operator">Operator</option>
                                <option value="Driver">Driver</option>
                                <option value="Dispatcher">Dispatcher</option>
                                <option value="Driver-Operator">Driver-Operator</option>
                                <option value="Transport Entrepreneur">Transport Entrepreneur</option>
                                <option value="Investor Associate">Investor Associate</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="date_of_birth" class="input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Place of Birth</label>
                            <input type="text" name="place_of_birth" class="input">
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sex</label>
                            <select name="sex" class="input">
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Civil Status <span
                                    class="text-red-500">*</span></label>
                            <select name="civil_status" class="input" required>
                                <option value="">Select...</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Citizenship</label>
                            <select name="citizenship" class="input">
                                <option value="">Select...</option>
                                <option value="Filipino" selected>Filipino</option>
                                <option value="Afghan">Afghan</option>
                                <option value="Albanian">Albanian</option>
                                <option value="Algerian">Algerian</option>
                                <option value="American">American</option>
                                <option value="Andorran">Andorran</option>
                                <option value="Angolan">Angolan</option>
                                <option value="Antiguan">Antiguan</option>
                                <option value="Argentine">Argentine</option>
                                <option value="Armenian">Armenian</option>
                                <option value="Australian">Australian</option>
                                <option value="Austrian">Austrian</option>
                                <option value="Azerbaijani">Azerbaijani</option>
                                <option value="Bahamian">Bahamian</option>
                                <option value="Bahraini">Bahraini</option>
                                <option value="Bangladeshi">Bangladeshi</option>
                                <option value="Barbadian">Barbadian</option>
                                <option value="Belarusian">Belarusian</option>
                                <option value="Belgian">Belgian</option>
                                <option value="Belizean">Belizean</option>
                                <option value="Beninese">Beninese</option>
                                <option value="Bhutanese">Bhutanese</option>
                                <option value="Bolivian">Bolivian</option>
                                <option value="Bosnian">Bosnian</option>
                                <option value="Botswanan">Botswanan</option>
                                <option value="Brazilian">Brazilian</option>
                                <option value="Bruneian">Bruneian</option>
                                <option value="Bulgarian">Bulgarian</option>
                                <option value="Burkinabé">Burkinabé</option>
                                <option value="Burundian">Burundian</option>
                                <option value="Cambodian">Cambodian</option>
                                <option value="Cameroonian">Cameroonian</option>
                                <option value="Canadian">Canadian</option>
                                <option value="Cape Verdean">Cape Verdean</option>
                                <option value="Central African">Central African</option>
                                <option value="Chadian">Chadian</option>
                                <option value="Chilean">Chilean</option>
                                <option value="Chinese">Chinese</option>
                                <option value="Colombian">Colombian</option>
                                <option value="Comorian">Comorian</option>
                                <option value="Congolese">Congolese</option>
                                <option value="Costa Rican">Costa Rican</option>
                                <option value="Croatian">Croatian</option>
                                <option value="Cuban">Cuban</option>
                                <option value="Cypriot">Cypriot</option>
                                <option value="Czech">Czech</option>
                                <option value="Danish">Danish</option>
                                <option value="Djiboutian">Djiboutian</option>
                                <option value="Dominican">Dominican</option>
                                <option value="Dutch">Dutch</option>
                                <option value="East Timorese">East Timorese</option>
                                <option value="Ecuadorian">Ecuadorian</option>
                                <option value="Egyptian">Egyptian</option>
                                <option value="Emirati">Emirati</option>
                                <option value="Equatorial Guinean">Equatorial Guinean</option>
                                <option value="Eritrean">Eritrean</option>
                                <option value="Estonian">Estonian</option>
                                <option value="Eswatini">Eswatini</option>
                                <option value="Ethiopian">Ethiopian</option>
                                <option value="Fijian">Fijian</option>
                                <option value="Finnish">Finnish</option>
                                <option value="French">French</option>
                                <option value="Gabonese">Gabonese</option>
                                <option value="Gambian">Gambian</option>
                                <option value="Georgian">Georgian</option>
                                <option value="German">German</option>
                                <option value="Ghanaian">Ghanaian</option>
                                <option value="Greek">Greek</option>
                                <option value="Grenadian">Grenadian</option>
                                <option value="Guatemalan">Guatemalan</option>
                                <option value="Guinean">Guinean</option>
                                <option value="Guinea-Bissauan">Guinea-Bissauan</option>
                                <option value="Guyanese">Guyanese</option>
                                <option value="Haitian">Haitian</option>
                                <option value="Honduran">Honduran</option>
                                <option value="Hungarian">Hungarian</option>
                                <option value="I-Kiribati">I-Kiribati</option>
                                <option value="Icelandic">Icelandic</option>
                                <option value="Indian">Indian</option>
                                <option value="Indonesian">Indonesian</option>
                                <option value="Iranian">Iranian</option>
                                <option value="Iraqi">Iraqi</option>
                                <option value="Irish">Irish</option>
                                <option value="Israeli">Israeli</option>
                                <option value="Italian">Italian</option>
                                <option value="Ivorian">Ivorian</option>
                                <option value="Jamaican">Jamaican</option>
                                <option value="Japanese">Japanese</option>
                                <option value="Jordanian">Jordanian</option>
                                <option value="Kazakhstani">Kazakhstani</option>
                                <option value="Kenyan">Kenyan</option>
                                <option value="Kittitian">Kittitian</option>
                                <option value="Korean (North)">Korean (North)</option>
                                <option value="Korean (South)">Korean (South)</option>
                                <option value="Kosovar">Kosovar</option>
                                <option value="Kuwaiti">Kuwaiti</option>
                                <option value="Kyrgyzstani">Kyrgyzstani</option>
                                <option value="Laotian">Laotian</option>
                                <option value="Latvian">Latvian</option>
                                <option value="Lebanese">Lebanese</option>
                                <option value="Lesothan">Lesothan</option>
                                <option value="Liberian">Liberian</option>
                                <option value="Libyan">Libyan</option>
                                <option value="Liechtensteiner">Liechtensteiner</option>
                                <option value="Lithuanian">Lithuanian</option>
                                <option value="Luxembourgish">Luxembourgish</option>
                                <option value="Macedonian">Macedonian</option>
                                <option value="Malagasy">Malagasy</option>
                                <option value="Malawian">Malawian</option>
                                <option value="Malaysian">Malaysian</option>
                                <option value="Maldivian">Maldivian</option>
                                <option value="Malian">Malian</option>
                                <option value="Maltese">Maltese</option>
                                <option value="Marshallese">Marshallese</option>
                                <option value="Mauritanian">Mauritanian</option>
                                <option value="Mauritian">Mauritian</option>
                                <option value="Mexican">Mexican</option>
                                <option value="Micronesian">Micronesian</option>
                                <option value="Moldovan">Moldovan</option>
                                <option value="Monégasque">Monégasque</option>
                                <option value="Mongolian">Mongolian</option>
                                <option value="Montenegrin">Montenegrin</option>
                                <option value="Moroccan">Moroccan</option>
                                <option value="Mozambican">Mozambican</option>
                                <option value="Myanmarese">Myanmarese</option>
                                <option value="Namibian">Namibian</option>
                                <option value="Nauruan">Nauruan</option>
                                <option value="Nepalese">Nepalese</option>
                                <option value="New Zealander">New Zealander</option>
                                <option value="Nicaraguan">Nicaraguan</option>
                                <option value="Nigerien">Nigerien</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="Norwegian">Norwegian</option>
                                <option value="Omani">Omani</option>
                                <option value="Pakistani">Pakistani</option>
                                <option value="Palauan">Palauan</option>
                                <option value="Palestinian">Palestinian</option>
                                <option value="Panamanian">Panamanian</option>
                                <option value="Papua New Guinean">Papua New Guinean</option>
                                <option value="Paraguayan">Paraguayan</option>
                                <option value="Peruvian">Peruvian</option>
                                <option value="Polish">Polish</option>
                                <option value="Portuguese">Portuguese</option>
                                <option value="Qatari">Qatari</option>
                                <option value="Romanian">Romanian</option>
                                <option value="Russian">Russian</option>
                                <option value="Rwandan">Rwandan</option>
                                <option value="Saint Lucian">Saint Lucian</option>
                                <option value="Salvadoran">Salvadoran</option>
                                <option value="Samoan">Samoan</option>
                                <option value="San Marinese">San Marinese</option>
                                <option value="São Toméan">São Toméan</option>
                                <option value="Saudi">Saudi</option>
                                <option value="Senegalese">Senegalese</option>
                                <option value="Serbian">Serbian</option>
                                <option value="Seychellois">Seychellois</option>
                                <option value="Sierra Leonean">Sierra Leonean</option>
                                <option value="Singaporean">Singaporean</option>
                                <option value="Slovak">Slovak</option>
                                <option value="Slovenian">Slovenian</option>
                                <option value="Solomon Islander">Solomon Islander</option>
                                <option value="Somali">Somali</option>
                                <option value="South African">South African</option>
                                <option value="South Sudanese">South Sudanese</option>
                                <option value="Spanish">Spanish</option>
                                <option value="Sri Lankan">Sri Lankan</option>
                                <option value="Sudanese">Sudanese</option>
                                <option value="Surinamese">Surinamese</option>
                                <option value="Swedish">Swedish</option>
                                <option value="Swiss">Swiss</option>
                                <option value="Syrian">Syrian</option>
                                <option value="Taiwanese">Taiwanese</option>
                                <option value="Tajik">Tajik</option>
                                <option value="Tanzanian">Tanzanian</option>
                                <option value="Thai">Thai</option>
                                <option value="Togolese">Togolese</option>
                                <option value="Tongan">Tongan</option>
                                <option value="Trinidadian">Trinidadian</option>
                                <option value="Tunisian">Tunisian</option>
                                <option value="Turkish">Turkish</option>
                                <option value="Turkmen">Turkmen</option>
                                <option value="Tuvaluan">Tuvaluan</option>
                                <option value="Ugandan">Ugandan</option>
                                <option value="Ukrainian">Ukrainian</option>
                                <option value="Uruguayan">Uruguayan</option>
                                <option value="Uzbekistani">Uzbekistani</option>
                                <option value="Vanuatuan">Vanuatuan</option>
                                <option value="Venezuelan">Venezuelan</option>
                                <option value="Vietnamese">Vietnamese</option>
                                <option value="Vincentian">Vincentian</option>
                                <option value="Yemeni">Yemeni</option>
                                <option value="Zambian">Zambian</option>
                                <option value="Zimbabwean">Zimbabwean</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact No.</label>
                            <input type="text" name="contact_no" class="input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="present_address" class="input" rows="2"></textarea>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i data-lucide="heart" class="w-4 h-4 text-pink-500"></i>
                        Family Information
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spouse Name</label>
                            <input type="text" name="spouse_name" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spouse Date of Birth</label>
                            <input type="date" name="spouse_date_birth" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Spouse Place of Birth</label>
                            <input type="text" name="spouse_place_birth" class="input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Sons</label>
                            <input type="number" name="number_son" class="input" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Daughters</label>
                            <input type="number" name="number_daughter" class="input" min="0" value="0">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 flex justify-end gap-3">
                    <button type="button" data-action="closeModal" data-arg='["addMemberModal"]'
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Member Detail Modal -->
    <div id="memberDetailModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto hidden">
        <div
            class="bg-slate-50 rounded-3xl w-full max-w-5xl my-auto shadow-2xl border border-slate-200 flex flex-col max-h-[94vh] overflow-hidden">

            <!-- Top Header Bar -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Member Profile & Accounts
                        </h2>
                        <p class="text-[10px] text-slate-400 font-medium">Cooperative member management</p>
                    </div>
                    <span id="detail-member-id"
                        class="ml-2 px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-mono font-bold rounded-lg border border-slate-200">--</span>
                </div>
                <div class="flex items-center gap-2">
                    <button data-action="printMemberSOA"
                        class="px-3 py-2 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        Print SOA
                    </button>
                    <button data-action="closeModal" data-arg='["memberDetailModal"]'
                        class="p-2 hover:bg-slate-100 rounded-xl transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                <!-- Hero Banner -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-800 text-white font-extrabold text-xl shadow-lg ring-2 ring-indigo-500/20 flex items-center justify-center overflow-hidden shrink-0">
                            <img id="detail-profile-pic" src="" alt="" class="w-full h-full object-cover hidden">
                            <span id="detail-avatar">--</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 id="detail-name" class="text-xl font-extrabold text-slate-900">--</h3>
                                <span id="detail-status"
                                    class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-extrabold uppercase rounded-full">--</span>
                                <span id="detail-category"
                                    class="px-2.5 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-extrabold uppercase rounded-full">--</span>
                            </div>
                            <p id="detail-middle-name" class="text-xs text-slate-400 mb-2">--</p>
                            <p id="detail-join-date" class="text-[10px] text-slate-400 font-medium mb-3">--</p>
                            <div class="flex flex-wrap gap-3">
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span id="detail-email" class="font-medium">--</span>
                                    <button data-action="copy-prev"
                                        class="p-0.5 hover:bg-slate-100 rounded transition-colors" title="Copy email">
                                        <i data-lucide="copy" class="w-3 h-3 text-slate-400"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span id="detail-phone" class="font-medium">--</span>
                                    <button data-action="copy-prev"
                                        class="p-0.5 hover:bg-slate-100 rounded transition-colors" title="Copy phone">
                                        <i data-lucide="copy" class="w-3 h-3 text-slate-400"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Cards -->
                    <div class="space-y-4 mt-5">
                        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 w-fit">
                            <button data-action="filterBalanceCards" data-arg='["all"]'
                                class="balance-filter-pill px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors bg-slate-900 text-white"
                                data-filter="all">All Balances</button>
                            <button data-action="filterBalanceCards" data-arg='["share"]'
                                class="balance-filter-pill px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors text-slate-500 hover:bg-slate-50"
                                data-filter="share">Share Capital</button>
                            <button data-action="filterBalanceCards" data-arg='["savings"]'
                                class="balance-filter-pill px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors text-slate-500 hover:bg-slate-50"
                                data-filter="savings">Savings</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Share Capital Card -->
                            <div id="balance-card-share"
                                class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl p-6 text-white shadow-xl shadow-indigo-100 flex flex-col justify-between min-h-[220px] relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <i data-lucide="coins" class="w-4 h-4 text-indigo-200"></i>
                                        <span
                                            class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-200">Share
                                            Capital</span>
                                    </div>
                                    <p id="detail-sc-amount" class="font-mono text-3xl sm:text-4xl font-bold mt-2">₱0.00</p>
                                    <p class="text-xs text-indigo-200 mt-1"><span id="detail-sc-shares">0</span> Shares</p>
                                    <div class="mt-2">
                                        <span id="detail-sc-status"
                                            class="inline-block px-2 py-0.5 bg-white/15 text-white text-[10px] font-extrabold uppercase rounded-full">No
                                            Account</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a id="detail-sc-see-more" href="#" target="_blank"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-white/80 hover:text-white transition-colors">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        Ledger Details
                                    </a>
                                </div>
                            </div>

                            <!-- Savings Card -->
                            <div id="balance-card-savings"
                                class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between min-h-[220px]">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <i data-lucide="landmark" class="w-4 h-4 text-slate-400"></i>
                                        <span
                                            class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Regular
                                            Savings</span>
                                    </div>
                                    <p id="detail-savings-amount"
                                        class="font-mono text-3xl sm:text-4xl font-bold text-slate-800 mt-2">₱0.00</p>
                                    <p id="detail-savings-status" class="text-xs text-slate-400 mt-1">No savings account
                                        linked</p>
                                </div>
                                <div class="mt-4">
                                    <a id="detail-savings-see-more" href="#"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        Transaction History
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-slate-200">
                    <div class="flex gap-0 overflow-x-auto">
                        <button data-action="switchMemberTab" data-arg='["overview"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-indigo-600 text-indigo-600 bg-white"
                            data-tab="overview">Overview</button>
                        <button data-action="switchMemberTab" data-arg='["financials"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-transparent text-slate-400 hover:text-slate-600"
                            data-tab="financials">Financials & Balances</button>
                        <button data-action="switchMemberTab" data-arg='["personal"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-transparent text-slate-400 hover:text-slate-600"
                            data-tab="personal">Personal & Family</button>
                        <button data-action="switchMemberTab" data-arg='["govids"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-transparent text-slate-400 hover:text-slate-600"
                            data-tab="govids">Government IDs</button>
                        <button data-action="switchMemberTab" data-arg='["vehicles"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-transparent text-slate-400 hover:text-slate-600"
                            data-tab="vehicles">Vehicles & Fleet</button>
                        <button data-action="switchMemberTab" data-arg='["settings"]'
                            class="member-tab-btn px-4 py-3 text-xs font-bold uppercase tracking-wider whitespace-nowrap border-b-2 border-transparent text-slate-400 hover:text-slate-600"
                            data-tab="settings">Account Settings</button>
                    </div>
                </div>

                <!-- Tab: Overview -->
                <div id="tab-overview" class="member-tab-panel">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Primary Bio -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center"><i
                                        data-lucide="user" class="w-3.5 h-3.5 text-indigo-600"></i></div>
                                Personal Details
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Date of Birth</span>
                                    <span id="detail-dob" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Sex</span>
                                    <span id="detail-sex" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Civil Status</span>
                                    <span id="detail-civil-status" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Citizenship</span>
                                    <span id="detail-citizenship" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Address</span>
                                    <span id="detail-address"
                                        class="text-xs font-semibold text-slate-800 text-right max-w-[200px]">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Present Address</span>
                                    <span id="detail-present-address"
                                        class="text-xs font-semibold text-slate-800 text-right max-w-[220px]">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Permanent Address</span>
                                    <span id="detail-permanent-address-2"
                                        class="text-xs font-semibold text-slate-800 text-right max-w-[220px]">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i
                                        data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-600"></i></div>
                                Account Details
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Membership</span>
                                    <span id="detail-membership" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Place of Birth</span>
                                    <span id="detail-pob" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Blood Type</span>
                                    <span id="detail-blood-type" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Height</span>
                                    <span id="detail-height" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Weight</span>
                                    <span id="detail-weight" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Gov IDs Quick View -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center"><i
                                        data-lucide="id-card" class="w-3.5 h-3.5 text-blue-600"></i></div>
                                Verified IDs
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="detail-id-chip inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600"
                                    data-field="sss">SSS: <span id="detail-sss-id">--</span></span>
                                <span
                                    class="detail-id-chip inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600"
                                    data-field="philhealth">PH: <span id="detail-philhealth-id">--</span></span>
                                <span
                                    class="detail-id-chip inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600"
                                    data-field="pagibig">PAG: <span id="detail-pagibig-id">--</span></span>
                                <span
                                    class="detail-id-chip inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600"
                                    data-field="tin">TIN: <span id="detail-tin-id">--</span></span>
                            </div>
                        </div>

                        <!-- Family Summary -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-pink-50 flex items-center justify-center"><i
                                        data-lucide="heart" class="w-3.5 h-3.5 text-pink-500"></i></div>
                                Family Summary
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Spouse</span>
                                    <span id="detail-spouse-name" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Children</span>
                                    <span class="text-xs font-semibold text-slate-800"><span id="detail-number-son">0</span>
                                        sons, <span id="detail-number-daughter">0</span> daughters</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Financials & Balances -->
                <div id="tab-financials" class="member-tab-panel hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Share Capital Summary -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center"><i
                                        data-lucide="coins" class="w-3.5 h-3.5 text-indigo-600"></i></div>
                                Share Capital
                            </h4>
                            <div class="space-y-2.5 mt-3">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Total Amount</span>
                                    <span id="fin-sc-amount" class="text-sm font-mono font-bold text-slate-800">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Total Shares</span>
                                    <span id="fin-sc-shares" class="text-sm font-bold text-slate-800">0</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Status</span>
                                    <span id="fin-sc-status"
                                        class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">No
                                        Account</span>
                                </div>
                            </div>
                            <a id="fin-sc-link" href="#"
                                class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                View Ledger
                            </a>
                        </div>

                        <!-- Savings Summary -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center"><i
                                        data-lucide="landmark" class="w-3.5 h-3.5 text-emerald-600"></i></div>
                                Regular Savings
                            </h4>
                            <div class="space-y-2.5 mt-3">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Current Balance</span>
                                    <span id="fin-savings-amount"
                                        class="text-sm font-mono font-bold text-slate-800">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Interest Accrued</span>
                                    <span id="fin-savings-interest"
                                        class="text-sm font-mono font-bold text-emerald-600">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Status</span>
                                    <span id="fin-savings-status"
                                        class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">No
                                        Account</span>
                                </div>
                            </div>
                            <a id="fin-savings-link" href="#"
                                class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                View Transactions
                            </a>
                        </div>
                    </div>

                    <!-- Active Loans -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm mt-6">
                        <h4
                            class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center"><i
                                    data-lucide="hand-coins" class="w-3.5 h-3.5 text-amber-600"></i></div>
                            Active Loans
                            <span id="fin-loans-count"
                                class="ml-auto px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-extrabold rounded-full">0</span>
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th
                                            class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Reference</th>
                                        <th
                                            class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Type</th>
                                        <th
                                            class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 text-right">
                                            Loan Amount</th>
                                        <th
                                            class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 text-right">
                                            Monthly</th>
                                        <th
                                            class="px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 text-right">
                                            Total Payable</th>
                                    </tr>
                                </thead>
                                <tbody id="fin-loans-body">
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center">
                                                <i data-lucide="check-circle" class="w-6 h-6 text-slate-300"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-400">No active loans</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Personal & Family -->
                <div id="tab-personal" class="member-tab-panel hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Info -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center"><i
                                        data-lucide="user" class="w-3.5 h-3.5 text-indigo-600"></i></div>
                                Personal Information
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Date of Birth</span>
                                    <span id="detail-dob-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Sex</span>
                                    <span id="detail-sex-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Civil Status</span>
                                    <span id="detail-civil-status-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Citizenship</span>
                                    <span id="detail-citizenship-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Place of Birth</span>
                                    <span id="detail-pob-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Blood Type</span>
                                    <span id="detail-blood-type-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Height</span>
                                    <span id="detail-height-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Weight</span>
                                    <span id="detail-weight-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Address & Contact -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i
                                        data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-600"></i></div>
                                Address & Contact
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Address</span>
                                    <span id="detail-address-2"
                                        class="text-xs font-semibold text-slate-800 text-right max-w-[220px]">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Phone</span>
                                    <span id="detail-phone-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Family -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-pink-50 flex items-center justify-center"><i
                                        data-lucide="heart" class="w-3.5 h-3.5 text-pink-500"></i></div>
                                Family Information
                            </h4>
                            <div class="space-y-2.5">
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Spouse Name</span>
                                    <span id="detail-spouse-name-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Spouse Birthdate</span>
                                    <span id="detail-spouse-dob-2" class="text-xs font-semibold text-slate-800">--</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                    <span class="text-xs text-slate-400">Sons</span>
                                    <span id="detail-number-son-2" class="text-xs font-semibold text-slate-800">0</span>
                                </div>
                                <div class="flex justify-between items-center py-1.5">
                                    <span class="text-xs text-slate-400">Daughters</span>
                                    <span id="detail-number-daughter-2"
                                        class="text-xs font-semibold text-slate-800">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center"><i
                                        data-lucide="star" class="w-3.5 h-3.5 text-amber-500"></i></div>
                                Skills & Expertise
                            </h4>
                            <div id="detail-skills-container" class="flex flex-wrap gap-1.5">
                                <span id="detail-skills" class="text-xs text-slate-500">No skills specified</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signature on File -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-3 md:col-span-2">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                            <i data-lucide="pen-tool" class="w-3.5 h-3.5 text-slate-600"></i>
                        </div>
                        Signature on File
                    </h4>
                    <div class="flex justify-center items-center py-4">
                        <img id="detail-signature-img" src="" alt="Signature"
                            class="max-w-full max-h-32 border border-slate-200 rounded-lg bg-white hidden">
                        <span id="detail-signature-empty" class="text-xs text-slate-400">No signature on file.</span>
                    </div>
                </div>

                <!-- Tab: Government IDs -->
                <div id="tab-govids" class="member-tab-panel hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <span class="text-blue-600 font-extrabold text-xs">SSS</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">SSS ID</p>
                                <p id="detail-sss-id-2" class="text-sm font-bold text-slate-800 truncate">Not provided</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <span class="text-blue-600 font-extrabold text-xs">PH</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PhilHealth ID</p>
                                <p id="detail-philhealth-id-2" class="text-sm font-bold text-slate-800 truncate">Not
                                    provided</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <span class="text-blue-600 font-extrabold text-xs">PAG</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PAG-IBIG ID</p>
                                <p id="detail-pagibig-id-2" class="text-sm font-bold text-slate-800 truncate">Not provided
                                </p>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <span class="text-blue-600 font-extrabold text-xs">TIN</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">TIN ID</p>
                                <p id="detail-tin-id-2" class="text-sm font-bold text-slate-800 truncate">Not provided</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Vehicles & Fleet -->
                <div id="tab-vehicles" class="member-tab-panel hidden">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h4
                                class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center"><i
                                        data-lucide="car" class="w-3.5 h-3.5 text-emerald-600"></i></div>
                                Registered Vehicles
                            </h4>
                        </div>
                        <div id="detail-vehicles-container">
                            <table class="w-full">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th
                                            class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Vehicle Type</th>
                                        <th
                                            class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Plate Number</th>
                                        <th
                                            class="px-5 py-3 text-center text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                            Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-vehicles-body">
                                    <tr>
                                        <td colspan="3" class="px-5 py-10 text-center">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center">
                                                <i data-lucide="car" class="w-6 h-6 text-slate-300"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-400">No vehicles registered</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Account Settings -->
                <div id="tab-settings" class="member-tab-panel hidden">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i
                                    data-lucide="settings" class="w-3.5 h-3.5 text-slate-600"></i></div>
                            Administrative Controls
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1.5">Account
                                    Status</label>
                                <select name="role" id="detail-role"
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                                    <option value="Pending">Pending</option>
                                    <option value="Member">Member</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Resignation Request Panel -->
                    <div id="detail-resignation-card"
                        class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4 mt-6 hidden">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-orange-50 flex items-center justify-center"><i
                                    data-lucide="log-out" class="w-3.5 h-3.5 text-orange-500"></i></div>
                            Resignation Request
                            <span id="detail-resign-status-badge"
                                class="ml-auto px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full">--</span>
                        </h4>

                        <div class="space-y-2.5">
                            <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                <span class="text-xs text-slate-400">Requested On</span>
                                <span id="detail-resign-requested" class="text-xs font-semibold text-slate-800">--</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                                <span class="text-xs text-slate-400">Share Capital Preference</span>
                                <span id="detail-resign-preference" class="text-xs font-semibold text-slate-800">--</span>
                            </div>
                            <div id="detail-resign-release-row"
                                class="flex justify-between items-center py-1.5 border-b border-slate-100 hidden">
                                <span class="text-xs text-slate-400">Release Date</span>
                                <span id="detail-resign-release-date" class="text-xs font-semibold text-slate-800">--</span>
                            </div>
                            <div id="detail-resign-reason-row" class="py-1.5 hidden">
                                <span class="text-xs text-slate-400 block mb-1">Rejection Reason</span>
                                <span id="detail-resign-reason" class="text-xs font-semibold text-slate-800">--</span>
                            </div>
                        </div>

                        <!-- Pending: Approve / Reject -->
                        <div id="detail-resign-pending-actions" class="flex items-center gap-3 hidden">
                            <form id="detail-resign-approve-form" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors flex items-center justify-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    Approve
                                </button>
                            </form>
                            <button type="button" id="detail-resign-reject-btn"
                                class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors flex items-center justify-center gap-2">
                                <i data-lucide="x" class="w-4 h-4"></i>
                                Reject
                            </button>
                        </div>

                        <!-- Approved + withdraw + not yet released: Release button -->
                        <div id="detail-resign-release-actions" class="hidden">
                            <form id="detail-resign-release-form" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors flex items-center justify-center gap-2">
                                    <i data-lucide="unlock" class="w-4 h-4"></i>
                                    Release Share Capital
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 bg-white shrink-0">
                <span class="text-[10px] text-slate-400 font-medium">Cooperative Member Records &bull; CDA Compliant</span>
                <div class="flex items-center gap-3">
                    <button data-action="closeModal" data-arg='["memberDetailModal"]'
                        class="px-5 py-2.5 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-100 transition-colors">Close</button>
                    <button id="detail-save-btn"
                        class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl font-bold uppercase tracking-wider text-xs transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Save & Confirm Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Application Review Modal -->
    <div id="pendingDetailModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto hidden">
        <div
            class="bg-slate-50 rounded-3xl w-full max-w-4xl my-auto shadow-2xl border border-slate-200 flex flex-col max-h-[94vh] overflow-hidden">

            <!-- Top Header Bar -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Pending Application
                            Review</h2>
                        <p class="text-[10px] text-slate-400 font-medium">Details submitted in the member application</p>
                    </div>
                    <span id="review-member-id"
                        class="ml-2 px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-mono font-bold rounded-lg border border-slate-200">--</span>
                </div>
                <div class="flex items-center gap-2">
                    <button data-action="closeModal" data-arg='["pendingDetailModal"]'
                        class="p-2 hover:bg-slate-100 rounded-xl transition-colors">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </button>
                </div>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                <!-- Hero Banner -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-5">
                        <div id="review-avatar-circle"
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-extrabold text-xl shadow-lg ring-2 ring-amber-500/20 flex items-center justify-center overflow-hidden shrink-0">
                            <img id="review-profile-pic" src="" alt="" class="w-full h-full object-cover hidden">
                            <span id="review-avatar">--</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 id="review-name" class="text-xl font-extrabold text-slate-900">--</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span id="review-category"
                                    class="px-2.5 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-extrabold uppercase rounded-full">--</span>
                            </div>
                            <p id="review-applied" class="text-[10px] text-slate-400 font-medium mt-1.5">--</p>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i data-lucide="user"
                                class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Personal Information
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Date
                                of Birth</label>
                            <p id="review-dob" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Sex</label>
                            <p id="review-sex" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Civil
                                Status</label>
                            <p id="review-civil-status" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Citizenship</label>
                            <p id="review-citizenship" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Place
                                of Birth</label>
                            <p id="review-pob" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Blood
                                Type</label>
                            <p id="review-blood-type" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Height</label>
                            <p id="review-height" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Weight</label>
                            <p id="review-weight" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i data-lucide="mail"
                                class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Contact Information
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Email</label>
                            <p id="review-email" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Contact
                                Number</label>
                            <p id="review-phone" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Present
                                Address</label>
                            <p id="review-address" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Permanent
                                Address</label>
                            <p id="review-permanent-address" class="text-xs font-semibold text-slate-800">--</p>
                        </div>
                    </div>
                </div>

                <!-- Government IDs -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i
                                data-lucide="id-card" class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Government IDs
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">SSS
                                ID</label>
                            <p id="review-sss" class="text-xs font-semibold text-slate-800">Not provided</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">PhilHealth
                                ID</label>
                            <p id="review-philhealth" class="text-xs font-semibold text-slate-800">Not provided</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Pag-IBIG
                                ID</label>
                            <p id="review-pagibig" class="text-xs font-semibold text-slate-800">Not provided</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">TIN
                                ID</label>
                            <p id="review-tin" class="text-xs font-semibold text-slate-800">Not provided</p>
                        </div>
                    </div>
                </div>

                <!-- Family -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i data-lucide="users"
                                class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Family
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Spouse
                                Name</label>
                            <p id="review-spouse" class="text-xs font-semibold text-slate-800">Not specified</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Number
                                of Sons</label>
                            <p id="review-sons" class="text-xs font-semibold text-slate-800">0</p>
                        </div>
                        <div><label
                                class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-1">Number
                                of Daughters</label>
                            <p id="review-daughters" class="text-xs font-semibold text-slate-800">0</p>
                        </div>
                    </div>
                </div>

                <!-- Registered Vehicles -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i data-lucide="car"
                                class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Registered Vehicles
                    </h4>
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                    Vehicle Type</th>
                                <th
                                    class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                    Plate Number</th>
                                <th
                                    class="px-5 py-3 text-center text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                    Qty</th>
                            </tr>
                        </thead>
                        <tbody id="review-vehicles-body">
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center">
                                    <p class="text-xs font-semibold text-slate-400">No vehicles registered</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Skills -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center"><i
                                data-lucide="sparkles" class="w-3.5 h-3.5 text-slate-600"></i></div>
                        Skills
                    </h4>
                    <p id="review-skills" class="text-xs font-semibold text-slate-800">No skills specified</p>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 bg-white shrink-0">
                <span class="text-[10px] text-slate-400 font-medium">Review the application before approving</span>
                <div class="flex items-center gap-3">
                    <button data-action="closeModal" data-arg='["pendingDetailModal"]'
                        class="px-5 py-2.5 text-slate-600 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-100 transition-colors">Close</button>
                    <form id="review-decline-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" data-action="stop-propagation"
                            data-confirm="Are you sure you want to decline this application? The member record will be removed."
                            class="px-5 py-2.5 rounded-xl font-bold uppercase tracking-wider text-xs transition-colors border border-red-200 text-red-600 bg-white hover:bg-red-50 flex items-center gap-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            Decline
                        </button>
                    </form>
                    <form id="review-accept-form" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold uppercase tracking-wider text-xs transition-colors bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Accept Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="members-data">
                                            {!! json_encode($members, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!}
                                            </script>



    @php $rejectedResignations = $rejectedResignations ?? collect(); @endphp
    @if($rejectedResignations->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>
                Rejected Resignation Requests
                <span
                    class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">{{ $rejectedResignations->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Requested</th>
                                <th>Rejected On</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedResignations as $rr)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center">
                                                <span
                                                    class="text-white font-bold text-sm">{{ strtoupper(substr($rr->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($rr->user->last_name ?? '', 0, 1)) }}</span>
                                            </div>
                                            <span class=" text-sm font-medium text-gray-900">{{ $rr->user->first_name ?? '' }}
                                                {{ $rr->user->last_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600">{{ $rr->created_at->format('M d, Y') }}</td>
                                    <td class="text-sm text-gray-600">{{ $rr->updated_at->format('M d, Y') }}</td>
                                    <td class="text-sm text-gray-700 max-w-xs">{{ $rr->rejection_reason ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <script nonce="{{ csp_nonce() }}">
        document.querySelectorAll('.js-reject-resign-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const id = this.dataset.resignId;
                document.getElementById('reject_resign_id').value = id;
                document.getElementById('reject_resign_reason').value = '';
                document.getElementById('reject_resign_error').classList.add('hidden');
                openModal('rejectResignModal');
            });
        });



        function resetRejectResignForm(id) {
            document.getElementById('reject_resign_id').value = id;
            document.getElementById('reject_resign_select').value = '';
            document.getElementById('reject_resign_reason').value = '';
            document.getElementById('reject_resign_other_wrap').classList.add('hidden');
            document.getElementById('reject_resign_error').classList.add('hidden');
        }

        document.querySelectorAll('.js-reject-resign-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                resetRejectResignForm(this.dataset.resignId);
                openModal('rejectResignModal');
            });
        });

        // Show the textarea only when "Other" is selected
        document.getElementById('reject_resign_select')?.addEventListener('change', function () {
            const wrap = document.getElementById('reject_resign_other_wrap');
            const textarea = document.getElementById('reject_resign_reason');
            const isOther = this.value === 'Other';

            wrap.classList.toggle('hidden', !isOther);
            if (isOther) {
                textarea.focus();
            } else {
                textarea.value = '';
            }
        });

        document.getElementById('rejectResignForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('reject_resign_id').value;
            const selected = document.getElementById('reject_resign_select').value;
            const typed = document.getElementById('reject_resign_reason').value.trim();
            const reason = selected === 'Other' ? typed : selected;
            const errorEl = document.getElementById('reject_resign_error');
            const btn = document.getElementById('rejectResignSubmitBtn');

            if (!reason) {
                errorEl.textContent = selected === 'Other'
                    ? 'Please type the reason.'
                    : 'Please select a reason.';
                errorEl.classList.remove('hidden');
                return;
            }

            errorEl.classList.add('hidden');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Submitting...';

            fetch('/resignation/' + id + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ rejection_reason: reason }),
            })
                .then(async r => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok || data.success !== true) {
                        const validationMsg = data.errors ? Object.values(data.errors)[0][0] : null;
                        throw new Error(validationMsg || data.message || 'Something went wrong.');
                    }
                    return data;
                })
                .then(() => location.reload())
                .catch(err => {
                    errorEl.textContent = err.message;
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        const membersData = {{ Js::from($members->items()) }};
        const adminsData = {{ Js::from($adminList) }};

        function setGovIdChip(el, path) {
            el.textContent = path ? 'Uploaded' : 'Not provided';
        }

        function setGovIdFull(el, path) {
            el.innerHTML = '';
            if (!path) { el.textContent = 'Not provided'; return; }
            const a = document.createElement('a');
            a.href = '/storage/' + path;
            a.target = '_blank';
            a.rel = 'noopener';
            a.className = 'text-indigo-600 hover:underline';
            a.textContent = 'View uploaded file';
            el.appendChild(a);
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.add('hidden'));
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.dropdown')) {
                closeAllDropdowns();
            }
        });

        @if(auth()->user()?->isMainAdmin())
            // Admin Edit Form Handler
            document.getElementById('editAdminForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);

                fetch('{{ route('admin.update') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Success', data.message, 'success');
                            closeModal('editAdminModal');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    })
                    .catch(() => showToast('Error', 'Failed to update admin account', 'error'));
            });

            function openEditAdminModal(id) {
                const adminList = JSON.parse(document.getElementById('admin-list-data').textContent);
                const rolesData = JSON.parse(document.getElementById('roles-data').textContent);
                const admin = adminList.find(a => a.id === id);
                if (!admin) return;

                document.getElementById('edit_admin_id').value = admin.id;
                document.getElementById('edit_first_name').value = admin.first_name;
                document.getElementById('edit_last_name').value = admin.last_name;
                document.getElementById('edit_email').value = admin.email || '';
                document.getElementById('edit_role').value = admin.role;

                updateEditRolePerms(admin.role, rolesData);

                document.getElementById('edit_role').onchange = function () {
                    updateEditRolePerms(this.value, rolesData);
                };

                openModal('editAdminModal');
            }

            function updateEditRolePerms(slug, rolesData) {
                const container = document.getElementById('edit_role_perms_display');
                const role = rolesData.find(r => r.slug === slug);
                container.innerHTML = '';
                if (!role || !role.sidebar_permissions) {
                    container.innerHTML = '<span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Full Access</span>';
                    return;
                }
                if (role.sidebar_permissions.length === 0) {
                    container.innerHTML = '<span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-full">No Access</span>';
                    return;
                }
                role.sidebar_permissions.forEach(p => {
                    const span = document.createElement('span');
                    span.className = 'text-xs bg-primary-50 text-primary-700 px-1.5 py-0.5 rounded';
                    span.textContent = p;
                    container.appendChild(span);
                });
            }

            function confirmDeleteAdmin(id, name) {
                if (!confirm(`Are you sure you want to deactivate "${name}"? They will lose access to the system.`)) return;

                const formData = new FormData();
                formData.append('id', id);

                fetch('{{ route('admin.delete') }}', {
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
                    .catch(() => showToast('Error', 'Failed to deactivate admin account', 'error'));
            }

            document.querySelectorAll('.js-confirm-delete-admin').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    confirmDeleteAdmin(Number(this.dataset.memberId), this.dataset.memberName);
                });
            });
        @endif

            function openMemberDetailModal(memberId) {
                let member = membersData.find(m => m.id === memberId);
                if (!member) {
                    member = adminsData.find(m => m.id === memberId);
                }
                if (!member) return;

                const initials = (member.first_name?.charAt(0) || '') + (member.last_name?.charAt(0) || '');
                const fullName = (member.first_name || '') + ' ' + (member.last_name || '');

                document.getElementById('detail-avatar').textContent = initials.toUpperCase();
                document.getElementById('detail-name').textContent = fullName;
                document.getElementById('detail-middle-name').textContent = member.middle_name ? member.middle_name : '';
                const isAdmin = !['member', 'pending', 'inactive'].includes(String(member.role || '').toLowerCase());
                document.getElementById('detail-member-id').textContent = (isAdmin ? 'ADM-' : 'MEM-') + String(member.id).padStart(4, '0');
                document.getElementById('detail-email').textContent = member.email || 'N/A';
                document.getElementById('detail-present-address').textContent = member.present_address || 'N/A';
                document.getElementById('detail-phone').textContent = member.contact_no || 'N/A';
                document.getElementById('detail-dob').textContent = member.date_of_birth || 'N/A';
                document.getElementById('detail-sex').textContent = member.sex || 'N/A';
                document.getElementById('detail-civil-status').textContent = member.civil_status || 'N/A';
                document.getElementById('detail-address').textContent = member.present_address || 'N/A';
                document.getElementById('detail-membership').textContent = isAdmin ? 'Allied Workers' : (member.membership_category || 'N/A');
                document.getElementById('detail-citizenship').textContent = member.citizenship || 'N/A';
                document.getElementById('detail-pob').textContent = member.place_of_birth || 'N/A';
                document.getElementById('detail-blood-type').textContent = member.blood_type || 'N/A';
                document.getElementById('detail-height').textContent = member.height || 'N/A';
                document.getElementById('detail-weight').textContent = member.weight || 'N/A';
                document.getElementById('detail-permanent-address-2').textContent = member.permanent_address || 'N/A';

                // Duplicate fields in Personal & Family tab
                document.getElementById('detail-dob-2').textContent = member.date_of_birth || 'N/A';
                document.getElementById('detail-sex-2').textContent = member.sex || 'N/A';
                document.getElementById('detail-civil-status-2').textContent = member.civil_status || 'N/A';
                document.getElementById('detail-citizenship-2').textContent = member.citizenship || 'N/A';
                document.getElementById('detail-pob-2').textContent = member.place_of_birth || 'N/A';
                document.getElementById('detail-blood-type-2').textContent = member.blood_type || 'N/A';
                document.getElementById('detail-height-2').textContent = member.height || 'N/A';
                document.getElementById('detail-weight-2').textContent = member.weight || 'N/A';
                document.getElementById('detail-address-2').textContent = member.present_address || 'N/A';
                document.getElementById('detail-phone-2').textContent = member.contact_no || 'N/A';

                // Profile pic
                const profilePic = document.getElementById('detail-profile-pic');
                const avatar = document.getElementById('detail-avatar');
                if (member.profile_picture) {
                    profilePic.src = '/storage/' + member.profile_picture;
                    profilePic.classList.remove('hidden');
                    avatar.classList.add('hidden');
                } else {
                    profilePic.classList.add('hidden');
                    avatar.classList.remove('hidden');
                }

                // Signature
                const sigImg = document.getElementById('detail-signature-img');
                const sigEmpty = document.getElementById('detail-signature-empty');
                if (member.signature) {
                    sigImg.src = member.signature;
                    sigImg.classList.remove('hidden');
                    sigEmpty.classList.add('hidden');
                } else {
                    sigImg.classList.add('hidden');
                    sigEmpty.classList.remove('hidden');
                }

                // Join date
                const joinDate = member.created_at;
                document.getElementById('detail-join-date').textContent = joinDate ? 'Member since ' + new Date(joinDate).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' }) : '';

                // Family (Overview tab)
                document.getElementById('detail-spouse-name').textContent = member.spouse_name || 'Not specified';
                document.getElementById('detail-number-son').textContent = member.number_son || 0;
                document.getElementById('detail-number-daughter').textContent = member.number_daughter || 0;
                // Family (Personal tab)
                document.getElementById('detail-spouse-name-2').textContent = member.spouse_name || 'Not specified';
                document.getElementById('detail-spouse-dob-2').textContent = member.spouse_date_birth || 'Not specified';
                document.getElementById('detail-number-son-2').textContent = member.number_son || 0;
                document.getElementById('detail-number-daughter-2').textContent = member.number_daughter || 0;

                // Skills
                const skillsEl = document.getElementById('detail-skills');
                if (member.skills && member.skills.trim()) {
                    const skillsArray = member.skills.split(',').map(s => s.trim()).filter(s => s);
                    if (skillsArray.length > 0) {
                        skillsEl.innerHTML = skillsArray.map(skill =>
                            `<span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-bold rounded-lg">${skill}</span>`
                        ).join('');
                    } else {
                        skillsEl.textContent = 'No skills specified';
                    }
                } else {
                    skillsEl.textContent = 'No skills specified';
                }

                // Gov IDs — quick chips (Overview)
                setGovIdChip(document.getElementById('detail-sss-id'), member.sss_id);
                setGovIdChip(document.getElementById('detail-philhealth-id'), member.philhealth_id);
                setGovIdChip(document.getElementById('detail-pagibig-id'), member.pagibig_id);
                setGovIdChip(document.getElementById('detail-tin-id'), member.tin_id);
                // Gov IDs — full tab with view links
                setGovIdFull(document.getElementById('detail-sss-id-2'), member.sss_id);
                setGovIdFull(document.getElementById('detail-philhealth-id-2'), member.philhealth_id);
                setGovIdFull(document.getElementById('detail-pagibig-id-2'), member.pagibig_id);
                setGovIdFull(document.getElementById('detail-tin-id-2'), member.tin_id);

                // Vehicles
                const vehiclesBody = document.getElementById('detail-vehicles-body');
                const esc = (value) => {
                    const div = document.createElement('div');
                    div.textContent = value;
                    return div.innerHTML;
                };
                if (member.vehicles && member.vehicles.length > 0) {
                    vehiclesBody.innerHTML = member.vehicles.map(v => `
                                                                                                                                <tr class="border-t border-slate-100">
                                                                                                                                    <td class="px-5 py-3 text-xs font-semibold text-slate-800">${esc(v.vehicle_type || 'N/A')}</td>
                                                                                                                                    <td class="px-5 py-3 text-xs font-mono font-bold text-slate-800">${esc(v.plate_no || 'N/A')}</td>
                                                                                                                                    <td class="px-5 py-3 text-xs font-semibold text-slate-800 text-center">${esc(v.quantity || 1)}</td>
                                                                                                                                </tr>
                                                                                                                            `).join('');
                } else {
                    vehiclesBody.innerHTML = '<tr><td colspan="3" class="px-5 py-10 text-center"><div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center"><i data-lucide="car" class="w-6 h-6 text-slate-300"></i></div><p class="text-xs font-semibold text-slate-400">No vehicles registered</p></td></tr>';
                }

                // Status badge
                const statusEl = document.getElementById('detail-status');
                if (['admin', 'general-manager'].includes(member.role)) {
                    statusEl.className = 'px-2.5 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-extrabold uppercase rounded-full';
                    statusEl.textContent = 'Admin';
                } else if (member.role === 'Member' || member.role === 'member' || member.role === 'active') {
                    statusEl.className = 'px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-extrabold uppercase rounded-full';
                    statusEl.textContent = 'Active';
                } else if (member.role === 'pending' || member.role === 'Pending') {
                    statusEl.className = 'px-2.5 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-extrabold uppercase rounded-full';
                    statusEl.textContent = 'Pending';
                } else {
                    statusEl.className = 'px-2.5 py-0.5 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-extrabold uppercase rounded-full';
                    statusEl.textContent = member.role || 'N/A';
                }

                document.getElementById('detail-category').textContent = isAdmin ? 'Allied Workers' : (member.membership_category || 'N/A');

                // Share Capital
                document.getElementById('detail-sc-amount').textContent = '₱' + (member.sc_total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                document.getElementById('detail-sc-shares').textContent = member.sc_total_shares || 0;

                const scStatusEl = document.getElementById('detail-sc-status');
                if (member.sc_status === 'Active') {
                    scStatusEl.className = 'inline-block px-2 py-0.5 bg-white/15 text-white text-[10px] font-extrabold uppercase rounded-full';
                    scStatusEl.textContent = 'Active';
                } else if (member.sc_status === 'No Account') {
                    scStatusEl.className = 'inline-block px-2 py-0.5 bg-white/15 text-white/60 text-[10px] font-extrabold uppercase rounded-full';
                    scStatusEl.textContent = 'No Account';
                } else {
                    scStatusEl.className = 'inline-block px-2 py-0.5 bg-white/15 text-white text-[10px] font-extrabold uppercase rounded-full';
                    scStatusEl.textContent = member.sc_status || 'Inactive';
                }

                document.getElementById('detail-sc-see-more').href = '/dashboard-sharecapitals?member=' + member.id;

                // Savings Card (hero balance cards)
                document.getElementById('detail-savings-amount').textContent = '₱' + (member.savings_balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                const savingsStatusEl = document.getElementById('detail-savings-status');
                if (member.savings_status === 'Active') {
                    savingsStatusEl.textContent = 'Savings account active';
                    savingsStatusEl.className = 'text-xs text-emerald-500 mt-1';
                } else if (member.savings_status === 'No Account') {
                    savingsStatusEl.textContent = 'No savings account linked';
                    savingsStatusEl.className = 'text-xs text-slate-400 mt-1';
                } else {
                    savingsStatusEl.textContent = member.savings_status || 'No savings account linked';
                    savingsStatusEl.className = 'text-xs text-slate-400 mt-1';
                }
                document.getElementById('detail-savings-see-more').href = '/dashboard-savings?member=' + member.id;

                // Financials tab — Share Capital
                document.getElementById('fin-sc-amount').textContent = '₱' + (member.sc_total_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                document.getElementById('fin-sc-shares').textContent = member.sc_total_shares || 0;
                const finScStatusEl = document.getElementById('fin-sc-status');
                if (member.sc_status === 'Active') {
                    finScStatusEl.textContent = 'Active';
                    finScStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600';
                } else if (member.sc_status === 'No Account') {
                    finScStatusEl.textContent = 'No Account';
                    finScStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                } else {
                    finScStatusEl.textContent = member.sc_status || 'Inactive';
                    finScStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                }
                document.getElementById('fin-sc-link').href = '/dashboard-sharecapitals?member=' + member.id;

                // Financials tab — Savings
                document.getElementById('fin-savings-amount').textContent = '₱' + (member.savings_balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                document.getElementById('fin-savings-interest').textContent = '₱' + (member.savings_interest || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                const finSaStatusEl = document.getElementById('fin-savings-status');
                if (member.savings_status === 'Active') {
                    finSaStatusEl.textContent = 'Active';
                    finSaStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600';
                } else if (member.savings_status === 'No Account') {
                    finSaStatusEl.textContent = 'No Account';
                    finSaStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                } else {
                    finSaStatusEl.textContent = member.savings_status || 'Inactive';
                    finSaStatusEl.className = 'text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                }
                document.getElementById('fin-savings-link').href = '/dashboard-savings?member=' + member.id;

                // Financials tab — Active Loans
                document.getElementById('fin-loans-count').textContent = member.active_loans_count || 0;
                const loansBody = document.getElementById('fin-loans-body');
                if (member.active_loans && member.active_loans.length > 0) {
                    loansBody.innerHTML = member.active_loans.map(l => `
                                                                                                                                <tr class="border-t border-slate-100 hover:bg-slate-50">
                                                                                                                                    <td class="px-4 py-3 text-xs font-mono font-bold text-indigo-600">${l.reference_no || 'N/A'}</td>
                                                                                                                                    <td class="px-4 py-3 text-xs font-semibold text-slate-800">${l.lending_type || 'N/A'}</td>
                                                                                                                                    <td class="px-4 py-3 text-xs font-mono font-bold text-slate-800 text-right">₱${(l.lending_amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                                                                                                                                    <td class="px-4 py-3 text-xs font-mono font-semibold text-slate-600 text-right">₱${(l.monthly_payment || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                                                                                                                                    <td class="px-4 py-3 text-xs font-mono font-bold text-amber-600 text-right">₱${(l.total_payment || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                                                                                                                                </tr>
                                                                                                                            `).join('');
                } else {
                    loansBody.innerHTML = '<tr><td colspan="5" class="px-5 py-10 text-center"><div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center"><i data-lucide="check-circle" class="w-6 h-6 text-slate-300"></i></div><p class="text-xs font-semibold text-slate-400">No active loans</p></td></tr>';
                }

                const roleSelect = document.getElementById('detail-role');
                const ROLES = ['Pending', 'Member', 'inactive'];
                Array.from(roleSelect.options).forEach(o => { if (!ROLES.includes(o.value)) o.remove(); });
                roleSelect.disabled = false;

                if (isAdmin) {
                    let opt = Array.from(roleSelect.options).find(o => o.value === (member.role || 'Admin'));
                    if (!opt) {
                        opt = document.createElement('option');
                        opt.value = member.role || 'Admin';
                        opt.textContent = member.role || 'Admin';
                        roleSelect.appendChild(opt);
                    }
                    roleSelect.value = opt.value;
                    roleSelect.disabled = true;
                } else {
                    const roleValue = { member: 'Member', active: 'Member', pending: 'Pending', inactive: 'inactive' }[String(member.role || 'member').toLowerCase()] || '';
                    roleSelect.value = roleValue;
                }

                // Reset to Overview tab
                // Resignation Request panel
                const resignCard = document.getElementById('detail-resignation-card');
                const rr = member.resignation_request;

                if (!rr) {
                    resignCard.classList.add('hidden');
                } else {
                    resignCard.classList.remove('hidden');

                    document.getElementById('detail-resign-requested').textContent = rr.created_at
                        ? new Date(rr.created_at).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })
                        : '--';
                    document.getElementById('detail-resign-preference').textContent = rr.withdraw_share_capital
                        ? 'Withdraw after 60 days'
                        : 'Leave with cooperative';

                    const badge = document.getElementById('detail-resign-status-badge');
                    const pendingActions = document.getElementById('detail-resign-pending-actions');
                    const releaseActions = document.getElementById('detail-resign-release-actions');
                    const releaseRow = document.getElementById('detail-resign-release-row');
                    const reasonRow = document.getElementById('detail-resign-reason-row');

                    pendingActions.classList.add('hidden');
                    releaseActions.classList.add('hidden');
                    releaseRow.classList.add('hidden');
                    reasonRow.classList.add('hidden');

                    if (rr.status === 'pending') {
                        badge.className = 'ml-auto px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-amber-50 text-amber-600 border border-amber-100';
                        badge.textContent = 'Pending';
                        pendingActions.classList.remove('hidden');

                        document.getElementById('detail-resign-approve-form').action = '/resignation/' + rr.id + '/approve';
                        document.getElementById('detail-resign-reject-btn').onclick = function () {
                            resetRejectResignForm(rr.id);
                            openModal('rejectResignModal');
                        };
                    } else if (rr.status === 'approved') {
                        if (rr.withdraw_share_capital && !rr.is_released) {
                            badge.className = 'ml-auto px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-blue-50 text-blue-600 border border-blue-100';
                            badge.textContent = 'Awaiting Release';
                            releaseRow.classList.remove('hidden');
                            document.getElementById('detail-resign-release-date').textContent = rr.release_date
                                ? new Date(rr.release_date).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })
                                : '--';

                            const daysLeft = rr.release_date ? Math.ceil((new Date(rr.release_date) - new Date()) / 86400000) : 0;
                            if (daysLeft <= 0) {
                                releaseActions.classList.remove('hidden');
                                document.getElementById('detail-resign-release-form').action = '/resignation/' + rr.id + '/release';
                            }
                        } else {
                            badge.className = 'ml-auto px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-slate-100 text-slate-500 border border-slate-200';
                            badge.textContent = 'Resigned';
                        }
                    } else if (rr.status === 'rejected') {
                        badge.className = 'ml-auto px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-red-50 text-red-600 border border-red-100';
                        badge.textContent = 'Rejected';
                        reasonRow.classList.remove('hidden');
                        document.getElementById('detail-resign-reason').textContent = rr.rejection_reason || '--';
                    }
                }

                // Reset to Overview tab
                switchMemberTab('overview');
                filterBalanceCards('all');

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                openModal('memberDetailModal');
                window.currentMemberId = member.id;
                window.currentMember = member;
            }

        function openMemberReviewModal(memberId) {
            const member = membersData.find(m => m.id === memberId);
            if (!member) return;

            const initials = (member.first_name?.charAt(0) || '') + (member.last_name?.charAt(0) || '');
            const fullName = (member.first_name || '') + ' ' + (member.last_name || '');

            document.getElementById('review-avatar').textContent = initials.toUpperCase();
            document.getElementById('review-name').textContent = fullName;
            document.getElementById('review-member-id').textContent = 'MEM-' + String(member.id).padStart(4, '0');
            document.getElementById('review-category').textContent = member.membership_category || 'Investor Associate';
            document.getElementById('review-applied').textContent = member.created_at
                ? 'Application submitted ' + new Date(member.created_at).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' })
                : '';

            const reviewProfilePic = document.getElementById('review-profile-pic');
            const reviewAvatar = document.getElementById('review-avatar');
            if (member.profile_picture) {
                reviewProfilePic.src = '/storage/' + member.profile_picture;
                reviewProfilePic.classList.remove('hidden');
                reviewAvatar.classList.add('hidden');
            } else {
                reviewProfilePic.classList.add('hidden');
                reviewAvatar.classList.remove('hidden');
            }

            document.getElementById('review-dob').textContent = member.date_of_birth || 'N/A';
            document.getElementById('review-sex').textContent = member.sex || 'N/A';
            document.getElementById('review-civil-status').textContent = member.civil_status || 'N/A';
            document.getElementById('review-citizenship').textContent = member.citizenship || 'N/A';
            document.getElementById('review-pob').textContent = member.place_of_birth || 'N/A';
            document.getElementById('review-blood-type').textContent = member.blood_type || 'N/A';
            document.getElementById('review-height').textContent = member.height || 'N/A';
            document.getElementById('review-weight').textContent = member.weight || 'N/A';

            document.getElementById('review-email').textContent = member.email || 'N/A';
            document.getElementById('review-phone').textContent = member.contact_no || 'N/A';
            document.getElementById('review-address').textContent = member.present_address || 'N/A';
            document.getElementById('review-permanent-address').textContent = member.permanent_address || 'N/A';


            setGovIdFull(document.getElementById('review-sss'), member.sss_id);
            setGovIdFull(document.getElementById('review-philhealth'), member.philhealth_id);
            setGovIdFull(document.getElementById('review-pagibig'), member.pagibig_id);
            setGovIdFull(document.getElementById('review-tin'), member.tin_id);

            const recordVehiclesBody = document.getElementById('review-vehicles-body');
            const esc = (value) => {
                const div = document.createElement('div');
                div.textContent = value;
                return div.innerHTML;
            };
            if (member.vehicles && member.vehicles.length > 0) {
                recordVehiclesBody.innerHTML = member.vehicles.map(v => `
                                                                                                                                <tr class="border-t border-slate-100">
                                                                                                                                    <td class="px-5 py-3 text-xs font-semibold text-slate-800">${esc(v.vehicle_type || 'N/A')}</td>
                                                                                                                                    <td class="px-5 py-3 text-xs font-mono font-bold text-slate-800">${esc(v.plate_no || 'N/A')}</td>
                                                                                                                                    <td class="px-5 py-3 text-xs font-semibold text-slate-800 text-center">${esc(v.quantity || 1)}</td>
                                                                                                                                </tr>
                                                                                                                            `).join('');
            } else {
                recordVehiclesBody.innerHTML = '<tr><td colspan="3" class="px-5 py-6 text-center"><p class="text-xs font-semibold text-slate-400">No vehicles registered</p></td></tr>';
            }

            const skillsEl = document.getElementById('review-skills');
            if (member.skills && member.skills.trim()) {
                const skillsArray = member.skills.split(',').map(s => s.trim()).filter(s => s);
                skillsEl.innerHTML = skillsArray.length > 0
                    ? skillsArray.map(skill =>
                        `<span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-bold rounded-lg mr-1.5 mb-1.5">${skill}</span>`
                    ).join('')
                    : 'No skills specified';
            } else {
                skillsEl.textContent = 'No skills specified';
            }

            document.getElementById('review-accept-form').action = '/approve-user/' + memberId;
            document.getElementById('review-decline-form').action = '/dashboard-members/decline/' + memberId;

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            openModal('pendingDetailModal');
        }

        function switchMemberTab(tabName) {
            document.querySelectorAll('.member-tab-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.member-tab-btn').forEach(b => {
                b.classList.remove('border-indigo-600', 'text-indigo-600', 'bg-white');
                b.classList.add('border-transparent', 'text-slate-400');
            });
            const panel = document.getElementById('tab-' + tabName);
            if (panel) panel.classList.remove('hidden');
            const btn = document.querySelector('.member-tab-btn[data-tab="' + tabName + '"]');
            if (btn) {
                btn.classList.add('border-indigo-600', 'text-indigo-600', 'bg-white');
                btn.classList.remove('border-transparent', 'text-slate-400');
            }
        }

        function filterBalanceCards(filter) {
            const shareCard = document.getElementById('balance-card-share');
            const savingsCard = document.getElementById('balance-card-savings');
            shareCard.style.display = (filter === 'all' || filter === 'share') ? '' : 'none';
            savingsCard.style.display = (filter === 'all' || filter === 'savings') ? '' : 'none';
            document.querySelectorAll('.balance-filter-pill').forEach(pill => {
                if (pill.dataset.filter === filter) {
                    pill.classList.add('bg-slate-900', 'text-white');
                    pill.classList.remove('text-slate-500');
                } else {
                    pill.classList.remove('bg-slate-900', 'text-white');
                    pill.classList.add('text-slate-500');
                }
            });
        }

        function copyToClipboard(element) {
            const text = element.textContent || element.innerText;
            if (!text || text === '--' || text === 'N/A' || text === 'Not provided') return;
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 z-[99999] px-3 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl shadow-lg';
                toast.textContent = 'Copied!';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 1500);
            });
        }

        function printMemberSOA() {
            const member = window.currentMember || membersData.find(m => m.id === window.currentMemberId);
            if (!member) {
                showToast('Error', 'Member data unavailable. Please reopen the member profile and try again.');
                return;
            }

            const soaEsc = (value) => {
                const div = document.createElement('div');
                div.textContent = String(value ?? '');
                return div.innerHTML;
            };
            const fmt = (n) => '₱' + (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const fullName = soaEsc([member.first_name, member.last_name].filter(Boolean).join(' '));
            const memberId = 'MEM-' + String(member.id).padStart(4, '0');
            const asOf = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
            const memberSince = member.created_at ? new Date(member.created_at).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' }) : '—';

            const loans = Array.isArray(member.active_loans) ? member.active_loans : [];
            const loanRows = loans.length > 0
                ? loans.map(l => `
                                                                                                                                <tr>
                                                                                                                                    <td>${soaEsc(l.reference_no || '—')}</td>
                                                                                                                                    <td>${soaEsc(l.lending_type || '—')}</td>
                                                                                                                                    <td class="num">${fmt(l.lending_amount)}</td>
                                                                                                                                    <td class="num">${fmt(l.monthly_payment)}</td>
                                                                                                                                    <td class="num">${fmt(l.total_payment)}</td>
                                                                                                                                </tr>`).join('')
                : '<tr><td colspan="5">No active loans</td></tr>';

            const scAmount = Number(member.sc_total_amount) || 0;
            const savingsBalance = Number(member.savings_balance) || 0;
            const scrOpen = '<scr' + 'ipt>';

            const html = `<!DOCTYPE html>
                                                                                                            <html lang="en">
                                                                                                            <head>
                                                                                                                <meta charset="UTF-8">
                                                                                                                <title>Statement of Account - ${fullName}</title>
                                                                                                                <style>
                                                                                                                    * { margin: 0; padding: 0; box-sizing: border-box; }
                                                                                                                    body { font-family: 'Courier New', Courier, monospace; font-size: 10px; color: #000; padding: 15px 20px; background: #fff; }
                                                                                                                    .header { margin-bottom: 18px; text-align: center; }
                                                                                                                    .header .coop-name { font-size: 14px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
                                                                                                                    .header .title { font-size: 13px; font-weight: 700; margin-top: 4px; }
                                                                                                                    .header .subtitle { font-size: 10px; color: #333; margin-top: 2px; }
                                                                                                                    table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 16px; }
                                                                                                                    thead th { background: #e0e0e0; border: 1px solid #000; padding: 5px 8px; font-weight: 700; text-align: center; text-transform: uppercase; font-size: 9px; }
                                                                                                                    tbody td { border: 1px solid #000; padding: 4px 8px; }
                                                                                                                    .num { text-align: right; font-family: 'Courier New', Courier, monospace; }
                                                                                                                    .totals-row td { font-weight: 700; background: #e8e8e8; border-top: 2px solid #000; }
                                                                                                                    .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; margin: 14px 0 4px; letter-spacing: 0.5px; }
                                                                                                                    .toolbar { margin-bottom: 12px; }
                                                                                                                    .toolbar button { padding: 6px 18px; font-size: 12px; cursor: pointer; background: #1E2A4A; color: #fff; border: none; border-radius: 4px; margin-right: 6px; }
                                                                                                                    .toolbar .close { background: #6c757d; }
                                                                                                                    .footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #000; display: flex; justify-content: space-between; font-size: 8px; color: #555; }
                                                                                                                    @@media print {
                                                                                                                        body { padding: 10px 15px; font-size: 9px; }
                                                                                                                        thead th { font-size: 8px; padding: 4px 6px; }
                                                                                                                        tbody td { padding: 3px 6px; }
                                                                                                                        .toolbar { display: none !important; }
                                                                                                                    }
                                                                                                                    @@page { size: portrait; margin: 15mm; }
                                                                                                                </style>
                                                                                                            </head>
                                                                                                            <body>
                                                                                                                <div class="toolbar">
                                                                                                                    <button id="btn-print">Print / Save as PDF</button>
                                                                                                                    <button id="btn-close" class="close">Close</button>
                                                                                                                </div>

                                                                                                                <div class="header">
                                                                                                                    <div class="coop-name">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
                                                                                                                    <div class="title">STATEMENT OF ACCOUNT</div>
                                                                                                                    <div class="subtitle">${fullName} &middot; ${memberId} &middot; As of ${asOf}</div>
                                                                                                                </div>

                                                                                                                <table>
                                                                                                                    <tbody>
                                                                                                                        <tr><td><strong>MEMBER NAME</strong></td><td>${fullName}</td></tr>
                                                                                                                        <tr><td><strong>MEMBERSHIP NO.</strong></td><td>${memberId}</td></tr>
                                                                                                                        <tr><td><strong>MEMBERSHIP CATEGORY</strong></td><td>${soaEsc(member.membership_category || 'Investor Associate')}</td></tr>
                                                                                                                        <tr><td><strong>EMAIL</strong></td><td>${soaEsc(member.email)}</td></tr>
                                                                                                                        <tr><td><strong>CONTACT NO.</strong></td><td>${soaEsc(member.contact_no)}</td></tr>
                                                                                                                        <tr><td><strong>PRESENT ADDRESS</strong></td><td>${soaEsc(member.present_address)}</td></tr>
                                                                                                                        <tr><td><strong>MEMBER SINCE</strong></td><td>${memberSince}</td></tr>
                                                                                                                        <tr><td><strong>STATEMENT DATE</strong></td><td>${asOf}</td></tr>
                                                                                                                    </tbody>
                                                                                                                </table>

                                                                                                                <div class="section-label">Account Balances</div>
                                                                                                                <table>
                                                                                                                    <thead>
                                                                                                                        <tr><th>Account</th><th>Details</th><th>Status</th><th>Amount</th></tr>
                                                                                                                    </thead>
                                                                                                                    <tbody>
                                                                                                                        <tr>
                                                                                                                            <td>Share Capital</td>
                                                                                                                            <td>${Number(member.sc_total_shares) || 0} shares</td>
                                                                                                                            <td>${soaEsc(member.sc_status || 'No Account')}</td>
                                                                                                                            <td class="num">${fmt(scAmount)}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td>Regular Savings</td>
                                                                                                                            <td>Current balance</td>
                                                                                                                            <td>${soaEsc(member.savings_status || 'No Account')}</td>
                                                                                                                            <td class="num">${fmt(savingsBalance)}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td>Savings Interest Accrued</td>
                                                                                                                            <td>&mdash;</td>
                                                                                                                            <td>&mdash;</td>
                                                                                                                            <td class="num">${fmt(member.savings_interest)}</td>
                                                                                                                        </tr>
                                                                                                                        <tr class="totals-row">
                                                                                                                            <td colspan="3">TOTAL EQUITY (Share Capital + Savings)</td>
                                                                                                                            <td class="num">${fmt(scAmount + savingsBalance)}</td>
                                                                                                                        </tr>
                                                                                                                    </tbody>
                                                                                                                </table>

                                                                                                                <div class="section-label">Active Loans</div>
                                                                                                                <table>
                                                                                                                    <thead>
                                                                                                                        <tr><th>Reference</th><th>Type</th><th class="num">Loan Amount</th><th class="num">Monthly</th><th class="num">Total Payable</th></tr>
                                                                                                                    </thead>
                                                                                                                    <tbody>${loanRows}</tbody>
                                                                                                                </table>

                                                                                                                <div class="footer">
                                                                                                                    <span>Generated on ${asOf}</span>
                                                                                                                    <span>Kingsland Pala-Pala Multi-Purpose Cooperative</span>
                                                                                                                </div>

                                                                                                                ${scrOpen}
                                                                                                                    setTimeout(function () { window.print(); }, 300);
                                                                                                                    document.addEventListener('click', function (e) {
                                                                                                                        if (e.target.id === 'btn-print') window.print();
                                                                                                                        if (e.target.id === 'btn-close') window.close();
                                                                                                                    });
                                                                                                                <\/script>
                                                                                                            </body>
                                                                                                            </html>`;

            const w = window.open('', '_blank', 'width=900,height=760');
            if (!w) {
                showToast('Error', 'Popup blocked. Please allow popups for this site.');
                return;
            }
            w.document.open();
            w.document.write(html);
            w.document.close();
            w.focus();
        }

        document.getElementById('detail-save-btn').addEventListener('click', function () {
            const memberId = window.currentMemberId;
            const role = document.getElementById('detail-role').value;

            fetch('/dashboard-members/update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id: memberId,
                    role: role
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', 'Member updated successfully');
                        closeModal('memberDetailModal');
                        location.reload();
                    } else {
                        showToast('Error', data.message || 'Failed to update member');
                    }
                })
                .catch(error => {
                    console.error('Update error:', error);
                    showToast('Error', 'Failed to update member');
                });
        });
    </script>

    <!-- <script nonce="{{ csp_nonce() }}">
                                                                                            fetch('/resignation/' + id + '/reject', {
                                                                                                method: 'POST',
                                                                                                headers: {
                                                                                                    'Content-Type': 'application/json',
                                                                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                                                                                        ?? document.querySelector('input[name="_token"]')?.value,
                                                                                                    'Accept': 'application/json',
                                                                                                },
                                                                                                body: JSON.stringify({ rejection_reason: reason }),
                                                                                            })
                                                                                        </script> -->
@endsection