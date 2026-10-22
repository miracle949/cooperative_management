<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminars</title>
    <link rel="icon" href="images/websitelogo.png" type="image/png">

    <link rel="stylesheet" href="css_folder/seminars.css">
    <link rel="stylesheet" href="css_folder/loading.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="../font-awesome-icon/css/all.min.css">
</head>

<body>

    <div class="container-fluid m-0 p-0">
        @include("components.offcanvas")
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")

            <div class="main-parent">
                @if(session('success'))
                    <div class="sem-flash-success">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="sem-flash-error">
                        <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="sem-flash-info">
                        <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
                    </div>
                @endif

                <div class="main-header">
                    <h3>Seminars</h3>
                    <p>Your path to full membership — complete all three sessions to unlock upgrade.</p>
                </div>

                <div class="member-card">
                    <div class="member-left">
                        <div class="member-tag">Membership Track</div>
                        <h3>{{ $heroTitle }}</h3>
                        <p>{{ $heroSubtitle }}</p>
                        @if ($heroNextLine)
                            <span><i class="fa fa-clock"></i> {{ $heroNextLine }}</span>
                        @endif
                    </div>
                    <!-- <div class="member-right">
                        <div class="member-box-parent">
                            <div class="member-box-card">
                                <p>{{ $totalSeminars }}</p>
                                <span>Total</span>
                            </div>
                            <div class="member-box-card">
                                <p>{{ $completedCount }}</p>
                                <span>Completed</span>
                            </div>
                            <div class="member-box-card">
                                <p>{{ $remainingCount }}</p>
                                <span>Remaining</span>
                            </div>
                        </div>
                    </div> -->
                </div>

                <div class="member-box-parent">
                    <div class="member-box-card">
                        <span>Total</span>
                        <p>{{ $totalSeminars }}</p>
                    </div>
                    <div class="member-box-card">
                        <span>Completed</span>
                        <p>{{ $completedCount }}</p>
                    </div>
                    <div class="member-box-card">
                        <span>Remaining</span>
                        <p>{{ $remainingCount }}</p>
                    </div>
                </div>

                {{-- ══ UPCOMING SEMINARS ══ --}}
                <div class="sem-section">
                    <div class="sem-section-head">
                        <h3>Upcoming Seminars</h3>
                        <p>Sessions you're registered for</p>
                    </div>

                    @if ($upcomingSeminars->isEmpty())
                        <div class="sem-empty">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No upcoming seminars scheduled yet.</p>
                        </div>
                    @else
                        <div class="sem-list">
                            @foreach ($upcomingSeminars as $sem)
                                <div class="sem-item">
                                    <div class="sem-item-left">
                                        <div class="sem-icon sem-icon-scheduled">
                                            <i class="fa-solid fa-calendar-check"></i>
                                        </div>
                                        <div class="sem-text">
                                            <h4>{{ $sem['label'] }}</h4>
                                            <span class="sem-schedule-line">
                                                <i class="fa-solid fa-calendar-day"></i>
                                                {{ $sem['datetime']->format('M d, Y · g:i A') }}
                                                ·
                                                @if ($sem['delivery_type'] === 'online')
                                                    Online
                                                    @if ($sem['online_link'])
                                                        <a href="{{ $sem['online_link'] }}" target="_blank">Join link</a>
                                                    @endif
                                                @else
                                                    F2F · {{ $sem['meetup_place'] ?? 'Venue TBA' }}
                                                    @if ($sem['exact_venue'])
                                                        ({{ $sem['exact_venue'] }})
                                                    @endif
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="sem-status-pill sem-status-scheduled">Scheduled</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ══ YOUR SEMINARS — history of attended/completed sessions ══ --}}
                <div class="sem-section">
                    <div class="sem-section-head">
                        <h3>Your Seminars</h3>
                        <p>Attendance history for completed sessions</p>
                    </div>

                    @if ($seminarHistory->isEmpty())
                        <div class="sem-empty">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <p>You haven't attended any seminars yet.</p>
                        </div>
                    @else
                        <div class="sem-list">
                            @foreach ($seminarHistory as $sem)
                                <div class="sem-item">
                                    <div class="sem-item-left">
                                        <div class="sem-icon sem-icon-{{ $sem['status'] }}">
                                            @if ($sem['status'] === 'attended')
                                                <i class="fa-solid fa-check"></i>
                                            @elseif ($sem['status'] === 'missed')
                                                <i class="fa-solid fa-xmark"></i>
                                            @else
                                                <i class="fa-solid fa-hourglass-half"></i>
                                            @endif
                                        </div>
                                        <div class="sem-text">
                                            <h4>{{ $sem['label'] }}</h4>
                                            <span class="sem-schedule-line">
                                                <i class="fa-solid fa-calendar-day"></i>
                                                {{ $sem['datetime']->format('M d, Y · g:i A') }}
                                                ·
                                                {{ $sem['delivery_type'] === 'online' ? 'Online' : 'F2F · ' . ($sem['meetup_place'] ?? 'Venue TBA') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="sem-status-pill sem-status-{{ $sem['status'] }}">
                                        @switch($sem['status'])
                                            @case('attended')
                                                Attended
                                                @break
                                            @case('missed')
                                                Missed
                                                @break
                                            @default
                                                Pending Review
                                        @endswitch
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ══ ENTER SEMINAR PASSCODE (removed) ══ --}}

            </div>
        </div>
    </div>

</body>

</html>