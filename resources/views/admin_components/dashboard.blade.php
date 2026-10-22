@extends('layouts.admin')

@section('title', 'Dashboard - CoopAdmin')

@section('content')
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="text-sm text-gray-500">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                    <span class="text-gray-900 font-medium">Dashboard</span>
                </li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700">
            <i data-lucide="check-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
            <i data-lucide="alert-circle" class="w-4 h-4 inline-block mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500">Overview of your cooperative's key metrics and pending items</p>
    </div>

    <!-- Key Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

        {{-- Members Card --}}
        <a href="{{ route('dashboard.members') }}"
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-primary-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-primary-50 flex items-center justify-center group-hover:bg-primary-100 transition-colors">
                    <i data-lucide="users" class="w-5 h-5 text-primary-600"></i>
                </div>
                @if($pendingMembersCount > 0)
                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                    {{ $pendingMembersCount }} pending
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Members</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($totalMembers) }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-primary-500"></i>
            </div>
        </a>

        {{-- Savings Card --}}
        <a href="{{ route('financial.activity', ['tab' => 'savings']) }}"
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-success-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-success-50 flex items-center justify-center group-hover:bg-success-100 transition-colors">
                    <i data-lucide="piggy-bank" class="w-5 h-5 text-success-600"></i>
                </div>
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Savings Balance</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">₱{{ number_format($totalSavings, 2) }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-success-500"></i>
            </div>
        </a>

        {{-- Share Capital Card --}}
        <a href="{{ route('financial.activity', ['tab' => 'share-capitals']) }}"
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-indigo-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                    <i data-lucide="coins" class="w-5 h-5 text-indigo-600"></i>
                </div>
                @if($pendingWithdrawalsCount > 0)
                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                    {{ $pendingWithdrawalsCount }} withdrawals
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Share Capital</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">₱{{ number_format($totalShareCapital, 2) }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-indigo-500"></i>
            </div>
        </a>

        {{-- Loans Card (modal on pending) --}}
        <div data-action="dashboard-card" @if($pendingLoansCount > 0)data-open="openPendingLoansModal"@else data-url="{{ route('lendings') }}" @endif
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-warning-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-warning-50 flex items-center justify-center group-hover:bg-warning-100 transition-colors">
                    <i data-lucide="banknote" class="w-5 h-5 text-warning-600"></i>
                </div>
                @if($pendingLoansCount > 0)
                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                    {{ $pendingLoansCount }} pending
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Active Loans</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">₱{{ number_format($activeLoans, 2) }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-warning-500"></i>
            </div>
        </div>

        {{-- Pending Resignations Card (modal) --}}
        <div data-action="openResignationsModal"
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-danger-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-danger-50 flex items-center justify-center group-hover:bg-danger-100 transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5 text-danger-600"></i>
                </div>
                @if($pendingResignationsCount > 0)
                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                    {{ $pendingResignationsCount }} pending
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Pending Resignations</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $pendingResignationsCount }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-danger-500"></i>
            </div>
        </div>

        {{-- Upcoming Seminars Card (modal) --}}
        <div data-action="dashboard-card" @if($upcomingSeminarsCount > 0)data-open="openSeminarsModal"@else data-url="{{ route('seminars.index') }}" @endif
            class="group relative bg-white rounded-xl border border-gray-100 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-sky-200 cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center group-hover:bg-sky-100 transition-colors">
                    <i data-lucide="graduation-cap" class="w-5 h-5 text-sky-600"></i>
                </div>
                @if($upcomingSeminarsCount > 0)
                <span class="px-2 py-0.5 bg-sky-100 text-sky-700 text-xs font-semibold rounded-full">
                    {{ $upcomingSeminarsCount }} upcoming
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 font-medium mb-1">Upcoming Seminars</p>
            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $upcomingSeminarsCount }}</p>
            <div class="absolute bottom-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                <i data-lucide="arrow-right" class="w-4 h-4 text-sky-500"></i>
            </div>
        </div>

    </div>

    <!-- To-Do List & Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- To-Do List -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">To-Do List</h2>
            </div>

            <div class="grid grid-cols-3 gap-2 mb-6">
                <button data-action="switchTodoTab" data-arg='["members"]' id="todo-tab-members" class="todo-tab-btn active px-4 py-2 bg-primary-600 text-white rounded-lg font-medium text-sm shadow-md text-center">
                    Member Approvals <span class="ml-1 px-2 py-0.5 bg-white/20 rounded-full">{{ $pendingMembersCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["loans"]' id="todo-tab-loans" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Loan Applications <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingLoansCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["withdrawals"]' id="todo-tab-withdrawals" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Withdraw Share Capital <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingWithdrawalsCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["savings_deposits"]' id="todo-tab-savings_deposits" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Savings Deposits <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingSavingsDepositsCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["savings_withdrawals"]' id="todo-tab-savings_withdrawals" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Savings Withdrawals <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingSavingsWithdrawalsCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["share_capital_deposits"]' id="todo-tab-share_capital_deposits" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Share Capital Deposits <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingShareCapitalDepositsCount }}</span>
                </button>
                <button data-action="switchTodoTab" data-arg='["loan_payments"]' id="todo-tab-loan_payments" class="todo-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200 text-center">
                    Loan Payments <span class="ml-1 px-2 py-0.5 bg-gray-200 rounded-full">{{ $pendingLoanPaymentsCount }}</span>
                </button>
            </div>

            <div id="todo-content-members" class="todo-content max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingMembersList as $item)
                <a href="{{ route('dashboard.members', ['filter' => 'pending']) }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-semibold text-primary-600">{{ $item['initials'] }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending member approvals</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-loans" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingLoansList as $item)
                <a href="{{ route('lendings', ['filter' => 'pending']) }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending loan applications</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-withdrawals" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingWithdrawalsList as $item)
                <a href="{{ route('sharecapitals', ['filter' => 'withdrawal', 'status' => 'pending']) }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="banknote" class="w-5 h-5 text-danger-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending withdrawal requests</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-savings_deposits" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingSavingsDepositsList as $item)
                <a href="{{ route('savings') }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-success-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 text-success-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending savings deposits</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-savings_withdrawals" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingSavingsWithdrawalsList as $item)
                <a href="{{ route('savings') }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="banknote" class="w-5 h-5 text-danger-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending savings withdrawals</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-share_capital_deposits" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingShareCapitalDepositsList as $item)
                <a href="{{ route('sharecapitals') }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-success-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="arrow-down-circle" class="w-5 h-5 text-success-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending share capital deposits</p>
                </div>
                @endforelse
            </div>

            <div id="todo-content-loan_payments" class="todo-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($pendingLoanPaymentsList as $item)
                <a href="{{ route('payments') }}" class="block p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-warning-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5 text-warning-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $item['type'] }} - ₱{{ number_format($item['amount'], 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $item['time'] }}</p>
                        </div>
                        <span class="badge badge-warning">Pending</span>
                    </div>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No pending loan payments</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
            </div>

            <div class="flex flex-wrap gap-2 mb-6">
                <button data-action="switchActivityTab" data-arg='["transactions"]' id="activity-tab-transactions" class="activity-tab-btn active px-4 py-2 bg-primary-600 text-white rounded-lg font-medium text-sm shadow-md">
                    Transactions
                </button>
                <button data-action="switchActivityTab" data-arg='["approvals"]' id="activity-tab-approvals" class="activity-tab-btn px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium text-sm hover:bg-gray-200">
                    Approvals
                </button>
            </div>

            <div id="activity-content-transactions" class="activity-content max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($recentTransactions as $activity)
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl mb-3">
                    @if($activity['type'] === 'savings')
                    <div class="w-10 h-10 {{ $activity['subtype'] === 'deposit' ? 'bg-success-100' : 'bg-danger-100' }} rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="{{ $activity['subtype'] === 'deposit' ? 'arrow-down-circle' : 'arrow-up-circle' }}" class="w-5 h-5 {{ $activity['subtype'] === 'deposit' ? 'text-success-500' : 'text-danger-600' }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-sm text-gray-500">{{ $activity['user'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold {{ $activity['subtype'] === 'deposit' ? 'text-success-600' : 'text-danger-600' }}">{{ $activity['subtype'] === 'deposit' ? '+' : '-' }}₱{{ number_format($activity['amount'], 2) }}</p>
                        <span class="badge badge-success text-xs">{{ $activity['status'] }}</span>
                    </div>
                    @else
                    <div class="w-10 h-10 {{ $activity['subtype'] === 'subscription' ? 'bg-primary-100' : ($activity['subtype'] === 'deposit' ? 'bg-success-100' : 'bg-warning-100') }} rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="coins" class="w-5 h-5 {{ $activity['subtype'] === 'subscription' ? 'text-primary-600' : ($activity['subtype'] === 'deposit' ? 'text-success-500' : 'text-warning-600') }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-sm text-gray-500">{{ $activity['user'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">₱{{ number_format($activity['amount'], 2) }}</p>
                        <span class="badge badge-success text-xs">{{ $activity['status'] }}</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No recent transactions</p>
                </div>
                @endforelse
            </div>

            <div id="activity-content-approvals" class="activity-content hidden max-h-[350px] overflow-y-auto scrollbar-hide">
                @forelse($recentMemberApprovals as $activity)
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl mb-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="user-{{ $activity['status'] === 'Active' ? 'check' : 'plus' }}" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <p class="text-sm text-gray-500">{{ $activity['user'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                    </div>
                    <span class="badge badge-{{ $activity['status'] === 'Active' ? 'success' : 'warning' }}">{{ $activity['status'] }}</span>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                    <p>No recent approvals</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Announcements Section -->
    @if($currentUser && $currentUser->isMainAdmin())
        <div id="createAnnouncementModal" class="modal-overlay hidden">
            <div class="modal max-w-xl">
                <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="megaphone" class="w-5 h-5" style="color: #fff;"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Create Announcement</h2>
                                <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Publish a new announcement to all members</p>
                            </div>
                        </div>
                        <button data-action="closeModal" data-arg='["createAnnouncementModal"]' style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('announcements.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <input type="text" name="title" class="input" placeholder="Announcement title..." required>
                    </div>
                    <div>
                        <textarea name="content" class="input" rows="5" placeholder="Write your announcement content here..." required></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" data-action="closeModal" data-arg='["createAnnouncementModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Publish
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="createPollModal" class="modal-overlay hidden">
            <div class="modal max-w-xl">
                <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="bar-chart-3" class="w-5 h-5" style="color: #fff;"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Create Poll</h2>
                                <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Create a poll for members to vote on</p>
                            </div>
                        </div>
                        <button data-action="closeModal" data-arg='["createPollModal"]' style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                        </button>
                    </div>
                </div>
                <form data-action="createPoll" data-arg='["|event|"]' class="p-6 space-y-4">
                    @csrf
                    <div>
                        <input type="text" id="pollQuestion" class="input" placeholder="Poll question..." required>
                    </div>
                    <div id="pollOptionsContainer" class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" class="input poll-option flex-1" placeholder="Option 1" required>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" class="input poll-option flex-1" placeholder="Option 2" required>
                        </div>
                    </div>
                    <button type="button" data-action="addPollOption" id="addPollOptionBtn" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Option
                    </button>
                    <div class="flex items-center gap-3">
                        <input type="datetime-local" id="pollExpiresAt" class="input flex-1">
                        <label class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                            <input type="checkbox" id="pollNoExpiry" checked data-action="togglePollExpiry" class="rounded">
                            No expiry
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" data-action="closeModal" data-arg='["createPollModal"]' class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            Create Poll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card mb-8">
        <div class="p-5 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <i data-lucide="megaphone" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Community</h2>
                        <p class="text-sm text-gray-500">Announcements and polls</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($currentUser && $currentUser->isMainAdmin())
                        <button data-action="openModal" data-arg='["createAnnouncementModal"]' class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <i data-lucide="megaphone" class="w-4 h-4"></i>
                            New Announcement
                        </button>
                        <button data-action="openModal" data-arg='["createPollModal"]' class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            New Poll
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="border-b border-gray-100">
            <div class="flex">
                <button data-action="switchCommunityTab" data-arg='["announcements"]' id="community-tab-announcements" class="px-5 py-3 text-sm font-medium text-primary-600 border-b-2 border-primary-600 transition-colors">
                    <i data-lucide="megaphone" class="w-4 h-4 inline mr-1"></i> Announcements
                </button>
                <button data-action="switchCommunityTab" data-arg='["polls"]' id="community-tab-polls" class="px-5 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 transition-colors">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-1"></i> Polls
                </button>
            </div>
        </div>

        <!-- Announcements Tab -->
        <div id="community-content-announcements" class="p-5 space-y-4">
            @forelse($announcements as $announcement)
                <div class="border border-gray-100 rounded-xl p-5 hover:shadow-sm transition-shadow" id="announcement-{{ $announcement->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center flex-shrink-0 shadow-md">
                            <span class="text-white font-bold text-sm">
                                {{ strtoupper(substr($announcement->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($announcement->user->last_name ?? '', 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $announcement->user->first_name ?? '' }} {{ $announcement->user->last_name ?? '' }}</span>
                                @if($announcement->user && in_array($announcement->user->role, ['admin', 'general-manager']))
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        <i data-lucide="shield" class="w-3 h-3 mr-1"></i> Admin
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                    {{ $announcement->created_at->format('M d, Y h:i A') }}
                                </span>
                                @if($currentUser && $currentUser->isMainAdmin())
                                    <button data-action="deleteAnnouncement" data-arg='[{{ $announcement->id }},"|el|"]' class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Delete announcement">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-1">{{ $announcement->title }}</h3>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ trim($announcement->content) }}</p>

                            <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                                <button data-action="toggleLike" data-arg='[{{ $announcement->id }},"|el|"]'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $announcement->likes->contains('user_id', $currentUser->id ?? 0) ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}">
                                    <i data-lucide="heart" class="w-4 h-4"></i>
                                    <span class="like-count">{{ $announcement->likes_count }}</span>
                                </button>
                                <button data-action="toggleComments" data-arg='[{{ $announcement->id }}]'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-50 text-gray-600 hover:bg-gray-100 transition-all duration-200">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>{{ $announcement->comments_count }}</span>
                                </button>
                            </div>

                            <div id="comments-{{ $announcement->id }}" class="mt-4 space-y-3" style="display: none;">
                                <div class="space-y-3">
                                    @foreach($announcement->comments as $comment)
                                        <div class="flex gap-3 bg-gray-50 rounded-lg p-3 group" id="comment-{{ $comment->id }}">
                                            <div class="w-7 h-7 rounded-full {{ $comment->user && in_array($comment->user->role, ['admin', 'general-manager']) ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 'bg-gradient-to-br from-primary-300 to-primary-500' }} flex items-center justify-center flex-shrink-0">
                                                <span class="text-white font-bold text-xs">
                                                    {{ $comment->user ? strtoupper(substr($comment->user->first_name ?? '', 0, 1)) . strtoupper(substr($comment->user->last_name ?? '', 0, 1)) : '??' }}
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-semibold text-gray-900">{{ $comment->user->first_name ?? '' }} {{ $comment->user->last_name ?? '' }}</span>
                                                    @if($comment->user && in_array($comment->user->role, ['admin', 'general-manager']))
                                                        <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-700">Admin</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700">{{ $comment->comment }}</p>
                                            </div>
                                            @if($currentUser && $currentUser->isMainAdmin())
                                                <button data-action="deleteComment" data-arg='[{{ $announcement->id }},{{ $comment->id }},"|el|"]' class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all" title="Delete comment">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <form data-action="postComment" data-arg='["|event|",{{ $announcement->id }}]' class="flex gap-2">
                                    @csrf
                                    <input type="text" class="input flex-1" placeholder="Write a comment..." required>
                                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                        <i data-lucide="send" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <i data-lucide="megaphone" class="w-7 h-7 text-gray-300"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">No announcements yet</h3>
                    <p class="text-xs text-gray-500">Announcements you create will appear here.</p>
                </div>
            @endforelse
        </div>

        <!-- Polls Tab -->
        <div id="community-content-polls" class="p-5 space-y-4" style="display: none;">
            @forelse($polls as $poll)
                @php
                    $pollResults = $poll->results;
                    $totalVotes = count($poll->votes);
                    $userVote = $poll->votes->firstWhere('user_id', $currentUser->id ?? 0);
                    $hasVoted = $userVote !== null;
                    $isExpired = $poll->isExpired();
                    $maxVotes = max($pollResults, 1);
                @endphp
                <div class="border border-gray-100 rounded-xl p-5 hover:shadow-sm transition-shadow" id="poll-{{ $poll->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center flex-shrink-0 shadow-md">
                            <i data-lucide="bar-chart-3" class="w-5 h-5 text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $poll->user->first_name ?? '' }} {{ $poll->user->last_name ?? '' }}</span>
                                @if($poll->user && in_array($poll->user->role, ['admin', 'general-manager']))
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        <i data-lucide="shield" class="w-3 h-3 mr-1"></i> Admin
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                    {{ $poll->created_at->format('M d, Y h:i A') }}
                                </span>
                                @if($poll->expires_at)
                                    <span class="text-xs {{ $isExpired ? 'text-red-500' : 'text-orange-500' }}">
                                        <i data-lucide="timer" class="w-3 h-3 inline mr-1"></i>
                                        {{ $isExpired ? 'Expired' : 'Expires ' . $poll->expires_at->format('M d, Y h:i A') }}
                                    </span>
                                @endif
                                @if($currentUser && $currentUser->isMainAdmin())
                                    <button data-action="deletePoll" data-arg='[{{ $poll->id }},"|el|"]' class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Delete poll">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-gray-900 mb-3">{{ $poll->question }}</h3>
                            <div class="space-y-2" id="poll-options-{{ $poll->id }}">
                                @foreach($poll->options as $i => $option)
                                    @php
                                        $count = $pollResults[$i] ?? 0;
                                        $pct = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
                                        $isChosen = $hasVoted && $userVote->option_index === $i;
                                    @endphp
                                    <div class="relative">
                                        @if($hasVoted || $isExpired)
                                            <div class="w-full h-9 rounded-lg overflow-hidden relative bg-gray-100">
                                                <div class="h-full rounded-lg transition-all duration-500 {{ $isChosen ? 'bg-primary-500' : 'bg-gray-200' }}" style="width: {{ $pct }}%"></div>
                                                <div class="absolute inset-0 flex items-center justify-between px-3">
                                                    <span class="text-sm font-medium {{ $isChosen ? 'text-white' : 'text-gray-700' }}">{{ $option }}</span>
                                                    <span class="text-sm font-semibold {{ $isChosen ? 'text-white' : 'text-gray-500' }}">{{ $pct }}% <span class="font-normal">({{ $count }})</span></span>
                                                </div>
                                            </div>
                                        @else
                                            <button data-action="votePoll" data-arg='[{{ $poll->id }},{{ $i }},"|el|"]' class="w-full text-left px-3 py-2 rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition-all text-sm font-medium text-gray-700">
                                                {{ $option }}
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-xs text-gray-500">
                                {{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}
                                @if($hasVoted)
                                    · You voted
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <i data-lucide="bar-chart-3" class="w-7 h-7 text-gray-300"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">No polls yet</h3>
                    <p class="text-xs text-gray-500">Create a poll to get feedback from members.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Savings Activity -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Savings Activity</h2>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative w-40 h-40">
                    @php
                        $totalSavingsTx = $totalDeposits + $totalWithdrawals;
                        $depositPercent = $totalSavingsTx > 0 ? ($totalDeposits / $totalSavingsTx) * 100 : 50;
                        $withdrawalPercent = $totalSavingsTx > 0 ? ($totalWithdrawals / $totalSavingsTx) * 100 : 50;
                        $depositDash = ($depositPercent / 100) * 251.2;
                        $withdrawalDash = ($withdrawalPercent / 100) * 251.2;
                    @endphp
                    <svg viewBox="0 0 100 100" class="transform -rotate-90 w-40 h-40">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#fecaca" stroke-width="12" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="12"
                            stroke-dasharray="{{ $depositDash }} 251.2" stroke-dashoffset="0" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="12"
                            stroke-dasharray="{{ $withdrawalDash }} 251.2" stroke-dashoffset="{{ -$depositDash }}" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSavingsTx) }}</p>
                            <p class="text-xs text-gray-500">Total Transactions</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-success-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Deposits</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $totalDeposits }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-danger-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Withdrawals</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $totalWithdrawals }}</span>
                </div>
            </div>
        </div>

        <!-- Loan Repayments Activity -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Loan Repayments Activity</h2>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative w-40 h-40">
                    @php
                        $loanDisbursed = \App\Models\lending_program_tbl::where('status', 'Approved')->count();
                        $loanRepayments = \App\Models\lending_program_tbl::where('status', 'Completed')->count();
                        $totalLoanTx = $loanDisbursed + $loanRepayments;
                        $depositPercent = $totalLoanTx > 0 ? ($loanDisbursed / $totalLoanTx) * 100 : 50;
                        $repaymentDash = ($depositPercent / 100) * 251.2;
                    @endphp
                    <svg viewBox="0 0 100 100" class="transform -rotate-90 w-40 h-40">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#fecaca" stroke-width="12" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="12"
                            stroke-dasharray="{{ $repaymentDash }} 251.2" stroke-dashoffset="0" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="12"
                            stroke-dasharray="251.2" stroke-dashoffset="{{ -$repaymentDash }}" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($loanDisbursed) }}</p>
                            <p class="text-xs text-gray-500">Active Loans</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-primary-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Repayments</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $loanRepayments }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                        <span class="text-sm text-gray-600">Pending</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ \App\Models\lending_program_tbl::where('status', 'Pending')->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Share Capital Activity -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Share Capital Activity</h2>
            </div>

            <div class="flex items-center justify-center">
                <div class="relative w-40 h-40">
                    @php
                        $shareDeposit = \App\Models\share_capital_transaction_tbl::where('type', 'Deposit')->count();
                        $shareWithdraw = \App\Models\share_capital_transaction_tbl::where('type', 'Withdrawal')->count();
                        $totalShareTx = $shareDeposit + $shareWithdraw;
                        $depositPercent = $totalShareTx > 0 ? ($shareDeposit / $totalShareTx) * 100 : 50;
                        $withdrawalPercent = $totalShareTx > 0 ? ($shareWithdraw / $totalShareTx) * 100 : 50;
                        $depositDash = ($depositPercent / 100) * 251.2;
                        $withdrawalDash = ($withdrawalPercent / 100) * 251.2;
                    @endphp
                    <svg viewBox="0 0 100 100" class="transform -rotate-90 w-40 h-40">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#fecaca" stroke-width="12" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22c55e" stroke-width="12"
                            stroke-dasharray="{{ $depositDash }} 251.2" stroke-dashoffset="0" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="12"
                            stroke-dasharray="{{ $withdrawalDash }} 251.2" stroke-dashoffset="{{ -$depositDash }}" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalShareTx) }}</p>
                            <p class="text-xs text-gray-500">Total Transactions</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-success-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Deposits</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $shareDeposit }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-danger-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Withdraws</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $shareWithdraw }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Distribution & Audit Logs -->
    @php $showAuditLogs = auth()->user()?->isGeneralManager(); @endphp
    <div class="grid grid-cols-1 {{ $showAuditLogs ? 'lg:grid-cols-2' : '' }} gap-6">
        <!-- Loan Distribution -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Loan Distribution</h2>
                    <p class="text-sm text-gray-500">By loan type</p>
                </div>
                <a href="{{ route('financial.activity') }}#loan-interest-rates"
                    class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-700 font-medium">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    Settings
                </a>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Loan Type</th>
                            <th class="text-center">Count</th>
                            <th class="w-2/5">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalLoanCount = array_sum($loanTypeCounts);
                            $colors = ['primary-600', 'success-500', 'warning-500', 'danger-500', 'indigo-500', 'rose-500', 'cyan-500'];
                            $colorIndex = 0;
                            $displayTypes = $loanTypes ?? array_keys($loanTypeCounts);
                        @endphp
                        @forelse($displayTypes as $type)
                        <tr class="js-open-loan-type cursor-pointer hover:bg-gray-50 transition-colors"
                            data-loan-type="{{ $type }}">
                            <td class="text-sm font-medium text-gray-900">{{ $type }}</td>
                            <td class="text-center">
                                <span class="text-lg font-bold text-gray-900">{{ $loanTypeCounts[$type] ?? 0 }}</span>
                            </td>
                            <td>
                                @php $c = $colors[$colorIndex % count($colors)]; @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $c }} h-2 rounded-full" style="width: {{ $totalLoanCount > 0 ? (($loanTypeCounts[$type] ?? 0) / $totalLoanCount) * 100 : 0 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @php $colorIndex++; @endphp
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">
                                <i data-lucide="banknote" class="w-12 h-12 mx-auto mb-2 text-gray-300"></i>
                                <p>No loan data available</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($showAuditLogs)
        <!-- Audit Logs -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Audit Logs</h2>
                    <p class="text-sm text-gray-500">Recent system activities</p>
                </div>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View All</a>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-xs text-primary-600 font-medium">{{ strtoupper(substr($log->admin_name ?? ($log->user->first_name ?? 'S'), 0, 1)) }}</span>
                                    </div>
                                    <span class="text-sm text-gray-900">{{ $log->admin_name ?? ($log->user->first_name ?? 'System') . ' ' . ($log->user->last_name ?? '') }}</span>
                                </div>
                            </td>
                            <td><span class="text-sm text-gray-600">{{ $log->action }}</span></td>
                            <td><span class="text-xs text-gray-500">{{ $log->created_at?->diffForHumans() }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No audit logs available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    @include('admin_components.dashboard_modals')

    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;
            A.register('dashboard-card', function (e, el) {
                var fn = el.getAttribute('data-open');
                if (fn && typeof window[fn] === 'function') { window[fn](); }
                var url = el.getAttribute('data-url');
                if (url) { window.location.href = url; }
            });
        })();

        // To-Do List Tab Switching
        function switchTodoTab(tab) {
            const contents = document.querySelectorAll('.todo-content');
            const buttons = document.querySelectorAll('.todo-tab-btn');
            contents.forEach(content => content.classList.add('hidden'));
            buttons.forEach(btn => {
                btn.classList.remove('bg-primary-600', 'text-white', 'shadow-md');
                btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                const badge = btn.querySelector('span');
                if (badge) {
                    badge.classList.remove('bg-white/20');
                    badge.classList.add('bg-gray-200');
                }
            });
            document.getElementById('todo-content-' + tab).classList.remove('hidden');
            document.getElementById('todo-tab-' + tab).classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            document.getElementById('todo-tab-' + tab).classList.add('bg-primary-600', 'text-white', 'shadow-md');
            const activeBadge = document.getElementById('todo-tab-' + tab).querySelector('span');
            if (activeBadge) {
                activeBadge.classList.remove('bg-gray-200');
                activeBadge.classList.add('bg-white/20');
            }
        }

        // Recent Activity Tab Switching
        function switchActivityTab(tab) {
            const contents = document.querySelectorAll('.activity-content');
            const buttons = document.querySelectorAll('.activity-tab-btn');
            contents.forEach(content => content.classList.add('hidden'));
            buttons.forEach(btn => {
                btn.classList.remove('bg-primary-600', 'text-white', 'shadow-md');
                btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            });
            document.getElementById('activity-content-' + tab).classList.remove('hidden');
            document.getElementById('activity-tab-' + tab).classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            document.getElementById('activity-tab-' + tab).classList.add('bg-primary-600', 'text-white', 'shadow-md');
        }

        // Announcements
        function toggleLike(announcementId, btn) {
            fetch('/announcements/' + announcementId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const countSpan = btn.querySelector('.like-count');
                    countSpan.textContent = data.count;
                    if (data.liked) {
                        btn.classList.remove('bg-gray-50', 'text-gray-600', 'hover:bg-gray-100');
                        btn.classList.add('bg-red-50', 'text-red-600', 'hover:bg-red-100');
                    } else {
                        btn.classList.remove('bg-red-50', 'text-red-600', 'hover:bg-red-100');
                        btn.classList.add('bg-gray-50', 'text-gray-600', 'hover:bg-gray-100');
                    }
                }
            })
            .catch(() => { showToast('Error', 'Failed to update like.', 'error'); });
        }

        function toggleComments(announcementId) {
            const el = document.getElementById('comments-' + announcementId);
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }

        function postComment(event, announcementId) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('input[type="text"]');
            const comment = input.value.trim();
            if (!comment) return;

            fetch('/announcements/' + announcementId + '/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ comment: comment }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const c = data.comment;
                    const escHtml = (value) => {
                        const el = document.createElement('div');
                        el.textContent = value;
                        return el.innerHTML;
                    };
                    const isAdmin = ['admin', 'general-manager'].includes(c.user.role);
                    const badge = isAdmin ? '<span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded bg-blue-100 text-blue-700">Admin</span>' : '';
                    const avatarBg = isAdmin ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 'bg-gradient-to-br from-primary-300 to-primary-500';
                    const initials = (c.user.first_name?.[0] || '') + (c.user.last_name?.[0] || '');

                    const isCurrentAdmin = {{ $currentUser && $currentUser->isMainAdmin() ? 'true' : 'false' }};
                    const deleteBtn = isCurrentAdmin
                        ? '<button data-action="deleteComment" data-arg=\'[' + announcementId + ', ' + c.id + ', "|el|"]\' class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 opacity-0 group-hover:opacity-100 transition-all" title="Delete comment"><i data-lucide="trash-2" class="w-4 h-4"></i></button>'
                        : '';

                    const div = document.createElement('div');
                    div.className = 'flex gap-3 bg-gray-50 rounded-lg p-3 group';
                    div.id = 'comment-' + c.id;
                    div.innerHTML = '<div class="w-7 h-7 rounded-full ' + avatarBg + ' flex items-center justify-center flex-shrink-0"><span class="text-white font-bold text-xs">' + escHtml(initials.toUpperCase()) + '</span></div><div class="flex-1 min-w-0"><div class="flex items-center gap-2 mb-1"><span class="text-sm font-semibold text-gray-900">' + escHtml(c.user.first_name) + ' ' + escHtml(c.user.last_name) + '</span>' + badge + '<span class="text-xs text-gray-400">' + escHtml(c.created_at) + '</span></div><p class="text-sm text-gray-700">' + escHtml(c.comment) + '</p></div>' + deleteBtn;

                    const container = form.closest('#comments-' + announcementId).querySelector('.space-y-3');
                    container.appendChild(div);
                    input.value = '';

                    const countEl = form.closest('.border').querySelector('button[data-action="toggleComments"] span:last-child');
                    if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;

                    lucide.createIcons();
                }
            })
            .catch(() => { showToast('Error', 'Failed to post comment.', 'error'); });
        }

        function deleteComment(announcementId, commentId, btn) {
            if (!confirm('Delete this comment?')) return;
            const card = btn.closest('.border');

            fetch('/announcements/' + announcementId + '/comment/' + commentId + '/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('comment-' + commentId);
                    if (el) el.remove();
                    if (card) {
                        const countEl = card.querySelector('button[data-action="toggleComments"] span:last-child');
                        if (countEl) countEl.textContent = data.count;
                    }
                    showToast('Deleted', 'Comment removed.', 'success');
                }
            })
            .catch(() => { showToast('Error', 'Failed to delete comment.', 'error'); });
        }

        function deleteAnnouncement(announcementId, btn) {
            if (!confirm('Delete this announcement and all its comments?')) return;

            fetch('/announcements/' + announcementId + '/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('announcement-' + announcementId);
                    if (el) el.remove();
                    showToast('Deleted', 'Announcement removed.', 'success');
                }
            })
            .catch(() => { showToast('Error', 'Failed to delete announcement.', 'error'); });
        }

        // Community tabs
        function switchCommunityTab(tab) {
            document.querySelectorAll('[id^="community-tab-"]').forEach(btn => {
                btn.classList.remove('text-primary-600', 'border-primary-600');
                btn.classList.add('text-gray-500', 'border-transparent');
            });
            document.querySelectorAll('[id^="community-content-"]').forEach(el => el.style.display = 'none');
            document.getElementById('community-tab-' + tab).classList.remove('text-gray-500', 'border-transparent');
            document.getElementById('community-tab-' + tab).classList.add('text-primary-600', 'border-primary-600');
            document.getElementById('community-content-' + tab).style.display = 'block';
        }

        // Polls
        function togglePollExpiry() {
            const cb = document.getElementById('pollNoExpiry');
            const input = document.getElementById('pollExpiresAt');
            if (cb.checked) { input.value = ''; input.disabled = true; }
            else { input.disabled = false; }
        }

        function addPollOption() {
            const container = document.getElementById('pollOptionsContainer');
            const count = container.querySelectorAll('.poll-option').length;
            if (count >= 10) return;
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = '<input type="text" class="input poll-option flex-1" placeholder="Option ' + (count + 1) + '" required><button type="button" data-action="remove-parent" class="text-gray-400 hover:text-red-500"><i data-lucide="x" class="w-4 h-4"></i></button>';
            container.appendChild(div);
            lucide.createIcons();
        }

        function createPoll(event) {
            event.preventDefault();
            const question = document.getElementById('pollQuestion').value.trim();
            const options = Array.from(document.querySelectorAll('.poll-option')).map(el => el.value.trim()).filter(v => v);
            const expiresAt = document.getElementById('pollNoExpiry').checked ? null : document.getElementById('pollExpiresAt').value || null;

            if (!question || options.length < 2) {
                showToast('Error', 'Please fill in the question and at least 2 options.', 'error');
                return;
            }

            fetch('{{ route("announcements.poll.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ question, options, expires_at: expiresAt }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeModal('createPollModal');
                    showToast('Created', 'Poll created successfully.', 'success');
                    setTimeout(() => location.reload(), 800);
                }
            })
            .catch(() => { showToast('Error', 'Failed to create poll.', 'error'); });
        }

        function votePoll(pollId, optionIndex, btn) {
            fetch('/polls/' + pollId + '/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ option_index: optionIndex }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('poll-options-' + pollId);
                    const options = container.querySelectorAll('.relative');
                    options.forEach((el, i) => {
                        const count = data.results[i] || 0;
                        const pct = data.total_votes > 0 ? Math.round((count / data.total_votes) * 100) : 0;
                        const isChosen = data.voted_index === i;
                        el.innerHTML = '<div class="w-full h-9 rounded-lg overflow-hidden relative bg-gray-100"><div class="h-full rounded-lg transition-all duration-500 ' + (isChosen ? 'bg-primary-500' : 'bg-gray-200') + '" style="width: ' + pct + '%"></div><div class="absolute inset-0 flex items-center justify-between px-3"><span class="text-sm font-medium ' + (isChosen ? 'text-white' : 'text-gray-700') + '">' + el.querySelector('button')?.textContent?.trim() + '</span><span class="text-sm font-semibold ' + (isChosen ? 'text-white' : 'text-gray-500') + '">' + pct + '% <span class="font-normal">(' + count + ')</span></span></div></div>';
                    });
                    const countEl = document.getElementById('poll-' + pollId).querySelector('.text-xs.text-gray-500');
                    if (countEl) countEl.innerHTML = data.total_votes + ' ' + (data.total_votes === 1 ? 'vote' : 'votes') + ' · You voted';
                }
            })
            .catch(() => { showToast('Error', 'Failed to vote.', 'error'); });
        }

        function deletePoll(pollId, btn) {
            if (!confirm('Delete this poll and all its votes?')) return;

            fetch('/polls/' + pollId + '/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const el = document.getElementById('poll-' + pollId);
                    if (el) el.remove();
                    showToast('Deleted', 'Poll removed.', 'success');
                }
            })
            .catch(() => { showToast('Error', 'Failed to delete poll.', 'error'); });
        }
    </script>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endsection
