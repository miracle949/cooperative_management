@php
    $navSeminarsComplete = false;
    $navMemberId = 'MEM-0000';
    if (auth()->check()) {
        $navSeminarCompletion = \App\Models\SeminarCompletions_tbl::where('user_id', auth()->id())->first();
        $navSeminarsComplete = $navSeminarCompletion
            && $navSeminarCompletion->pmes_completed
            && $navSeminarCompletion->fundamentals_completed
            && $navSeminarCompletion->finance_completed;

        // Same format as the admin Accounts table (MEM-0027 / ADM-0002)
        $navIsStaff = !in_array(strtolower(auth()->user()->role ?? ''), ['member', 'pending', 'inactive']);
        $navMemberId = ($navIsStaff ? 'ADM-' : 'MEM-') . str_pad(auth()->id(), 4, '0', STR_PAD_LEFT);
    }
@endphp

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="images/logo2.png" alt="">
        <div class="side-text">
            <h3>KPMPCATS</h3>
            <p>Member Management</p>
        </div>

        <!-- <div class="button-accordion">
            <i class="fa fa-chevron-left"></i>
        </div> -->
    </div>
    <div class="sidebar-menu">
        <ul>
            <div class="navigation">
                <div class="menu" style="padding: 4px 10px 6px;">
                    <p>Main</p>
                </div>
                <li class="{{ request()->routeIs('MemberPortal') ? 'active' : '' }}">
                    <a href="{{ route('MemberPortal') }}" title="Dashboard">
                        <!-- <div class="nav-icon"> -->
                        <i class="fa fa-home"></i>
                        <!-- </div> -->
                        <span>Dashboard</span>
                    </a>
                </li>
                <!-- <div class="menu">
                    <p>Loan Management</p>
                </div> -->
                @if($navSeminarsComplete)
                    <li class="{{ request()->routeIs('LoanApplication') ? 'active' : '' }}">
                        <a href="{{ route('LoanApplication') }}" title="Loan Application">
                            <!-- <div class="nav-icon"> -->
                            <i class="fa fa-file"></i>
                            <!-- </div> -->
                            <span>Loan Application</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('LoanStatus') ? 'active' : '' }}">
                        <a href="{{ route('LoanStatus') }}" title="Loan Status">
                            <!-- <div class="nav-icon"> -->
                            <i class="fa fa-hand-holding-dollar"></i>
                            <!-- </div> -->
                            <span>Repayments</span>
                        </a>
                    </li>
                @endif
                <!-- <div class="menu">
                    <p>Member Finance</p>
                </div> -->
                {{-- <li class="{{ request()->routeIs('ShareCapitalMember') ? 'active' : '' }}">
                    <a href="{{ route('ShareCapitalMember') }}" title="Share Capital">
                        <i class="fa fa-coins"></i>
                        <span>Share Capital</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('savings.index') ? 'active' : '' }}">
                    <a href="{{ route('savings.index') }}">
                        <i class="fa fa-piggy-bank"></i>
                        <span>Savings</span>
                    </a>
                </li> --}}

                <!-- <div class="menu" style="padding: 10px 10px 8px;">
                    <p>Account</p>
                </div> -->

                @if($navSeminarsComplete)
                    <li class="{{ request()->routeIs('Financial') ? 'active' : '' }}">
                        <a href="{{ route('Financial') }}" title="Financial">
                            <i class="fa fa-wallet"></i>
                            <span>Financial</span>
                        </a>
                    </li>
                @endif

                {{-- <li class="{{ request()->routeIs('Seminars') ? 'active' : '' }}">
                    <a href="{{ route('Seminars') }}" title="Seminars">
                        <i class="fa fa-graduation-cap"></i>
                        <span>Seminars</span>
                    </a>
                </li> --}}

                {{-- <div class="sidebar-divider"></div>

                <div class="menu" style="padding: 10px 10px 8px;">
                    <p>Account</p>
                </div>

                <li class="{{ request()->routeIs('ProfileMember') ? 'active' : '' }}">
                    <a href="{{ route('ProfileMember') }}">
                        <i class="fa fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('transactions') ? 'active' : '' }}">
                    <a href="{{ route('transactions') }}">
                        <i class="fa fa-receipt"></i>
                        <span>Transaction</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('Notifications') ? 'active' : '' }}">
                    <a href="{{ route('Notifications') }}">
                        <i class="fa fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('Settings') ? 'active' : '' }}">
                    <a href="{{ route(" Settings") }}">
                        <i class="fa fa-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('logout') }}">
                        <i class="fa fa-sign-out"></i>
                        <span>Logout</span>
                    </a>
                </li> --}}
                {{-- <div class="menu" style="padding: 10px 10px 8px;">
                    <p>Account</p>
                </div>

                <li class="{{ request()->routeIs('ProfileMember') ? 'active' : '' }}">
                    <a href="{{ route('ProfileMember') }}">
                        <i class="fa fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('logout') }}">
                        <i class="fa fa-sign-out"></i>
                        <span>Logout</span>
                    </a>
                </li> --}}
            </div>

            {{-- <div class="logout">
                <li>
                    <a href="{{ route('logout') }}">
                        <i class="fa fa-sign-out"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </div> --}}

            <div class="account">
                @php $navPhoto = Auth::user()->otherinfo->profile_picture ?? null; @endphp
                <div
                    style="width:40px; height:40px; border-radius:50%; overflow:hidden; background:#EDF0F5; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-weight:600; color:#1E2A4A;">
                    @if($navPhoto)
                        <img src="{{ url('proof/' . $navPhoto) }}" alt=""
                            style="width:100%; height:100%; object-fit:cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}
                    @endif
                </div>
                <div class="first-name">
                    <div class="fullname">
                        <p style="margin: 0">
                            {{ auth()->user()->first_name ?? '' }}
                        </p>
                    </div>
                    <span>{{ $navMemberId }}</span>
                </div>
            </div>
        </ul>
    </div>
</div>