<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inactive Account</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>
</head>

<body style="background:#F3F4F6;">
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem;">
        <div
            style="background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04); border:1px solid #E2E8E5; max-width:520px; width:100%; overflow:hidden;">
            <div style="padding:2rem 2rem 1.5rem; text-align:center; border-bottom:1px solid #E2E8E5;">
                <div
                    style="width:64px; height:64px; border-radius:50%; background:#FEF3C7; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                    <span style="font-size:28px;">&#9888;&#65039;</span>
                </div>
                <h1 style="font-size:1.35rem; font-weight:700; color:#111827; margin:0 0 .35rem;">Account Inactive</h1>
                <p style="font-size:.9rem; color:#6B7280; margin:0; line-height:1.55;">
                    Hello, {{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}. Your membership is currently
                    inactive.
                </p>
            </div>

            <div style="padding:1.5rem 2rem 2rem;">
                @php
                    $status = strtolower((string) $user->status);
                    $resignation = \App\Models\ResignationRequest_tbl::where('user_id', $user->id)
                        ->where('status', 'approved')->latest()->first();
                    $releaseDate = $resignation?->release_date
                        ? \Carbon\Carbon::parse($resignation->release_date)->format('M d, Y') : null;
                @endphp

                {{-- 1. Alert message --}}
                @if($status === 'reactivation_pending')
                    <div style="background:#FEF3C7; border:1px solid #FDE68A; color:#92400E; border-radius:12px; padding:1rem 1.25rem; font-size:.875rem; line-height:1.6; margin-bottom:1.25rem;">
                        Your reactivation request is <strong>under review</strong>. You will be able to sign back in once an admin
                        approves it. If you haven't received a decision within a few days, please contact the cooperative office.
                    </div>
                @elseif($status === 'awaiting_release')
                    <div style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; border-radius:12px; padding:1rem 1.25rem; font-size:.875rem; line-height:1.6; margin-bottom:1.25rem;">
                        Your resignation has been <strong>approved</strong>. Your share capital will be released after the
                        60-day holding period{{ $releaseDate ? ' (on ' . $releaseDate . ')' : '' }}.
                    </div>
                @elseif($status === 'resigned')
                    <div style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; border-radius:12px; padding:1rem 1.25rem; font-size:.875rem; line-height:1.6; margin-bottom:1.25rem;">
                        Your resignation has been <strong>approved</strong>. Your share capital remains with the cooperative.
                        If you would like to rejoin, you can submit a reactivation request below.
                    </div>
                @else
                    <div style="background:#FEF3C7; border:1px solid #FDE68A; color:#92400E; border-radius:12px; padding:1rem 1.25rem; font-size:.875rem; line-height:1.6; margin-bottom:1.25rem;">
                        You can't access member services while your account is inactive. If you would like to rejoin, you can
                        submit a reactivation request below.
                    </div>
                @endif

                {{-- 2. Membership details --}}
                <div style="background:#F9FAFB; border:1px solid #E5E7EB; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem;">
                    <p style="font-size:.75rem; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:#9CA3AF; margin:0 0 .5rem;">Membership Details</p>
                    <div style="display:flex; justify-content:space-between; font-size:.875rem; padding:.25rem 0;">
                        <span style="color:#6B7280;">Membership Category</span>
                        <span style="font-weight:600; color:#111827;">{{ $otherInfo->membership_category ?? '—' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:.875rem; padding:.25rem 0;">
                        <span style="color:#6B7280;">Date Joined</span>
                        <span style="font-weight:600; color:#111827;">
                            @if($user->created_at)
                                {{ $user->created_at->format('M d, Y') }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>

                {{-- 3. Remaining share capital --}}
                <div style="background:#F9FAFB; border:1px solid #E5E7EB; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
                    <p style="font-size:.75rem; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:#9CA3AF; margin:0 0 .25rem;">Remaining Share Capital</p>
                    @if($scAccount && (float) $scBalance['amount'] > 0)
                        <p style="font-size:1.4rem; font-weight:700; color:#059669; margin:0;">&#8369;{{ number_format((float) $scBalance['amount'], 2) }}</p>
                        <p style="font-size:.75rem; color:#6B7280; margin:.15rem 0 0;">
                            {{ number_format((float) $scBalance['shares']) }}
                            share{{ (float) $scBalance['shares'] == 1 ? '' : 's' }} retained
                        </p>
                    @else
                        <p style="font-size:.9rem; font-weight:500; color:#6B7280; margin:0;">No remaining share capital</p>
                    @endif
                </div>

                {{-- 4. Action button --}}
                @if($status === 'reactivation_pending')
                    <button type="button" disabled
                        style="width:100%; padding:.85rem 1rem; border:none; border-radius:12px; background:#E5E7EB; color:#9CA3AF; font-weight:600; font-size:.95rem; cursor:not-allowed;">
                        Reactivation Under Review
                    </button>
                @elseif($status === 'awaiting_release')
                    <button type="button" disabled
                        style="width:100%; padding:.85rem 1rem; border:none; border-radius:12px; background:#E5E7EB; color:#9CA3AF; font-weight:600; font-size:.95rem; cursor:not-allowed;">
                        Reactivation unavailable until share capital is released
                    </button>
                @else
                    <form method="POST" action="{{ route('member.reactivate') }}">
                        @csrf
                        <button type="submit" data-action="stop-propagation"
                            data-confirm="Submit a reactivation request? An admin will review and approve it."
                            style="width:100%; padding:.85rem 1rem; border:none; border-radius:12px; background:#059669; color:#fff; font-weight:600; font-size:.95rem; cursor:pointer;">
                            Request Reactivation
                        </button>
                    </form>
                    <p style="font-size:.75rem; color:#9CA3AF; text-align:center; margin:1rem 0 0; line-height:1.5;">
                        Submitting a request does not change your share capital, savings, or any financial records.
                    </p>
                @endif

                {{-- 5. Log out --}}
                <p style="margin-top:1.25rem; text-align:center;">
                    <a href="{{ route('logout') }}" style="color:#6B7280; font-size:.85rem; text-decoration:underline;">Log out</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>