<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Forgot Password</title>
    <link rel="icon" href="{{ asset('images/websitelogo.png') }}" type="image/png">

    {{-- Intentionally NOT loading login.css, loading.css or app.js:
         they put an overlay / click handlers on the page that block this form. --}}
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
        .alert-info   { background: #eefaf1; border: 1px solid #bfe5c9; color: #1f7a3d; }
        .alert-danger { background: #fef0f0; border: 1px solid #f5c6c6; color: #c92a2a; }
        .alert ul { margin: 0; padding-left: 18px; }

        label { display: block; margin-bottom: 6px; font-size: 13.5px; font-weight: 600; }

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

        .btn {
            display: block;
            width: 100%;
            margin-top: 16px;
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
        .btn:hover, .btn:focus { background: #131a30; border-color: #131a30; }
        .btn:disabled { opacity: .7; cursor: not-allowed; }

        .back { text-align: center; margin-top: 16px; }
        .back a { font-size: 14px; font-weight: 500; color: #1a56db; }

        @media (max-width: 480px) {
            .auth-wrap { align-items: flex-start; padding: 16px 12px; }
            .auth-card { padding: 28px 20px; border-radius: 16px; margin-top: 8vh; }
            .auth-card h2 { font-size: 22px; }
            .auth-card .sub { font-size: 13px; }
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
    </style>
</head>

<body>
    <div class="auth-wrap">
        <div class="auth-card">
            <h2>Forgot Password</h2>
            <p class="sub">Enter the email linked to your cooperative account and we will send you a reset link.</p>

            @if (session('status'))
                <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                @csrf

                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="field"
                    value="{{ old('email') }}" required autofocus autocomplete="email">

                <button type="submit" class="btn" id="sendBtn">
                    <span class="spinner"></span><span class="btn-text">Send Reset Link</span>
                </button>
            </form>

            <div class="back">
                <a href="{{ route('login') }}">Back to sign in</a>
            </div>
        </div>
    </div>
    
    <script>
        (function () {
            var form = document.getElementById('forgotForm');
            var btn = document.getElementById('sendBtn');
            var text = btn.querySelector('.btn-text');

            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('loading');
                text.textContent = 'Sending...';
            });

            // reset the button if the user comes back with the browser Back button
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    text.textContent = 'Send Reset Link';
                }
            });
        })();
    </script>
</body>

</html>