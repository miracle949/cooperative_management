<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - {{ $date }}</title>
    <script src="{{ asset('js/csp-events.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a1a2e;
            padding: 30px;
            background: #fff;
        }
        .report-header {
            text-align: center;
            border-bottom: 3px double #1a1a2e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .report-header h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .report-header .sub {
            font-size: 13px;
            color: #555;
        }
        .report-header .date-display {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin-top: 6px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .section-title.savings {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .section-title.share-capital {
            background: #e3f2fd;
            color: #1565c0;
        }
        .section-title.loans {
            background: #fff3e0;
            color: #e65100;
        }
        .section-title.repayments {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .section-title.cooperative {
            background: #e8eaf6;
            color: #283593;
        }
        .summary-card {
            background: #f8f9fa;
            border: 2px solid #1a1a2e;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        .summary-card h3 {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }
        .summary-item .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
        }
        .summary-item .value {
            font-size: 20px;
            font-weight: 700;
        }
        .summary-item .value.inflow {
            color: #2e7d32;
        }
        .summary-item .value.outflow {
            color: #c62828;
        }
        .summary-item .value.net.positive {
            color: #2e7d32;
        }
        .summary-item .value.net.negative {
            color: #c62828;
        }
        .net-breakdown {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px dashed #ccc;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
            font-size: 13px;
        }
        .net-breakdown .bd-item .bd-label {
            font-weight: 600;
            color: #555;
        }
        .table {
            font-size: 13px;
            margin-bottom: 0;
        }
        .table th {
            background: #f1f3f5;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom-width: 2px;
            white-space: nowrap;
        }
        .table td, .table th {
            padding: 8px 10px;
            vertical-align: middle;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .badge {
            font-size: 11px;
            padding: 3px 8px;
        }
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-warning {
            background: #fff8e1;
            color: #f57f17;
        }
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        .badge-info {
            background: #e3f2fd;
            color: #1565c0;
        }
        .mb-4 {
            margin-bottom: 24px;
        }
        .mb-3 {
            margin-bottom: 16px;
        }
        .mt-3 {
            margin-top: 16px;
        }
        .text-muted {
            color: #6c757d;
        }
        .section-summary {
            font-size: 13px;
            font-weight: 500;
            padding: 8px 0 4px;
        }
        .section-summary span {
            margin-right: 20px;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .no-print {
            display: block;
        }

        @@media print {
            @page {
                size: landscape;
                margin: 12mm;
            }
            body {
                padding: 0;
                font-size: 11px;
            }
            .no-print {
                display: none !important;
            }
            .report-header h1 {
                font-size: 18px;
            }
            .table th {
                background: #f1f3f5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .section-title.savings,
            .section-title.share-capital,
            .section-title.loans,
            .section-title.repayments,
            .section-title.cooperative {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-card {
                border: 1.5px solid #333;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table td, .table th {
                padding: 5px 7px;
            }
            .mb-4 {
                margin-bottom: 14px;
            }
            .section-summary {
                font-size: 11px;
            }
            .summary-grid {
                gap: 10px;
            }
            .summary-item .value {
                font-size: 17px;
            }
        }
    </style>
</head>
<body>

    <div class="report-header">
        <h1>Kingsland Pala-Pala Cooperative</h1>
        <div class="sub">Daily Transaction Report</div>
        <div class="date-display">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</div>
    </div>

    <div class="mb-4">
        <div class="section-title savings">Savings Transactions</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Reference No.</th>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($savings as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                    <td class="text-muted">{{ $tx->reference_no ?? 'N/A' }}</td>
                    <td>{{ $tx->member_name ?? 'Unknown' }}</td>
                    <td>
                        @if($tx->type === 'deposit')
                            <span class="badge badge-success">Deposit</span>
                        @else
                            <span class="badge badge-danger">Withdrawal</span>
                        @endif
                    </td>
                    <td>₱{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->payment_method ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No savings transactions on this date.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="section-summary">
            <span>Deposits: <strong>{{ $summary['savings_deposits_count'] }}</strong> transactions — ₱{{ number_format($summary['savings_deposits_total'], 2) }}</span>
            <span>Withdrawals: <strong>{{ $summary['savings_withdrawals_count'] }}</strong> transactions — ₱{{ number_format($summary['savings_withdrawals_total'], 2) }}</span>
            <span>Net: <strong>₱{{ number_format($summary['savings_net'], 2) }}</strong></span>
        </div>
    </div>

    <div class="mb-4">
        <div class="section-title share-capital">Share Capital Transactions</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Reference No.</th>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Shares</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shareCapital as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                    <td class="text-muted">{{ $tx->reference_no ?? 'N/A' }}</td>
                    <td>{{ $tx->member_name ?? 'Unknown' }}</td>
                    <td>
                        @if(in_array($tx->type, ['Deposit', 'Subscription']))
                            <span class="badge badge-success">{{ $tx->type }}</span>
                        @else
                            <span class="badge badge-danger">{{ $tx->type }}</span>
                        @endif
                    </td>
                    <td>{{ $tx->shares ?? 0 }}</td>
                    <td>₱{{ number_format($tx->total_amount, 2) }}</td>
                    <td>{{ $tx->payment_method ?? 'N/A' }}</td>
                    <td>
                        @if(in_array($tx->status, ['Completed', 'Approved']))
                            <span class="badge badge-success">{{ $tx->status }}</span>
                        @elseif($tx->status === 'Pending')
                            <span class="badge badge-warning">Pending</span>
                        @else
                            <span class="badge badge-danger">{{ $tx->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No share capital transactions on this date.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="section-summary">
            <span>Contributions: <strong>{{ $summary['sc_deposits_count'] }}</strong> transactions — ₱{{ number_format($summary['sc_deposits_total'], 2) }}</span>
            <span>Withdrawals: <strong>{{ $summary['sc_withdrawals_count'] }}</strong> transactions — ₱{{ number_format($summary['sc_withdrawals_total'], 2) }}</span>
            <span>Net: <strong>₱{{ number_format($summary['sc_net'], 2) }}</strong></span>
        </div>
    </div>

    <div class="mb-4">
        <div class="section-title loans">Loan Disbursements</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Reference No.</th>
                    <th>Member</th>
                    <th>Loan Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                    <td class="text-muted">{{ $tx->reference_no ?? 'N/A' }}</td>
                    <td>{{ $tx->member_name ?? 'Unknown' }}</td>
                    <td>{{ $tx->lending_type ?? 'N/A' }}</td>
                    <td>₱{{ number_format($tx->lending_amount, 2) }}</td>
                    <td><span class="badge badge-success">{{ $tx->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No loan disbursements on this date.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="section-summary">
            <span>Total Disbursed: <strong>{{ $summary['loans_disbursed_count'] }}</strong> loans — ₱{{ number_format($summary['loans_disbursed_total'], 2) }}</span>
        </div>
    </div>

    <div class="mb-4">
        <div class="section-title repayments">Loan Repayments</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Reference No.</th>
                    <th>Member</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repayments as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</td>
                    <td class="text-muted">{{ $tx->reference_no ?? 'N/A' }}</td>
                    <td>{{ $tx->member_name ?? 'Unknown' }}</td>
                    <td>₱{{ number_format($tx->amount_paid, 2) }}</td>
                    <td>{{ $tx->payment_method ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No loan repayments on this date.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="section-summary">
            <span>Total Repayments: <strong>{{ $summary['repayments_count'] }}</strong> tx — ₱{{ number_format($summary['repayments_total'], 2) }}</span>
        </div>
    </div>

    <div class="mb-4">
        <div class="section-title cooperative">Cooperative Capital Outlays & Expenditures</div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cooperativeTransactions as $tx)
                <tr>
                    <td>{{ $tx->description ?? 'N/A' }}</td>
                    <td>{{ $tx->category ?? 'N/A' }}</td>
                    <td>
                        @if($tx->transaction_type === 'expense')
                            <span class="badge badge-danger">Expense</span>
                        @else
                            <span class="badge badge-success">Investment</span>
                        @endif
                    </td>
                    <td>₱{{ number_format($tx->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No cooperative transactions on this date.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="section-summary">
            <span>Expenses: <strong>{{ $summary['cooperative_expenses_count'] }}</strong> tx — ₱{{ number_format($summary['cooperative_expenses_total'], 2) }}</span>
            <span>Investments: <strong>{{ $summary['cooperative_investments_count'] }}</strong> tx — ₱{{ number_format($summary['cooperative_investments_total'], 2) }}</span>
            <span>Net: <strong>₱{{ number_format($summary['cooperative_net'], 2) }}</strong></span>
        </div>
    </div>

    <div class="summary-card">
        <h3>Daily Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Inflow</div>
                <div class="value inflow">₱{{ number_format($summary['grand_inflow'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Outflow</div>
                <div class="value outflow">₱{{ number_format($summary['grand_outflow'], 2) }}
                    <span style="font-size:11px;display:block;font-weight:400;color:#666;">inc. coop expenses</span>
                </div>
            </div>
            <div class="summary-item">
                <div class="label">Net Flow</div>
                @php $netFlow = $summary['grand_inflow'] - $summary['grand_outflow']; @endphp
                <div class="value net {{ $netFlow >= 0 ? 'positive' : 'negative' }}">₱{{ number_format($netFlow, 2) }}</div>
            </div>
        </div>
        <div class="net-breakdown">
            <div class="bd-item">
                <div class="bd-label">Savings Net</div>
                <div>₱{{ number_format($summary['savings_net'], 2) }}</div>
            </div>
            <div class="bd-item">
                <div class="bd-label">Share Capital Net</div>
                <div>₱{{ number_format($summary['sc_net'], 2) }}</div>
            </div>
            <div class="bd-item">
                <div class="bd-label">Loans - Repayments</div>
                <div>₱{{ number_format($summary['repayments_total'] - $summary['loans_disbursed_total'], 2) }}</div>
            </div>
            <div class="bd-item">
                <div class="bd-label">Cooperative Net</div>
                <div>₱{{ number_format($summary['cooperative_net'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="no-print text-center mt-3">
        <button class="btn btn-primary" data-action="print">Print / Save as PDF</button>
        <button class="btn btn-secondary" data-action="close">Close</button>
    </div>

    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t g:i A') }} &mdash; Kingsland Pala-Pala Cooperative Management System
    </div>

</body>
</html>
