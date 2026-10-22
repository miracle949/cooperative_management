<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings</title>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css_folder/settings.css">
    <link rel="stylesheet" href="css_folder/loading.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid p-0 m-0">
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")

            <div class="main-parent">
                <h3>Settings</h3>
                <p>Manage how your account, notifications and security are configured</p>

                <div class="settings-parent">
                    <div class="settings-sidebar">
                        <a href="#notifications" class="active" data-target="notifications">
                            <i class="fa fa-bell"></i>
                            Notifications
                        </a>

                        <a href="#security" data-target="security">
                            <i class="fa fa-shield-halved"></i>
                            Security
                        </a>

                        <a href="#sessions" data-target="sessions">
                            <i class="fa fa-desktop"></i>
                            Sessions
                        </a>

                        <a href="#danger-zone" data-target="danger-zone">
                            <i class="fa fa-triangle-exclamation"></i>
                            Danger Zone
                        </a>
                    </div>

                    <div class="settings-rightbar">

                        <div class="settings-card" id="notifications">
                            <div class="card-header">
                                <h4>Notifications</h4>
                            </div>
                            <div class="card-body">
                                <div class="opt-row">
                                    <div class="opt-icon">
                                        <i class="fa fa-hand-holding-dollar"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Loan Payment Reminders</strong>
                                        <span>Get notified 3 days before a due date</span>
                                    </div>
                                    <div class="switch {{ $settings->loan_reminders ? 'on' : '' }}"
                                         data-field="loan_reminders"></div>
                                </div>

                                <div class="opt-row">
                                    <div class="opt-icon gold">
                                        <i class="fa fa-piggy-bank"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Savings & Share Capital Updates</strong>
                                        <span>Deposit confirmations and dividend posts</span>
                                    </div>
                                    <div class="switch {{ $settings->savings_updates ? 'on' : '' }}"
                                         data-field="savings_updates"></div>
                                </div>

                                <div class="opt-row">
                                    <div class="opt-icon mint">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Email Digest</strong>
                                        <span>Weekly summary of your account activity</span>
                                    </div>
                                    <div class="switch {{ $settings->email_digest ? 'on' : '' }}"
                                         data-field="email_digest"></div>
                                </div>

                                <div class="opt-row">
                                    <div class="opt-icon">
                                        <i class="fa fa-bullhorn"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Cooperative Announcements</strong>
                                        <span>Meetings, elections and general assembly notices</span>
                                    </div>
                                    <div class="switch {{ $settings->announcements ? 'on' : '' }}"
                                         data-field="announcements"></div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-card" id="security">
                            <div class="card-header">
                                <h4>Security</h4>
                            </div>
                            <div class="card-body">
                                <div class="opt-row">
                                    <div class="opt-icon">
                                        <i class="fa fa-key"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Password</strong>
                                        <span>Last changed {{ $passwordChangedAt }}</span>
                                    </div>
                                    <span class="link-btn" id="openPasswordModal">Change</span>
                                </div>

                                <div class="opt-row">
                                    <div class="opt-icon mint">
                                        <i class="fa fa-mobile-screen"></i>
                                    </div>
                                    <div class="opt-text">
                                        <strong>Login alerts</strong>
                                        <span>Notify on new device sign-in</span>
                                    </div>
                                    <div class="switch {{ $settings->login_alerts ? 'on' : '' }}"
                                         data-field="login_alerts"></div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-card" id="sessions">
                            <div class="card-header">
                                <h4>Active Sessions</h4>
                            </div>
                            <div class="card-body">
                                <div class="session-row">
                                    <div class="session-icon">
                                        <i class="fa fa-desktop"></i>
                                    </div>
                                    <div class="session-text">
                                        <strong>Chrome on Windows · Imus City</strong>
                                        <span>127.0.0.1 · Active now</span>
                                    </div>
                                    <span class="session-tag">This device</span>
                                </div>

                                <div class="session-row">
                                    <div class="session-icon">
                                        <i class="fa fa-mobile-screen"></i>
                                    </div>
                                    <div class="session-text">
                                        <strong>KPMPCATS App · Android</strong>
                                        <span>Last active Jul 22, 2026, 8:14 PM</span>
                                    </div>
                                    <span class="link-btn">Sign out</span>
                                </div>

                                <div class="session-row">
                                    <div class="session-icon">
                                        <i class="fa fa-desktop"></i>
                                    </div>
                                    <div class="session-text">
                                        <strong>Safari on macOS · Dasmariñas</strong>
                                        <span>Last active Jul 18, 2026, 6:02 PM</span>
                                    </div>
                                    <span class="link-btn">Sign out</span>
                                </div>
                            </div>
                        </div>

                        <div class="settings-card" id="danger-zone">
                            <div class="card-header">
                                <h4>Danger Zone</h4>
                            </div>
                            <div class="card-body">
                                <div class="danger-row">
                                    <div class="danger-text">
                                        <strong>Download my data</strong>
                                        <span>Export a copy of your membership records as PDF</span>
                                    </div>
                                    <a href="{{ route('settings.export') }}" class="export">
                                        <i class="fa fa-download"></i>
                                        Export
                                    </a>
                                </div>
                                <div class="danger-row">
                                    <div class="danger-text">
                                        <strong>Request account deactivation</strong>
                                        <span>
                                            @if($pendingDeactivation)
                                                Your deactivation request is pending review.
                                            @else
                                                Temporarily disable access pending clearance of balances
                                            @endif
                                        </span>
                                    </div>
                                    <button class="deactivate" id="openDeactivateModal" type="button"
                                            {{ $pendingDeactivation ? 'disabled' : '' }}>
                                        <i class="fa fa-triangle-exclamation"></i>
                                        {{ $pendingDeactivation ? 'Pending' : 'Request' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Password Modal --}}
    <div class="modal-overlay" id="passwordModal">
        <div class="custom-modal-box">
            <div class="custom-modal-head">
                <h4>Change Password</h4>
                <span class="modal-close" data-close="passwordModal">&times;</span>
            </div>
            <div class="custom-modal-body">
                <div class="modal-error" id="passwordError"></div>
                <label>Current Password</label>
                <input type="password" id="current_password" class="modal-input">

                <label>New Password</label>
                <input type="password" id="new_password" class="modal-input">

                <label>Confirm New Password</label>
                <input type="password" id="new_password_confirmation" class="modal-input">
            </div>
            <div class="custom-modal-foot">
                <button class="modal-cancel" data-close="passwordModal">Cancel</button>
                <button class="modal-save" id="savePasswordBtn">Save Password</button>
            </div>
        </div>
    </div>

    {{-- Deactivation Modal --}}
    <div class="modal-overlay" id="deactivateModal">
        <div class="modal-box">
            <div class="modal-head">
                <h4>Request Account Deactivation</h4>
                <span class="modal-close" data-close="deactivateModal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-error" id="deactivateError"></div>
                <p style="font-size:13px;color:var(--muted);margin-bottom:12px;">
                    This will submit a request to our staff. Your account stays active until it's reviewed and any outstanding balances are cleared.
                </p>
                <label>Reason (optional)</label>
                <textarea id="deactivate_reason" class="modal-input" rows="3"></textarea>
            </div>
            <div class="modal-foot">
                <button class="modal-cancel" data-close="deactivateModal">Cancel</button>
                <button class="modal-save deactivate-confirm" id="confirmDeactivateBtn">Submit Request</button>
            </div>
        </div>
    </div>

    <div class="toast" id="settingsToast"></div>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // ── Scrollspy ─────────────────────────────────────────
            const sidebarLinks = document.querySelectorAll(".settings-sidebar a");
            const sections = Array.from(sidebarLinks).map(link => document.getElementById(link.dataset.target));
            const scrollContainer = document.querySelector(".main-parent");

            function setActiveLink(activeLink) {
                sidebarLinks.forEach(link => link.classList.remove("active"));
                activeLink.classList.add("active");
            }

            sidebarLinks.forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    const target = document.getElementById(this.dataset.target);
                    if (target) {
                        scrollContainer.scrollTo({ top: target.offsetTop - 20, behavior: "smooth" });
                    }
                });
            });

            function onScroll() {
                const scrollPos = scrollContainer.scrollTop + 100;
                let currentIndex = 0;
                sections.forEach((section, i) => {
                    if (section && section.offsetTop <= scrollPos) currentIndex = i;
                });
                setActiveLink(sidebarLinks[currentIndex]);
            }
            scrollContainer.addEventListener("scroll", onScroll);
            onScroll();

            // ── Toast helper ─────────────────────────────────────
            function showToast(message, isError = false) {
                const toast = document.getElementById('settingsToast');
                toast.textContent = message;
                toast.classList.toggle('error', isError);
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 3000);
            }

            // ── Toggle switches → save to DB ─────────────────────
            document.querySelectorAll('.switch[data-field]').forEach(sw => {
                sw.addEventListener('click', function () {
                    const field = this.dataset.field;
                    const willBeOn = !this.classList.contains('on');

                    this.classList.toggle('on');
                    this.style.pointerEvents = 'none';

                    fetch("{{ route('settings.toggle') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ field: field, value: willBeOn })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.style.pointerEvents = '';
                        if (!data.success) {
                            this.classList.toggle('on'); // revert
                            showToast('Could not save setting.', true);
                        } else {
                            showToast('Setting saved.');
                        }
                    })
                    .catch(() => {
                        this.style.pointerEvents = '';
                        this.classList.toggle('on'); // revert
                        showToast('Network error. Try again.', true);
                    });
                });
            });

            // ── Modal open/close ──────────────────────────────────
            function openModal(id) { document.getElementById(id).classList.add('show'); }
            function closeModal(id) { document.getElementById(id).classList.remove('show'); }

            document.getElementById('openPasswordModal').addEventListener('click', () => openModal('passwordModal'));
            document.getElementById('openDeactivateModal')?.addEventListener('click', () => openModal('deactivateModal'));

            document.querySelectorAll('[data-close]').forEach(el => {
                el.addEventListener('click', () => closeModal(el.dataset.close));
            });

            // ── Change password ───────────────────────────────────
            document.getElementById('savePasswordBtn').addEventListener('click', function () {
                const errorBox = document.getElementById('passwordError');
                errorBox.style.display = 'none';

                const payload = {
                    current_password: document.getElementById('current_password').value,
                    new_password: document.getElementById('new_password').value,
                    new_password_confirmation: document.getElementById('new_password_confirmation').value,
                };

                fetch("{{ route('settings.changePassword') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        errorBox.textContent = data.message || 'Something went wrong.';
                        errorBox.style.display = 'block';
                        return;
                    }
                    closeModal('passwordModal');
                    showToast(data.message);
                    document.getElementById('current_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('new_password_confirmation').value = '';
                })
                .catch(() => {
                    errorBox.textContent = 'Network error. Try again.';
                    errorBox.style.display = 'block';
                });
            });

            // ── Request deactivation ──────────────────────────────
            document.getElementById('confirmDeactivateBtn')?.addEventListener('click', function () {
                const errorBox = document.getElementById('deactivateError');
                errorBox.style.display = 'none';

                fetch("{{ route('settings.requestDeactivation') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: document.getElementById('deactivate_reason').value })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        errorBox.textContent = data.message || 'Something went wrong.';
                        errorBox.style.display = 'block';
                        return;
                    }
                    closeModal('deactivateModal');
                    showToast(data.message);
                    setTimeout(() => window.location.reload(), 1200);
                })
                .catch(() => {
                    errorBox.textContent = 'Network error. Try again.';
                    errorBox.style.display = 'block';
                });
            });
        });
    </script>

</body>

</html>