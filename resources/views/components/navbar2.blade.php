@php
    $navSeminarsComplete = false;
    if (auth()->check()) {
        $navSeminarCompletion = \App\Models\SeminarCompletions_tbl::where('user_id', auth()->id())->first();
        $navSeminarsComplete = $navSeminarCompletion
            && $navSeminarCompletion->pmes_completed
            && $navSeminarCompletion->fundamentals_completed
            && $navSeminarCompletion->finance_completed;
    }
@endphp
<nav id="dashboard-nav"
    class="tw:flex justify-content-between align-items-center tw:w-[100%] tw:h-[80px] tw:bg-[#ffffff]">
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <div class="nav-logo">
        {{-- <h2 class="m-0" style="font-size: 25px">LOGO</h2> --}}
        <!-- <img src="images/logo2.png" width="50px" height="50px" style="border-radius: 50%" alt="">
    <h3>KPMPCATS</h3> -->
        {{-- <h2 class="mw-100 m-0" style="font-size: 14px; width: 200px;">Kingsland Pala-Pala MPC & Transport Service
        </h2> --}}

        <img src="images/logo2.png" width="45px" height="45px" style="border-radius: 50%" alt="" class="logo-navbar">

        <!-- <div class="nav-menu">
        <i class="fa fa-bars" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu1"
            aria-controls="staticBackdrop"></i>
    </div> -->

        <!-- <i class="fa fa-bars" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu1"
            aria-controls="staticBackdrop"></i> -->
        <button type="button" class="collapse-button" data-action="toggleSidebar">
            <i class="fa fa-bars"></i>
        </button>

        <!-- <h5>Good day, {{ $username }} <span>Here's your overview</span></h5> -->
    </div>

    <!-- <div class="nav-list nav-list2">
    <ul class="tw:flex tw:gap-x-[4rem] m-0 p-0">
        <li class="tw:list-none">
            <a href="{{ route("MemberPortal") }}"
                class="tw:no-underline tw:text-[15.5px] text-decoration-none">Home</a>
        </li>

        <li class="tw:list-none">
            <a href="{{ route("LoanApplication") }}"
                class="tw:no-underline tw:text-[15.5px] text-decoration-none">Loan Application</a>
        </li>

        <li class="tw:list-none">
            <a href="{{ route("LoanStatus") }}"
                class="tw:no-underline tw:text-[15.5px] text-decoration-none">Loan
                Status</a>
        </li>

        <li class="tw:list-none">
            <a href="{{ route("ShareCapitalMember") }}"
                class="tw:no-underline tw:text-[15.5px] text-decoration-none">Share Capital</a>
        </li>

        <li class="tw:list-none">
            <a href="{{ route("savings.index") }}"
                class="tw:no-underline tw:text-[15.5px] text-decoration-none">Savings</a>
        </li>
    </ul>
