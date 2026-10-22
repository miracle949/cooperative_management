<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Receipts Journal (Detailed) - {{ $fromDate }} to {{ $toDate }}</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        thead th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 5px 4px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        tbody td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: top;
        }
        .num {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        .center {
            text-align: center;
        }
        .code-cell {
            text-align: center;
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
        }
        .no-print { display: block; }
        .page-count { text-align: right; }

        @@media print {
            body { padding: 10px 15px; font-size: 9px; }
            thead th { font-size: 8px; padding: 4px 3px; }
            tbody td { padding: 2px 3px; font-size: 8.5px; }
            .header .coop-name { font-size: 12px; }
            .header .title { font-size: 11px; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
        @@page {
            size: landscape;
            margin: 10mm 12mm;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="coop-name">Kingsland Pala-Pala Multi-Purpose Cooperative</div>
        <div class="title">CASH RECEIPTS JOURNAL (Detailed)</div>
        <div class="subtitle">For the Period {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:10%;">Date</th>
                <th style="width:10%;">Ref No.</th>
                <th style="width:20%;">Particulars</th>
                <th style="width:8%;">Code</th>
                <th style="width:14%;">Account Title</th>
                <th style="width:9%;">Debit</th>
                <th style="width:9%;">Credit</th>
                <th style="width:8%;">SL Code</th>
                <th style="width:12%;">SL Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $i => $entry)
            <tr class="{{ $i % 2 === 0 ? 'row-even' : '' }}">
                <td>{{ \Carbon\Carbon::parse($entry['date'])->format('m/d/Y') }}</td>
                <td class="center">{{ $entry['reference_no'] ?? '—' }}</td>
                <td>{{ $entry['particulars'] }}</td>
                <td class="code-cell">{{ $entry['code'] }}</td>
                <td>{{ $entry['account_title'] }}</td>
                <td class="num">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                <td class="num">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                <td class="center">{{ $entry['sl_code'] ?? '—' }}</td>
                <td>{{ $entry['sl_name'] ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:20px;color:#999;">No journal entries found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        @if($entries->isNotEmpty())
        <tfoot>
            <tr class="totals-row">
                <td colspan="5" style="text-align:right;">TOTAL</td>
                <td class="num">₱{{ number_format($totalDebit, 2) }}</td>
                <td class="num">₱{{ number_format($totalCredit, 2) }}</td>
                <td colspan="2"></td>
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
