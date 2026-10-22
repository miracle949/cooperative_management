<?php

namespace App\Http\Controllers;

use App\Models\CooperativeTransaction;
use App\Models\lending_program_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\share_capital_transaction_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{   
    public function index(Request $request)
    {
        // Default landing = Daily Report
        if (!$request->hasAny(['view', 'from_date', 'to_date', 'report_type', 'chart', 'export'])) {
            return redirect()->route('reports.daily');
        }

        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->endOfMonth()->format('Y-m-d');
        $fromDate = $this->validDateParam($request, 'from_date', $defaultFrom);
        $toDate = $this->validDateParam($request, 'to_date', $defaultTo);
        $reportType = $request->get('report_type', 'all');
        $chartType = $request->get('chart', 'all');

        $totalDeposits = savings_transaction_tbl::where('type', 'deposit')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->sum('amount') ?? 0;

        $totalWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->sum('amount') ?? 0;

        $loansIssued = lending_program_tbl::where('status', 'Approved')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->sum('lending_amount') ?? 0;

        $loanInterest = lending_program_tbl::where('status', 'Approved')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->sum('total_interest') ?? 0;

        $netIncome = $loanInterest;

        $savingsTrend = [];
        $lendingTrend = [];
        $shareCapitalTrend = [];
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M');
            $monthNum = $month->format('n');
            $year = $month->format('Y');

            $deposits = savings_transaction_tbl::where('type', 'deposit')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('amount') ?? 0;

            $loans = lending_program_tbl::where('status', 'Approved')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('lending_amount') ?? 0;

            $depositCap = share_capital_transaction_tbl::whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
                ->where('status', 'Completed')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('total_amount') ?? 0;

            $withdrawCap = share_capital_transaction_tbl::where('type', 'Withdrawal')
                ->whereIn('status', ['Approved', 'approved'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('total_amount') ?? 0;

            $shareCap = $depositCap - $withdrawCap;

            $savingsTrend[] = round($deposits / 1000, 1);
            $lendingTrend[] = round($loans / 1000, 1);
            $shareCapitalTrend[] = round($shareCap / 1000, 1);
        }

        $savingsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M');
            $monthNum = $month->format('n');
            $year = $month->format('Y');

            $deposits = savings_transaction_tbl::where('type', 'deposit')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('amount') ?? 0;

            $loans = lending_program_tbl::where('status', 'Approved')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->sum('lending_amount') ?? 0;

            $savingsByMonth[$monthName] = [
                'savings' => round($deposits / 1000, 1),
                'loans' => round($loans / 1000, 1),
            ];
        }

        $transactions = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name"),
                DB::raw("'Savings' as category")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('st.created_at', 'desc')
            ->limit(50)
            ->get();

        $deposits = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->where('st.type', 'deposit')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('st.created_at', 'desc')
            ->limit(10)
            ->get();

        $withdrawals = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->where('st.type', 'withdrawal')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('st.created_at', 'desc')
            ->limit(10)
            ->get();

        $loans = lending_program_tbl::with('user')
            ->where('status', 'Approved')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($loan) {
                return [
                    'created_at' => $loan->created_at,
                    'reference_no' => $loan->reference_no,
                    'amount' => $loan->lending_amount,
                    'purpose' => $loan->purpose_loan,
                    'status' => $loan->status,
                    'member_name' => ($loan->user->first_name ?? 'Unknown') . ' ' . ($loan->user->last_name ?? ''),
                ];
            });

        $depositsCount = DB::table('savings_transaction_tbls as st')
            ->where('st.type', 'deposit')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->count();

        $withdrawalsCount = DB::table('savings_transaction_tbls as st')
            ->where('st.type', 'withdrawal')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->count();

        $loansCount = lending_program_tbl::where('status', 'Approved')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->count();

        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportMonthlyCsv($fromDate, $toDate, $transactions, $totalDeposits, $totalWithdrawals, $loansIssued, $netIncome);
        }

        return view('admin_components.reports', compact(
            'fromDate',
            'toDate',
            'reportType',
            'chartType',
            'totalDeposits',
            'totalWithdrawals',
            'loansIssued',
            'netIncome',
            'savingsByMonth',
            'savingsTrend',
            'lendingTrend',
            'shareCapitalTrend',
            'months',
            'transactions',
            'deposits',
            'withdrawals',
            'loans',
            'depositsCount',
            'withdrawalsCount',
            'loansCount'
        ));
    }

    public function daily(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $savingsTransactions = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.id',
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                'st.payment_method',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->whereDate('st.created_at', $date)
            ->orderBy('st.created_at', 'desc')
            ->get();

        $shareCapitalTransactions = DB::table('share_capital_transaction_tbls as sct')
            ->select(
                'sct.id',
                'sct.created_at',
                'sct.reference_no',
                'sct.total_amount',
                'sct.shares',
                'sct.type',
                'sct.payment_method',
                'sct.status',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('share_capital_account_tbls as sca', 'sct.share_capital_account_id', '=', 'sca.id')
            ->leftJoin('users_tbls as u', 'sca.user_id', '=', 'u.id')
            ->whereDate('sct.created_at', $date)
            ->orderBy('sct.created_at', 'desc')
            ->get();

        $loanDisbursements = DB::table('lending_program_tbls as lp')
            ->select(
                'lp.id',
                'lp.created_at',
                'lp.reference_no',
                'lp.lending_amount',
                'lp.lending_type',
                'lp.status',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('users_tbls as u', 'lp.user_id', '=', 'u.id')
            ->whereDate('lp.created_at', $date)
            ->where('lp.status', 'Approved')
            ->orderBy('lp.created_at', 'desc')
            ->get();

        $loanRepayments = DB::table('lending_repayments_tbls as lr')
            ->select(
                'lr.id',
                'lr.created_at',
                'lr.reference_no',
                'lr.amount_paid',
                'lr.payment_method',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('users_tbls as u', 'lr.user_id', '=', 'u.id')
            ->whereDate('lr.created_at', $date)
            ->orderBy('lr.created_at', 'desc')
            ->get();

        $savingsDeposits = $savingsTransactions->where('type', 'deposit');
        $savingsWithdrawals = $savingsTransactions->where('type', 'withdrawal');

        $scDeposits = $shareCapitalTransactions->whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE]);
        $scWithdrawals = $shareCapitalTransactions->where('type', 'Withdrawal');

        $cooperativeTransactions = CooperativeTransaction::whereDate('transaction_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $cooperativeExpenses = $cooperativeTransactions->where('transaction_type', 'expense');
        $cooperativeInvestments = $cooperativeTransactions->where('transaction_type', 'investment');

        $summary = [
            'savings_deposits_total' => $savingsDeposits->sum('amount'),
            'savings_deposits_count' => $savingsDeposits->count(),
            'savings_withdrawals_total' => $savingsWithdrawals->sum('amount'),
            'savings_withdrawals_count' => $savingsWithdrawals->count(),
            'savings_net' => $savingsDeposits->sum('amount') - $savingsWithdrawals->sum('amount'),
            'sc_deposits_total' => $scDeposits->sum('total_amount'),
            'sc_deposits_count' => $scDeposits->count(),
            'sc_withdrawals_total' => $scWithdrawals->sum('total_amount'),
            'sc_withdrawals_count' => $scWithdrawals->count(),
            'sc_net' => $scDeposits->sum('total_amount') - $scWithdrawals->sum('total_amount'),
            'loans_disbursed_total' => $loanDisbursements->sum('lending_amount'),
            'loans_disbursed_count' => $loanDisbursements->count(),
            'repayments_total' => $loanRepayments->sum('amount_paid'),
            'repayments_count' => $loanRepayments->count(),
            'cooperative_expenses_total' => $cooperativeExpenses->sum('amount'),
            'cooperative_expenses_count' => $cooperativeExpenses->count(),
            'cooperative_investments_total' => $cooperativeInvestments->sum('amount'),
            'cooperative_investments_count' => $cooperativeInvestments->count(),
            'cooperative_net' => $cooperativeInvestments->sum('amount') - $cooperativeExpenses->sum('amount'),
            'grand_inflow' => $savingsDeposits->sum('amount') + $scDeposits->sum('total_amount') + $loanRepayments->sum('amount_paid') + $cooperativeInvestments->sum('amount'),
            'grand_outflow' => $savingsWithdrawals->sum('amount') + $scWithdrawals->sum('total_amount') + $loanDisbursements->sum('lending_amount') + $cooperativeExpenses->sum('amount'),
        ];

        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportCsv($date, $savingsTransactions, $shareCapitalTransactions, $loanDisbursements, $loanRepayments, $cooperativeTransactions, $summary);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportPdf($date, $savingsTransactions, $shareCapitalTransactions, $loanDisbursements, $loanRepayments, $cooperativeTransactions, $summary);
        }

        $cooperativeStats = [
            'total_expenses' => CooperativeTransaction::where('transaction_type', 'expense')->sum('amount'),
            'total_investments' => CooperativeTransaction::where('transaction_type', 'investment')->sum('amount'),
        ];

        return view('admin_components.reports', compact(
            'date',
            'savingsTransactions',
            'shareCapitalTransactions',
            'loanDisbursements',
            'loanRepayments',
            'cooperativeTransactions',
            'summary',
            'savingsDeposits',
            'savingsWithdrawals',
            'scDeposits',
            'scWithdrawals',
            'cooperativeExpenses',
            'cooperativeInvestments',
            'cooperativeStats'
        ));
    }

    private function exportCsv($date, $savings, $shareCapital, $loans, $repayments, $cooperativeTransactions, $summary)
    {
        $filename = "daily_report_{$date}.csv";
        $handle = fopen('php://temp', 'w+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ["Daily Transaction Report - {$date}"]);
        fputcsv($handle, []);

        fputcsv($handle, ['SAVINGS TRANSACTIONS']);
        fputcsv($handle, ['Reference #', 'Member', 'Type', 'Amount', 'Payment Method', 'Status']);
        foreach ($savings as $tx) {
            fputcsv($handle, [
                $tx->reference_no ?? 'N/A',
                $tx->member_name ?? 'Unknown',
                ucfirst($tx->type),
                number_format($tx->amount, 2),
                $tx->payment_method ?? 'N/A',
                $tx->status ?? 'Completed',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ["Deposits: {$summary['savings_deposits_count']} tx, ₱" . number_format($summary['savings_deposits_total'], 2)]);
        fputcsv($handle, ["Withdrawals: {$summary['savings_withdrawals_count']} tx, ₱" . number_format($summary['savings_withdrawals_total'], 2)]);
        fputcsv($handle, ['Net: ₱' . number_format($summary['savings_net'], 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['SHARE CAPITAL TRANSACTIONS']);
        fputcsv($handle, ['Reference #', 'Member', 'Type', 'Shares', 'Amount', 'Payment Method', 'Status']);
        foreach ($shareCapital as $tx) {
            fputcsv($handle, [
                $tx->reference_no ?? 'N/A',
                $tx->member_name ?? 'Unknown',
                ucfirst($tx->type),
                $tx->shares ?? 0,
                number_format($tx->total_amount, 2),
                $tx->payment_method ?? 'N/A',
                $tx->status ?? 'Completed',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ["Contributions: {$summary['sc_deposits_count']} tx, ₱" . number_format($summary['sc_deposits_total'], 2)]);
        fputcsv($handle, ["Withdrawals: {$summary['sc_withdrawals_count']} tx, ₱" . number_format($summary['sc_withdrawals_total'], 2)]);
        fputcsv($handle, ['Net: ₱' . number_format($summary['sc_net'], 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['LOAN DISBURSEMENTS']);
        fputcsv($handle, ['Reference #', 'Member', 'Loan Type', 'Amount', 'Status']);
        foreach ($loans as $tx) {
            fputcsv($handle, [
                $tx->reference_no ?? 'N/A',
                $tx->member_name ?? 'Unknown',
                $tx->lending_type ?? 'N/A',
                number_format($tx->lending_amount, 2),
                $tx->status ?? 'N/A',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ["Total Disbursed: {$summary['loans_disbursed_count']} loans, ₱" . number_format($summary['loans_disbursed_total'], 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['LOAN REPAYMENTS']);
        fputcsv($handle, ['Reference #', 'Member', 'Amount Paid', 'Payment Method']);
        foreach ($repayments as $tx) {
            fputcsv($handle, [
                $tx->reference_no ?? 'N/A',
                $tx->member_name ?? 'Unknown',
                number_format($tx->amount_paid, 2),
                $tx->payment_method ?? 'N/A',
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ["Total Repayments: {$summary['repayments_count']} tx, ₱" . number_format($summary['repayments_total'], 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['COOPERATIVE CAPITAL OUTLAYS & EXPENDITURES']);
        fputcsv($handle, ['Description', 'Category', 'Type', 'Amount']);
        foreach ($cooperativeTransactions as $tx) {
            fputcsv($handle, [
                $tx->description ?? 'N/A',
                $tx->category ?? 'N/A',
                ucfirst($tx->transaction_type),
                number_format($tx->amount, 2),
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ["Expenses: {$summary['cooperative_expenses_count']} tx, ₱" . number_format($summary['cooperative_expenses_total'], 2)]);
        fputcsv($handle, ["Investments: {$summary['cooperative_investments_count']} tx, ₱" . number_format($summary['cooperative_investments_total'], 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['DAILY SUMMARY']);
        fputcsv($handle, ['Total Inflow: ₱' . number_format($summary['grand_inflow'], 2)]);
        fputcsv($handle, ['Total Outflow: ₱' . number_format($summary['grand_outflow'], 2)]);
        fputcsv($handle, ['Net Flow: ₱' . number_format($summary['grand_inflow'] - $summary['grand_outflow'], 2)]);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function exportMonthlyCsv($fromDate, $toDate, $transactions, $totalDeposits, $totalWithdrawals, $loansIssued, $netIncome)
    {
        $filename = "monthly_report_{$fromDate}_to_{$toDate}.csv";
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ["Monthly Transaction Report - {$fromDate} to {$toDate}"]);
        fputcsv($handle, []);

        fputcsv($handle, ['SUMMARY']);
        fputcsv($handle, ['Total Deposits', '₱' . number_format($totalDeposits, 2)]);
        fputcsv($handle, ['Total Withdrawals', '₱' . number_format($totalWithdrawals, 2)]);
        fputcsv($handle, ['Loans Issued', '₱' . number_format($loansIssued, 2)]);
        fputcsv($handle, ['Net Income', '₱' . number_format($netIncome, 2)]);
        fputcsv($handle, []);

        fputcsv($handle, ['DETAILED TRANSACTIONS']);
        fputcsv($handle, ['Date', 'Time', 'Reference No.', 'Member', 'Category', 'Amount']);
        foreach ($transactions as $tx) {
            fputcsv($handle, [
                \Carbon\Carbon::parse($tx->created_at)->addHours(8)->format('Y-m-d'),
                \Carbon\Carbon::parse($tx->created_at)->addHours(8)->format('g:i A'),
                $tx->reference_no ?? 'N/A',
                $tx->member_name ?? 'Unknown',
                ucfirst($tx->type),
                number_format($tx->amount, 2),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function exportPdf($date, $savings, $shareCapital, $loans, $repayments, $cooperativeTransactions, $summary)
    {
        $html = view('admin_components.reports_pdf', compact(
            'date',
            'savings',
            'shareCapital',
            'loans',
            'repayments',
            'cooperativeTransactions',
            'summary'
        ))->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    // ═══════════════════════════════════════════════════════════════
    //  Double-Entry Journal Report Methods
    // ═══════════════════════════════════════════════════════════════

    private function getJournalEntries($fromDate, $toDate)
    {
        $entries = collect();

        // 1. Savings Deposits → Debit 1111 Cash / Credit 2111 Savings Deposits
        $savingsDeposits = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->where('st.type', 'deposit')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('st.created_at')
            ->get();

        foreach ($savingsDeposits as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Savings Deposit - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => $tx->amount,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Savings Deposit - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '2111',
                'account_title' => 'SAVING DEPOSITS',
                'debit' => 0,
                'credit' => $tx->amount,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 2. Savings Withdrawals → Debit 2111 Savings Deposits / Credit 1111 Cash
        $savingsWithdrawals = DB::table('savings_transaction_tbls as st')
            ->select(
                'st.created_at',
                'st.reference_no',
                'st.amount',
                'st.type',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('savings_account_tbls as sa', 'st.savings_account_id', '=', 'sa.id')
            ->leftJoin('users_tbls as u', 'sa.user_id', '=', 'u.id')
            ->where('st.type', 'withdrawal')
            ->whereBetween('st.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('st.created_at')
            ->get();

        foreach ($savingsWithdrawals as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Savings Withdrawal - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '2111',
                'account_title' => 'SAVING DEPOSITS',
                'debit' => $tx->amount,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Savings Withdrawal - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => 0,
                'credit' => $tx->amount,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 3. Share Capital Deposits/Subscriptions → Debit 1111 Cash / Credit 3031 SC Build Up
        $scDeposits = DB::table('share_capital_transaction_tbls as sct')
            ->select(
                'sct.created_at',
                'sct.reference_no',
                'sct.total_amount',
                'sct.type',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('share_capital_account_tbls as sca', 'sct.share_capital_account_id', '=', 'sca.id')
            ->leftJoin('users_tbls as u', 'sca.user_id', '=', 'u.id')
            ->whereIn('sct.type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
            ->whereIn('sct.status', ['Completed', 'Approved'])
            ->whereBetween('sct.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('sct.created_at')
            ->get();

        foreach ($scDeposits as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Share Capital ' . $tx->type . ' - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => $tx->total_amount,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Share Capital ' . $tx->type . ' - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '3031',
                'account_title' => 'DEPOSIT FOR SHARE CAPITAL BUILD UP',
                'debit' => 0,
                'credit' => $tx->total_amount,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 4. Share Capital Withdrawals → Debit 3031 SC Build Up / Credit 1111 Cash
        $scWithdrawals = DB::table('share_capital_transaction_tbls as sct')
            ->select(
                'sct.created_at',
                'sct.reference_no',
                'sct.total_amount',
                'sct.type',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('share_capital_account_tbls as sca', 'sct.share_capital_account_id', '=', 'sca.id')
            ->leftJoin('users_tbls as u', 'sca.user_id', '=', 'u.id')
            ->where('sct.type', 'Withdrawal')
            ->whereIn('sct.status', ['Approved', 'approved'])
            ->whereBetween('sct.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('sct.created_at')
            ->get();

        foreach ($scWithdrawals as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Share Capital Withdrawal - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '3031',
                'account_title' => 'DEPOSIT FOR SHARE CAPITAL BUILD UP',
                'debit' => $tx->total_amount,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Share Capital Withdrawal - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => 0,
                'credit' => $tx->total_amount,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 5. Loan Disbursements → Debit 11381 Other Current Receivables / Credit 1111 Cash
        $loanDisbursements = DB::table('lending_program_tbls as lp')
            ->select(
                'lp.created_at',
                'lp.reference_no',
                'lp.lending_amount',
                'lp.lending_type',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('users_tbls as u', 'lp.user_id', '=', 'u.id')
            ->where('lp.status', 'Approved')
            ->whereBetween('lp.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('lp.created_at')
            ->get();

        foreach ($loanDisbursements as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Loan Disbursement (' . ($tx->lending_type ?? 'N/A') . ') - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '11381',
                'account_title' => 'OTHER CURRENT RECEIVABLES',
                'debit' => $tx->lending_amount,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Loan Disbursement (' . ($tx->lending_type ?? 'N/A') . ') - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => 0,
                'credit' => $tx->lending_amount,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 6. Loan Repayments → Debit 1111 Cash / Credit 11381 Other Current Receivables
        $loanRepayments = DB::table('lending_repayments_tbls as lr')
            ->select(
                'lr.created_at',
                'lr.reference_no',
                'lr.amount_paid',
                'u.id as user_id',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as member_name")
            )
            ->leftJoin('users_tbls as u', 'lr.user_id', '=', 'u.id')
            ->whereBetween('lr.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->orderBy('lr.created_at')
            ->get();

        foreach ($loanRepayments as $tx) {
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Loan Repayment - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => $tx->amount_paid,
                'credit' => 0,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
            $entries->push([
                'date' => $tx->created_at,
                'reference_no' => $tx->reference_no,
                'particulars' => 'Loan Repayment - ' . ($tx->member_name ?? 'Unknown'),
                'code' => '11381',
                'account_title' => 'OTHER CURRENT RECEIVABLES',
                'debit' => 0,
                'credit' => $tx->amount_paid,
                'sl_code' => $tx->user_id,
                'sl_name' => $tx->member_name ?? 'Unknown',
            ]);
        }

        // 7. Cooperative Expenses → Debit 40211 Operation Dues / Credit 1111 Cash
        $coopExpenses = CooperativeTransaction::where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->get();

        foreach ($coopExpenses as $tx) {
            $entries->push([
                'date' => $tx->transaction_date,
                'reference_no' => 'COOP-' . $tx->id,
                'particulars' => 'Cooperative Expense - ' . $tx->description,
                'code' => '40211',
                'account_title' => 'OPERATION DUES',
                'debit' => $tx->amount,
                'credit' => 0,
                'sl_code' => null,
                'sl_name' => null,
            ]);
            $entries->push([
                'date' => $tx->transaction_date,
                'reference_no' => 'COOP-' . $tx->id,
                'particulars' => 'Cooperative Expense - ' . $tx->description,
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => 0,
                'credit' => $tx->amount,
                'sl_code' => null,
                'sl_name' => null,
            ]);
        }

        // 8. Cooperative Investments → Debit 4035 Sales-Rice/Egg Trading / Credit 1111 Cash
        $coopInvestments = CooperativeTransaction::where('transaction_type', 'investment')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->get();

        foreach ($coopInvestments as $tx) {
            $entries->push([
                'date' => $tx->transaction_date,
                'reference_no' => 'COOP-' . $tx->id,
                'particulars' => 'Cooperative Investment - ' . $tx->description,
                'code' => '4035',
                'account_title' => 'SALES-RICE / EGG TRADING',
                'debit' => $tx->amount,
                'credit' => 0,
                'sl_code' => null,
                'sl_name' => null,
            ]);
            $entries->push([
                'date' => $tx->transaction_date,
                'reference_no' => 'COOP-' . $tx->id,
                'particulars' => 'Cooperative Investment - ' . $tx->description,
                'code' => '1111',
                'account_title' => 'CASH ON HAND',
                'debit' => 0,
                'credit' => $tx->amount,
                'sl_code' => null,
                'sl_name' => null,
            ]);
        }

        return $entries->sortBy('date')->values();
    }

    public function journalDetailed(Request $request)
    {
        $fromDate = $this->validDateParam($request, 'from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $this->validDateParam($request, 'to_date', now()->endOfMonth()->format('Y-m-d'));

        $entries = $this->getJournalEntries($fromDate, $toDate);

        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        return view('admin_components.journal_detailed', compact(
            'entries',
            'fromDate',
            'toDate',
            'totalDebit',
            'totalCredit'
        ));
    }

    public function journalSummary(Request $request)
    {
        $fromDate = $this->validDateParam($request, 'from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $this->validDateParam($request, 'to_date', now()->endOfMonth()->format('Y-m-d'));

        $entries = $this->getJournalEntries($fromDate, $toDate);

        $grouped = $entries->groupBy('code')->map(function ($rows, $code) {
            return [
                'code' => $code,
                'account_title' => $rows->first()['account_title'],
                'total_debit' => $rows->sum('debit'),
                'total_credit' => $rows->sum('credit'),
            ];
        })->values();

        $grandDebit = $grouped->sum('total_debit');
        $grandCredit = $grouped->sum('total_credit');

        return view('admin_components.journal_summary', compact(
            'grouped',
            'fromDate',
            'toDate',
            'grandDebit',
            'grandCredit'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    //  Statement of Operations
    // ═══════════════════════════════════════════════════════════════

    private function resolveStatementPeriod(Request $request)
    {
        $period = $request->get('period', 'this_year');
        $now = \Carbon\Carbon::now();

        $map = [
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
        ];

        if ($period === 'custom') {
            $fromDate = $this->validDateParam($request, 'from_date', $now->copy()->startOfYear()->format('Y-m-d'));
            $toDate = $this->validDateParam($request, 'to_date', $now->copy()->endOfYear()->format('Y-m-d'));

            if ($fromDate > $toDate) {
                $tmp = $fromDate;
                $fromDate = $toDate;
                $toDate = $tmp;
            }
        } else {
            [$start, $end] = $map[$period] ?? $map['this_year'];
            $fromDate = $start->format('Y-m-d');
            $toDate = $end->format('Y-m-d');
        }

        return compact('period', 'fromDate', 'toDate');
    }

    private function getStatementOfOperationsData($fromDate, $toDate)
    {
        $dateRange = [$fromDate, $toDate . ' 23:59:59'];

        $interestIncome = DB::table('lending_repayments_tbls')
            ->where('status', 'Completed')
            ->whereBetween('created_at', $dateRange)
            ->sum('interest_paid') ?? 0;

        $serviceFeeIncome = DB::table('lending_repayments_tbls')
            ->where('status', 'Completed')
            ->whereBetween('created_at', $dateRange)
            ->sum('service_fee_paid') ?? 0;

        $lateFeeIncome = DB::table('lending_repayments_tbls')
            ->where('status', 'Completed')
            ->whereBetween('created_at', $dateRange)
            ->sum(DB::raw('COALESCE(late_fee, 0)')) ?? 0;

        $totalRevenue = $interestIncome + $serviceFeeIncome + $lateFeeIncome;

        $expensesByCategory = CooperativeTransaction::where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        $totalExpenses = $expensesByCategory->sum('total');

        return compact(
            'fromDate',
            'toDate',
            'interestIncome',
            'serviceFeeIncome',
            'lateFeeIncome',
            'totalRevenue',
            'expensesByCategory',
            'totalExpenses'
        );
    }

    public function statementOfOperations(Request $request)
    {
        $resolved = $this->resolveStatementPeriod($request);
        $period = $resolved['period'];
        $fromDate = $resolved['fromDate'];
        $toDate = $resolved['toDate'];

        $data = $this->getStatementOfOperationsData($fromDate, $toDate);
        $statementView = true;

        $cooperativeStats = [
            'total_expenses' => CooperativeTransaction::where('transaction_type', 'expense')->sum('amount'),
            'total_investments' => CooperativeTransaction::where('transaction_type', 'investment')->sum('amount'),
        ];

        return view('admin_components.reports', array_merge($data, compact('statementView', 'cooperativeStats', 'period')));
    }

    public function statementOfOperationsPrint(Request $request)
    {
        $resolved = $this->resolveStatementPeriod($request);
        $fromDate = $resolved['fromDate'];
        $toDate = $resolved['toDate'];

        $data = $this->getStatementOfOperationsData($fromDate, $toDate);

        return view('admin_components.statement_operations_print', $data);
    }
}