</div> -->

    <style>
        /* Notification panel: wrap long text everywhere */
        #nb-notif-panel p {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        /* ── Phones / small tablets: compact card under the bell ── */
        @media only screen and (max-width: 576px) {
            #nb-notif-panel {
                position: fixed !important;
                top: 78px !important;
                right: 12px !important;
                left: auto !important;
                width: 340px !important;
                max-width: calc(100vw - 24px) !important;
                margin: 0 !important;
                border-radius: 14px !important;
                border: 1px solid #E5EAF3 !important;
                box-shadow: 0 12px 32px rgba(30, 42, 74, .16) !important;
                overflow: hidden;
                z-index: 1100 !important;
                background: #fff;
            }

            /* Header row */
            #nb-notif-panel>div:first-child {
                padding: 12px 14px !important;
                font-size: 14px !important;
            }

            /* List: scrolls inside the card, stays clear of the bottom nav */
            #nb-notif-panel>div:last-child {
                max-height: min(380px, calc(100dvh - 78px - 130px)) !important;
                overflow-y: auto !important;
                overscroll-behavior: contain;
            }

            /* Each notification row */
            #nb-notif-panel>div:last-child>a {
                padding: 11px 14px !important;
                gap: 10px !important;
            }

            #nb-notif-panel>div:last-child>a p:first-child {
                font-size: 12.5px !important;
            }

            #nb-notif-panel>div:last-child>a p:nth-child(2) {
                font-size: 11.5px !important;
            }
        }

        @media only screen and (max-width: 360px) {
            #nb-notif-panel {
                right: 8px !important;
                max-width: calc(100vw - 16px) !important;
            }
        }

        /* Hide "Switch to Allied Worker" on screens 1200px and below — every page */
        @media only screen and (max-width: 1200px) {
            #dashboard-nav .aw-switch-item {
                display: none !important;
            }
        }

        #nb-notif-panel {
            z-index: 1100;
        }

        .nb-notif-item {
            cursor: pointer;
            transition: background .15s;
        }

        .nb-notif-item:hover,
        .nb-notif-item:focus-visible {
            background: #f8fafc;
            outline: none;
        }

        #nb-mark-all:hover {
            background: #eff6ff;
        }

        /* Non-clickable notifications (seminars, login attempt) */
        .nb-notif-item.nb-notif-static {
            cursor: default;
        }

        .nb-notif-item.nb-notif-static:hover {
            background: transparent;
        }

        /* After "Mark all as read": keep the item, just dim it */
        .nb-notif-item.nb-read {
            opacity: .55;
        }
    </style>

    @include('components.login-switch-modal')

    <div class="nav-menu dashboard">
        <i class="fa fa-bars" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu2"
            aria-controls="staticBackdrop"></i>
    </div>

    <div class="nav-acc2" id="nav-acc2">
        <ul class="m-0 p-0">
            <div class="nb-notif-wrap">
                <button type="button" id="nb-notif-btn" aria-label="Notifications" aria-expanded="false"
                    style="background:none; border:none; cursor:pointer; position:relative; padding:8px; display:flex; align-items:center;">
                    <i class="fa-solid fa-bell" style="font-size: 17px; color: var(--muted);"></i>
                    @if($navNotifications->count() > 0)
                        <span id="nb-notif-badge"
                            style="position:absolute; top:-2px; right:-2px; background:#dc2626; color:#fff; font-size:10px; font-weight:700; min-width:16px; height:16px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 3px; line-height:1;">
                            {{ $navNotifications->count() }}
                        </span>
                    @endif
                </button>

                <div id="nb-notif-panel">
                    <div
                        style="padding:12px 16px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; gap:10px;">
                        <span style="font-weight:700; font-size:14px; color:#1a1a1a;">Notifications</span>
                        @if($navNotifications->count() > 0)
                            <button type="button" id="nb-mark-all"
                                style="background:none; border:none; color:#1D4ED8; font-size:12px; font-weight:600; cursor:pointer; padding:4px 8px; border-radius:6px;">
                                Mark all as read
                            </button>
                        @endif
                    </div>

                    <div id="nb-notif-list" style="max-height:360px; overflow-y:auto;">
                        @foreach($navNotifications as $n)
                            @php
                                $nClickable = $n['clickable'] ?? true;

                                $nTitle = strtolower($n['title'] ?? '');
                                $nIcon = $n['icon'] ?? 'fa-bell';
                                $nColor = $n['color'] ?? 'green';
                                if (\Illuminate\Support\Str::contains($nTitle, ['confirmed', 'approved', 'accepted', 'received', 'released', 'completed', 'successful', 'verified'])) {
                                    $nColor = 'green';
                                    $nIcon = 'fa-circle-check';
                                } elseif (\Illuminate\Support\Str::contains($nTitle, ['returned', 'declined', 'rejected', 'voided', 'overdue', 'failed', 'cancelled'])) {
                                    $nColor = 'red';
                                }
                            @endphp
                            <a @if($nClickable) href="{{ $n['url'] ?? '#' }}" data-url="{{ $n['url'] ?? '#' }}" @endif
                                class="nb-notif-item {{ $nClickable ? '' : 'nb-notif-static' }}"
                                data-notif-id="{{ $n['id'] }}"
                                style="display:flex; gap:10px; padding:12px 16px; border-bottom:1px solid #f5f5f5; text-decoration:none; color:inherit;">
                                <div
                                    style="width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background: {{ $nColor === 'red' ? '#fee2e2' : ($nColor === 'gold' ? '#fef3c7' : '#d1fae5') }};">
                                    <i class="fa-solid {{ $nIcon }}"
                                        style="font-size:13px; color: {{ $nColor === 'red' ? '#dc2626' : ($nColor === 'gold' ? '#b45309' : '#059669') }};"></i>
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <p style="margin:0; font-size:13px; font-weight:600; color:#1a1a1a;">{{ $n['title'] }}
                                    </p>
                                    <p style="margin:2px 0 0; font-size:12px; color:#666; line-height:1.4;">
                                        {{ $n['message'] }}
                                    </p>
                                    <p style="margin:4px 0 0; font-size:11px; color:#999;">{{ $n['time'] }}</p>
                                </div>
                            </a>
                        @endforeach

                        <div id="nb-notif-empty"
                            style="{{ $navNotifications->count() > 0 ? 'display:none;' : '' }} padding:30px 16px; text-align:center; color:#999; font-size:13px;">
                            <i class="fa-regular fa-bell-slash"
                                style="font-size:20px; display:block; margin-bottom:8px; opacity:0.5;"></i>
                            No notifications right now.
                        </div>
                    </div>
                </div>
            </div>

            <script nonce="{{ csp_nonce() }}">
                (function () {
                    const btn = document.getElementById('nb-notif-btn');
                    const panel = document.getElementById('nb-notif-panel');
                    const list = document.getElementById('nb-notif-list');
                    const wrap = document.querySelector('.nb-notif-wrap');
                    if (!btn || !panel || !list) return;

                    const CSRF = '{{ csrf_token() }}';
                    const URL_MARK_ONE = '{{ route('notifications.markRead') }}';
                    const URL_MARK_ALL = '{{ route('notifications.markAllRead') }}';

                    function isOpen() { return panel.style.display === 'block'; }
                    function openPanel() { panel.style.display = 'block'; btn.setAttribute('aria-expanded', 'true'); }
                    function closePanel() { panel.style.display = 'none'; btn.setAttribute('aria-expanded', 'false'); }

                    function setBadge(n) {
                        const badge = document.getElementById('nb-notif-badge');
                        if (!badge) return;
                        if (n <= 0) { badge.remove(); } else { badge.textContent = n; }
                    }

                    function showEmpty() {
                        list.querySelectorAll('.nb-notif-item').forEach(function (el) { el.remove(); });
                        const empty = document.getElementById('nb-notif-empty');
                        if (empty) empty.style.display = 'block';
                        const markAll = document.getElementById('nb-mark-all');
                        if (markAll) markAll.remove();
                        setBadge(0);
                    }

                    function post(url, body) {
                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: JSON.stringify(body || {}),
                            keepalive: true,
                            credentials: 'same-origin'
                        });
                    }

                    // Bell: open / close
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        isOpen() ? closePanel() : openPanel();
                    });

                    // Click outside closes the panel
                    document.addEventListener('click', function (e) {
                        if (isOpen() && wrap && !wrap.contains(e.target)) closePanel();
                    });

                    // Esc closes the panel
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && isOpen()) { closePanel(); btn.focus(); }
                    });

                    // Click one notification: mark it read, then go to its page
                    list.addEventListener('click', function (e) {
                        const item = e.target.closest('.nb-notif-item');
                        if (!item) return;

                        // Seminar / login-attempt notifications do nothing when clicked
                        if (item.classList.contains('nb-notif-static')) {
                            e.preventDefault();
                            return;
                        }

                        e.preventDefault();

                        const id = item.getAttribute('data-notif-id');
                        const url = item.getAttribute('data-url');

                        const badge = document.getElementById('nb-notif-badge');
                        if (badge && !item.classList.contains('nb-read')) {
                            setBadge((parseInt(badge.textContent.trim(), 10) || 0) - 1);
                        }

                        post(URL_MARK_ONE, { id: id }).catch(function () { });

                        if (!url || url === '#') {
                            item.remove();
                            if (!list.querySelector('.nb-notif-item')) showEmpty();
                            return;
                        }
                        window.location.href = url;
                    });

                    // Mark all as read — keep the items in the list, just dim them
                    const markAll = document.getElementById('nb-mark-all');
                    if (markAll) {
                        markAll.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            markAll.disabled = true;
                            markAll.textContent = 'Marking…';

                            post(URL_MARK_ALL, {})
                                .then(function (res) { return res.json(); })
                                .then(function (data) {
                                    if (data && data.success) {
                                        list.querySelectorAll('.nb-notif-item').forEach(function (el) {
                                            el.classList.add('nb-read');
                                        });
                                        setBadge(0);          // remove the red badge
                                        markAll.remove();     // nothing left to mark
                                    } else {
                                        markAll.disabled = false;
                                        markAll.textContent = 'Mark all as read';
                                    }
                                })
                                .catch(function () {
                                    markAll.disabled = false;
                                    markAll.textContent = 'Mark all as read';
                                });
                        });
                    }
                })();
            </script>

            <script nonce="{{ csp_nonce() }}">
                function handleNotifClick(e, el, id, url) {
                    if (e && e.preventDefault) e.preventDefault();

                    // Optimistically shrink/hide the badge so the count feels instant
                    const badge = document.querySelector('.nb-notif-wrap span');
                    if (badge) {
                        const current = parseInt(badge.textContent.trim(), 10) || 0;
                        const next = current - 1;
                        if (next <= 0) {
                            badge.remove();
                        } else {
                            badge.textContent = next;
                        }
                    }

                    // Fire-and-forget: mark this notification read server-side.
                    fetch('{{ route('notifications.markRead') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id: id }),
                        keepalive: true
                    }).catch(() => { });

                    // No destination URL (e.g. resignation status updates) — just mark it
                    // read and remove it from the open panel, don't navigate away.
                    if (!url || url === '#') {
                        if (el) {
                            el.style.opacity = '0';
                            setTimeout(() => el.remove(), 150);
                        }
                        return;
                    }

                    // Navigate ourselves, since data-action click already prevented default.
                    window.location.href = url;
                }

                function toggleNavNotif(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const panel = document.getElementById('nb-notif-panel');
                    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
                }

                document.addEventListener('click', function (e) {
                    const panel = document.getElementById('nb-notif-panel');
                    const wrap = document.querySelector('.nb-notif-wrap');
                    if (panel && wrap && !wrap.contains(e.target) && panel.style.display === 'block') {
                        panel.style.display = 'none';
                    }
                });
            </script>
            @if(auth()->user()?->isAlliedWorker())
                <li class="tw:list-none aw-switch-item" style="align-self:center; margin-right:12px;">
                    <button type="button" data-action="openAwSwitch"
                        style="background:#1D4ED8; color:#fff; border:none; border-radius:8px; padding:9px 15px; font-size:13px; font-weight:600; cursor:pointer;">
                        <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right:6px;"></i>Switch to Allied Worker
                    </button>
                </li>
            @endif
            <li>
                <a href="#" data-action="toggleDropdown" data-arg='["|event|"]'
                    class="tw:flex tw:justify-center tw:items-center tw:gap-x-[0.7rem] position-relative">
                    @php $navPhoto = Auth::user()->otherinfo->profile_picture ?? null; @endphp
                    <div class="first-last" style="overflow:hidden; padding:0;">
                        @if($navPhoto)
                            <img src="{{ url('proof/' . $navPhoto) }}" alt=""
                                style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                        @else
                            <p>{{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}</p>
                        @endif
                    </div>
                    <div class="name-email">
                        <p>
                            {{ auth()->user()->first_name ?? '' }}
                            <!-- {{ auth()->user()->last_name ?? '' }} -->
                        </p>
                        <!-- <p>
                        {{ auth()->user()->email ?? '' }}
                    </p> -->
                    </div>
                    <i class="fa fa-chevron-down"></i>

                </a>
                <!-- @if ($username)
                @php
                    $userId = Auth::id();
                    $navOtherinfo = \App\Models\Otherinfo_tbl::where('user_id', $userId)->first();
                    $navMembergovernIds = \App\Models\Membergovern_ids_tbl::where('user_id', $userId)->first();
                    $navMissingCount = 0;
                    if ($navOtherinfo && empty($navOtherinfo->contact_no))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->present_address))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->permanent_address))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->date_of_birth))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->place_of_birth))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->sex))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->civil_status))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->citizenship))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->blood_type))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->height))
                        $navMissingCount++;
                    if ($navOtherinfo && empty($navOtherinfo->weight))
                        $navMissingCount++;
                    if ($navMembergovernIds && empty($navMembergovernIds->sss_id))
                        $navMissingCount++;
                    if ($navMembergovernIds && empty($navMembergovernIds->philhealth_id))
                        $navMissingCount++;
                    if ($navMembergovernIds && empty($navMembergovernIds->pagibig_id))
                        $navMissingCount++;
                    if ($navMembergovernIds && empty($navMembergovernIds->tin_id))
                        $navMissingCount++;
                @endphp
                <a href="#" onclick="toggleDropdown(event)"
                    class="tw:flex tw:justify-center tw:items-center tw:gap-x-[0.7rem] position-relative">
                    <div class="first-last">
                        <p>{{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}</p>
                    </div>
                    <p style="margin: 0">
                        {{ auth()->user()->first_name ?? '' }}
                    </p>
                    @if($navMissingCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="font-size: 12px; padding: 4px 8px;">
                            {{ $navMissingCount }}
                        </span>
                    @endif
                    <i class="fa fa-chevron-down"></i>
                </a>
            @endif -->

                <ul>
                    @if($email)
                        <li>
                            <div class="card-icon" style="overflow:hidden; padding:0;">
                                @if($navPhoto)
                                    <img src="{{ url('proof/' . $navPhoto) }}" alt=""
                                        style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                @else
                                    <p>{{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}</p>
                                @endif
                            </div>
                            <p>{{ $email }}</p>
                        </li>
                    @endif
                    <hr>
                    <li>
                        <div class="card-icon"><i class="fa fa-user"></i></div>
                        <a href="{{ route('ProfileMember') }}">My Profile</a>
                    </li>
                    <li>
                        <div class="card-icon"><i class="fa fa-lock"></i></div>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            Reset Password
                        </a>
                    </li>
                    <li>
                        <div class="card-icon"><i class="fa fa-circle-question"></i></div>
                        <a href="{{ route('Faqs') }}">FAQs</a>
                    </li>
                    <li>
                        <div class="card-icon"><i class="fa fa-sign-out"></i></div>
                        <a href="{{ route('logout') }}">Logout</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <script nonce="{{ csp_nonce() }}">
        function toggleDropdown(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('nav-acc2').classList.toggle('open');
        }

        // document.addEventListener('click', function (e) {
        //     const wrap = document.getElementById('nav-acc2');
        //     if (!wrap.contains(e.target)) {
        //         wrap.classList.remove('open');
        //     }
        // });
    </script>

    <script nonce="{{ csp_nonce() }}">
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const rightbar = document.querySelector('.rightbar');

            sidebar.classList.toggle('collapsed');
            rightbar.classList.toggle('expanded');
        }
    </script>

    @if(auth()->user()?->isAlliedWorker())
        <div id="awSwitchModal"
            style="display:{{ $errors->has('aw_password') ? 'flex' : 'none' }}; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div
                style="background:#fff; border-radius:14px; padding:24px; width:94%; max-width:400px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                <h3 style="margin:0 0 4px; font-size:17px; color:#1a1a1a;">Switch to Allied Worker</h3>
                <p style="margin:0 0 18px; font-size:13px; color:#666;">Confirm your password to work as an Allied Worker.
                    Your member account stays intact.</p>
                <form action="{{ route('allied-workers.switch-to-aw') }}" method="POST">
                    @csrf
                    @error('aw_password')
                        <div
                            style="background:#fef0f0; border:1.5px solid #f5c6c6; border-radius:8px; padding:10px 12px; margin-bottom:12px; font-size:13px; color:#e03131; font-weight:600;">
                            <i class="fa-solid fa-circle-xmark" style="margin-right:6px;"></i>{{ $message }}
                        </div>
                    @enderror
                    <input type="password" name="password" required placeholder="Enter your password"
                        style="width:100%; padding:11px 13px; border:1px solid #ddd; border-radius:8px; font-size:14px; margin-bottom:14px;">
                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" data-action="closeAwSwitch"
                            style="padding:9px 14px; border:1px solid #ddd; border-radius:8px; background:#fff; color:#333; font-size:13px; cursor:pointer;">Cancel</button>
                        <button type="submit"
                            style="padding:9px 16px; border:none; border-radius:8px; background:#1D4ED8; color:#fff; font-size:13px; font-weight:600; cursor:pointer;">Switch
                            Mode</button>
                    </div>
                </form>
            </div>
        </div>
        <script nonce="{{ csp_nonce() }}">
            function openAwSwitch() { document.getElementById('awSwitchModal').style.display = 'flex'; }
            function closeAwSwitch() { document.getElementById('awSwitchModal').style.display = 'none'; }
        </script>
    @endif

</nav>

@auth
    <script nonce="{{ csp_nonce() }}">
        (function () {
            const PING_URL = "{{ route('member.statusPing') }}";
            const INACTIVE_URL = "{{ route('member.inactive') }}";
            const INTERVAL = 8000;
            let baseline = null, timer = null, acting = false;

            function go(url, text) {
                acting = true;
                clearInterval(timer);
                const d = document.createElement('div');
                d.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;padding:14px;' +
                    'background:#1f2937;color:#fff;text-align:center;font-weight:600';
                d.textContent = (text || 'Your account status has changed.') + ' Redirecting…';
                document.body.appendChild(d);
                setTimeout(function () { window.location.href = url; }, 1000);
            }

            function ping() {
                if (acting || document.hidden) return;

                fetch(PING_URL, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                    credentials: 'same-origin'
                })
                    .then(function (res) {
                        // Middleware bounced us somewhere — that IS the status change.
                        if (res.redirected) { go(res.url, 'Your account status has changed.'); return null; }
                        if (res.status === 401 || res.status === 419) { go("{{ route('login') }}", 'Your session ended.'); return null; }
                        if (!res.ok) return null;

                        const ct = res.headers.get('content-type') || '';
                        if (ct.indexOf('application/json') === -1) { go(INACTIVE_URL, 'Your account status has changed.'); return null; }

                        return res.json();
                    })
                    .then(function (data) {
                        if (!data || acting) return;

                        if (!data.authenticated) { go(data.redirect, 'Your session ended.'); return; }
                        if (data.redirect) { go(data.redirect, data.message); return; }

                        if (baseline === null) { baseline = data.fingerprint; return; }
                        if (baseline !== data.fingerprint) { go(window.location.href, 'Your account was updated.'); }
                    })
                    .catch(function () { /* offline — retry next tick */ });
            }

            ping();
            timer = setInterval(ping, INTERVAL);
            document.addEventListener('visibilitychange', function () { if (!document.hidden) ping(); });
        })();
    </script>
@endauth

@include('components.reset_password_modal')