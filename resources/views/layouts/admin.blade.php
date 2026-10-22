<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KPMPCATS')</title>
    <script src="{{ asset('vendor/tailwind/tailwind.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts/inter.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#edf0f5',
                            100: '#d0d6e4',
                            200: '#a8b3cc',
                            300: '#7d8db0',
                            400: '#566a96',
                            500: '#3a4e7a',
                            600: '#1E2A4A',
                            700: '#1a253e',
                            800: '#151e33',
                            900: '#0f1626',
                        },
                        success: {
                            50: '#edf0f5',
                            100: '#d0d6e4',
                            500: '#3a4e7a',
                            600: '#1E2A4A',
                            700: '#1a253e',
                        },
                        warning: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.5rem 0.875rem;
            color: #6b7a99;
            border-radius: 9px;
            transition-property: background-color, color;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
            font-weight: 600;
            font-size: 13.5px;
        }

        .sidebar-link:hover {
            background-color: #EEF3FF;
            color: #4F7FFA;
        }

        .sidebar-link.active {
            background-color: #EEF3FF;
            color: #4F7FFA;
            font-weight: 600;
            border-radius: 7px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 5px;
            height: 100%;
            background-color: #4F7FFA;
        }

        .sidebar-link i {
            width: 1rem;
            height: 1rem;
            color: #6b7a99;
            transition-property: color;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
        }

        .sidebar-link.active i {
            color: #4F7FFA;
        }

        .sidebar-link:hover i {
            color: #4F7FFA;
        }

        .sidebar-link.active:hover {
            background-color: #EEF3FF;
        }

        .card {
            background-color: white;
            border-radius: 1rem;
            box-shadow:
                0px 0px 0px 1px oklch(0 0 0 / 0.06),
                0px 1px 2px -1px oklch(0 0 0 / 0.06),
                0px 2px 4px 0px oklch(0 0 0 / 0.04);
            transition-property: box-shadow;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
        }

        .card:hover {
            box-shadow:
                0px 0px 0px 1px oklch(0 0 0 / 0.08),
                0px 1px 2px -1px oklch(0 0 0 / 0.08),
                0px 2px 4px 0px oklch(0 0 0 / 0.06);
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition-property: background-color, color, box-shadow, scale;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:active:not(:disabled) {
            scale: 0.96;
        }

        .btn-primary {
            background-color: #1E2A4A;
            color: white;
        }

        .btn-primary:hover {
            background-color: #25335A;
        }

        .btn-success {
            background-color: #3a4e7a;
            color: white;
        }

        .btn-success:hover {
            background-color: #1E2A4A;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-outline {
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background-color: #f9fafb;
        }

        .btn-sm {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            border-radius: 0.375rem;
        }

        .btn-warning {
            background-color: #d97706;
            color: white;
        }

        .btn-warning:hover {
            background-color: #b45309;
        }

        .input,
        .select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            outline: none;
            transition-property: border-color, box-shadow;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
        }

        .input:focus,
        .select:focus {
            border-color: #1E2A4A;
            box-shadow: 0 0 0 3px rgba(30, 42, 74, 0.1);
        }

        .badge {
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d0d6e4;
            color: #1E2A4A;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-primary {
            background-color: #d0d6e4;
            color: #1E2A4A;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            font-size: 0.875rem;
            text-align: left;
        }

        .table thead {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 500;
        }

        .table th {
            padding: 0.75rem 1rem;
        }

        .table td {
            padding: 0.75rem 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-visible {
            animation: modalEnter 200ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        .modal-exiting {
            animation: modalExit 150ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        @keyframes modalEnter {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalExit {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        .hidden {
            display: none !important;
        }

        .modal {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 32rem;
            width: 100%;
            margin: 0;
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: none;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .modal-visible .modal {
            animation: modalContentEnter 200ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        .modal-exiting .modal {
            animation: modalContentExit 150ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        .modal-visible>div {
            animation: modalContentEnter 200ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        .modal-exiting>div {
            animation: modalContentExit 150ms cubic-bezier(0.2, 0, 0, 1) forwards;
        }

        @keyframes modalContentEnter {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes modalContentExit {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }

            to {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }
        }

        /* Hide scrollbar for Chrome/Safari/Edge */
        .modal::-webkit-scrollbar {
            display: none;
        }

        .modal.max-w-4xl {
            max-width: 56rem;
        }

        .modal.max-w-lg {
            max-width: 36rem;
        }

        .modal>div:first-child {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            flex-shrink: 0;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #f3f4f6;
            padding: 0.25rem 0;
            z-index: 50;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dropdown-item:hover {
            background-color: #f9fafb;
        }

        .stat-card {
            background-color: white;
            border-radius: 1rem;
            box-shadow:
                0px 0px 0px 1px oklch(0 0 0 / 0.06),
                0px 1px 2px -1px oklch(0 0 0 / 0.06),
                0px 2px 4px 0px oklch(0 0 0 / 0.04);
            transition-property: box-shadow;
            transition-duration: 150ms;
            transition-timing-function: ease-out;
            padding: 1.5rem;
        }

        .stat-card:hover {
            box-shadow:
                0px 0px 0px 1px oklch(0 0 0 / 0.08),
                0px 1px 2px -1px oklch(0 0 0 / 0.08),
                0px 2px 4px 0px oklch(0 0 0 / 0.06);
        }

        .toast {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #f3f4f6;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 50;
            opacity: 1;
            transform: translateY(0);
            transition-property: opacity, transform;
            transition-duration: 200ms;
            transition-timing-function: cubic-bezier(0.2, 0, 0, 1);
        }

        .toast.toast-hidden {
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
        }

        [x-cloak] {
            display: none !important;
        }

        .nav-logo img {
            outline: 1px solid oklch(0 0 0 / 0.1);
            outline-offset: -1px;
        }

        .todo-content,
        .activity-content,
        [id^="community-content-"] {
            animation: tabFadeIn 150ms ease-out;
        }

        @keyframes tabFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="w-60 bg-white border-r border-[#E5EAF3] flex flex-col h-full flex-shrink-0">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-5 border-b border-[#E5EAF3]">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm">
                            <div class="nav-logo">
                                {{-- <h2 class="m-0" style="font-size: 25px">LOGO</h2> --}}
                                <img src="{{ asset('images/logo2.png') }}" width="50px" height="50px"
                                    style="border-radius: 50%" alt="">
                            </div>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-gray-900">KPMPCATS</h1>
                            <p class="text-xs text-gray-500">KPMPCATS Managment</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation -->
                @php
                    $user = auth()->user();
                    $role = $user ? \App\Models\Role::where('slug', $user->role)->first() : null;
                    $perms = $role?->sidebar_permissions;
                    $hasFullAccess = is_null($perms);
                @endphp
                <nav class="flex-1 p-2.5 space-y-0.5 overflow-y-auto">
                    @if($hasFullAccess || in_array('dashboard', $perms))
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            <span>Dashboard</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('members', $perms))
                        <a href="{{ route('dashboard.members') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('dashboard.members') ? 'active' : '' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span>Accounts</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('lendings', $perms))
                        <a href="{{ route('lendings') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('lendings') ? 'active' : '' }}">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                            <span>Loans</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('payments', $perms))
                        <a href="{{ route('payments') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('payments') ? 'active' : '' }}">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            <span>Payments</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('finance', $perms))
                        <a href="{{ route('financial.activity') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('financial.activity') ? 'active' : '' }}">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                            <span>Finance</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('reports', $perms))
                        <a href="{{ route('reports') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('reports', 'reports.*') ? 'active' : '' }}">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            <span>Reports</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('seminars', $perms))
                        <a href="{{ route('seminars.index') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('seminars.*') ? 'active' : '' }}">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                            <span>Seminar</span>
                        </a>
                    @endif
                    @if($user?->isGeneralManager())
                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('admin.audit-logs.index') ? 'active' : '' }}">
                            <i data-lucide="history" class="w-5 h-5"></i>
                            <span>Audit Logs</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('officers-committees', $perms))
                        <a href="{{ route('officers.committees') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('officers.committees') ? 'active' : '' }}">
                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                            <span>Officers & Committees</span>
                        </a>
                    @endif
                    @if($user?->isGeneralManager() || $user?->isMainAdmin())
                        <a href="{{ route('allied-workers.index') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('allied-workers.*') ? 'active' : '' }}">
                            <i data-lucide="handshake" class="w-5 h-5"></i>
                            <span>Allied Workers</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('support-reports', $perms ?? []) || $user?->isGeneralManager() || $user?->isMainAdmin())
                        <a href="{{ route('admin.support-reports.index') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('admin.support-reports.*') ? 'active' : '' }}">
                            <i data-lucide="life-buoy" class="w-5 h-5"></i>
                            <span>Member Appeal</span>
                        </a>
                    @endif
                    @if($hasFullAccess || in_array('settings', $perms))
                        <a href="{{ route('settings') }}"
                            class="sidebar-link justify-start {{ request()->routeIs('settings') ? 'active' : '' }}">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                            <span>Settings</span>
                        </a>
                    @endif
                </nav>

                <!-- User Info -->
                <div class="p-3 border-t border-[#E5EAF3] relative">
                    <button type="button" data-action="toggleUserDropdown" data-arg='["|event|"]'
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-[#EEF3FF] cursor-pointer transition-colors w-full text-left">
                        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center shadow-sm">
                            <span
                                class="text-primary-600 font-semibold text-sm">{{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->first_name ?? '' }}
                                {{ auth()->user()->last_name ?? '' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ ucfirst(auth()->user()->role ?? 'Administrator') }}
                            </p>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="userDropdownMenu"
                        class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <a href="{{ route('settings') }}"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-gray-500"></i>
                            <span class="text-sm">View Profile</span>
                        </a>
                        @if(auth()->user()?->isGeneralManager() || auth()->user()?->isMainAdmin())
                            <hr class="my-2 border-gray-100">
                            <a href="{{ route('2fa.manage') }}"
                                class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="shield-check" class="w-4 h-4 text-gray-500"></i>
                                <span class="text-sm">Two-Factor Auth</span>
                            </a>
                        @endif
                        <hr class="my-2 border-gray-100">
                        <a href="{{ route('logout') }}"
                            class="flex items-center gap-3 px-4 py-3 text-danger-600 hover:bg-red-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 flex-shrink-0 z-30">
                <div class="flex items-center justify-end px-6 py-4">
                    <!-- Right Side -->
                    <div class="flex items-center gap-4">
                        @if(session('aw_mode'))
                            <form action="{{ route('allied-workers.switch-to-member') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-lg px-3 py-2 transition-colors">
                                    <i data-lucide="arrow-left-to-line" class="w-4 h-4"></i>
                                    Return to Member mode
                                </button>
                            </form>
                        @endif
                        <!-- Settings Link -->
                        <a href="{{ route('settings') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            title="Settings">
                            <i data-lucide="settings" class="w-5 h-5 text-gray-600"></i>
                        </a>

                        <!-- User Profile -->
                        <a href="{{ route('settings') }}"
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 text-sm font-semibold">
                                    {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->first_name ?? '' }}
                                    {{ auth()->user()->last_name ?? '' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6 flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast toast-hidden">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" id="toast-icon">
            <i data-lucide="check-circle" class="w-5 h-5 text-success-500"></i>
        </div>
        <div>
            <p class="font-medium text-gray-900" id="toast-title">Success</p>
            <p class="text-sm text-gray-500" id="toast-message">Operation completed successfully</p>
        </div>
        <button type="button" data-action="hideToast" class="ml-4">
            <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
        </button>
    </div>

    <script nonce="{{ csp_nonce() }}">
        // Initialize Lucide icons
        lucide.createIcons();

        // Toast functions
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastTitle = document.getElementById('toast-title');
            const toastMessage = document.getElementById('toast-message');

            toastTitle.textContent = title;
            toastMessage.textContent = message;

            const icons = {
                success: 'check-circle',
                error: 'x-circle',
                warning: 'alert-triangle',
                info: 'info'
            };

            toastIcon.innerHTML = `<i data-lucide="${icons[type]}" class="w-5 h-5 text-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'}-500"></i>`;
            lucide.createIcons();

            toast.classList.remove('toast-hidden');
            clearTimeout(toast._hideTimer);
            toast._hideTimer = setTimeout(() => {
                toast.classList.add('toast-hidden');
            }, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('toast-hidden');
            clearTimeout(toast._hideTimer);
        }

        // Dropdown toggle
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');
        }

        // User dropdown toggle
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdownMenu');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdowns on outside click
        document.addEventListener('click', function (e) {
            const userDropdown = document.getElementById('userDropdownMenu');
            if (!e.target.closest('.p-3') && !userDropdown.classList.contains('hidden')) {
                userDropdown.classList.add('hidden');
            }
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });

        // Modal functions
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.remove('modal-exiting');
            modal.style.display = 'flex';
            modal.classList.add('modal-visible');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('modal-visible');
            modal.classList.add('modal-exiting');
            modal.addEventListener('animationend', function handler(e) {
                if (e.target !== modal) return;
                modal.classList.remove('modal-exiting');
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                modal.removeEventListener('animationend', handler);
            });
        }

        // Close modal on escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay:not(.hidden), #memberDetailModal:not(.hidden)').forEach(modal => {
                    modal.classList.remove('modal-visible');
                    modal.classList.add('modal-exiting');
                    modal.addEventListener('animationend', function handler(e) {
                        if (e.target !== modal) return;
                        modal.classList.remove('modal-exiting');
                        modal.classList.add('hidden');
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                        modal.removeEventListener('animationend', handler);
                    });
                });
            }
        });

        // Tab functions
        function switchTab(tabId, activeTab) {
            document.querySelectorAll('[id^="tab-"]').forEach(tab => {
                tab.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-primary-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-600');
            });
            activeTab.classList.remove('bg-gray-100', 'text-gray-600');
            activeTab.classList.add('bg-primary-600', 'text-white');
        }
    </script>

    @yield('scripts')
</body>

</html>