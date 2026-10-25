<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign In</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">
    <script src="{{ asset('js/csp-events.js') }}"></script>

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/login.css?=7">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style nonce="{{ csp_nonce() }}">
        #switchWait {
            display: none;
            margin: 1.25rem 46px 1.75rem;
            padding: 1.5rem 1.25rem;
            background: #f3f6ff;
            border: 1.5px solid #c9d6ff;
            border-radius: 12px;
            text-align: center;
            box-sizing: border-box;
        }

        #switchWait .sw-icon {
            font-size: 1.8rem;
            color: #1f2a52;
            margin-bottom: .5rem;
        }

        #switchWait .sw-title {
            margin: 0 0 .4rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2a52;
            text-transform: none;
            letter-spacing: normal;
        }

        #switchWait .sw-text {
            margin: 0 0 1.2rem;
            font-size: .85rem;
            line-height: 1.5;
            color: #555;
        }

        #switchWait .sw-btn {
            display: inline-block;
            width: auto;
            padding: .6rem 1.4rem;
            font-size: .85rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        #switchWait .sw-btn-outline {
            background: #fff;
            color: #1f2a52;
            border: 1.5px solid #1f2a52;
        }

        #switchWait .sw-btn-outline:hover {
            background: #1f2a52;
            color: #fff;
        }

        #switchWait .sw-btn-solid {
            display: none;
            background: #1f2a52;
            color: #fff;
            border: 1.5px solid #1f2a52;
        }

        #switchWait .sw-btn-solid:hover {
            background: #2d3a6b;
        }

        @media (max-width: 576px) {
            #switchWait {
                margin: 1rem 20px 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">

        <!-- <div class="bg"></div>
        <div class="grid-lines"></div>
        <div class="stripe"></div>
        <div class="stripe stripe-2"></div>
        <div class="corner corner-tl"></div>
        <div class="corner corner-br"></div>
        <div class="corner corner-tr"></div> -->

        <div class="loading-screen">
            <div class="load"></div>
        </div>

        {{-- LEFT PANEL --}}
        <div class="form-image">

            {{-- <video src="videos/video1.mp4" autoplay muted playsinline class="active"></video>
            <video src="videos/video2.mp4" autoplay muted playsinline></video>
            <video src="videos/video3.mp4" autoplay muted playsinline></video>
            <video src="videos/video4.mp4" autoplay muted playsinline></video>

            <div class="vid-dots">
                <div class="vid-dot active" id="dot0"></div>
                <div class="vid-dot" id="dot1"></div>
                <div class="vid-dot" id="dot2"></div>
                <div class="vid-dot" id="dot3"></div>
            </div> --}}

            <div class="form-sub-image">
                <!-- <div class="form-sub-tag">— MEMBER PORTAL</div>

                <div class="form-sub-body">
                    <h2>Your Financial Future <br> Starts <em>Right Here.</em></h2>
                    <p>Access your loans, savings, and cooperative benefits all in one secure place. Trusted by
                        thousands of members across the Philippines.</p>
                </div> -->

                {{-- <div class="form-image-logo">
                    <a href="{{ route(" index") }}">
                        <img src="images/logo5.jpg" alt="BrgySphere Logo">
                    </a>
                    <div class="form-image-logo-text">
                        <span class="logo-name">KPMPCATS</span>
                        <span class="logo-sub">MEMBERSHIP MANAGEMENT</span>
                    </div>
                </div>

                <div class="form-image-body">
                    <h2>Your Financial Future Starts <b>Right Here.</b></h2>
                    <p>Access your membership, monitor your loans & savings, and stay informed with the latest
                        announcements
                        — all in one place.</p>

                    <div class="form-image-features">

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div class="feature-text">
                                <span>Restricted Access - Authorized personnel only</span>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div class="feature-text">
                                <span>Member Records - Manage member information</span>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="feature-text">
                                <span>Loans & Savings - Track loans and savings</span>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div class="feature-text">
                                <span>Announcements - Stay updated with notices</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-image-footer">
                    <p>© 2026 Cooperative Management System ♥</p>
                </div> --}}
            </div>

        </div>

        <!-- <div class="form-sub-image">
            <div class="form-image-logo">
                <a href="{{ route("index") }}">
                    <img src="images/logo5.jpg" alt="BrgySphere Logo">
                </a>
                <div class="form-image-logo-text">
                    <span class="logo-name">KPMPCATS</span>
                    <span class="logo-sub">MEMBERSHIP MANAGEMENT</span>
                </div>
            </div>

            <div class="form-image-body">
                <h2>Welcome to your <b>Cooperative</b> Home</h2>
                <p>Access your membership, monitor your loans & savings, and stay informed with the latest announcements
                    — all in one place.</p>

                <div class="form-image-features">

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="feature-text">
                            <span>Restricted Access - Authorized personnel only</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div class="feature-text">
                            <span>Member Records - Manage member information</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="feature-text">
                            <span>Loans & Savings - Track loans and savings</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div class="feature-text">
                            <span>Announcements - Stay updated with notices</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="form-image-footer">
                <p>© 2026 Cooperative Management System ♥</p>
            </div>
        </div> -->

        {{-- RIGHT PANEL / FORM --}}
        <div class="form-box-parent">
            <div class="form-parent">

                <!-- <div class="nav-logo">
                    <img src="images/logo2.png" alt="">

                    <h3>KPMPCATS</h3>
                </div> -->

                <div class="nav-form">
                    <!-- <div class="nav-tag">Member Access</div> -->
                    <div class="nav-tag">
                        <a href="{{ route("index") }}">
                            <img src="images/logo2.png" alt="">
                            <h3>KPMPCATS</h3>
                        </a>
                    </div>
                    <h1 class="text-left">Sign in to your <b>account</b></h1>
                    <p class="text-left">Enter your credentials to access the member portal.</p>
                    <div class="form-nav-divider"></div>
                </div>

                <div id="switchWait">
                    <i class="fa-solid fa-mobile-screen-button sw-icon"></i>
                    <h3 id="switchTitle" class="sw-title">Waiting for approval…</h3>
                    <p id="switchText" class="sw-text">
                        This account is already open on another device or browser. We asked it to approve this login.
                    </p>
                    <button type="button" id="switchCancel" class="sw-btn sw-btn-outline">Cancel request</button>
                    <button type="button" id="switchBack" class="sw-btn sw-btn-solid">Back to sign in</button>
                </div>

                <form action="{{ route("UserLogin") }}" method="post" id="loginForm">
                    @csrf
                    <div class="form-sub-parent">
                        @if ($errors->any())
                            <div
                                style="background:#fef0f0; border:1.5px solid #f5c6c6; border-radius:4px; padding:0.8rem 1rem; margin: 1rem 0 1rem; font-size:0.85rem; color:#e03131; font-weight:600;">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('login') }}
                            </div>
                        @endif

                        <div class="form-input">
                            <label>Email Address</label>
                            <div style="position: relative;">
                                <div class="envelope" style="position: absolute; left: 16px; top: 36.2%;">
                                    <i class="fa fa-envelope"></i>
                                </div>
                                <input type="text" name="login" value="{{ old('login') }}"
                                    placeholder="Enter your email" class="mt-2" required>
                                <div class="focus-bar"></div>
                            </div>
                        </div>

                        <div class="form-input">
                            <label>Password</label>
                            <div style="position: relative;">
                                <div class="lock" style="position: absolute; left: 16px; top: 36.2%;">
                                    <i class="fa fa-lock"></i>
                                </div>
                                <input type="password" name="password" id="login-password"
                                    placeholder="Enter your password" class="mt-2" style="padding-right: 40px;"
                                    required>
                                <div class="focus-bar"></div>
                                <span data-action="toggleLoginPassword"
                                    style="position: absolute; right: 12px; top: 60%; transform: translateY(-50%); cursor: pointer; color: #888;">
                                    <i class="fa fa-eye" id="eye-login"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 tw:flex tw:justify-end form-forgot">
                            <a href="{{ route('password.request') }}">Forgot Password</a>
                        </div>

                        <div class="mt-4 form-button">
                            <button class="tw:w-full tw:py-1.5 tw:bg-black fw-bold tw:hover:bg-gray-700" id="login"
                                type="submit">
                                <span class="spinner"></span>
                                <span class="btn-text">Sign In</span>
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>

                        <div class="text-center form-change">
                            <label>Don't have an account? <a href="{{ route("RegisterPage") }}">Apply Membership <i
                                        class="fa fa-arrow-right"></i></a></label>
                        </div>

                        {{-- <div class="form-divider"></div> --}}

                        {{-- <div class="other-spec">
                            <div class="spec">
                                <span>12k+</span>
                                <p>Members</p>
                            </div>
                            <div class="spec">
                                <span>99.9%</span>
                                <p>Uptime</p>
                            </div>
                            <div class="spec">
                                <span>256-bit</span>
                                <p>Encryption</p>
                            </div>
                        </div> --}}

                    </div>
                </form>
            </div>
        </div>

        <script nonce="{{ csp_nonce() }}">
            // const vids = document.querySelectorAll('.form-image video');
            // const dots = document.querySelectorAll('.vid-dot');
            // let current = 0;

            // function switchTo(index) {
            //     vids[current].classList.remove('active');
            //     dots[current].classList.remove('active');
            //     current = index;
            //     vids[current].classList.add('active');
            //     dots[current].classList.add('active');
            //     vids[current].currentTime = 0; // rewind before playing
            //     vids[current].play();
            // }

            // vids.forEach((v, i) => {
            //     v.addEventListener('ended', () => {
            //         switchTo((i + 1) % vids.length); // loops back to 0 after video 4
            //     });
            // });

            // vids[0].play();

            function togglePw() {
                const pw = document.getElementById('pw');
                pw.type = pw.type === 'password' ? 'text' : 'password';
            }
        </script>

        <script nonce="{{ csp_nonce() }}">
            function toggleLoginPassword() {
                const input = document.getElementById('login-password');
                const icon = document.getElementById('eye-login');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }
        </script>

        <script nonce="{{ csp_nonce() }}">
            (function () {
                var form = document.getElementById('loginForm');
                var btn = document.getElementById('login');
                var text = btn.querySelector('.btn-text');

                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    btn.classList.add('loading');
                    text.textContent = 'Signing in...';
                });

                // reset the button if the user comes back with the browser Back button
                window.addEventListener('pageshow', function (e) {
                    if (e.persisted) {
                        btn.disabled = false;
                        btn.classList.remove('loading');
                        text.textContent = 'Sign In';
                    }
                });
            })();
        </script>

        <script nonce="{{ csp_nonce() }}">
            (function () {
                var STATUS_URL = "{{ route('login.switch.status') }}";
                var COMPLETE_URL = "{{ route('login.switch.complete') }}";
                var CANCEL_URL = "{{ route('login.switch.cancel') }}";

                var form = document.getElementById('loginForm');
                var panel = document.getElementById('switchWait');
                var title = document.getElementById('switchTitle');
                var text = document.getElementById('switchText');
                var cancel = document.getElementById('switchCancel');
                var back = document.getElementById('switchBack');
                var csrf = form.querySelector('input[name="_token"]').value;
                var timer = null;

                function stop() { if (timer) { clearInterval(timer); timer = null; } }

                function showWaiting() {
                    form.style.display = 'none';
                    panel.style.display = 'block';
                    cancel.style.display = 'inline-block';
                    back.style.display = 'none';
                    title.textContent = 'Waiting for approval…';
                    text.textContent = 'This account is already open on another device or browser. We asked it to approve this login.';
                    if (!timer) timer = setInterval(check, 2500);
                }

                function showResult(t, msg) {
                    stop();
                    form.style.display = 'none';
                    panel.style.display = 'block';
                    cancel.style.display = 'none';
                    back.style.display = 'inline-block';
                    title.textContent = t;
                    text.textContent = msg;
                }

                function showForm() {
                    stop();
                    panel.style.display = 'none';
                    form.style.display = '';
                    cancel.style.display = '';
                    back.style.display = '';
                    var btn = document.getElementById('login');
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    btn.querySelector('.btn-text').textContent = 'Sign In';
                }

                function post(url) {
                    return fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                }

                function complete() {
                    title.textContent = 'Approved! Signing you in…';
                    post(COMPLETE_URL).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.redirect) { window.location.href = d.redirect; }
                        else { showResult('Could not sign in', 'Please try signing in again.'); }
                    }).catch(function () {
                        showResult('Could not sign in', 'Please try signing in again.');
                    });
                }

                function check() {
                    fetch(STATUS_URL, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                        .then(function (r) {
                            if (!r.ok) throw new Error('bad status');
                            return r.json();
                        })
                        .then(function (d) {
                            if (d.state === 'pending') { showWaiting(); }
                            else if (d.state === 'approved') { stop(); complete(); }
                            else if (d.state === 'denied') { showResult('Request denied', 'The account owner did not allow this login.'); }
                            else if (d.state === 'expired') { showResult('No response', 'Nobody approved in time. If the other device is no longer available, the account unlocks automatically after 5 minutes of inactivity.'); }
                            // state === 'none' → do nothing, keep the form visible
                        })
                        .catch(function () { /* keep the form visible */ });
                }

                cancel.addEventListener('click', function () {
                    stop();
                    post(CANCEL_URL).finally(showForm);
                });
                back.addEventListener('click', showForm);

                check(); // on page load: are we already waiting?
            })();
        </script>

    </div>

</body>

</html>