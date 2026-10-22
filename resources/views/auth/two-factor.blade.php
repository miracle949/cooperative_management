@extends('layouts.admin')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Two-Factor Authentication</h2>
            <p class="text-sm text-gray-500 mt-1">Protect your privileged account with an authenticator app.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"
            style="background:#f0f9f4; border:1.5px solid #b8e0c9; color:#1d7a3f;">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"
            style="background:#fef0f0; border:1.5px solid #f5c6c6; color:#e03131;">{{ session('error') }}</div>
    @endif

    @if (! empty($recoveryCodes) && is_array($recoveryCodes))
        <div class="card p-6 mb-6" style="border: 2px solid #f59e0b;">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Recovery Codes — Save These Now</h3>
            <p class="text-sm text-gray-500 mb-4">These one-time codes are shown only once. Each code can be used a
                single time, store them somewhere safe. Do not share them.</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($recoveryCodes as $code)
                    <code class="block text-center bg-gray-100 rounded-lg px-3 py-2 text-sm font-mono font-semibold text-gray-900">{{ $code }}</code>
                @endforeach
            </div>
        </div>
    @endif

    @if ($record === null)
        {{-- Not enabled and never enrolled --}}
        <div class="card p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900">2FA is not enabled</h3>
            <p class="text-sm text-gray-500 mt-1 mb-4">Because you hold the {{ ucfirst($user->role) }} role, enabling
                two-factor authentication is strongly recommended.</p>
            <form action="{{ route('2fa.enroll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Enable Two-Factor Authentication</button>
            </form>
        </div>
    @elseif (! $record->enabled)
        {{-- Enrollment in progress: show QR + secret only during this session --}}
        <div class="card p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900">Step 2 — Scan & Confirm</h3>
            <p class="text-sm text-gray-500 mt-1 mb-4">Scan the QR code below with your authenticator app (Google
                Authenticator, Authy, Microsoft Authenticator, etc.), then enter the 6-digit code to finish setup.</p>

            @if ($qrSvg)
                <div class="flex justify-center mb-4">
                    <div class="p-3 border border-gray-200 rounded-2xl">{!! $qrSvg !!}</div>
                </div>
            @endif

            <div class="mb-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Or enter this secret manually:</p>
                <code class="inline-block bg-gray-100 rounded-lg px-4 py-2 text-sm font-mono font-semibold tracking-wider">{{ $secret }}</code>
            </div>

            @if ($errors->has('code'))
                <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"
                    style="background:#fef0f0; border:1.5px solid #f5c6c6; color:#e03131;">{{ $errors->first('code') }}</div>
            @endif

            <form action="{{ route('2fa.confirm') }}" method="POST">
                @csrf
                <label class="text-sm font-medium text-gray-700 block mb-1">Authenticator code</label>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required autofocus
                    class="input mb-4" placeholder="000000" maxlength="16">
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn btn-success">Confirm & Enable</button>
                    <a href="{{ route('logout') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    @else
        {{-- Enabled --}}
        <div class="card p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">2FA is enabled</h3>
                    <p class="text-sm text-gray-500 mt-1">Confirmed
                        {{ $record->confirmed_at ? $record->confirmed_at->format('M d, Y H:i') : '' }}. A fresh code is
                        required each time you sign in.</p>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
        </div>

        <div class="card p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Regenerate Recovery Codes</h3>
            <p class="text-sm text-gray-500 mt-1 mb-4">Replaces your current recovery codes immediately. The previous
                set stops working. You must confirm with a valid authenticator or recovery code.</p>
            @if ($errors->has('code'))
                <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"
                    style="background:#fef0f0; border:1.5px solid #f5c6c6; color:#e03131;">{{ $errors->first('code') }}</div>
            @endif
            <form action="{{ route('2fa.recovery.regenerate') }}" method="POST">
                @csrf
                <label class="text-sm font-medium text-gray-700 block mb-1">Confirm with a code</label>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                    class="input mb-4" placeholder="Authenticator or recovery code" maxlength="64">
                <button type="submit" class="btn btn-warning">Regenerate Recovery Codes</button>
            </form>
        </div>

        <div class="card p-6 mb-6" style="border:1px solid #fecaca;">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Disable Two-Factor Authentication</h3>
            <p class="text-sm text-gray-500 mt-1 mb-4">Disabling requires a currently valid authenticator or recovery
                code. Your stored secret and recovery codes are permanently removed.</p>
            <form action="{{ route('2fa.disable') }}" method="POST">
                @csrf
                <label class="text-sm font-medium text-gray-700 block mb-1">Confirm with a code</label>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                    class="input mb-4" placeholder="Authenticator or recovery code" maxlength="64">
                <button type="submit" class="btn btn-danger">Disable Two-Factor Authentication</button>
            </form>
        </div>
    @endif
</div>
@endsection