@extends('layouts/admin')

@section('title', 'System Audit Logs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Audit Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Track all system activities and changes</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="p-4 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, action, role, IP..."
                            class="input pl-10" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input" />
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Apply Filters
                </button>
                @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Clear Filters
                </a>
                @endif
            </div>
        </form>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>User / Admin Name</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Target</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs text-primary-600 font-semibold">
                                        {{ strtoupper(substr($log->admin_name ?? ($log->user->first_name ?? 'S'), 0, 1)) }}
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $log->admin_name ?? ($log->user->first_name ?? 'System') . ' ' . ($log->user->last_name ?? '') }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleBadge = match(strtolower($log->user_role ?? '')) {
                                    'admin' => 'badge-primary',
                                    'general-manager' => 'badge-primary',
                                    'officer' => 'badge-info',
                                    'member' => 'badge-success',
                                    default => 'badge-gray'
                                };
                            @endphp
                            <span class="badge {{ $roleBadge }}">{{ ucfirst($log->user_role ?? 'N/A') }}</span>
                        </td>
                        <td>
                            <span class="text-sm text-gray-900">{{ $log->action }}</span>
                        </td>
                        <td>
                            <span class="text-sm text-gray-500" title="{{ $log->details }}">
                                {{ Str::limit($log->details ?? '—', 50) }}
                            </span>
                        </td>
                        <td>
                            @if($log->target_type)
                            <div class="text-sm">
                                <span class="text-gray-600">{{ $log->target_type }}</span>
                                @if($log->target_id)
                                <span class="text-gray-400">#{{ $log->target_id }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm text-gray-500" title="{{ $log->created_at }}">
                                <div>{{ $log->created_at?->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at?->format('h:i A') }}</div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i data-lucide="file-text" class="w-12 h-12 text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-medium">No audit logs found</p>
                                <p class="text-sm text-gray-400 mt-1">
                                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                                        Try adjusting your filters
                                    @else
                                        Activity will appear here as actions are performed
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="border-t border-gray-100 rounded-b-xl bg-white flex items-center justify-between px-6 py-4">
            <p class="text-sm text-slate-500">
                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} records
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($logs->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $logs->previousPageUrl() }}"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif

                {{-- Page Numbers --}}
                @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 1), min($logs->lastPage(), $logs->currentPage() + 1)) as $page => $url)
                @if($page == $logs->currentPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-white text-sm font-medium">
                    {{ $page }}
                </span>
                @else
                <a href="{{ $url }}"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors text-sm font-medium">
                    {{ $page }}
                </a>
                @endif
                @endforeach

                {{-- Next --}}
                @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
