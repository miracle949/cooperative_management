<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement of Operations - {{ $fromDate }} to {{ $toDate }}</title>
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            color: #000;
            padding: 15px 20px;
            background: #fff;
        }
        .header { margin-bottom: 18px; text-align: center; }
        .header .coop-name { font-size: 14px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .header .title { font-size: 13px; font-weight: 700; margin-top: 4px; }
        .header .subtitle { font-size: 10px; color: #333; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 16px; }
        thead th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 5px 8px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }
        tbody td { border: 1px solid #000; padding: 4px 8px; }
        .num { text-align: right; font-family: 'Courier New', Courier, monospace; }
        .totals-row td { font-weight: 700; background: #e8e8e8; border-top: 2px solid #000; }
        .section-label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            margin-bottom: 4px; letter-spacing: 0.5px;
        }
        .no-print { display: block; }
        .footer {
            margin-top: 14px; padding-top: 6px; border-top: 1px solid #000;
            display: flex; justify-content: space-between; font-size: 8px; color: #555;
        }

        @@media print {
            body { padding: 10px 15px; font-size: 9px; }
            thead th { font-size: 8px; padding: 4px 6px; }
            tbody td { padding: 3px 6px; }
            .header .coop-name { font-size: 12px; }
            .header .title { font-size: 11px; }
            .no-print { display: none !important; }
        }
        @@page { size: portrait; margin: 15mm; }
    </style>
</head>
<body>

    <div class="header">
        <div class="coop-name">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
        <div class="title">STATEMENT OF OPERATIONS</div>
        <div class="subtitle">For the Period {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</div>
    </div>

    <p class="section-label">Revenue / Income</p>
    <table>
        <thead>
            <tr>
                <th style="width:60%; text-align:left;">Income Type</th>
                <th style="width:40%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Interest Income</td>
                <td class="num">₱{{ number_format($interestIncome, 2) }}</td>
            </tr>
            <tr>
                <td>Service Fee Income</td>
                <td class="num">₱{{ number_format($serviceFeeIncome, 2) }}</td>
            </tr>
            <tr>
                <td>Late Fee Income</td>
                <td class="num">₱{{ number_format($lateFeeIncome, 2) }}</td>
            </tr>
            <tr class="totals-row">
                <td>TOTAL REVENUE</td>
                <td class="num">₱{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="section-label">Expenses</p>
    <table>
        <thead>
            <tr>
                <th style="width:45%; text-align:left;">Category</th>
                <th style="width:15%;">Count</th>
                <th style="width:40%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expensesByCategory as $expense)
            <tr>
                <td>{{ $expense->category }}</td>
                <td class="num">{{ $expense->count }}</td>
                <td class="num">₱{{ number_format($expense->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;padding:12px;color:#999;">No expenses recorded for this period.</td>
            </tr>
            @endforelse
            @if($expensesByCategory->isNotEmpty())
            <tr class="totals-row">
                <td>TOTAL EXPENSES</td>
                <td class="num">{{ $expensesByCategory->sum('count') }}</td>
                <td class="num">₱{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <span>Date Printed: {{ now()->format('F d, Y \a\t g:i A') }}</span>
        <span>Page 1 of 1</span>
    </div>

    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button data-action="print" style="padding:8px 24px;font-size:13px;cursor:pointer;background:#1E2A4A;color:#fff;border:none;border-radius:6px;">Print / Save as PDF</button>
        <button data-action="close" style="padding:8px 24px;font-size:13px;cursor:pointer;background:#6c757d;color:#fff;border:none;border-radius:6px;margin-left:8px;">Close</button>
    </div>

    <script nonce="{{ csp_nonce() }}">
        window.onload = function() {
            setTimeout(function() {
                if (window.location.search.includes('print=1')) {
                    window.print();
                }
            }, 500);
        };
    </script>

</body>
</html>
