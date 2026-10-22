@php
    $footerSeminarsComplete = false;
    if (auth()->check()) {
        $footerCompletion = \App\Models\SeminarCompletions_tbl::where('user_id', auth()->id())->first();
        $footerSeminarsComplete = $footerCompletion
            && $footerCompletion->pmes_completed
            && $footerCompletion->fundamentals_completed
            && $footerCompletion->finance_completed;
    }
@endphp

<div class="footer-parent {{ $footerSeminarsComplete ? '' : 'footer-home-only' }}">
        <div class="footer-nav {{ (request()->routeIs('MemberPortal') || !$footerSeminarsComplete) ? 'active' : '' }}">
        <a href="{{ route('MemberPortal') }}" title="Dashboard">
            <i class="fa fa-home"></i>
            <span>Home</span>
        </a>
    </div>

    @if ($footerSeminarsComplete)
        <div class="footer-nav {{ request()->routeIs('LoanApplication') ? 'active' : '' }}">
            <a href="{{ route('LoanApplication') }}" title="Loan Application">
                <i class="fa fa-file"></i>
                <span>Loan</span>
            </a>
        </div>

        <div class="footer-nav {{ request()->routeIs('LoanStatus') ? 'active' : '' }}">
            <a href="{{ route('LoanStatus') }}" title="Loan Status">
                <i class="fa fa-hand-holding-dollar"></i>
                <span>Status</span>
            </a>
        </div>

        <div class="footer-nav {{ request()->routeIs('Financial') ? 'active' : '' }}">
            <a href="{{ route('Financial') }}">
                <i class="fa fa-wallet"></i>
                <span>Financial</span>
            </a>
        </div>
    @endif
</div>