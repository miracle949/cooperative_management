<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reset Password</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    <link rel="stylesheet" href="css_folder/login.css">
    <link rel="stylesheet" href="css_folder/loading.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            background: #F7F8FC;
            font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Arial, sans-serif;
            color: #1E2A4A;
        }

        .auth-wrap {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            padding-top: calc(24px + env(safe-area-inset-top, 0px));
            padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
        }

        .auth-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: #fff;
            border: 1px solid #E5EAF3;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 8px 30px rgba(30, 42, 74, .08);
        }

        .auth-card h2 { margin: 0 0 8px; font-size: 26px; font-weight: 600; line-height: 1.25; }
        .auth-card .sub { margin: 0 0 24px; font-size: 14px; line-height: 1.6; color: #6b7a99; }

        .alert {
            font-size: 13.5px;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 18px;
            overflow-wrap: anywhere;
        }
        .alert-danger { background: #fef0f0; border: 1px solid #f5c6c6; color: #c92a2a; }
        .alert ul { margin: 0; padding-left: 18px; }

        label { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; }

        .group { margin-bottom: 16px; }

        .field {
            display: block;
            width: 100%;
            padding: 11px 14px;
            font: inherit;
            font-size: 16px; /* stops iOS zoom on focus */
            color: #1E2A4A;
            background: #fff;
            border: 1.5px solid #E5EAF3;
            border-radius: 10px;
            outline: none;
        }
        .field:focus { border-color: #1E2A4A; box-shadow: 0 0 0 3px rgba(79, 127, 250, .15); }

        .hint { margin: 6px 0 0; font-size: 12.5px; color: #6b7a99; }

        .btn {
            display: block;
            width: 100%;
            margin-top: 24px;
            padding: 12px 16px;
            font: inherit;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #fff;
            background: #1E2A4A;
            border: 1px solid #1E2A4A;
            border-radius: 10px;
            cursor: pointer;
        }
        .btn:focus { background: #131a30; border-color: #131a30; }
        .btn:disabled { opacity: .7; cursor: not-allowed; }
        
        .btn:hover{
            background-color: #131a30;
            color: #ffffff;
        }
        
        .btn .spinner {
            display: none;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            vertical-align: -2px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        .btn.loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 480px) {
            .auth-wrap { align-items: flex-start; padding: 16px 12px; }
            .auth-card { padding: 28px 20px; border-radius: 16px; margin-top: 8vh; }
            .auth-card h2 { font-size: 22px; }
            .auth-card .sub { font-size: 13px; }
        }
    </style>
</head>

<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <h2>Create a New Password</h2>
            <p class="sub">Choose a new password for your account. It must be at least 8 characters.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="field"
                        value="{{ $email }}" required autocomplete="email">
                </div>

                <div class="group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" class="field"
                        required autocomplete="new-password" autofocus>
                    <p class="hint">At least 8 characters.</p>
                </div>

                <div class="group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="field" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn" id="resetBtn">
                    <span class="spinner"></span><span class="btn-text">Reset Password</span>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        (function () {
            var form = document.getElementById('resetForm');
            var btn = document.getElementById('resetBtn');
            var text = btn.querySelector('.btn-text');

            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('loading');
                text.textContent = 'Resetting...';
            });

            // reset the button if the user comes back with the browser Back button
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    text.textContent = 'Reset Password';
                }
            });
        })();
    </script>
</body>

</html>