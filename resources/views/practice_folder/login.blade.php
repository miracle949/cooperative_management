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
    <link rel="stylesheet" href="css_folder/login.css?=2">
    <link rel="stylesheet" href="css_folder/loading.css">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
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

                <form action="{{ route("UserLogin") }}" method="post">
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
                                <span>Sign In</span>
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

    </div>

</body>

</html>