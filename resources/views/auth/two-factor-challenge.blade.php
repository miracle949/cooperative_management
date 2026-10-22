<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Two-Factor Verification</title>
    <link rel="icon" href="{{ asset('images/websitelogo.png') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('css_folder/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css_folder/loading.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('font-awesome-icon/css/all.min.css') }}">
</head>

<body>

    <div class="container-fluid">

        <div class="loading-screen">
            <div class="load"></div>
        </div>

        {{-- LEFT PANEL --}}
        <div class="form-image">
            <div class="form-sub-image"></div>
        </div>

        {{-- RIGHT PANEL / FORM --}}
        <div class="form-box-parent">
            <div class="form-parent">

                <div class="nav-form">
                    <div class="nav-tag">
                        <a href="{{ route('index') }}">
                            <img src="{{ asset('images/logo2.png') }}" alt="">
                            <h3>KPMPCATS</h3>
                        </a>
                    </div>
                    <h1 class="text-left">Two-Factor <b>Verification</b></h1>
                    <p class="text-left">Enter the 6-digit code from your authenticator app to continue.</p>
                    <div class="form-nav-divider"></div>
                </div>

                <form action="{{ route('2fa.challenge.attempt') }}" method="post">
                    @csrf
                    <div class="form-sub-parent">
                        @if ($errors->any())
                            <div
                                style="background:#fef0f0; border:1.5px solid #f5c6c6; border-radius:4px; padding:0.8rem 1rem; margin: 1rem 0 1rem; font-size:0.85rem; color:#e03131; font-weight:600;">
                                <i class="fa-solid fa-circle-xmark"></i>
                                {{ $errors->first('code') }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div
                                style="background:#f0f9f4; border:1.5px solid #b8e0c9; border-radius:4px; padding:0.8rem 1rem; margin: 1rem 0 1rem; font-size:0.85rem; color:#1d7a3f; font-weight:600;">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="form-input">
                            <label>Authenticator / Recovery Code</label>
                            <div style="position: relative;">
                                <div class="lock" style="position: absolute; left: 16px; top: 36.2%;">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code"
                                    placeholder="000000 or recovery code" class="mt-2" required autofocus>
                                <div class="focus-bar"></div>
                            </div>
                        </div>

                        @if (isset($pendingUser))
                            <p class="form-forgot text-left mt-2"
                                style="font-size:0.8rem; color:#888; margin-bottom: 0.5rem;">
                                Signing in as {{ $pendingUser->first_name }} {{ $pendingUser->last_name }}
                                ({{ $pendingUser->email }}).
                            </p>
                        @endif

                        <div class="mt-4 form-button">
                            <button class="tw:w-full tw:py-1.5 tw:bg-black fw-bold tw:hover:bg-gray-700" type="submit">
                                <span>Verify Code</span>
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>

                        <div class="text-center form-change">
                            <label><a href="{{ route('logout') }}">Return to sign in</a></label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>

</html>