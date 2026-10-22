<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile</title>

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/notifications.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")

            <div class="main-parent">
                <div class="main-header">
                    <div>
                        <h3>Notifications</h3>
                        <p>Loan reminders, account activity and cooperative announcements</p>
                    </div>
                    <div>
                        <button id="markAllReadBtn" type="button">
                            <i class="fa fa-envelope-open"></i>
                            Mark all as read
                        </button>
                    </div>
                </div>

                <div class="main-body">
                    <div class="filters">
                        <div class="tab-group">
                            <div class="tab active" data-filter="all">All
                                <div class="tab-count">{{ $notifications->count() }}</div>
                            </div>
                            <div class="tab" data-filter="unread">Unread
                                <div class="tab-count">{{ $unreadCount }}</div>
                            </div>
                            <div class="tab" data-filter="important">Important
                                <div class="tab-count">{{ $importantCount }}</div>
                            </div>
                            <div class="tab" data-filter="inbox">Inbox
                                <div class="tab-count">{{ $inboxCount }}</div>
                            </div>
                            <div class="tab" data-filter="announcement">Announcements
                                <div class="tab-count">{{ $announcementCount }}</div>
                            </div>
                            <div class="tab" data-filter="spam">Spam
                                <div class="tab-count">{{ $spamCount }}</div>
                            </div>
                            <div class="tab" data-filter="social">Social
                                <div class="tab-count">{{ $socialCount }}</div>
                            </div>
                        </div>
                    </div>

                    @if($notifications->isEmpty())
                        <div class="empty-state" id="emptyState">
                            <i class="fa fa-bell-slash"></i>
                            <p>No notifications yet</p>
                        </div>
                    @else
                        <div id="notifList">
                            @foreach($grouped as $label => $items)
                                <div class="notif-group" data-group>
                                    <div class="group-label">{{ $label }}</div>
                                    <div class="ledger-page">
                                        @foreach($items as $notif)
                                            @php
                                                $titleLower = strtolower($notif->title ?? '');
                                                if (str_contains($titleLower, 'loan')) {
                                                    $iconClass = 'coral';
                                                    $iconFa = 'fa-hand-holding-dollar';
                                                } elseif (str_contains($titleLower, 'saving') || str_contains($titleLower, 'deposit')) {
                                                    $iconClass = 'blue';
                                                    $iconFa = 'fa-piggy-bank';
                                                } elseif ($notif->category === 'announcement') {
                                                    $iconClass = 'blue';
                                                    $iconFa = 'fa-bullhorn';
                                                } elseif ($notif->category === 'spam') {
                                                    $iconClass = 'coral';
                                                    $iconFa = 'fa-triangle-exclamation';
                                                } elseif ($notif->category === 'social') {
                                                    $iconClass = 'blue';
                                                    $iconFa = 'fa-users';
                                                } else {
                                                    $iconClass = 'coral';
                                                    $iconFa = 'fa-bell';
                                                }
                                            @endphp
                                            <div class="notif-row {{ $notif->is_read ? '' : 'unread' }}"
                                                data-category="{{ $notif->category }}"
                                                data-important="{{ $notif->is_important ? '1' : '0' }}"
                                                data-read="{{ $notif->is_read ? '1' : '0' }}" data-id="{{ $notif->id }}">
                                                <div class="notif-icon {{ $iconClass }}">
                                                    <i class="fa {{ $iconFa }}"></i>
                                                </div>
                                                <div class="notif-body">
                                                    <div class="notif-top">
                                                        <div class="notif-title">{{ $notif->title }}</div>
                                                        <div class="notif-time">
                                                            {{ \Carbon\Carbon::parse($notif->created_at)->format('g:i A') }}
                                                        </div>
                                                    </div>
                                                    <div class="notif-desc">{{ $notif->message }}</div>
                                                    <div class="notif-meta-row">
                                                        @if(!$notif->is_read)
                                                            <div class="notif-chip due">Unread</div>
                                                        @endif
                                                        <div class="notif-action">View</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="empty-state" id="emptyState" style="display:none;">
                            <i class="fa fa-bell-slash"></i>
                            <p>No notifications in this category</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab-group .tab');
            const groups = document.querySelectorAll('.notif-group');
            const emptyState = document.getElementById('emptyState');

            function applyFilter(filter) {
                let anyVisible = false;

                groups.forEach(group => {
                    let groupHasVisible = false;
                    group.querySelectorAll('.notif-row').forEach(row => {
                        const category = row.dataset.category;
                        const isImportant = row.dataset.important === '1';
                        const isUnread = row.dataset.read === '0';
                        let show;

                        switch (filter) {
                            case 'all':
                                show = true;
                                break;
                            case 'unread':
                                show = isUnread;
                                break;
                            case 'important':
                                show = isImportant;
                                break;
                            default:
                                show = category === filter;
                        }

                        row.style.display = show ? '' : 'none';
                        if (show) {
                            groupHasVisible = true;
                            anyVisible = true;
                        }
                    });
                    group.style.display = groupHasVisible ? '' : 'none';
                });

                if (emptyState) {
                    emptyState.style.display = anyVisible ? 'none' : 'block';
                }
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    applyFilter(this.dataset.filter);
                });
            });

            // Mark all as read
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function () {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch("{{ route('notifications.markAllRead') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelectorAll('.notif-row').forEach(row => {
                                    row.dataset.read = '1';
                                    row.classList.remove('unread');
                                    const chip = row.querySelector('.notif-chip.due');
                                    if (chip && chip.textContent.trim() === 'Unread') {
                                        chip.remove();
                                    }
                                });

                                const unreadTab = document.querySelector('.tab[data-filter="unread"] .tab-count');
                                if (unreadTab) unreadTab.textContent = '0';

                                const activeTab = document.querySelector('.tab.active');
                                if (activeTab) applyFilter(activeTab.dataset.filter);
                            }
                        })
                        .catch(err => console.error('Mark all as read failed:', err));
                });
            }

            applyFilter('all');
        });
    </script>

</body>

</html>