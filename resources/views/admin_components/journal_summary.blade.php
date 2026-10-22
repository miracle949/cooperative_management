<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Summary - {{ $fromDate }} to {{ $toDate }}</title>
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
        .header {
            margin-bottom: 14px;
        }
        .header .coop-name {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .title {
            font-size: 13px;
            font-weight: 700;
            margin-top: 2px;
        }
        .header .subtitle {
            font-size: 10px;
            color: #333;
            margin-top: 1px;
        }
        .balanced-metrics {
            margin-bottom: 14px;
            padding: 8px 12px;
            border: 1px solid #000;
            background: #f5f5f5;
            font-size: 11px;
            font-weight: 600;
        }
        .balanced-metrics .line {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 6px 6px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }
        tbody td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .num {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        .code-title {
            font-weight: 600;
        }
        tr.row-even {
            background: #f7f7f7;
        }
        .footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #555;
        }
        .totals-row td {
            font-weight: 700;
            background: #e8e8e8;
            border-top: 2px solid #000;
            font-size: 11px;
        }
        .no-print { display: block; }
        .page-count { text-align: right; }

        @@media print {
            body { padding: 10px 15px; font-size: 9px; }
            thead th { font-size: 8px; padding: 5px 4px; }
            tbody td { padding: 3px 5px; font-size: 9px; }
            .header .coop-name { font-size: 12px; }
            .header .title { font-size: 11px; }
            .balanced-metrics { font-size: 10px; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
        @@page {
            size: portrait;
            margin: 12mm 15mm;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="coop-name">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
        <div class="title">JOURNAL SUMMARY</div>
        <div class="subtitle">For the Period {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</div>
    </div>

    <div class="balanced-metrics">
        <div class="line">Total DEBIT:   ₱ {{ number_format($grandDebit, 2) }}</div>
        <div class="line">Total CREDIT:  ₱ {{ number_format($grandCredit, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40%;">Account Code &amp; Title</th>
                <th style="width:30%;">Total DEBIT</th>
                <th style="width:30%;">Total CREDIT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grouped as $i => $row)
            <tr class="{{ $i % 2 === 0 ? 'row-even' : '' }}">
                <td class="code-title">{{ $row['code'] }} — {{ $row['account_title'] }}</td>
                <td class="num">{{ $row['total_debit'] > 0 ? '₱' . number_format($row['total_debit'], 2) : '—' }}</td>
                <td class="num">{{ $row['total_credit'] > 0 ? '₱' . number_format($row['total_credit'], 2) : '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;padding:20px;color:#999;">No journal entries found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        @if($grouped->isNotEmpty())
        <tfoot>
            <tr class="totals-row">
                <td style="text-align:right;">GRAND TOTAL</td>
                <td class="num">₱{{ number_format($grandDebit, 2) }}</td>
                <td class="num">₱{{ number_format($grandCredit, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <span>Date Printed: {{ now()->format('F d, Y \a\t g:i A') }}</span>
        <span class="page-count">Page 1 of 1</span>
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
