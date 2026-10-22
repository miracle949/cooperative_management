<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile</title>

    {{-- AOS animation link css --}}
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    {{-- css link --}}
    <link rel="stylesheet" href="css_folder/transactions.css?=5">
    <link rel="stylesheet" href="css_folder/loading.css?=12">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/csp-events.js') }}"></script>

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">
</head>

<body>
    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")
        @include("components.sidebar")

        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            <div class="main-parent">
                <div class="main-header">
                    <h3>Transactions</h3>
                    <p>Every entry posted to your share capital, savings and loan accounts</p>
                </div>

                <div class="main-body">

                    <div class="filters">
                        <div class="tab-group">
                            <a href="{{ route('transactions', array_merge(request()->except('type', 'page'), ['type' => 'all'])) }}"
                                class="tab {{ $type === 'all' ? 'active' : '' }}">All</a>
                            <a href="{{ route('transactions', array_merge(request()->except('type', 'page'), ['type' => 'loans'])) }}"
                                class="tab {{ $type === 'loans' ? 'active' : '' }}">Loans</a>
                            <a href="{{ route('transactions', array_merge(request()->except('type', 'page'), ['type' => 'savings'])) }}"
                                class="tab {{ $type === 'savings' ? 'active' : '' }}">Savings</a>
                            <a href="{{ route('transactions', array_merge(request()->except('type', 'page'), ['type' => 'share_capital'])) }}"
                                class="tab {{ $type === 'share_capital' ? 'active' : '' }}">Share Capital</a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('transactions') }}" class="toolbar" id="tx-filter-form">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Search by description or reference no.">
                        </div>
                        <input type="date" class="filter-select" name="date" value="{{ $date }}"
                            data-action="tx-submit-filter">
                        <select class="filter-select " name="status" data-action="tx-submit-filter">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </form>

                    <div class="ledger-page overflow-x-auto">
                        <div class="table-scroll-wrapper">
                            <table class="tx-table table table-scroll m-0">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Reference No.</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="num">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                        <tr>
                                            <td>
                                                <div class="tx-desc-cell">
                                                    <div class="tx-icon {{ $tx['icon'] }}"><i
                                                            class="fa-solid {{ $tx['icon_fa'] }}"></i></div>
                                                    <div class="tx-desc">
                                                        <strong>{{ $tx['title'] }}</strong>
                                                        <span>{{ $tx['subtitle'] }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tx-ref">{{ $tx['reference_no'] }}</td>
                                            <td class="tx-date">{{ $tx['date_display'] }}<br>{{ $tx['time_display'] }}</td>
                                            <td><span
                                                    class="status-chip {{ $tx['status_class'] }}">{{ $tx['status_label'] }}</span>
                                            </td>
                                            <td class="tx-amt {{ $tx['amount'] >= 0 ? 'up' : 'down' }}">
                                                @if($tx['show_amount'] ?? true)
                                                    {{ $tx['amount'] >= 0 ? '+' : '-' }}₱{{ number_format(abs($tx['amount']), 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5"
                                                style="text-align:center; color:#aaa; padding:2rem; font-size:13px;">
                                                <i class="fa fa-inbox"
                                                    style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                                No transactions found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-bottom">
                            <span class="pg-count">Showing {{ $transactions->count() }} of {{ $transactions->total() }}</span>
                        
                            @if($transactions->lastPage() > 1)
                                <div class="page-btns">
                                    @if($transactions->onFirstPage())
                                        <span class="page-btn disabled" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i></span>
                                    @else
                                        <a href="{{ $transactions->previousPageUrl() }}" class="page-btn" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i></a>
                                    @endif
                        
                                    <span class="pg-info">{{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}</span>
                        
                                    @if($transactions->hasMorePages())
                                        <a href="{{ $transactions->nextPageUrl() }}" class="page-btn" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></a>
                                    @else
                                        <span class="page-btn disabled" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ csp_nonce() }}">
        (function () {
            const filterForm = document.getElementById('tx-filter-form');
            const searchInput = filterForm.querySelector('input[name="search"]');
            let searchDebounce;
            let controller = null;

            function buildUrl() {
                const params = new URLSearchParams();
                new FormData(filterForm).forEach(function (value, key) {
                    if (value !== '' && value !== null) params.set(key, value);
                });
                const qs = params.toString();
                return filterForm.getAttribute('action') + (qs ? '?' + qs : '');
            }

            async function loadTransactions(url, push) {
                if (controller) controller.abort();
                controller = new AbortController();

                const ledger = document.querySelector('.ledger-page');
                if (ledger) ledger.style.opacity = '0.5';

                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                        signal: controller.signal
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    const newLedger = doc.querySelector('.ledger-page');
                    const newTabs = doc.querySelector('.filters');
                    const newType = doc.querySelector('#tx-filter-form input[name="type"]');

                    // Not the expected page (e.g. session expired) → fall back to normal navigation
                    if (!newLedger || !newTabs) {
                        window.location.href = url;
                        return;
                    }

                    document.querySelector('.ledger-page').replaceWith(newLedger);
                    document.querySelector('.filters').replaceWith(newTabs);
                    if (newType) filterForm.querySelector('input[name="type"]').value = newType.value;

                    if (push !== false) history.pushState({}, '', url);
                } catch (err) {
                    if (err.name !== 'AbortError') window.location.href = url;
                }
            }

            // Date + status dropdown filters
            var A = window.CSP_actions;
            if (A) {
                A.register('tx-submit-filter', function () {
                    loadTransactions(buildUrl());
                });
            }

            // Enter key in the search box / any form submit
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                loadTransactions(buildUrl());
            });

            // Live search (keeps focus because the form itself is never replaced)
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(function () {
                        loadTransactions(buildUrl());
                    }, 400);
                });
            }

            // Tabs + pagination (event delegation, so it survives content swaps)
            document.addEventListener('click', function (e) {
                const link = e.target.closest('.tab-group a, .page-btns a.page-btn');
                if (!link || !link.href) return;
                e.preventDefault();
                loadTransactions(link.href);
            });

            // Browser back / forward
            window.addEventListener('popstate', function () {
                const p = new URLSearchParams(window.location.search);
                filterForm.querySelector('input[name="type"]').value = p.get('type') || 'all';
                filterForm.querySelector('input[name="search"]').value = p.get('search') || '';
                filterForm.querySelector('input[name="date"]').value = p.get('date') || '';
                filterForm.querySelector('select[name="status"]').value = p.get('status') || 'all';
                loadTransactions(window.location.href, false);
            });
        })();
    </script>

</body>

</html>