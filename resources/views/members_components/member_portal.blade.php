<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Homepage</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/homepage.css?=14">
    <link rel="stylesheet" href="css_folder/loading.css?=11">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style>
        /* ─── Toast ─────────────────────────────────────────── */
        .toast-message {
            position: fixed;
            right: 20px;
            top: 20px;
            padding: 1rem 1.5rem;
            color: var(--teal);
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            border: 1px solid #E2E8E5;
            width: 250px;
            display: flex;
            align-items: center;
            border-radius: 10px;
            gap: 1rem;
            z-index: 99999;
            overflow: hidden;
            animation: toastSlideIn .4s cubic-bezier(.22, 1, .36, 1) forwards;
        }

        .toast-message.hide {
            animation: toastFadeOut .4s ease-in forwards;
        }

        .toast-message::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            background-color: var(--teal);
            height: 100%;
            width: 5px;
        }

        .toast-message p {
            margin: 0;
            font-weight: 600;
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(60px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toastFadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(60px);
            }
        }

        /* ─── Skeleton overlay ───────────────────────────────── */
        /* Lives inside .main-parent, so it follows the real navbar,
           sidebar (expanded / collapsed / hidden) and mobile bottom nav. */
        .main-parent.sk-loading {
            position: relative;
            overflow: hidden !important;
        }

        #skeleton-overlay {
            position: absolute;
            inset: 0;
            padding: inherit;
            /* same padding as .main-parent at every breakpoint */
            background: #FBFBFD;
            /* same bg as .main-parent */
            z-index: 50;
            overflow: hidden;
            transition: opacity .45s ease;
        }

        #skeleton-overlay.sk-hide {
            opacity: 0;
            pointer-events: none;
        }

        @keyframes skshimmer {
            0% {
                background-position: -700px 0;
            }

            100% {
                background-position: 700px 0;
            }
        }

        .sk {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 700px 100%;
            animation: skshimmer 1.4s infinite linear;
            border-radius: 6px;
        }

        #page-content {
            transition: opacity .4s ease .1s;
        }

        #page-content.sk-ready {
            opacity: 1 !important;
        }

        /* Greeting (mirrors main h2) */
        .sk-title {
            min-height: 30px;
            display: flex;
            align-items: center;
        }

        .sk-title .sk {
            width: 320px;
            max-width: 80%;
            height: 22px;
        }

        /* Summary cards (mirrors .card-parent / .card-box) */
        .sk-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1.5rem 0;
        }

        .sk-cardbox {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        }

        .sk-cardbox-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sk-label {
            width: 45%;
            height: 14px;
        }

        .sk-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
        }

        .sk-value {
            width: 60%;
            height: 29px;
            margin-top: 10px;
        }

        .sk-sub {
            width: 50%;
            height: 15px;
            margin-top: 7px;
        }

        /* Panels (mirrors .card-box-summary and .parent-panel) */
        .sk-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .sk-bottom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .sk-panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .sk-panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .sk-panel-head.sk-no-border {
            border-bottom: none;
            padding-bottom: 0;
        }

        .sk-head-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .sk-h {
            width: 45%;
            height: 18px;
        }

        .sk-p {
            width: 75%;
            height: 14px;
        }

        .sk-link {
            width: 48px;
            height: 14px;
            flex-shrink: 0;
        }

        .sk-pills {
            display: flex;
            gap: 8px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }

        .sk-pills .sk {
            height: 32px;
            border-radius: 10px;
        }

        .sk-tabs {
            display: flex;
            border-bottom: 1px solid var(--line);
        }

        .sk-tabs>div {
            flex: 1;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sk-tabs .sk {
            width: 50%;
            height: 14px;
        }

        .sk-panel-body {
            padding: 0 20px 12px;
            height: 300px;
            overflow: hidden;
        }

        .sk-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1.5px solid var(--border);
        }

        .sk-row-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            flex-shrink: 0;
        }

        .sk-row-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .sk-row-info .sk:first-child {
            width: 60%;
            height: 13px;
        }

        .sk-row-info .sk:last-child {
            width: 40%;
            height: 11px;
        }

        .sk-row-amt {
            width: 70px;
            height: 14px;
            flex-shrink: 0;
        }

        /* Same breakpoints as homepage.css */
        @media (max-width: 965px) {
            .sk-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .sk-summary {
                display: flex;
                flex-direction: column;
            }

            .sk-bottom {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .sk-title {
                padding: 3rem 1.5rem 0;
            }

            .sk-cards {
                padding: 2rem 1.5rem 2.5rem;
            }

            .sk-summary {
                padding: 0 1.5rem;
            }

            .sk-bottom {
                padding: 0 2rem;
            }
        }

        @media (max-width: 460px) {
            .sk-icon {
                display: none;
            }
        }

        @media (max-width: 425px) {
            .sk-value {
                height: 27px;
                margin-top: 15px;
            }

            .sk-sub {
                margin-top: 12px;
            }
        }
    </style>
</head>

<body>

    {{-- Shown only when the screen is narrower than 1200px (see homepage.css) --}}

    {{-- Sidebar, navbar and bottom nav always visible --}}
    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            <div class="main-parent {{ session('just_logged_in') ? 'sk-loading' : '' }}">

                {{-- ═══════════════════════════════════════════════
                SKELETON OVERLAY — only shown right after login
                Mirrors the real dashboard layout below
                ═══════════════════════════════════════════════ --}}
                @if (session('just_logged_in'))
                    <div id="skeleton-overlay" aria-hidden="true">

                        {{-- Greeting --}}
                        <div class="sk-title">
                            <div class="sk"></div>
                        </div>

                        {{-- 6 summary cards --}}
                        <div class="sk-cards">
                            @for ($i = 0; $i < 6; $i++)
                                <div class="sk-cardbox">
                                    <div class="sk-cardbox-head">
                                        <div class="sk sk-label"></div>
                                        <div class="sk sk-icon"></div>
                                    </div>
                                    <div class="sk sk-value"></div>
                                    <div class="sk sk-sub"></div>
                                </div>
                            @endfor
                        </div>

                        {{-- Recent Transactions + Community --}}
                        <div class="sk-summary">
                            <div class="sk-panel">
                                <div class="sk-panel-head sk-no-border">
                                    <div class="sk-head-text">
                                        <div class="sk sk-h"></div>
                                        <div class="sk sk-p"></div>
                                    </div>
                                    <div class="sk sk-link"></div>
                                </div>
                                <div class="sk-pills">
                                    <div class="sk" style="width:48px;"></div>
                                    <div class="sk" style="width:72px;"></div>
                                    <div class="sk" style="width:84px;"></div>
                                    <div class="sk" style="width:112px;"></div>
                                </div>
                                <div class="sk-panel-body">
                                    @for ($i = 0; $i < 5; $i++)
                                        <div class="sk-row">
                                            <div class="sk sk-row-icon"></div>
                                            <div class="sk-row-info">
                                                <div class="sk"></div>
                                                <div class="sk"></div>
                                            </div>
                                            <div class="sk sk-row-amt"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="sk-panel">
                                <div class="sk-panel-head">
                                    <div class="sk-head-text">
                                        <div class="sk sk-h"></div>
                                        <div class="sk sk-p"></div>
                                    </div>
                                </div>
                                <div class="sk-tabs">
                                    <div>
                                        <div class="sk"></div>
                                    </div>
                                    <div>
                                        <div class="sk"></div>
                                    </div>
                                </div>
                                <div class="sk-panel-body">
                                    @for ($i = 0; $i < 5; $i++)
                                        <div class="sk-row">
                                            <div class="sk sk-row-icon"></div>
                                            <div class="sk-row-info">
                                                <div class="sk"></div>
                                                <div class="sk"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Dividends + Patronage Refund --}}
                        <div class="sk-bottom">
                            @for ($p = 0; $p < 2; $p++)
                                <div class="sk-panel">
                                    <div class="sk-panel-head">
                                        <div class="sk-head-text">
                                            <div class="sk sk-h"></div>
                                            <div class="sk sk-p"></div>
                                        </div>
                                    </div>
                                    <div class="sk-panel-body">
                                        @for ($i = 0; $i < 4; $i++)
                                            <div class="sk-row">
                                                <div class="sk sk-row-icon"></div>
                                                <div class="sk-row-info">
                                                    <div class="sk"></div>
                                                    <div class="sk"></div>
                                                </div>
                                                <div class="sk sk-row-amt"></div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endfor
                        </div>

                    </div>
                @endif

                <div id="page-content" @if(session('just_logged_in')) style="opacity:0;" @endif>
                    <main>

                        <h2>Good day, {{ $username }}! <span>Here's your overview</span></h2>

                        {{-- Only Allied Workers see this (and only on screens 1200px and below — see homepage.css) --}}
                        @if(auth()->user()?->isAlliedWorker())
                            <div class="desktop-view-banner" role="note">
                                <div class="dvb-window">
                                    <div class="dvb-dots">
                                        <span></span><span></span><span></span>
                                    </div>
                                    <svg class="dvb-smile" viewBox="0 0 120 80" aria-hidden="true">
                                        <line x1="28" y1="22" x2="40" y2="40" />
                                        <line x1="42" y1="14" x2="54" y2="32" />
                                        <path d="M18 68 Q80 70 104 12" />
                                    </svg>
                                </div>
                                <div class="dvb-text">
                                    <h4>Enable desktop view to access the full webpage experience and manage the admin
                                        panel.</h4>
                                    <p>Some sections may look cramped on smaller screens. Switch your browser to desktop
                                        view or use a wider screen.</p>
                                </div>
                            </div>
                        @endif

                        <div class="card-parent">

                            {{-- Savings Balance --}}
                            <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}" @if($seminarsUnlocked)
                            data-action="navigate" data-url="{{ route('Financial') }}" @else
                                data-action="show-locked-toast" @endif>
                                <div class="card-header">
                                    <p>Savings Balance</p>
                                    <div class="update">
                                        @if(!$seminarsUnlocked)
                                            <i class="fa-solid fa-lock"></i>
                                        @else
                                            <i class="fa fa-layer-group"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>₱ {{ number_format($savingsAccount->balance ?? 0, 2) }}</h5>
                                    <p>{{ $netSavingsThisMonth >= 0 ? '↑ +' : '↓ -' }}₱{{ number_format(abs($netSavingsThisMonth), 2) }}
                                        this month</p>
                                </div>
                            </div>

                            {{-- Share Capital --}}
                            <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}" @if($seminarsUnlocked)
                                data-action="navigate" data-url="{{ route('Financial', ['tab' => 'share_capital']) }}"
                            @else data-action="show-locked-toast" @endif>
                                <div class="card-header">
                                    <p>Share Capital</p>
                                    <div class="update">
                                        @if(!$seminarsUnlocked)
                                            <i class="fa-solid fa-lock"></i>
                                        @else
                                            <i class="fa fa-coins"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>₱ {{ number_format($shareCapitalBalance ?? 0, 2) }}</h5>
                                    <p>{{ $shareCapitalShares ?? 0 }} shares</p>
                                </div>
                            </div>

                            {{-- Seminars (unchanged, always unlocked) --}}
                            <div class="card-box" data-action="show-modal" data-target="seminarsModal">
                                <div class="card-header">
                                    <p>Seminars</p>
                                    <div class="update"><i class="fa fa-graduation-cap"></i></div>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $seminarsCompletedCount }} Attended</h5>
                                    <p>
                                        @if ($seminarsCompletedCount === $seminarsTotalCount)
                                            All seminars attended
                                        @else
                                            {{ $seminarsTotalCount - $seminarsCompletedCount }} remaining
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Active Loans --}}
                            <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}" @if($seminarsUnlocked)
                            data-action="navigate" data-url="{{ route('LoanStatus') }}" @else
                                data-action="show-locked-toast" @endif>
                                <div class="card-header">
                                    <p>Active Loans</p>
                                    <div class="update">
                                        @if(!$seminarsUnlocked)
                                            <i class="fa-solid fa-lock"></i>
                                        @else
                                            <i class="fa fa-piggy-bank"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $activeLoansCount }} Loan(s)</h5>
                                    <p>{{ $nextDueDisplay ? "Next due {$nextDueDisplay}" : 'No upcoming dues' }}</p>
                                </div>
                            </div>

                            {{-- Upcoming Dues --}}
                            <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}" @if($seminarsUnlocked)
                            data-action="show-modal" data-target="upcomingDuesModal" @else
                                data-action="show-locked-toast" @endif>
                                <div class="card-header">
                                    <p>Upcoming Dues</p>
                                    <div class="update">
                                        @if(!$seminarsUnlocked)
                                            <i class="fa-solid fa-lock"></i>
                                        @else
                                            <i class="fa fa-calendar-day"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $upcomingDues->count() }} Due(s)</h5>
                                    <p>{{ $nextDueDisplay ? "Next due {$nextDueDisplay}" : 'No upcoming dues' }}</p>
                                </div>
                            </div>

                            {{-- Overdue Loans --}}
                            <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}" @if($seminarsUnlocked)
                            data-action="show-modal" data-target="overdueLoansModal" @else
                                data-action="show-locked-toast" @endif>
                                <div class="card-header">
                                    <p>Overdue Loans</p>
                                    <div class="update">
                                        @if(!$seminarsUnlocked)
                                            <i class="fa-solid fa-lock"></i>
                                        @else
                                            <i class="fa fa-triangle-exclamation"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $overdueLoansDisplay->count() }} Loan(s)</h5>
                                    <p>{{ $overdueLoansDisplay->isNotEmpty() ? $overdueLoansDisplay->first()['subtitle'] : 'No overdue loans' }}
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="card-parent-mobile">

                            <div class="card-1">
                                {{-- Savings Balance --}}
                                <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}"
                                    @if($seminarsUnlocked) data-action="navigate" data-url="{{ route('Financial') }}"
                                    @else data-action="show-locked-toast" @endif>
                                    <div class="card-header">
                                        <p>Savings Balance</p>
                                        <div class="update">
                                            @if(!$seminarsUnlocked)
                                                <i class="fa-solid fa-lock"></i>
                                            @else
                                                <i class="fa fa-layer-group"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5>₱ {{ number_format($savingsAccount->balance ?? 0, 2) }}</h5>
                                        <p>{{ $netSavingsThisMonth >= 0 ? '↑ +' : '↓ -' }}₱{{ number_format(abs($netSavingsThisMonth), 2) }}
                                            this month</p>
                                    </div>
                                </div>

                                {{-- Share Capital --}}
                                <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}"
                                    @if($seminarsUnlocked) data-action="navigate"
                                    data-url="{{ route('Financial', ['tab' => 'share_capital']) }}" @else
                                    data-action="show-locked-toast" @endif>
                                    <div class="card-header">
                                        <p>Share Capital</p>
                                        <div class="update">
                                            @if(!$seminarsUnlocked)
                                                <i class="fa-solid fa-lock"></i>
                                            @else
                                                <i class="fa fa-coins"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5>₱ {{ number_format($shareCapitalBalance ?? 0, 2) }}</h5>
                                        <p>{{ $shareCapitalShares ?? 0 }} shares</p>
                                    </div>
                                </div>

                                {{-- Seminars --}}
                                <div class="card-box" data-action="show-modal" data-target="seminarsModal">
                                    <div class="card-header">
                                        <p>Seminars</p>
                                        <div class="update"><i class="fa fa-graduation-cap"></i></div>
                                    </div>
                                    <div class="card-body">
                                        <h5>{{ $seminarsCompletedCount }} Attended</h5>
                                        <p>
                                            @if ($seminarsCompletedCount === $seminarsTotalCount)
                                                All seminars attended
                                            @else
                                                {{ $seminarsTotalCount - $seminarsCompletedCount }} remaining
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-2">
                                {{-- Active Loans --}}
                                <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}"
                                    @if($seminarsUnlocked) data-action="navigate" data-url="{{ route('LoanStatus') }}"
                                    @else data-action="show-locked-toast" @endif>
                                    <div class="card-header">
                                        <p>Active Loans</p>
                                        <div class="update">
                                            @if(!$seminarsUnlocked)
                                                <i class="fa-solid fa-lock"></i>
                                            @else
                                                <i class="fa fa-piggy-bank"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5>{{ $activeLoansCount }} Loan(s)</h5>
                                        <p>{{ $nextDueDisplay ? "Next due {$nextDueDisplay}" : 'No upcoming dues' }}</p>
                                    </div>
                                </div>

                                {{-- Upcoming Dues --}}
                                <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}"
                                    @if($seminarsUnlocked) data-action="show-modal" data-target="upcomingDuesModal"
                                    @else data-action="show-locked-toast" @endif>
                                    <div class="card-header">
                                        <p>Upcoming Dues</p>
                                        <div class="update">
                                            @if(!$seminarsUnlocked)
                                                <i class="fa-solid fa-lock"></i>
                                            @else
                                                <i class="fa fa-calendar-day"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5>{{ $upcomingDues->count() }} Due(s)</h5>
                                        <p>{{ $nextDueDisplay ? "Next due {$nextDueDisplay}" : 'No upcoming dues' }}</p>
                                    </div>
                                </div>

                                {{-- Overdue Loans --}}
                                <div class="card-box {{ $seminarsUnlocked ? '' : 'card-locked' }}"
                                    @if($seminarsUnlocked) data-action="show-modal" data-target="overdueLoansModal"
                                    @else data-action="show-locked-toast" @endif>
                                    <div class="card-header">
                                        <p>Overdue Loans</p>
                                        <div class="update">
                                            @if(!$seminarsUnlocked)
                                                <i class="fa-solid fa-lock"></i>
                                            @else
                                                <i class="fa fa-triangle-exclamation"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5>{{ $overdueLoansDisplay->count() }} Loan(s)</h5>
                                        <p>{{ $overdueLoansDisplay->isNotEmpty() ? $overdueLoansDisplay->first()['subtitle'] : 'No overdue loans' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>

                    <!-- <div class="ask-box">
                        <div class="text-box">
                            <h4>Need financial assistance?</h4>
                            <p>Apply for a lending today — fast processing, exclusive member rates.</p>
                        </div>
                        <div class="link-box">
                            <a href="{{ route('LoanApplication') }}">
                                <i class="fa fa-plus"></i>
                                <span>Apply for a Loan</span>
                            </a>
                        </div>
                    </div>

                    <h3>Quick Summary</h3> -->

                    <section>
                        <div class="card-box-summary">
                            {{-- <div class="loan-overview">
                                <div class="loan-header">
                                    <div>
                                        <h4>Overdue Loans</h4>
                                        <p>Loans past due across all accounts</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('LoanStatus') }}">View all</a>
                                    </div>
                                </div>
                                <div class="recent-body">
                                    <div class="tx-list">
                                        @forelse ($overdueLoansDisplay as $overdue)
                                        <div class="tx-list-item">
                                            <div class="tx-icon {{ $overdue['icon'] }}"><i
                                                    class="fa-solid {{ $overdue['icon_fa'] }}"></i></div>
                                            <div class="tx-list-info">
                                                <strong>{{ $overdue['title'] }}</strong>
                                                <span>{{ $overdue['date_display'] }} · {{ $overdue['subtitle'] }}</span>
                                            </div>
                                            <div class="tx-list-amt down">
                                                +₱{{ number_format($overdue['amount'], 2) }}
                                            </div>
                                        </div>
                                        @empty
                                        <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                            <i class="fa fa-circle-check"
                                                style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                            No overdue loans.
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div> --}}

                            <div class="recent-transaction">
                                <div class="recent-header">
                                    <div class="recent-header-text">
                                        <h3>Recent Transactions</h3>
                                        <p>Latest account activity across all accounts</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('transactions') }}">View all</a>
                                    </div>
                                </div>

                                <div class="button-parent" style="">
                                    <button type="button" class="tx-tab-btn active" data-tx-filter="all"
                                        data-action="filterRecentTx" data-arg='["all","|el|"]'>All</button>
                                    <button type="button" class="tx-tab-btn" data-tx-filter="loans"
                                        data-action="filterRecentTx" data-arg='["loans","|el|"]'>Loans</button>
                                    <button type="button" class="tx-tab-btn" data-tx-filter="savings"
                                        data-action="filterRecentTx" data-arg='["savings","|el|"]'>Savings</button>
                                    <button type="button" class="tx-tab-btn" data-tx-filter="share_capital"
                                        data-action="filterRecentTx" data-arg='["share_capital","|el|"]'>Share
                                        Capital</button>
                                </div>

                                <div class="recent-body">
                                    <div class="tx-list" id="recentTxList">
                                        @forelse ($recentTransactions as $tx)
                                            <div class="tx-list-item" data-category="{{ $tx['category'] }}">
                                                <div class="tx-icon {{ $tx['icon'] }}"><i
                                                        class="fa-solid {{ $tx['icon_fa'] }}"></i></div>
                                                <div class="tx-list-info">
                                                    <strong>{{ $tx['title'] }}
                                                    </strong>
                                                    <span>{{ $tx['date_display'] }} · {{ $tx['time_display'] }}</span>
                                                </div>
                                                <div
                                                    style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                                    @if($tx['show_amount'] ?? true)
                                                        <div class="tx-list-amt {{ $tx['amount'] >= 0 ? 'up' : 'down' }}">
                                                            {{ $tx['amount'] >= 0 ? '+' : '-' }}₱{{ number_format(abs($tx['amount']), 2) }}
                                                        </div>
                                                    @endif
                                                    {{-- @if(($tx['status_class'] ?? '') === 'pending')
                                                    <span style="" class="pending-transaction">
                                                        Pending
                                                    </span>
                                                    @endif --}}
                                                </div>
                                            </div>
                                        @empty
                                            <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                                <i class="fa fa-inbox"
                                                    style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                                No transactions found.
                                            </div>
                                        @endforelse
                                    </div>
                                    <div id="recentTxEmpty"
                                        style="display:none; text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                        <i class="fa fa-inbox"
                                            style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                        No transactions in this category.
                                    </div>
                                </div>
                            </div>


                            <div class="panel graph announcements">
                                <div class="panel-head">
                                    <div>
                                        <h3>Community</h3>
                                        <p>Announcements and polls</p>
                                    </div>
                                </div>
                                <div style="display:flex; border-bottom: 1px solid var(--line);">
                                    <button type="button" data-action="switchMemberCommunityTab"
                                        data-arg='["announcements"]' id="member-community-tab-announcements"
                                        style="flex:1; padding:10px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; border-bottom:2px solid var(--teal); color:var(--teal);">
                                        <i class="fa-solid fa-bullhorn" style="margin-right:4px;"></i> Announcements
                                    </button>
                                    <button type="button" data-action="switchMemberCommunityTab" data-arg='["polls"]'
                                        id="member-community-tab-polls"
                                        style="flex:1; padding:10px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; color:var(--muted);">
                                        <i class="fa-solid fa-chart-bar" style="margin-right:4px;"></i> Polls
                                    </button>
                                </div>

                                {{-- Announcements Tab --}}
                                <div id="member-community-content-announcements" class="panel-body"
                                    style="height:auto; max-height:400px;">
                                    <div class="tx-list" id="announcementsList">
                                        @forelse ($announcements as $announcement)
                                            <div class="tx-list-item member-announcement-item"
                                                style="flex-direction:column; align-items:stretch; padding:12px 0; border-bottom:1px solid var(--line);">
                                                <div style="display:flex; align-items:center; gap:10px; cursor:pointer;"
                                                    data-action="toggleMemberAnnouncement"
                                                    data-arg='[{{ $announcement->id }}]'>
                                                    <div class="tx-icon gold"><i class="fa-solid fa-bullhorn"></i></div>
                                                    <div class="tx-list-info" style="flex:1;">
                                                        <strong>{{ $announcement->title }}</strong>
                                                        <span>
                                                            {{ $announcement->created_at->format('M d, Y') }}
                                                            @if($announcement->user)
                                                                · {{ $announcement->user->first_name }}
                                                                {{ $announcement->user->last_name }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <i class="fa-solid fa-chevron-down"
                                                        style="font-size:12px; color:var(--muted); transition:transform .2s; cursor:pointer;"
                                                        id="announcement-chevron-{{ $announcement->id }}"></i>
                                                </div>
                                                <div id="announcement-expanded-{{ $announcement->id }}"
                                                    style="display:none; margin-top:10px; padding-left:46px;">
                                                    <p
                                                        style="font-size:13px; color:#555; white-space:pre-wrap; line-height:1.5;">
                                                        {{ trim($announcement->content) }}
                                                    </p>
                                                    <div style="display:flex; gap:8px; margin-top:10px;">
                                                        <button type="button" data-action="toggleMemberLike"
                                                            data-arg='[{{ $announcement->id }},"|el|"]' data-stop
                                                            class="member-like-btn {{ $announcement->likes->contains('user_id', $user->id ?? 0) ? 'liked' : '' }}"
                                                            style="display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:8px; font-size:12px; font-weight:600; border:1px solid var(--line); background:{{ $announcement->likes->contains('user_id', $user->id ?? 0) ? '#FEE2E2' : 'var(--card)' }}; color:{{ $announcement->likes->contains('user_id', $user->id ?? 0) ? '#DC2626' : '#6B7280' }}; cursor:pointer;">
                                                            <i class="fa-solid fa-heart"></i> <span
                                                                class="like-count">{{ $announcement->likes_count }}</span>
                                                        </button>
                                                        <button type="button" data-action="toggleMemberComments"
                                                            data-arg='[{{ $announcement->id }}]' data-stop
                                                            style="display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:8px; font-size:12px; font-weight:600; border:1px solid var(--line); background:var(--card); color:#6B7280; cursor:pointer;">
                                                            <i class="fa-solid fa-comment"></i>
                                                            {{ $announcement->comments_count }}
                                                        </button>
                                                    </div>
                                                    <div id="member-comments-{{ $announcement->id }}"
                                                        style="display:none; margin-top:10px;">
                                                        <div class="member-comments-list"
                                                            style="display:flex; flex-direction:column; gap:6px;">
                                                            @foreach($announcement->comments as $comment)
                                                                <div
                                                                    style="display:flex; gap:8px; padding:8px; background:var(--bg); border-radius:8px;">
                                                                    <div
                                                                        style="width:24px; height:24px; border-radius:50%; background:{{ $comment->user && in_array($comment->user->role, ['admin', 'general-manager']) ? '#3B82F6' : 'var(--teal)' }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                                        <span
                                                                            style="color:#fff; font-size:10px; font-weight:700;">{{ strtoupper(substr($comment->user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($comment->user->last_name ?? '', 0, 1)) }}</span>
                                                                    </div>
                                                                    <div style="flex:1; min-width:0;">
                                                                        <div
                                                                            style="display:flex; align-items:center; gap:4px; margin-bottom:2px;">
                                                                            <span
                                                                                style="font-size:12px; font-weight:600;">{{ $comment->user->first_name ?? '' }}
                                                                                {{ $comment->user->last_name ?? '' }}</span>
                                                                            @if($comment->user && in_array($comment->user->role, ['admin', 'general-manager']))
                                                                                <span
                                                                                    style="font-size:10px; padding:1px 4px; background:#DBEAFE; color:#1D4ED8; border-radius:4px; font-weight:600;">Admin</span>
                                                                            @endif
                                                                            <span
                                                                                style="font-size:11px; color:var(--muted);">{{ $comment->created_at->diffForHumans() }}</span>
                                                                        </div>
                                                                        <p style="font-size:12px; color:#555; margin:0;">
                                                                            {{ $comment->comment }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <form data-action="postMemberComment"
                                                            data-arg='["|event|",{{ $announcement->id }}]'
                                                            style="display:flex; gap:6px; margin-top:8px;">
                                                            @csrf
                                                            <input type="text" placeholder="Write a comment..." required
                                                                style="flex:1; padding:6px 10px; border:1px solid var(--line); border-radius:8px; font-size:12px; background:var(--card); color:var(--text);">
                                                            <button type="submit"
                                                                style="padding:6px 12px; background:var(--teal); color:#fff; border:none; border-radius:8px; font-size:12px; cursor:pointer;"><i
                                                                    class="fa-solid fa-paper-plane"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div style="text-align: center; color: #aaa; padding: 2rem; font-size: 13px;">
                                                <i class="fa fa-bullhorn"
                                                    style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                                No announcements yet.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Polls Tab --}}
                                <div id="member-community-content-polls" class="panel-body"
                                    style="height:auto; max-height:400px; display:none;">
                                    @php $pollOptionsMap = []; @endphp
                                    @forelse($polls as $poll)
                                        @php
                                            $pollResults = $poll->results;
                                            $totalVotes = count($poll->votes);
                                            $userVote = $poll->votes->firstWhere('user_id', $user->id ?? 0);
                                            $hasVoted = $userVote !== null;
                                            $isExpired = $poll->isExpired();
                                            $pollOptionsMap[$poll->id] = $poll->options;
                                        @endphp
                                        <div style="padding:12px 0; border-bottom:1px solid var(--line);"
                                            id="member-poll-{{ $poll->id }}">
                                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                                <div
                                                    style="width:28px; height:28px; border-radius:8px; background:linear-gradient(135deg, #8B5CF6, #7C3AED); display:flex; align-items:center; justify-content:center;">
                                                    <i class="fa-solid fa-chart-bar"
                                                        style="color:#fff; font-size:12px;"></i>
                                                </div>
                                                <div style="flex:1;">
                                                    <strong style="font-size:13px;">{{ $poll->question }}</strong>
                                                    <span style="font-size:11px; color:var(--muted); margin-left:6px;">by
                                                        {{ $poll->user->first_name ?? '' }}
                                                        {{ $poll->user->last_name ?? '' }}</span>
                                                </div>
                                                @if($poll->expires_at)
                                                    <span
                                                        style="font-size:11px; {{ $isExpired ? 'color:#DC2626;' : 'color:#D97706;' }}">
                                                        <i class="fa-solid fa-clock" style="margin-right:2px;"></i>
                                                        {{ $isExpired ? 'Expired' : 'Expires ' . $poll->expires_at->format('M d') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="display:flex; flex-direction:column; gap:4px;"
                                                id="member-poll-options-{{ $poll->id }}">
                                                @foreach($poll->options as $i => $option)
                                                    @php
                                                        $count = $pollResults[$i] ?? 0;
                                                        $pct = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
                                                        $isChosen = $hasVoted && $userVote->option_index === $i;
                                                    @endphp
                                                    @if($hasVoted || $isExpired)
                                                        <div
                                                            style="position:relative; height:28px; border-radius:8px; overflow:hidden; background:var(--line);">
                                                            <div
                                                                style="height:100%; width:{{ $pct }}%; background:{{ $isChosen ? 'var(--teal)' : '#D1D5DB' }}; border-radius:8px; transition:width .5s;">
                                                            </div>
                                                            <div
                                                                style="position:absolute; inset:0; display:flex; align-items:center; justify-content:space-between; padding:0 10px;">
                                                                <span
                                                                    style="font-size:12px; font-weight:600; color:{{ $isChosen ? '#fff' : '#555' }};">{{ $option }}</span>
                                                                <span
                                                                    style="font-size:11px; font-weight:600; color:{{ $isChosen ? '#fff' : '#888' }};">{{ $pct }}%
                                                                    <span style="font-weight:400;">({{ $count }})</span></span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <button type="button" data-action="voteMemberPoll"
                                                            data-arg='[{{ $poll->id }},{{ $i }}]'
                                                            style="width:100%; text-align:left; padding:6px 10px; border:1px solid var(--line); border-radius:8px; font-size:12px; font-weight:600; background:var(--card); color:var(--text); cursor:pointer;">
                                                            {{ $option }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div style="font-size:11px; color:var(--muted); margin-top:6px;">
                                                {{ $totalVotes }} {{ Str::plural('vote', $totalVotes) }}
                                                @if($hasVoted) · You voted @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div style="text-align: center; color: #aaa; padding: 2rem; font-size: 13px;">
                                            <i class="fa-solid fa-chart-bar"
                                                style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                                            No polls yet.
                                        </div>
                                    @endforelse
                                </div>
                            </div>



                        </div>
                        <div class="parent-panel panel-1">

                            {{-- Dividends (left) --}}
                            <div class="panel graph announcements">
                                <div class="panel-head"
                                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div>
                                        <h3>Dividends</h3>
                                        <p>Interest on your share capital</p>
                                    </div>
                                    {{-- <a href="{{ route('Financial', ['tab' => 'dividends']) }}"
                                        style="font-size:12.5px; font-weight:600; color:var(--teal); white-space:nowrap;">View
                                        all</a> --}}
                                </div>
                                <div class="recent-body">
                                    <div class="tx-list">
                                        @forelse ($recentDividends as $d)
                                            <div class="tx-list-item">
                                                <div class="tx-icon gold"><i class="fa-solid fa-coins"></i></div>
                                                <div class="tx-list-info">
                                                    <strong>{{ $d['label'] }}</strong>
                                                    <span>{{ $d['date'] }}</span>
                                                </div>
                                                <div
                                                    style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                                    <div class="tx-list-amt up">+₱{{ number_format($d['amount'], 2) }}</div>
                                                    @if(strtolower($d['status']) !== 'paid' && strtolower($d['status']) !== 'completed')
                                                        <span
                                                            style="font-size:12.5px; color:var(--muted);">{{ ucfirst($d['status']) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                                <i class="fa fa-gift"
                                                    style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                                No dividends recorded yet.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Patronage Refund (right) --}}
                            <div class="panel graph announcements">
                                <div class="panel-head panel-patronage" style="">
                                    <div>
                                        <h3>Patronage Refund</h3>
                                        <p>Based on your loan interest &amp; fees paid</p>
                                    </div>
                                    {{-- <a href="{{ route('Financial', ['tab' => 'patronage']) }}"
                                        style="font-size:12.5px; font-weight:600; color:var(--teal); white-space:nowrap;">View
                                        all</a> --}}
                                </div>
                                <div class="recent-body">
                                    <div class="tx-list">
                                        @forelse ($recentPatronage as $p)
                                            <div class="tx-list-item">
                                                <div class="tx-icon mint"><i class="fa-solid fa-percent"></i></div>
                                                <div class="tx-list-info">
                                                    <strong>{{ $p['label'] }}</strong>
                                                    <span>{{ $p['date'] }}</span>
                                                </div>
                                                <div
                                                    style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                                    <div class="tx-list-amt up">+₱{{ number_format($p['amount'], 2) }}</div>
                                                    @if(strtolower($p['status']) !== 'paid' && strtolower($p['status']) !== 'completed')
                                                        <span
                                                            style="font-size:12.5px; color:var(--muted);">{{ ucfirst($p['status']) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                                <i class="fa fa-percent"
                                                    style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                                No patronage refunds recorded yet.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- <div class="parent-panel panel-2" style="margin-top:1.5rem;">
                            <div class="panel graph">
                                <div class="panel-head"
                                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div>
                                        <h3>Account Balance</h3>
                                        <p>Share capital, savings &amp; loan balance overview</p>
                                    </div>
                                    <div class="balance-date">
                                        <select id="balanceMonthSelect" data-action="updateBalanceMonth">
                                            @foreach (range(1, 12) as $m)
                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ (int)
                                                explode('-', $balanceMonth)[1]===$m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <select id="balanceYearSelect" data-action="updateBalanceMonth">
                                            @foreach ($availableYears as $year)
                                            <option value="{{ $year }}" {{ (int) explode('-', $balanceMonth)[0]===$year
                                                ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="pie-chart-wrap">
                                        <canvas id="accountBalancePie" width="220" height="220"></canvas>
                                    </div>
                                    <div class="chart-legend">
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--gold);"></span>Share Capital</div>
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--blue);"></span>Savings</div>
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--coral);"></span>Loan Balance</div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel graph">
                                <div class="panel-head"
                                    style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div>
                                        <h3>Share Capital Growth</h3>
                                        <p>Net contributions over the last 6 months</p>
                                    </div>
                                    <div class="year-filter">
                                        <select data-action="goto-year" data-base-url="{{ url()->current() }}">
                                            @foreach ($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year==$selectedYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="chart-wrap">
                                        @foreach ($shareCapitalGrowth as $month)
                                        <div class="bar-col {{ $month['is_current'] ? 'active' : '' }}">
                                            <div class="bar" style="height:{{ $month['height_percent'] }}%">
                                                <div class="bar-tooltip">
                                                    <div class="bar-tooltip-title">{{ $month['label'] }}</div>
                                                    <div class="bar-tooltip-row">
                                                        <span
                                                            class="bar-tooltip-dot {{ $month['is_current'] ? 'dot-gold' : 'dot-blue' }}"></span>
                                                        <span class="bar-tooltip-label">Net Contribution:</span>
                                                        <span class="bar-tooltip-value">
                                                            {{ $month['net'] >= 0 ? '₱' : '-₱' }}{{
                                                            number_format(abs($month['net']), 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="bar-month">{{ $month['label'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    <!-- <div class="chart-legend">
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--blue);"></span>Prior months</div>
                                        <div class="legend-item"><span class="legend-dot"
                                                style="background:var(--gold);"></span>Current month</div>
                                    </div> -->
                                </div>
                            </div>
                        </div> --}}


                        {{-- ═══════════════════════════════════════════════
                        FINANCIAL TRENDS CHART
                        Copied from newCoop4.html sample dashboard
                        ═══════════════════════════════════════════════ --}}
                        <!-- <div class="share-graph">
                            <div class="share-parent">
                                <h2>Text</h2>
                            </div>

                        </div> -->
                        {{-- end Financial Trends Chart --}}

                        <!-- Resign from Cooperative -->
                        <!-- <div class="ask-box" style="margin-top: 2rem; border: 1px solid #fecaca; background: #fef2f2;">
                        <div class="text-box">
                            <h4 style="color: #dc2626;">Leave the Cooperative?</h4>
                            <p style="color: #1e293b;">If you wish to resign from the cooperative, you may submit a resignation request. A 60-day holding period applies for share capital withdrawal.</p>
                        </div>
                        <div class="link-box">
                            <button data-action="show-modal" data-target="resignModal" style="background: #dc2626; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-sign-out-alt"></i>
                                <span>Request Resignation</span>
                            </button>
                        </div>
                    </div> -->


                        <!-- Resignation Modal -->
                        <div id="resignModal"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;"
                            data-overlay>
                            <div
                                style="background:#fff; border-radius:12px; max-width:450px; width:90%; padding:0; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
                                <div
                                    style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                                    <h3 style="margin:0; font-size:18px; font-weight:700; color:#111827;">Request
                                        Resignation</h3>
                                    <button data-action="hide-modal" data-target="resignModal"
                                        style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
                                </div>
                                <form method="POST" action="{{ route('resignation.request') }}" style="padding:24px;">
                                    @csrf
                                    <p style="font-size:14px; color:#6b7280; margin-bottom:20px;">Please select your
                                        preference for your share capital:</p>
                                    <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:24px;">
                                        <label class="resign-option"
                                            style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer; transition:all .2s;">
                                            <input type="radio" name="withdraw_share_capital" value="1"
                                                style="accent-color:#1E2A4A;" required
                                                data-action="resign-option-select">
                                            <div>
                                                <strong style="display:block; color:#111827; font-size:15px;">Withdraw
                                                    Share Capital</strong>
                                                <span style="font-size:13px; color:#6b7280;">I want my share capital
                                                    paid out after 60 days</span>
                                            </div>
                                        </label>
                                        <label class="resign-option"
                                            style="display:flex; align-items:center; gap:12px; padding:14px 16px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer; transition:all .2s;">
                                            <input type="radio" name="withdraw_share_capital" value="0"
                                                style="accent-color:#1E2A4A;" required
                                                data-action="resign-option-select">
                                            <div>
                                                <strong style="display:block; color:#111827; font-size:15px;">Leave
                                                    Share Capital</strong>
                                                <span style="font-size:13px; color:#6b7280;">I leave my share capital
                                                    with the cooperative</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div style="display:flex; gap:12px;">
                                        <button type="button" data-action="hide-modal" data-target="resignModal"
                                            style="flex:1; padding:12px; background:#f3f4f6; color:#374151; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Cancel</button>
                                        <button type="submit"
                                            style="flex:1; padding:12px; background:#dc2626; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;"
                                            data-confirm="Are you sure you want to submit a resignation request? This action will be reviewed by admin.">Submit
                                            Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Report a Problem Modal -->
                        <div id="reportProblemModal" class="modal fade" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
                                    <div class="modal-header"
                                        style="border-bottom:1px solid #e5e7eb; padding:1.1rem 1.4rem;">
                                        <h5 class="modal-title" style="font-size:15px; font-weight:700; color:#111827;">
                                            Report a Problem</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('support.report.store') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body"
                                            style="padding:1.2rem 1.4rem; display:flex; flex-direction:column; gap:12px;">

                                            @if ($errors->hasAny(['category', 'category_other', 'subject', 'message', 'proof']))
                                                <div
                                                    style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; border-radius:8px; padding:10px 12px; font-size:12.5px;">
                                                    {{ $errors->first() }}
                                                </div>
                                            @endif

                                            @if (session('report_success'))
                                                <div
                                                    style="background:#e7f6ec; border:1px solid #b7e4c3; color:#1a7f37; border-radius:8px; padding:10px 12px; font-size:12.5px;">
                                                    {{ session('report_success') }}
                                                </div>
                                            @endif

                                            <div>
                                                <label
                                                    style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Category</label>
                                                <select name="category" id="reportCategorySelect" required
                                                    class="form-select"
                                                    style="border-radius:10px; border:1.5px solid #e0e0e0; height:44px; font-size:14px;">
                                                    <option value="" disabled selected>Select a category</option>
                                                    <option value="Balance Discrepancy">Balance Discrepancy</option>
                                                    <option value="Loan Requirement">Loan Requirement</option>
                                                    <option value="Payment Reflection">Payment Reflection</option>
                                                    <option value="Rejected / Invalid Deposit">Rejected / Invalid
                                                        Deposit</option>
                                                    <option value="Other">Other</option>
                                                </select>

                                                {{-- Shown only when "Other" is selected --}}
                                                <input type="text" name="category_other" id="reportCategoryOther"
                                                    maxlength="80" placeholder="Please specify your category"
                                                    style="display:none; width:100%; margin-top:8px; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box;">
                                            </div>

                                            <div>
                                                <label
                                                    style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Subject</label>
                                                <input type="text" name="subject" required maxlength="150"
                                                    placeholder="Short summary of the issue"
                                                    style="width:100%; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box;">
                                            </div>

                                            <div>
                                                <label
                                                    style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Details</label>
                                                <textarea name="message" required maxlength="2000" rows="4"
                                                    placeholder="Describe what happened, including the reference number if it's about a transaction"
                                                    style="width:100%; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box; resize:vertical;"></textarea>
                                            </div>

                                            <div>
                                                <label
                                                    style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Attach
                                                    Screenshot <span
                                                        style="color:#bbb; text-transform:none;">(optional)</span></label>
                                                <input type="file" name="proof" accept="image/png,image/jpeg,image/jpg"
                                                    class="form-control" style="border-radius:10px; font-size: 14px;">
                                            </div>

                                        </div>
                                        <div class="modal-footer"
                                            style="background:#f8f9fa; border-top:1px solid rgba(0,0,0,0.1); padding:1rem 1.4rem;">
                                            <button type="submit"
                                                style="width:100%; padding:0.75rem; background:var(--teal); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                                <i class="fa fa-paper-plane" style="margin-right:6px;"></i> Submit
                                                Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Net Standing Modal -->
                        <div id="netStandingModal"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;"
                            data-overlay>
                            <div
                                style="background:#fff; border-radius:16px; max-width:420px; width:90%; padding:0; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
                                <div
                                    style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <h3 style="margin:0; font-size:15px; font-weight:700; color:#111827;">Net
                                            Standing</h3>
                                        <p style="margin: 3.2px 0 0; font-size: 13.5px;color: var(--muted);">Overall
                                            Financial Position</p>
                                    </div>
                                    <button data-action="hide-modal" data-target="netStandingModal"
                                        style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
                                </div>

                                <div style="padding:24px;">

                                    <div style="display:flex; gap:8px; margin-bottom:15px;">
                                        <select id="standingMonthSelect" class="form-select"
                                            data-action="updateStandingMonth"
                                            style="flex:1; padding:8px 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:13px; font-weight:600; color:#111827;">
                                            @foreach (range(1, 12) as $m)
                                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ (int) explode('-', $standingMonth)[1] === $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select id="standingYearSelect" class="form-select"
                                            data-action="updateStandingMonth"
                                            style="flex:1; padding:8px 12px; border:1px solid #e5e7eb; border-radius:10px; font-size:13px; font-weight:600; color:#111827;">
                                            @foreach ($availableYears as $year)
                                                <option value="{{ $year }}" {{ (int) explode('-', $standingMonth)[0] === $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div
                                        style="text-align:center; padding:16px 0 20px; border-bottom:1px dashed var(--border); margin-bottom:16px;">
                                        <p
                                            style="margin:0; font-size:12.5px; color:#808080; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">
                                            Net Standing</p>
                                        <h2
                                            style="margin:6px 0 0; font-size:32px; font-weight:800; color:{{ $netStandingAsOf >= 0 ? 'var(--teal)' : '#DC2626' }};">
                                            ₱{{ number_format($netStandingAsOf, 2) }}
                                        </h2>
                                    </div>

                                    <div style="display:flex; flex-direction:column; gap:12px;">
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; font-size:14px;">
                                            <span
                                                style="display:flex; align-items:center; gap:8px; color:var(--muted); font-weight: 600;">
                                                <span
                                                    style="width:10px; height:10px; border-radius:50%; background:var(--gold);"></span>
                                                Share Capital
                                            </span>
                                            <strong
                                                style="color:#1a1a1a;">₱{{ number_format($shareCapitalStandingAsOf, 2) }}</strong>
                                        </div>
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; font-size:14px;">
                                            <span
                                                style="display:flex; align-items:center; gap:8px; color:var(--muted); font-weight: 600;">
                                                <span
                                                    style="width:10px; height:10px; border-radius:50%; background:var(--blue);"></span>
                                                Savings
                                            </span>
                                            <strong style="color:#1a1a1a;">+
                                                ₱{{ number_format($savingsStandingAsOf, 2) }}</strong>
                                        </div>
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; font-size:14px; padding-bottom:12px; border-bottom:1px solid #f0f0f0;">
                                            <span
                                                style="display:flex; align-items:center; gap:8px; color:var(--muted); font-weight: 600;">
                                                <span
                                                    style="width:10px; height:10px; border-radius:50%; background:var(--coral);"></span>
                                                Loan Balance
                                            </span>
                                            <strong style="color:#DC2626;">−
                                                ₱{{ number_format($loanStandingAsOf, 2) }}</strong>
                                        </div>
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; font-size:14.5px;">
                                            <span style="font-weight:700; color:#1a1a1a;">Total</span>
                                            <strong
                                                style="color:{{ $netStandingAsOf >= 0 ? 'var(--teal)' : '#DC2626' }};">₱{{ number_format($netStandingAsOf, 2) }}</strong>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>
                </div>{{-- end #page-content --}}
            </div>{{-- end .main-parent --}}
        </div>{{-- end .rightbar --}}
    </div>{{-- end .container-fluid --}}

    <!-- Upcoming Dues Modal -->
    <div id="upcomingDuesModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;"
        data-overlay>
        <div
            style="background:#fff; border-radius:16px; max-width:480px; width:90%; padding:0; box-shadow:0 25px 60px rgba(0,0,0,0.3); max-height:80vh; display:flex; flex-direction:column;">
            <div
                style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:700; color:#111827;">Upcoming Dues</h3>
                    <p style="margin:3.2px 0 0; font-size:13.5px; color:var(--muted);">Your next loan payments across
                        all accounts</p>
                </div>
                <button data-action="hide-modal" data-target="upcomingDuesModal"
                    style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
            </div>

            <div style="padding: 0 24px; overflow-y:auto; height: 368px;">
                @forelse ($upcomingDues as $due)
                    <a href="{{ route('LoanStatus', ['loan_id' => $due['loan_id']]) }}"
                        style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px; padding:14px 0; border-bottom:1px solid var(--border); cursor:pointer;">
                        <div class="tx-icon {{ $due['icon'] }}"><i class="fa-solid {{ $due['icon_fa'] }}"></i></div>
                        <div style="flex:1;">
                            <strong style="display:block; font-size:14px; color:#111827;">{{ $due['title'] }}</strong>
                            <span style="font-size:12.5px; color:var(--muted);">{{ $due['date_display'] }} ·
                                {{ $due['subtitle'] }}</span>
                        </div>
                        <div style="font-size: 14px;font-weight:700; color:#DC2626;">
                            ₱{{ number_format($due['amount'], 2) }}
                        </div>
                    </a>
                @empty
                    <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                        <i class="fa fa-circle-check" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                        No upcoming dues.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Overdue Loans Modal -->
    <div id="overdueLoansModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;"
        data-overlay>
        <div
            style="background:#fff; border-radius:16px; max-width:480px; width:90%; padding:0; box-shadow:0 25px 60px rgba(0,0,0,0.3); max-height:80vh; display:flex; flex-direction:column;">
            <div
                style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:700; color:#111827;">Overdue Loans</h3>
                    <p style="margin:3.2px 0 0; font-size:13.5px; color:var(--muted);">Loans past due across all
                        accounts</p>
                </div>
                <button data-action="hide-modal" data-target="overdueLoansModal"
                    style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
            </div>

            <div style="padding: 0 24px; overflow-y:auto; height: 368px;">
                @forelse ($overdueLoansDisplay as $overdue)
                    <a href="{{ route('LoanStatus', ['loan_id' => $overdue['loan_id']]) }}"
                        style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px; padding:14px 0; border-bottom:1px solid var(--border); cursor:pointer;">
                        <div class="tx-icon {{ $overdue['icon'] }}"><i class="fa-solid {{ $overdue['icon_fa'] }}"></i></div>
                        <div style="flex:1;">
                            <strong style="display:block; font-size:14px; color:#111827;">{{ $overdue['title'] }}</strong>
                            <span style="font-size:12.5px; color:var(--muted);">{{ $overdue['date_display'] }} ·
                                {{ $overdue['subtitle'] }}</span>
                        </div>
                        <div style="font-size: 14px;font-weight:700; color:#DC2626;">
                            +₱{{ number_format($overdue['amount'], 2) }}
                        </div>
                    </a>
                @empty
                    <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                        <i class="fa fa-circle-check" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                        No overdue loans.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Seminars Modal -->
    <div id="seminarsModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;"
        data-overlay>
        <div
            style="background:#fff; border-radius:16px; max-width:460px; width:90%; padding:0; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
            <div
                style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:700; color:#111827;">Seminars</h3>
                    <p style="margin:3.2px 0 0; font-size:13.5px; color:var(--muted);">Your membership training progress
                    </p>
                </div>
                <button data-action="hide-modal" data-target="seminarsModal"
                    style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
            </div>

            {{-- Tab buttons --}}
            <div style="display:flex; border-bottom:1px solid #e5e7eb;">
                <button id="seminarTabAttended" data-action="switchSeminarTab" data-arg='["attended"]'
                    style="flex:1; padding:10px; font-size:13px; font-weight:600; border:none; cursor:pointer; background:transparent; color:var(--teal); border-bottom:2px solid var(--teal);">
                    Attended
                </button>
                <button id="seminarTabPasscode" data-action="switchSeminarTab" data-arg='["passcode"]'
                    style="flex:1; padding:10px; font-size:13px; font-weight:600; border:none; cursor:pointer; background:transparent; color:#9ca3af; border-bottom:2px solid transparent;">
                    Enter Passcode
                </button>
            </div>

            {{-- Attended tab --}}
            <div id="seminarAttendedPanel" style="padding: 0 24px; overflow-y:auto; height: 310px;">
                @if($upcomingSeminars->isNotEmpty())
                    <div style="padding:14px 0 4px;">
                        <strong style="font-size:12px; text-transform:uppercase; color:var(--muted);">Scheduled</strong>
                        @foreach($upcomingSeminars as $up)
                            <div
                                style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--border);">
                                <div
                                    style="width:40px; height:40px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#FEF3C7;">
                                    <i class="fa-solid fa-calendar-day" style="font-size:14px; color:#b45309;"></i>
                                </div>
                                <div style="flex:1;">
                                    <strong style="display:block; font-size:14px; color:#111827;">{{ $up['label'] }}</strong>
                                    <span style="font-size:12.5px; color:var(--muted);">
                                        {{ $up['datetime']->format('M d, Y · g:i A') }} ·
                                        {{ $up['delivery_type'] === 'online' ? 'Online' : 'F2F · ' . ($up['meetup_place'] ?? 'Venue TBA') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @forelse ($seminarsSummary as $s)
                        <div
                            style="display:flex; align-items:center; gap:12px; padding:14px 0; border-bottom:1px solid var(--border);">
                            <div
                                style="width:40px; height:40px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#EDF0F5;">
                                <i class="fa-solid fa-circle-check" style="font-size:14px; color:var(--teal);"></i>
                            </div>
                            <div style="flex:1;">
                                <strong style="display:block; font-size:14px; color:#111827;">{{ $s['label'] }}</strong>
                                <span style="font-size:12.5px; color:var(--muted);">
                                    Attended{{ $s['attended_datetime'] ? ' · ' . \Carbon\Carbon::parse($s['attended_datetime'])->format('M d, Y') : '' }}
                                </span>
                            </div>
                            <div
                                style="padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap;flex-shrink: 0; background-color: #EDF0F5; color: var(--teal);">
                                Attended
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                            <i class="fa fa-hourglass-half" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                            No seminars attended yet.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Passcode tab --}}
            <div id="seminarPasscodePanel" style="padding: 16px 24px; display:none; overflow-y:auto; height: 310px;">
                @php
                    $hasUncompleted = collect($seminarCompletedFlags ?? [])->contains(false);
                @endphp

                @if (!$hasUncompleted)
                    <div style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                        <i class="fa-solid fa-circle-check"
                            style="font-size:24px; display:block; margin-bottom:8px; color:var(--teal);"></i>
                        You have completed all seminars!
                    </div>
                @else
                    <form action="{{ route('Seminars.verifyPasscode') }}" method="POST" id="seminarPasscodeForm">
                        @csrf
                        <div style="margin-bottom:14px;">
                            <label
                                style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Seminar
                                Type</label>
                            <select name="seminar_type" id="seminarTypeSelect" required
                                style="width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13.5px; color:#111827; background:#fff; outline:none;">
                                <option value="" disabled {{ old('seminar_type') ? '' : 'selected' }}>Select a seminar
                                </option>
                                @foreach ($seminarTypeLabels as $key => $label)
                                    @unless ($seminarCompletedFlags[$key] ?? false)
                                        <option value="{{ $key }}" {{ old('seminar_type') === $key ? 'selected' : '' }}>{{ $label }}
                                        </option>
                                    @endunless
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label
                                style="display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Passcode</label>
                            <input type="text" name="passcode" id="seminarPasscodeInput" required maxlength="64"
                                placeholder="Enter the seminar passcode" value="{{ old('passcode') }}"
                                style="width:100%; padding:10px 12px; border:1.5px solid {{ $errors->has('passcode') ? '#dc2626' : '#e5e7eb' }}; background:{{ $errors->has('passcode') ? '#fef2f2' : '#fff' }}; border-radius:10px; font-size:13.5px; color:#111827; outline:none;">
                            @if ($errors->has('passcode') || $errors->has('seminar_type'))
                                <p id="seminarPasscodeError"
                                    style="margin:6px 0 0; font-size:12px; font-weight:600; color:#dc2626;">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $errors->first('passcode') ?: $errors->first('seminar_type') }}
                                </p>
                            @endif
                        </div>

                        <button type="submit"
                            style="width:100%; padding:11px; background:var(--teal); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; transition:opacity 0.2s;">
                            <i class="fa-solid fa-key" style="margin-right:6px;"></i> Verify Passcode
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if ($errors->hasAny(['category', 'category_other', 'subject', 'message', 'proof']))
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('reportProblemModal');
                if (el && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        </script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        function switchSeminarTab(tab) {
            const attended = document.getElementById('seminarAttendedPanel');
            const passcode = document.getElementById('seminarPasscodePanel');
            const btnAttended = document.getElementById('seminarTabAttended');
            const btnPasscode = document.getElementById('seminarTabPasscode');

            if (tab === 'attended') {
                attended.style.display = 'block';
                passcode.style.display = 'none';
                btnAttended.style.color = 'var(--teal)';
                btnAttended.style.borderBottom = '2px solid var(--teal)';
                btnPasscode.style.color = '#9ca3af';
                btnPasscode.style.borderBottom = '2px solid transparent';
            } else {
                attended.style.display = 'none';
                passcode.style.display = 'block';
                btnAttended.style.color = '#9ca3af';
                btnAttended.style.borderBottom = '2px solid transparent';
                btnPasscode.style.color = 'var(--teal)';
                btnPasscode.style.borderBottom = '2px solid var(--teal)';
            }
        }

        function filterRecentTx(category, btn) {
            document.querySelectorAll('.tx-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const items = document.querySelectorAll('#recentTxList .tx-list-item');
            let visibleCount = 0;

            items.forEach(item => {
                const match = category === 'all' || item.dataset.category === category;
                item.style.display = match ? 'flex' : 'none';
                if (match) visibleCount++;
            });

            document.getElementById('recentTxEmpty').style.display = visibleCount === 0 ? 'block' : 'none';
        }
    </script>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;
            A.register('resign-option-select', function (e, el) {
                document.querySelectorAll('.resign-option').forEach(function (l) { l.style.borderColor = '#e5e7eb'; });
                var label = el.closest('label');
                if (label) label.style.borderColor = '#1E2A4A';
            });
        })();
    </script>


    @if (request('open_standing_modal'))
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('netStandingModal').style.display = 'flex';
            });
        </script>
    @endif


    {{-- ─── Skeleton dismiss logic — only runs after login ─── --}}
    @if (session('just_logged_in'))
        <script nonce="{{ csp_nonce() }}">
            (function () {
                var MIN_DISPLAY = 2000;
                var startTime = Date.now();
                var pageLoaded = false;
                var dismissed = false;

                function dismissSkeleton() {
                    if (dismissed) return;
                    dismissed = true;

                    var overlay = document.getElementById('skeleton-overlay');
                    var content = document.getElementById('page-content');
                    var parent = document.querySelector('.main-parent.sk-loading');

                    if (content) content.classList.add('sk-ready');

                    if (overlay) {
                        overlay.classList.add('sk-hide');
                        overlay.addEventListener('transitionend', function () {
                            overlay.remove();
                            if (parent) parent.classList.remove('sk-loading');
                        }, { once: true });
                    } else if (parent) {
                        parent.classList.remove('sk-loading');
                    }
                }

                function tryDismiss() {
                    if (!pageLoaded) return;
                    var elapsed = Date.now() - startTime;
                    var remaining = MIN_DISPLAY - elapsed;
                    if (remaining <= 0) {
                        dismissSkeleton();
                    } else {
                        setTimeout(dismissSkeleton, remaining);
                    }
                }

                if (document.readyState === 'complete') {
                    pageLoaded = true;
                    tryDismiss();
                } else {
                    window.addEventListener('load', function () {
                        pageLoaded = true;
                        tryDismiss();
                    });
                }

                setTimeout(dismissSkeleton, 6000);
            })();
        </script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        (function () {
            const style = getComputedStyle(document.documentElement);
            const colors = {
                gold: style.getPropertyValue('--gold').trim() || '#C9A84C',
                blue: style.getPropertyValue('--blue').trim() || '#5B8DEF',
                coral: style.getPropertyValue('--coral').trim() || '#FF8A75',
            };

            const ctx = document.getElementById('accountBalancePie');
            if (ctx) {
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Share Capital', 'Savings', 'Loan Balance'],
                        datasets: [{
                            data: [
                                {{ $accountBalanceChart[0]['value'] ?? 0 }},
                        {{ $accountBalanceChart[1]['value'] ?? 0 }},
                            {{ $accountBalanceChart[2]['value'] ?? 0 }},
                            ],
                            backgroundColor: [colors.gold, colors.blue, colors.coral],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        responsive: false,
                    },
                });
            }
        })();
    </script>


    <script nonce="{{ csp_nonce() }}">
        function filterLoans(status) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.querySelectorAll('.loan-box').forEach(box => {
                box.style.display = (status === 'all' || box.dataset.status === status) ? 'block' : 'none';
            });
        }

        // Member community tabs
        function switchMemberCommunityTab(tab) {
            document.querySelectorAll('[id^="member-community-tab-"]').forEach(btn => {
                btn.style.borderBottomColor = 'transparent';
                btn.style.color = 'var(--muted)';
            });
            document.querySelectorAll('[id^="member-community-content-"]').forEach(el => el.style.display = 'none');
            document.getElementById('member-community-tab-' + tab).style.borderBottomColor = 'var(--teal)';
            document.getElementById('member-community-tab-' + tab).style.color = 'var(--teal)';
            document.getElementById('member-community-content-' + tab).style.display = 'block';
        }

        // Member announcements expand/collapse
        function toggleMemberAnnouncement(id) {
            const el = document.getElementById('announcement-expanded-' + id);
            const chevron = document.getElementById('announcement-chevron-' + id);
            if (el.style.display === 'none') {
                el.style.display = 'block';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                el.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0)';
            }
        }

        function toggleMemberComments(id) {
            const el = document.getElementById('member-comments-' + id);
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }

        function toggleMemberLike(announcementId, btn) {
            fetch('/announcements/' + announcementId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const countSpan = btn.querySelector('.like-count');
                        countSpan.textContent = data.count;
                        if (data.liked) {
                            btn.style.background = '#FEE2E2';
                            btn.style.color = '#DC2626';
                            btn.classList.add('liked');
                        } else {
                            btn.style.background = 'var(--card)';
                            btn.style.color = '#6B7280';
                            btn.classList.remove('liked');
                        }
                    }
                })
                .catch(() => { });
        }

        function postMemberComment(event, announcementId) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('input[type="text"]');
            const comment = input.value.trim();
            if (!comment) return;

            fetch('/announcements/' + announcementId + '/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ comment: comment }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const c = data.comment;
                        const escHtml = (value) => {
                            const el = document.createElement('div');
                            el.textContent = value;
                            return el.innerHTML;
                        };
                        const isAdmin = ['admin', 'general-manager'].includes(c.user.role);
                        const container = form.previousElementSibling;
                        const div = document.createElement('div');
                        div.style.cssText = 'display:flex; gap:8px; padding:8px; background:var(--bg); border-radius:8px;';
                        div.innerHTML = '<div style="width:24px; height:24px; border-radius:50%; background:' + (isAdmin ? '#3B82F6' : 'var(--teal)') + '; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><span style="color:#fff; font-size:10px; font-weight:700;">' + escHtml((c.user.first_name?.[0] || '') + (c.user.last_name?.[0] || '')) + '</span></div><div style="flex:1; min-width:0;"><div style="display:flex; align-items:center; gap:4px; margin-bottom:2px;"><span style="font-size:12px; font-weight:600;">' + escHtml(c.user.first_name) + ' ' + escHtml(c.user.last_name) + '</span>' + (isAdmin ? '<span style="font-size:10px; padding:1px 4px; background:#DBEAFE; color:#1D4ED8; border-radius:4px; font-weight:600;">Admin</span>' : '') + '<span style="font-size:11px; color:var(--muted);">' + escHtml(c.created_at) + '</span></div><p style="font-size:12px; color:#555; margin:0;">' + escHtml(c.comment) + '</p></div>';
                        container.appendChild(div);
                        input.value = '';
                    }
                })
                .catch(() => { });
        }

        const memberPollOptions = {{ Js::from($pollOptionsMap) }};

        function voteMemberPoll(pollId, optionIndex) {
            fetch('/polls/' + pollId + '/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ option_index: optionIndex }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('member-poll-options-' + pollId);
                        const options = memberPollOptions[pollId] || [];
                        container.innerHTML = '';
                        data.results.forEach((count, i) => {
                            const pct = data.total_votes > 0 ? Math.round((count / data.total_votes) * 100) : 0;
                            const isChosen = data.voted_index === i;
                            const escHtml = (value) => {
                                const el = document.createElement('div');
                                el.textContent = value;
                                return el.innerHTML;
                            };
                            const div = document.createElement('div');
                            div.style.cssText = 'position:relative; height:28px; border-radius:8px; overflow:hidden; background:var(--line);';
                            div.innerHTML = '<div style="height:100%; width:' + pct + '%; background:' + (isChosen ? 'var(--teal)' : '#D1D5DB') + '; border-radius:8px; transition:width .5s;"></div><div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:space-between; padding:0 10px;"><span style="font-size:12px; font-weight:600; color:' + (isChosen ? '#fff' : '#555') + ';">' + escHtml(options[i] || 'Option ' + (i + 1)) + '</span><span style="font-size:11px; font-weight:600; color:' + (isChosen ? '#fff' : '#888') + ';">' + pct + '% <span style="font-weight:400;">(' + count + ')</span></span></div>';
                            container.appendChild(div);
                        });
                        const pollEl = document.getElementById('member-poll-' + pollId);
                        const voteCountEl = pollEl.querySelector('div[style*="font-size:11px"]');
                        if (voteCountEl) voteCountEl.innerHTML = data.total_votes + ' ' + (data.total_votes === 1 ? 'vote' : 'votes') + ' · You voted';
                    }
                })
                .catch(() => { });
        }
    </script>

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            var select = document.getElementById('reportCategorySelect');
            var other = document.getElementById('reportCategoryOther');
            if (!select || !other) return;

            select.addEventListener('change', function () {
                var isOther = select.value === 'Other';
                other.style.display = isOther ? 'block' : 'none';
                other.required = isOther;   // only required when visible
                if (isOther) {
                    other.focus();
                } else {
                    other.value = '';
                }
            });
        });
    </script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>


    {{-- Toast after submitting a report --}}
    @if (session('report_success'))
        <div class="toast-message" id="reportToast" role="status" style="width:320px;">
            <i class="fa-solid fa-circle-check"></i>
            <p>{{ session('report_success') }}</p>
        </div>

        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                var toast = document.getElementById('reportToast');
                if (!toast) return;
                setTimeout(function () {
                    toast.classList.add('hide');
                    setTimeout(function () { toast.remove(); }, 400);
                }, 4000);
            });
        </script>
    @endif

    {{-- Reopen the Seminars modal on the Passcode tab if the code was rejected --}}
    @if ($errors->has('passcode') || $errors->has('seminar_type'))
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('seminarsModal').style.display = 'flex';
                switchSeminarTab('passcode');

                // Clear the red state as soon as the member starts typing again
                var input = document.getElementById('seminarPasscodeInput');
                var err = document.getElementById('seminarPasscodeError');
                input?.addEventListener('input', function () {
                    input.style.borderColor = '#e5e7eb';
                    input.style.background = '#fff';
                    if (err) err.style.display = 'none';
                }, { once: true });
            });
        </script>
    @endif

    {{-- Toast after a successful passcode --}}
    @if (session('seminar_success'))
        <div class="toast-message" id="seminarToast" role="status" style="width:320px;">
            <i class="fa-solid fa-circle-check"></i>
            <p>{{ session('seminar_success') }}</p>
        </div>

        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                var toast = document.getElementById('seminarToast');
                if (!toast) return;
                setTimeout(function () {
                    toast.classList.add('hide');
                    setTimeout(function () { toast.remove(); }, 400);
                }, 4000);
            });
        </script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        (function () {
            var A = window.CSP_actions;
            if (!A) return;

            A.register('show-locked-toast', function () {
                var existing = document.getElementById('lockedToast');
                if (existing) existing.remove();

                var toast = document.createElement('div');
                toast.className = 'toast-message';
                toast.id = 'lockedToast';
                toast.innerHTML = '<i class="fa-solid fa-lock"></i><p>Complete all 3 seminars to unlock this section.</p>';
                document.body.appendChild(toast);

                setTimeout(function () {
                    toast.classList.add('hide');
                    setTimeout(function () { toast.remove(); }, 400);
                }, 3500);
            });
        })();
    </script>

</body>

</html>