@extends('layouts.admin')

@section('title', 'Seminars - CoopAdmin')

@php
    // Uses the new suggestions route when it exists, otherwise falls back to the old search route.
    $memberSearchUrl = \Illuminate\Support\Facades\Route::has('seminars.member-suggestions')
        ? route('seminars.member-suggestions')
        : route('seminars.member-search');
@endphp

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
                    <span class="text-gray-900 font-medium">Seminars</span>
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

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                <span class="font-semibold text-red-800">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Seminars</h1>
            <p class="text-sm text-gray-500">Manage pre-membership seminars and completion tracking</p>
        </div>
        <div class="flex items-center gap-3">
            <button data-action="openModal" data-arg='["createTypeModal"]' class="btn btn-primary">
                <i data-lucide="layers" class="w-4 h-4"></i>
                Create Seminar Type
            </button>
            <button data-action="openModal" data-arg='["scheduleSeminarModal"]' class="btn btn-primary">
                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                Schedule Seminar
            </button>
        </div>
    </div>

    <!-- Member Seminar Progress Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="graduation-cap" class="w-5 h-5 text-primary-500"></i>
                Member Seminar Progress
            </h2>
            <p class="text-sm text-gray-500">Click a member to view the seminars they have attended and not attended yet.
            </p>
        </div>

        <form method="GET" action="{{ route('seminars.index') }}"
            class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 sm:items-center">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search member name..."
                    class="input pl-10">
            </div>
            <select name="status" class="input sm:w-52">
                <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                <option value="completed" {{ ($statusFilter ?? 'all') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="incomplete" {{ ($statusFilter ?? 'all') === 'incomplete' ? 'selected' : '' }}>Incomplete
                </option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th class="text-center">Completed</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Scheduled Seminar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="cursor-pointer hover:bg-gray-50 transition-colors" data-action="openMemberModal"
                            data-arg='[{{ $user->id }}]'>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full {{ $user->role === 'pending' ? 'bg-gradient-to-br from-yellow-400 to-orange-400' : 'bg-gradient-to-br from-primary-400 to-primary-600' }} flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ $user->first_name }}
                                            {{ $user->last_name }}</span>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="text-sm font-semibold text-gray-700">
                                    {{ ($user->completion->pmes_completed ? 1 : 0) + ($user->completion->fundamentals_completed ? 1 : 0) + ($user->completion->finance_completed ? 1 : 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($user->completion->pmes_completed && $user->completion->fundamentals_completed && $user->completion->finance_completed)
                                    <span class="badge badge-success">Complete</span>
                                @else
                                    <span class="badge badge-warning">Incomplete</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!empty($user->scheduled_seminar))
                                    <span class="badge badge-primary">{{ $user->scheduled_seminar }}</span>
                                @else
                                    <span class="badge badge-gray">No Schedule</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-12">
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

        @if($users->hasPages())
            @php
                $paginator = $users->appends([
                    'status' => $statusFilter ?? 'all',
                    'search' => $search ?? '',
                ]);
                $currentPage = $users->currentPage();
                $lastPage = $users->lastPage();
            @endphp
            <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between rounded-b-xl bg-white">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-700">{{ $users->firstItem() ?? 0 }}</span>
                    to <span class="font-medium text-gray-700">{{ $users->lastItem() ?? 0 }}</span>
                    of <span class="font-medium text-gray-700">{{ $users->total() }}</span> members
                </p>
                <div class="flex items-center gap-1.5">
                    @if($users->onFirstPage())
                        <span
                            class="w-9 h-9 flex items-center justify-center text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">&lt;</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}"
                            class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">&lt;</a>
                    @endif

                    @for($i = 1; $i <= $lastPage; $i++)
                        @if($i == $currentPage)
                            <span class="w-9 h-9 flex items-center justify-center text-sm font-bold text-white rounded-lg select-none"
                                style="background-color: #1E2A4A;">{{ $i }}</span>
                        @else
                            <a href="{{ $paginator->url($i) }}"
                                class="w-9 h-9 flex items-center justify-center text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($users->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}"
                            class="w-9 h-9 flex items-center justify-center text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-all no-underline">&gt;</a>
                    @else
                        <span
                            class="w-9 h-9 flex items-center justify-center text-sm text-gray-300 border border-gray-200 rounded-lg cursor-not-allowed select-none">&gt;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Schedule Seminar Modal -->
    <div id="scheduleSeminarModal" class="modal-overlay hidden">
        <div class="modal max-w-2xl">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="calendar-plus" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Schedule Seminar</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Create a new
                                seminar session</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["scheduleSeminarModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('seminars.schedule') }}" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seminar Type <span
                                class="text-red-500">*</span></label>
                        <select name="seminar_type" class="input" required data-action="togglePasscodeFields">
                            <option value="">Select...</option>
                            @foreach($seminarTypes as $type)
                                <option value="{{ $type->slug }}" {{ old('seminar_type') === $type->slug ? 'selected' : '' }} {{ in_array($type->slug, \App\Http\Controllers\SeminarController::CORE_TYPES) ? 'data-core="1"' : '' }}>{{ $type->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Date & Time <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" name="schedule_datetime" class="input" required
                            value="{{ old('schedule_datetime') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Type <span
                            class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="delivery_type" value="online" class="w-4 h-4 text-primary-600"
                                data-action="toggleDeliveryFields" {{ old('delivery_type', 'online') === 'online' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Online</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="delivery_type" value="f2f" class="w-4 h-4 text-primary-600"
                                data-action="toggleDeliveryFields" {{ old('delivery_type') === 'f2f' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Face-to-Face</span>
                        </label>
                    </div>
                </div>

                <div id="onlineFields" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Online Link <span
                                class="text-red-500">*</span></label>
                        <input type="url" name="online_link" class="input" placeholder="https://meet.google.com/..."
                            value="{{ old('online_link') }}">
                    </div>
                </div>

                <div id="f2fFields" class="space-y-4" style="display:none">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meetup Place <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="meetup_place" class="input" placeholder="e.g. Coop Hall, Main Office"
                            value="{{ old('meetup_place') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Exact Venue <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="exact_venue" class="input" placeholder="e.g. Room 201, 2nd Floor"
                            value="{{ old('exact_venue') }}">
                    </div>
                </div>

                <div id="passcodeFields" class="space-y-4">
                    <div
                        class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800 flex items-start gap-2">
                        <i data-lucide="key-round" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <p id="passcodeHint"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seminar Passcode <span
                                    class="text-red-500" id="passcodeRequiredStar">*</span></label>
                            <div class="relative">
                                <input type="text" name="passcode" id="passcodeInput" class="input pr-28"
                                    placeholder="Generate a code" required maxlength="64" value="{{ old('passcode') }}">
                                <button type="button" data-action="generatePasscode"
                                    class="btn btn-primary btn-xs px-2 py-1 absolute right-1.5 top-1/2 -translate-y-1/2"
                                    title="Generate a random passcode">
                                    <i data-lucide="dices" class="w-3.5 h-3.5"></i>
                                    Generate
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid Days</label>
                            <input type="number" name="valid_days" class="input" value="{{ old('valid_days', 1) }}" min="1"
                                max="365">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Attendees <span
                            class="text-red-500">*</span></label>

                    <div id="attendeeSearchBox" class="relative mb-2">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <input type="text" id="attendeeSearchInput" placeholder="Click to see members or type a name..."
                            class="input pl-10" autocomplete="off">
                        <div id="attendeeDropdown"
                            class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-56 overflow-y-auto hidden">
                        </div>
                    </div>

                    <div id="selectedAttendees" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                        @if(old('attendees'))
                            @foreach(old('attendees') as $attId)
                                <span
                                    class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-xs font-medium px-2.5 py-1 rounded-full"
                                    data-id="{{ $attId }}">
                                    <span class="attendee-name">Member #{{ $attId }}</span>
                                    <button type="button" data-remove-attendee="{{ $attId }}"
                                        class="hover:text-primary-900">&times;</button>
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div id="attendeesHiddenInputs"></div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" data-action="closeModal" data-arg='["scheduleSeminarModal"]'
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        Schedule Seminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Attendance Modal -->
    <div id="attendanceModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="clipboard-check" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Manage Attendance</h2>
                            <p id="attendanceModalSubtitle"
                                style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Mark attendees for
                                this seminar</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["attendanceModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="attendanceList" class="space-y-3">
                    <div class="text-center py-8 text-gray-400">
                        <i data-lucide="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading attendees...</p>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end">
                <button data-action="closeModal" data-arg='["attendanceModal"]'
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
            </div>
        </div>
    </div>

    <!-- Member Seminar Details Modal -->
    <div id="memberSeminarModal" class="modal-overlay hidden">
        <div class="modal max-w-lg">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="graduation-cap" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Member Seminars</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Attended and
                                not-yet-attended seminars</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["memberSeminarModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="memberSeminarBody">
                    <div class="text-center py-8 text-gray-400">
                        <i data-lucide="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
                        <p class="text-sm">Loading member seminars...</p>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end">
                <button data-action="closeModal" data-arg='["memberSeminarModal"]'
                    class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Close</button>
            </div>
        </div>
    </div>

    <!-- Create Seminar Type Modal -->
    <div id="createTypeModal" class="modal-overlay hidden">
        <div class="modal max-w-md">
            <div style="background: linear-gradient(135deg, #1E2A4A 0%, #25335A 100%); padding: 1.25rem 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="layers" class="w-5 h-5" style="color: #fff;"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: #fff; margin: 0;">Create Seminar Type</h2>
                            <p style="margin: 4px 0 0 0; color: rgba(255,255,255,0.7); font-size: 12px;">Add an additional
                                seminar type beyond the core three</p>
                        </div>
                    </div>
                    <button data-action="closeModal" data-arg='["createTypeModal"]'
                        style="background: rgba(255,255,255,0.1); border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="x" class="w-5 h-5" style="color: #fff;"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('seminars.store-type') }}" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seminar Type Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="label" class="input" placeholder="e.g. Leadership Training" required
                        maxlength="100">
                    <p class="text-xs text-gray-400 mt-1">A unique name for the new seminar type.</p>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" data-action="closeModal" data-arg='["createTypeModal"]'
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Create Seminar Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function toggleDeliveryFields() {
            const online = document.querySelector('input[name="delivery_type"][value="online"]').checked;
            document.getElementById('onlineFields').style.display = online ? 'block' : 'none';
            document.getElementById('f2fFields').style.display = online ? 'none' : 'block';

            const onlineLink = document.querySelector('input[name="online_link"]');
            const meetupPlace = document.querySelector('input[name="meetup_place"]');
            const exactVenue = document.querySelector('input[name="exact_venue"]');
            onlineLink.required = online;
            meetupPlace.required = !online;
            exactVenue.required = !online;
        }

        function generatePasscode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            const arr = new Uint32Array(3);
            crypto.getRandomValues(arr);
            const input = document.getElementById('passcodeInput');
            input.value = 'coop' + Array.from(arr, n => chars[n % chars.length]).join('');
            input.focus();
        }

        function togglePasscodeFields() {
            const input = document.getElementById('passcodeInput');
            const star = document.getElementById('passcodeRequiredStar');
            const hint = document.getElementById('passcodeHint');
            input.required = true;
            star.textContent = '*';
            hint.textContent = 'Enter a passcode members will use to mark this seminar complete. The code expires after the set number of days.';
        }

        function openMemberModal(userId) {
            const users = @json($users->items());
            const types = @json($seminarTypes);
            const user = users.find(u => u.id === userId);
            if (!user) return;

            const modal = document.getElementById('memberSeminarModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';

            const completion = user.completion || {};
            const attendedTypes = user.attended_types || [];
            const coreSlugs = ['pmes', 'fundamentals', 'finance'];

            let html = '';
            html += `
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                            <span class="text-white font-bold text-base">${(user.first_name?.[0] || '') + (user.last_name?.[0] || '')}</span>
                        </div>
                        <div>
                            <span class="text-base font-semibold text-gray-900">${user.first_name} ${user.last_name || ''}</span>
                            <p class="text-xs text-gray-400">${user.email}</p>
                        </div>
                    </div>`;

            const scheduled = user.scheduled_seminars || [];
            if (scheduled.length) {
                html += `<div class="mb-5 space-y-3">`;
                scheduled.forEach(s => {
                    html += `
                <div class="p-3 rounded-lg bg-blue-50 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Scheduled Seminar</span>
                        <span class="text-xs font-medium text-blue-600">${s.label}</span>
                    </div>
                    ${s.passcode ? `
                    <div class="mt-2 flex items-center justify-between bg-white rounded-lg border border-blue-200 px-3 py-2">
                        <span class="text-xs font-medium text-blue-700">Member Passcode</span>
                        <code class="text-sm font-mono font-bold text-blue-900 tracking-wider">${s.passcode}</code>
                    </div>` : ''}
                    <p class="text-xs text-blue-600 mt-2">Share this code with ${user.first_name} so they can mark ${s.label} complete.</p>
                </div>`;
                });
                html += `</div>`;
            }

            types.forEach(t => {
                const attended = coreSlugs.includes(t.slug)
                    ? completion[t.slug + '_completed'] === true
                    : attendedTypes.includes(t.slug);
                const boxClass = attended ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                const icon = attended ? 'check-circle' : 'x-circle';
                const iconClass = attended ? 'text-green-600' : 'text-red-500';
                const label = attended ? 'Attended' : 'Not attended';

                html += `
                        <div class="flex items-center justify-between p-3 border rounded-lg ${boxClass} mb-2">
                            <span class="text-sm font-medium text-gray-800">${t.label}</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold ${iconClass}">
                                <i data-lucide="${icon}" class="w-4 h-4"></i>${label}
                            </span>
                        </div>`;
            });

            document.getElementById('memberSeminarBody').innerHTML = html;
            lucide.createIcons();
        }

        function openAttendanceModal(seminarId) {
            const modal = document.getElementById('attendanceModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            document.getElementById('attendanceList').innerHTML =
                '<div class="text-center py-8 text-gray-400"><i data-lucide="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i><p class="text-sm">Loading attendees...</p></div>';

            fetch('{{ route("seminars.index") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.text())
                .then(() => {
                    const seminars = @json($upcomingSeminars->merge($pastSeminars));
                    const seminar = seminars.find(s => s.id === seminarId);
                    if (!seminar) {
                        document.getElementById('attendanceList').innerHTML =
                            '<div class="text-center py-8 text-gray-400"><p class="text-sm">Seminar not found.</p></div>';
                        return;
                    }
                    document.getElementById('attendanceModalSubtitle').textContent =
                        seminar.seminar_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) +
                        ' - ' + new Date(seminar.schedule_datetime).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });

                    if (seminar.attendees.length === 0) {
                        document.getElementById('attendanceList').innerHTML =
                            '<div class="text-center py-8 text-gray-400"><i data-lucide="user-x" class="w-8 h-8 mx-auto mb-2"></i><p class="text-sm">No attendees assigned.</p></div>';
                        lucide.createIcons();
                        return;
                    }

                    let html = '';
                    seminar.attendees.forEach(a => {
                        const name = a.user ? a.user.first_name + ' ' + (a.user.last_name || '') : 'Unknown';
                        const initials = a.user ? (a.user.first_name?.[0] || '') + (a.user.last_name?.[0] || '') : '??';
                        const statusColor = a.status === 'attended' ? 'bg-green-500' : a.status === 'absent' ? 'bg-red-500' : 'bg-yellow-400';
                        const statusLabel = a.status === 'attended' ? 'Attended' : a.status === 'absent' ? 'Absent' : 'Pending';

                        html += `
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">${initials}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">${name}</span>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="w-2 h-2 rounded-full ${statusColor}"></span>
                                                <span class="text-xs text-gray-500">${statusLabel}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button data-action="markAttendance" data-arg=\'[${seminar.id}, ${a.user_id}, "attended"]\' class="btn btn-success btn-xs px-2.5 py-1.5 ${a.status === 'attended' ? 'opacity-50 cursor-not-allowed' : ''}" ${a.status === 'attended' ? 'disabled' : ''}>
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Attended
                                        </button>
                                        <button data-action="markAttendance" data-arg=\'[${seminar.id}, ${a.user_id}, "absent"]\' class="btn btn-danger btn-xs px-2.5 py-1.5 ${a.status === 'absent' ? 'opacity-50 cursor-not-allowed' : ''}" ${a.status === 'absent' ? 'disabled' : ''}>
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            Absent
                                        </button>
                                    </div>
                                </div>
                            `;
                    });
                    document.getElementById('attendanceList').innerHTML = html;
                    lucide.createIcons();
                });
        }

        function markAttendance(seminarId, userId, status) {
            fetch('{{ route("seminars.attendance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ seminar_id: seminarId, user_id: userId, status: status }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message, 'success');
                        openAttendanceModal(seminarId);
                    }
                })
                .catch(() => {
                    showToast('Error', 'Failed to update attendance.', 'error');
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleDeliveryFields();
            togglePasscodeFields();

            @if($errors->any())
                openModal('scheduleSeminarModal');
            @endif
            });

        /* ═══════════════════════════════════════════════════════════
           ATTENDEE SEARCH WITH MEMBER SUGGESTIONS
           - click the box  -> suggested members appear
           - type           -> list filters from the first letter
           - click a member -> added to the list, then press "Schedule Seminar"
           Uses its own listeners (no data-action), so it does not depend on csp-events.js.
           ═══════════════════════════════════════════════════════════ */
        const MEMBER_SEARCH_URL = "{{ $memberSearchUrl }}";
        const selectedAttendeeMap = {};
        const attendeeCache = {};
        let attendeeSearchTimeout = null;
        let attendeeRequestId = 0;
        let lastAttendeeResults = [];

        const attendeeInput = document.getElementById('attendeeSearchInput');
        const attendeeDropdown = document.getElementById('attendeeDropdown');
        const attendeeBox = document.getElementById('attendeeSearchBox');
        const selectedAttendeesEl = document.getElementById('selectedAttendees');

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function loadAttendeeSuggestions(q) {
            const requestId = ++attendeeRequestId;

            if (attendeeDropdown.classList.contains('hidden')) {
                attendeeDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Loading members...</div>';
                attendeeDropdown.classList.remove('hidden');
            }

            fetch(MEMBER_SEARCH_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (members) {
                    if (requestId !== attendeeRequestId) return;
                    lastAttendeeResults = Array.isArray(members) ? members : [];
                    renderAttendeeDropdown(lastAttendeeResults, q);
                })
                .catch(function (err) {
                    if (requestId !== attendeeRequestId) return;
                    console.error('Member search failed:', err);
                    attendeeDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-red-500">Could not load members. Please try again.</div>';
                });
        }

        function renderAttendeeDropdown(members, q) {
            if (!members.length) {
                attendeeDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">No members found</div>';
                attendeeDropdown.classList.remove('hidden');
                return;
            }

            const header = q === ''
                ? '<div class="px-3 py-1.5 text-xs font-semibold text-gray-400 bg-gray-50 sticky top-0">Suggested members</div>'
                : '';

            attendeeDropdown.innerHTML = header + members.map(function (m) {
                attendeeCache[m.id] = m;
                const already = !!selectedAttendeeMap[m.id];
                const initials = ((m.first_name || '')[0] || '') + ((m.last_name || '')[0] || '');
                const roleBg = m.role === 'pending' ? 'from-yellow-400 to-orange-400' : 'from-primary-400 to-primary-600';

                return '<div data-member-id="' + Number(m.id) + '" data-added="' + (already ? '1' : '0') + '" ' +
                    'class="flex items-center gap-3 px-3 py-2 ' + (already ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-50') + '">' +
                    '<div class="w-8 h-8 rounded-full bg-gradient-to-br ' + roleBg + ' flex items-center justify-center flex-shrink-0">' +
                    '<span class="text-white font-bold text-xs">' + escapeHtml(initials.toUpperCase()) + '</span>' +
                    '</div>' +
                    '<div class="min-w-0">' +
                    '<span class="text-sm font-medium text-gray-700">' + escapeHtml(m.first_name) + ' ' + escapeHtml(m.last_name || '') + '</span>' +
                    '<span class="text-xs text-gray-400 block truncate">' + escapeHtml(m.email) + '</span>' +
                    '</div>' +
                    (already ? '<span class="ml-auto text-xs text-gray-400">Added</span>' : '') +
                    '</div>';
            }).join('');

            attendeeDropdown.classList.remove('hidden');
        }

        function addAttendee(id) {
            id = Number(id);
            const m = attendeeCache[id];
            if (!m || selectedAttendeeMap[id]) return;

            selectedAttendeeMap[id] = { id: id, first_name: m.first_name, last_name: m.last_name };
            syncAttendeesInput();
            renderSelectedAttendees();

            attendeeInput.value = '';
            loadAttendeeSuggestions('');
        }

        function removeAttendee(id) {
            id = Number(id);
            delete selectedAttendeeMap[id];
            syncAttendeesInput();
            renderSelectedAttendees();

            if (!attendeeDropdown.classList.contains('hidden')) {
                renderAttendeeDropdown(lastAttendeeResults, attendeeInput.value.trim());
            }
        }

        function syncAttendeesInput() {
            const container = document.getElementById('attendeesHiddenInputs');
            container.innerHTML = '';
            Object.keys(selectedAttendeeMap).forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'attendees[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        function renderSelectedAttendees() {
            selectedAttendeesEl.innerHTML = Object.values(selectedAttendeeMap).map(function (m) {
                return '<span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-xs font-medium px-2.5 py-1 rounded-full">' +
                    '<span class="attendee-name">' + escapeHtml(m.first_name) + ' ' + escapeHtml(m.last_name || '') + '</span>' +
                    '<button type="button" data-remove-attendee="' + Number(m.id) + '" class="hover:text-primary-900">&times;</button>' +
                    '</span>';
            }).join('');
        }

        /* Show suggestions as soon as the box is focused / clicked */
        attendeeInput.addEventListener('focus', function () {
            loadAttendeeSuggestions(attendeeInput.value.trim());
        });

        attendeeInput.addEventListener('click', function () {
            if (attendeeDropdown.classList.contains('hidden')) {
                loadAttendeeSuggestions(attendeeInput.value.trim());
            }
        });

        /* Filter while typing (works from the first letter) */
        attendeeInput.addEventListener('input', function () {
            clearTimeout(attendeeSearchTimeout);
            attendeeSearchTimeout = setTimeout(function () {
                loadAttendeeSuggestions(attendeeInput.value.trim());
            }, 250);
        });

        /* Enter adds the first available suggestion instead of submitting the form */
        attendeeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const first = lastAttendeeResults.find(function (m) { return !selectedAttendeeMap[m.id]; });
                if (first) addAttendee(first.id);
            } else if (e.key === 'Escape') {
                attendeeDropdown.classList.add('hidden');
            }
        });

        /* Pick a member: mousedown fires before the input loses focus, so the click can never be lost */
        attendeeDropdown.addEventListener('mousedown', function (e) {
            e.preventDefault();
            const row = e.target.closest('[data-member-id]');
            if (!row || row.dataset.added === '1') return;
            addAttendee(row.dataset.memberId);
        });

        /* Close the list when clicking anywhere outside the search box */
        document.addEventListener('mousedown', function (e) {
            if (!attendeeBox.contains(e.target)) {
                attendeeDropdown.classList.add('hidden');
            }
        });

        /* Remove a selected attendee */
        selectedAttendeesEl.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-remove-attendee]');
            if (btn) removeAttendee(btn.dataset.removeAttendee);
        });

        /* Restore previously selected attendees after a validation error */
        @if(old('attendees'))
            @foreach(old('attendees') as $attId)
                selectedAttendeeMap[{{ (int) $attId }}] = { id: {{ (int) $attId }}, first_name: 'Member', last_name: '#{{ (int) $attId }}' };
            @endforeach
            syncAttendeesInput();
            renderSelectedAttendees();
        @endif

        document.querySelector('#scheduleSeminarModal form')?.addEventListener('submit', function (e) {
            if (Object.keys(selectedAttendeeMap).length === 0) {
                e.preventDefault();
                showToast('Validation Error', 'Please select at least one attendee.', 'error');
            }
        });
    </script>
@endsection