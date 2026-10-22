@php
    $m = fn($n) => '₱' . number_format((float) $n, 2);
    $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account</title>
    <style>
        /* DejaVu Sans is needed so the ₱ sign renders in the PDF */
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10px; color: #1a1a1a; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #1E2A4A; padding-bottom: 10px; margin-bottom: 14px; }
        .coop { font-size: 14px; font-weight: bold; letter-spacing: 1px; color: #1E2A4A; text-transform: uppercase; }
        .title { font-size: 12px; font-weight: bold; margin-top: 4px; }
        .period { display: inline-block; margin-top: 6px; background: #1E2A4A; color: #fff; padding: 3px 12px; border-radius: 10px; font-size: 10px; }
        .sub { color: #666; font-size: 9px; margin-top: 5px; }
        h4 { font-size: 10.5px; text-transform: uppercase; color: #1E2A4A; margin: 16px 0 5px; letter-spacing: .5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th { background: #1E2A4A; color: #fff; padding: 5px 6px; font-size: 8.5px; text-transform: uppercase; text-align: left; }
        td { border-bottom: 1px solid #e3e6ee; padding: 5px 6px; }
        tr:nth-child(even) td { background: #fafbfd; }
        .num { text-align: right; }
        th.num { text-align: right; }
        .info td { border: 1px solid #e3e6ee; background: #fff; }
        .info td:first-child { width: 28%; background: #f4f6fb; font-weight: bold; color: #555; }
        .total td { font-weight: bold; background: #e9edf6 !important; border-top: 1.5px solid #1E2A4A; }
        .empty { text-align: center; color: #888; padding: 10px; }
        .pos { color: #15803d; }
        .neg { color: #b91c1c; }
        .note { margin-top: 18px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 8px; color: #777; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="coop">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
        <div class="title">STATEMENT OF ACCOUNT</div>
        <div class="period">{{ $periodType === 'monthly' ? 'Monthly Report' : 'Annual Report' }} · {{ $periodLabel }}</div>
        <div class="sub">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</div>
    </div>

    <table class="info">
        <tr><td>Member Name</td><td>{{ $fullName }}</td></tr>
        <tr><td>Membership No.</td><td>{{ $memberId }}</td></tr>
        <tr><td>Category</td><td>{{ $otherinfo->membership_category ?? 'Investor Associate' }}</td></tr>
        <tr><td>Email</td><td>{{ $user->email }}</td></tr>
        <tr><td>Contact No.</td><td>{{ $otherinfo->contact_no ?? '—' }}</td></tr>
        <tr><td>Address</td><td>{{ $otherinfo->present_address ?? '—' }}</td></tr>
    </table>

    <h4>Account Summary</h4>
    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th class="num">Opening</th>
                <th class="num">Credits</th>
                <th class="num">Debits</th>
                <th class="num">Closing</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Share Capital</td>
                <td class="num">{{ $m($sc['opening']) }}</td>
                <td class="num pos">{{ $m($sc['credits']) }}</td>
                <td class="num neg">{{ $m($sc['debits']) }}</td>
                <td class="num">{{ $m($sc['closing']) }}</td>
            </tr>
            <tr>
                <td>Regular Savings</td>
                <td class="num">{{ $m($sv['opening']) }}</td>
                <td class="num pos">{{ $m($sv['credits']) }}</td>
                <td class="num neg">{{ $m($sv['debits']) }}</td>
                <td class="num">{{ $m($sv['closing']) }}</td>
            </tr>
            <tr class="total">
                <td>Total Equity</td>
                <td class="num">{{ $m($sc['opening'] + $sv['opening']) }}</td>
                <td class="num">{{ $m($sc['credits'] + $sv['credits']) }}</td>
                <td class="num">{{ $m($sc['debits'] + $sv['debits']) }}</td>
                <td class="num">{{ $m($sc['closing'] + $sv['closing']) }}</td>
            </tr>
        </tbody>
    </table>

    <h4>Share Capital Transactions</h4>
    <table>
        <thead><tr><th>Date</th><th>Description</th><th>Reference</th><th class="num">Amount</th></tr></thead>
        <tbody>
            @forelse($sc['rows'] as $r)
                <tr>
                    <td>{{ $r['date']->format('M d, Y') }}</td>
                    <td>{{ $r['description'] }}</td>
                    <td>{{ $r['reference'] }}</td>
                    <td class="num {{ $r['amount'] >= 0 ? 'pos' : 'neg' }}">{{ $r['amount'] >= 0 ? '+' : '-' }} {{ $m(abs($r['amount'])) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No share capital transactions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>Savings Transactions</h4>
    <table>
        <thead><tr><th>Date</th><th>Description</th><th>Reference</th><th class="num">Amount</th></tr></thead>
        <tbody>
            @forelse($sv['rows'] as $r)
                <tr>
                    <td>{{ $r['date']->format('M d, Y') }}</td>
                    <td>{{ $r['description'] }}</td>
                    <td>{{ $r['reference'] }}</td>
                    <td class="num {{ $r['amount'] >= 0 ? 'pos' : 'neg' }}">{{ $r['amount'] >= 0 ? '+' : '-' }} {{ $m(abs($r['amount'])) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No savings transactions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>Loan Repayments Made</h4>
    <table>
        <thead><tr><th>Date</th><th>Loan</th><th>Installment</th><th>Reference</th><th class="num">Amount Paid</th></tr></thead>
        <tbody>
            @forelse($repayments as $r)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($r->payment_date)->format('M d, Y') }}</td>
                    <td>{{ $r->lending_type }} ({{ $r->loan_ref }})</td>
                    <td>#{{ $r->payment_number }}</td>
                    <td>{{ $r->reference_no ?? '—' }}</td>
                    <td class="num">{{ $m($r->amount_paid) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No loan repayments in this period.</td></tr>
            @endforelse
            @if($repayments->count())
                <tr class="total"><td colspan="4">Total Repaid</td><td class="num">{{ $m($repayments->sum('amount_paid')) }}</td></tr>
            @endif
        </tbody>
    </table>

    <h4>Active Loans (balance as of {{ $generatedAt->format('M d, Y') }})</h4>
    <table>
        <thead><tr><th>Reference</th><th>Type</th><th class="num">Loan Amount</th><th class="num">Monthly</th><th class="num">Outstanding</th></tr></thead>
        <tbody>
            @forelse($loans as $l)
                <tr>
                    <td>{{ $l->reference_no }}</td>
                    <td>{{ $l->lending_type }}</td>
                    <td class="num">{{ $m($l->lending_amount) }}</td>
                    <td class="num">{{ $m($l->monthly_payment) }}</td>
                    <td class="num">{{ $m($l->remaining_balance ?? $l->total_payment) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No active loans.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="note">
        Computer-generated statement · Generated {{ $generatedAt->format('M d, Y g:i A') }} ·
        Kingsland Pala-Pala Multi-Purpose Cooperative
    </div>
</body>
</html>