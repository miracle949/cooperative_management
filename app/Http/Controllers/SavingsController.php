<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\lending_repayments_tbl;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\SavingsInterestSetting;
use App\Models\share_capital_account_tbl;
use App\Models\share_capital_transaction_tbl;
use App\Models\Users_tbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    /**
     * Generate a unique reference number.
     * Format: SAV-DEP-20260326-A3F9 or SAV-WDR-20260326-B7K2
     */
    private function generateReferenceNo(string $type): string
    {
        $prefix = $type === 'deposit' ? 'SAV-DEP' : 'SAV-WDR';
        $date = Carbon::today()->format('Ymd');

        do {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $refNo = "{$prefix}-{$date}-{$random}";
        } while (savings_transaction_tbl::where('reference_no', $refNo)->exists());

        return $refNo;
    }

    /**
     * Show the savings page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        // ★ NEW: search/date filters for transaction history
        $ref = trim((string) $request->query('ref', ''));
        $date = $request->query('date', '');
        $status = strtolower(trim((string) $request->query('status', 'all')));

        // ★ NEW: growth chart year selector
        $growthYear = (int) $request->query('growth_year', Carbon::now()->year);

        // Get or create savings account
        $savingsAccount = savings_account_tbl::where('user_id', $user->id)->first();

        if (! $savingsAccount) {
            $savingsAccount = savings_account_tbl::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'status' => 'active',
                'opened_at' => Carbon::today(),
            ]);
        }

        // ── Savings Balance ──
        $regularSavingsBalance = $this->computeSavingsBalance($savingsAccount->id);
        $totalSavingsBalance = $regularSavingsBalance;

        // Regular Savings rate + frequency — from the new interest settings table
        $sirSettings = \App\Models\SavingsInterestSetting::getOrCreate();
        $regularSavingsRate = (float) $sirSettings->annual_rate;
        $regularSavingsFrequency = $sirSettings->frequency_label;

        // Estimated interest accrued this period, prorated by days elapsed.
        // Actual crediting happens via the scheduled SavingsInterestService job.
        $frequencyDivisor = $sirSettings->frequency_divisor;
        $periodRate = $regularSavingsRate / 100 / $frequencyDivisor;

        // Estimate how far into the current period we are (prorate fraction)
        $now = Carbon::now();
        $periodFraction = match ($sirSettings->release_frequency) {
            'monthly' => $now->day / $now->daysInMonth,
            'quarterly' => $daysElapsedInQuarter = Carbon::create($now->year, ((intdiv($now->month - 1, 3)) * 3) + 1, 1)->diffInDays($now) + 1,
            'semi-annual' => $halfStartMonth = $now->month < 7 ? 1 : 7,
            default => 1,
        };
        if ($sirSettings->release_frequency === 'semi-annual') {
            $halfStart = Carbon::create($now->year, $halfStartMonth, 1)->startOfDay();
            $periodFraction = $halfStart->diffInDays($now) + 1;
            $periodDays = 182;
            $periodFraction = $periodFraction / $periodDays;
        } elseif ($sirSettings->release_frequency === 'annual') {
            $periodFraction = $now->dayOfYear / 365;
        } elseif ($sirSettings->release_frequency === 'monthly') {
            $periodFraction = $now->day / $now->daysInMonth;
        } else {
            // quarterly
            $qStartMonth = ((intdiv($now->month - 1, 3)) * 3) + 1;
            $qStart = Carbon::create($now->year, $qStartMonth, 1)->startOfDay();
            $qDays = $qStart->diffInDays($qStart->copy()->addMonths(3)->subDay()) + 1;
            $periodFraction = ($qStart->diffInDays($now) + 1) / $qDays;
        }

        $estimatedQuarterInterest = round(
            $regularSavingsBalance * $periodRate * $periodFraction,
            2
        );

        // List of years the member actually has transactions in (always includes current year)
        $availableGrowthYears = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->selectRaw('DISTINCT YEAR(transaction_date) as yr')
            ->pluck('yr')
            ->push(Carbon::now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        $isCurrentYear = $growthYear === Carbon::now()->year;

        if ($isCurrentYear) {
            // Rolling last 6 months ending this month
            $growthStart = Carbon::now()->startOfMonth()->subMonths(5);
            $growthMonths = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));
        } else {
            // Full calendar year Jan–Dec of the selected year
            $growthStart = Carbon::createFromDate($growthYear, 1, 1)->startOfMonth();
            $growthMonths = collect(range(0, 11))->map(fn ($i) => Carbon::createFromDate($growthYear, 1, 1)->addMonths($i));
        }

        $growthTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->where('transaction_date', '>=', $growthStart)
            ->when(! $isCurrentYear, fn ($q) => $q->whereYear('transaction_date', $growthYear))
            ->whereIn('type', ['deposit', 'withdrawal'])
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->get()
            ->groupBy(fn ($tx) => Carbon::parse($tx->transaction_date)->format('Y-m'));

        $savingsGrowth = collect();
        foreach ($growthMonths as $month) {
            $key = $month->format('Y-m');
            $monthTxs = $growthTxs->get($key, collect());

            $net = $monthTxs->sum(fn ($tx) => $tx->type === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

            $savingsGrowth->push([
                'label' => $month->format('M'),
                'net' => $net,
                'is_current' => $month->isSameMonth(Carbon::now()),
            ]);
        }

        $maxGrowth = $savingsGrowth->max(fn ($m) => max($m['net'], 0)) ?: 1;

        $savingsGrowth = $savingsGrowth->map(function ($m) use ($maxGrowth) {
            $m['height_percent'] = $m['net'] > 0
                ? max(6, round(($m['net'] / $maxGrowth) * 78))
                : 4;

            return $m;
        });

        $hasShareCapital = \Illuminate\Support\Facades\DB::table('share_capital_account_tbls')
            ->where('user_id', $user->id)
            ->where('status', 'Active')
            ->where('total_shares', '>', 0)
            ->exists();

        // if (!$savingsAccount) {
        //     $savingsAccount = savings_account_tbl::create([
        //         'user_id' => $user->id,
        //         'balance' => 0.00,
        //         'status' => 'active',
        //         'opened_at' => Carbon::today(),
        //     ]);
        // }

        // ★ NEW: type filter — all / deposit / withdrawal / interest_credit
        $type = $request->query('type', 'all');

        $transactionsQuery = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->whereIn('type', ['deposit', 'withdrawal', ShareCapital::CONVERSION_TYPE])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if (in_array($type, ['deposit', 'withdrawal'])) {
            $transactionsQuery->where('type', $type);
        }

        // ★ NEW: filter by reference no.
        if ($ref !== '') {
            $transactionsQuery->where('reference_no', 'like', '%'.$ref.'%');
        }

        // ★ NEW: filter by specific date
        if ($status !== 'all') {
            $transactionsQuery->where('status', $status);
        }

        // ★ NEW: 10 per page, keeps ?type=/?ref=/?date= on every pagination link automatically
        $transactions = $transactionsQuery->paginate(10)->withQueryString();

        $totalMonths = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->groupByRaw("DATE_FORMAT(transaction_date, '%Y-%m')")
            ->count();

        $monthlyAverage = $totalMonths > 0
            ? $regularSavingsBalance / $totalMonths
            : 0;

        $lastUpdated = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->orderBy('transaction_date', 'desc')
            ->value('transaction_date');

        $lastUpdated = $lastUpdated
            ? Carbon::parse($lastUpdated)->diffForHumans()
            : 'No transactions yet';

        $monthsActive = (int) ceil(
            Carbon::parse($savingsAccount->opened_at)->floatDiffInMonths(Carbon::today())
        );

        $availableStatuses = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->whereNotNull('status')
            ->pluck('status')
            ->map(fn ($s) => ucfirst($s))
            ->unique()
            ->sortBy(fn ($s) => strtolower($s))
            ->values();

        // The QR the admin uploaded in Settings → Payment Methods Management
        $gcashPaymentMethod = \App\Models\PaymentMethod::where('method_name', 'GCash')
            ->where('is_active', true)
            ->first();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        return view(
            'members_components.savings',
            [
                'username' => $username,
                'email' => $email,
            ],
            compact(
                'savingsAccount',
                'transactions',
                'type',
                'ref',
                'date',
                'status',              // ← ADD THIS
                'availableStatuses',   // ← ADD THIS
                'growthYear',
                'availableGrowthYears',
                'totalMonths',
                'monthlyAverage',
                'lastUpdated',
                'monthsActive',
                'hasShareCapital',
                'regularSavingsBalance',
                'totalSavingsBalance',
                'regularSavingsRate',
                'regularSavingsFrequency',
                'savingsGrowth',
                'estimatedQuarterInterest',
                'gcashPaymentMethod',
                'paymentMethods'
            )
        );
    }

    /**
     * Handle deposit.
     */
    public function deposit(Request $request)
    {

        $activeMethods = \App\Models\PaymentMethod::where('is_active', true)
            ->pluck('method_name')
            ->map(fn ($m) => strtolower(trim($m)))
            ->toArray();

        $hasQr = \App\Models\PaymentMethod::where('method_name', $request->payment_method)
            ->where('has_qr_code', true)
            ->exists();

        $rules = [
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
            'payment_method' => ['required', 'string', \Illuminate\Validation\Rule::in($activeMethods)],
        ];

        if ($hasQr) {
            $rules['gcash_proof'] = 'required|image|mimes:jpg,jpeg,png|max:5120';
            $rules['gcash_reference_no'] = 'required|string|size:13';
        }

        $request->validate($rules);

        $gcashProofPath = $request->hasFile('gcash_proof')
            ? $request->file('gcash_proof')->store('documents/gcash_proofs', 'public')
            : null;

        $user = Auth::user();
        $savingsAccount = savings_account_tbl::where('user_id', $user->id)->firstOrFail();
        $referenceNo = $this->generateReferenceNo('deposit');

        $scAccount = share_capital_account_tbl::where('user_id', $user->id)->first();
        $hasShareCapital = false;

        if ($scAccount) {
            [, $currentShares] = (new ShareCapital)->computeBalanceAndShares($scAccount);
            $hasShareCapital = $currentShares > 0;
        }

        if (! $hasShareCapital) {
            return redirect()->route('Financial', ['tab' => 'savings'])
                ->with('error', 'You must have an active Share Capital account before you can deposit or withdraw savings.');
        }

        if ($hasQr && $request->gcash_reference_no) {
            $refExists = savings_transaction_tbl::where('gcash_reference_no', $request->gcash_reference_no)
                    ->where('status', '!=', 'voided')
                    ->exists()
                || share_capital_transaction_tbl::where('gcash_reference_no', $request->gcash_reference_no)
                    ->where('status', '!=', 'voided')
                    ->exists()
                || lending_repayments_tbl::where('gcash_reference_no', $request->gcash_reference_no)
                    ->where('status', '!=', 'voided')
                    ->exists();
            if ($refExists) {
                return redirect()->route('Financial', ['tab' => 'savings'])
                    ->with('error', 'This reference number has already been used for a transaction.');
            }
        }

        // Balance is NOT touched here — it only changes once an admin approves this
        // transaction. balance_after reflects the current (unchanged) balance so
        // the receipt/history row is accurate while the request is still pending.
        savings_transaction_tbl::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'gcash_number' => $request->gcash_number,
            'gcash_reference_no' => $hasQr ? $request->gcash_reference_no : null,
            'gcash_proof_path' => $gcashProofPath,
            'balance_after' => $savingsAccount->balance,
            'note' => $request->note,
            'reference_no' => $referenceNo,
            'transaction_date' => Carbon::today(),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        AuditLog::log(
            'Member Savings Deposit Request',
            "Requested deposit of ₱{$request->amount} to savings, pending approval (Ref: {$referenceNo})",
            'savings',
            $savingsAccount->id
        );

        $memberUser = Auth::user();
        $memberName = trim(($memberUser->first_name ?? '').' '.($memberUser->last_name ?? '')) ?: 'Member';

        return redirect()->route('Financial', ['tab' => 'savings'])
            ->with('deposit_success', true)
            ->with('deposit_amount', $request->amount)
            ->with('deposit_reference', $referenceNo)
            ->with('deposit_balance', $savingsAccount->balance)
            ->with('deposit_pending', true)
            ->with('deposit_member', $memberName)
            ->with('deposit_method', ucfirst($request->payment_method));
    }

    /**
     * Handle withdrawal.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
            'gcash_number' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $savingsAccount = savings_account_tbl::where('user_id', $user->id)->firstOrFail();

        $scAccount = share_capital_account_tbl::where('user_id', $user->id)->first();
        $hasShareCapital = false;

        if ($scAccount) {
            [, $currentShares] = (new ShareCapital)->computeBalanceAndShares($scAccount);
            $hasShareCapital = $currentShares > 0;
        }

        if (! $hasShareCapital) {
            return redirect()->route('Financial', ['tab' => 'savings'])
                ->with('error', 'You must have an active Share Capital account before you can deposit or withdraw savings.');
        }

        $availableBalance = $this->computeSavingsBalance($savingsAccount->id);

        if ($request->amount > $availableBalance) {
            return back()->withErrors(['amount' => 'Insufficient balance. Available: ₱ '.number_format($availableBalance, 2)]);
        }

        $referenceNo = $this->generateReferenceNo('withdrawal');

        savings_transaction_tbl::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'payment_method' => 'gcash',
            'gcash_number' => $request->gcash_number,
            'balance_after' => $savingsAccount->balance,
            'note' => $request->note,
            'reference_no' => $referenceNo,
            'transaction_date' => Carbon::today(),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        AuditLog::log(
            'Member Savings Withdrawal Request',
            "Requested withdrawal of ₱{$request->amount} from savings, pending disbursement via GCash to {$request->gcash_number} (Ref: {$referenceNo})",
            'savings',
            $savingsAccount->id
        );

        $memberName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Member';

        return redirect()->route('Financial', ['tab' => 'savings'])
            ->with('withdraw_success', true)
            ->with('withdraw_amount', $request->amount)
            ->with('withdraw_reference', $referenceNo)
            ->with('withdraw_balance', $savingsAccount->balance)
            ->with('withdraw_pending', true)
            ->with('withdraw_member', $memberName)
            ->with('withdraw_method', 'GCash');
    }

    /**
     * Computes the Regular Savings balance purely from completed transactions,
     * and syncs it back to the stored balance column so the database stays
     * consistent with what's displayed (e.g. in phpMyAdmin, exports, admin
     * screens) instead of drifting whenever a transaction's status changes.
     */
    public function computeSavingsBalance($savingsAccountId): float
    {
        $credits = savings_transaction_tbl::where('savings_account_id', $savingsAccountId)
            ->whereIn('type', ['deposit', 'interest_credit'])
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->sum('amount');

        $debits = savings_transaction_tbl::where('savings_account_id', $savingsAccountId)
            ->where(function ($q) {
                $q->where('type', 'withdrawal')
                  ->orWhere('type', ShareCapital::CONVERSION_TYPE);
            })
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->sum('amount');

        $computedBalance = (float) $credits - (float) $debits;

        // Keep the stored column in sync so every other place that still reads
        // ->balance directly (admin views, exports, receipts) shows the same figure.
        savings_account_tbl::where('id', $savingsAccountId)
            ->where('balance', '!=', $computedBalance)
            ->update(['balance' => $computedBalance]);

        return $computedBalance;
    }

    public function downloadReceipt(string $referenceNo, Request $request)
    {
        $user = Auth::user();

        $tx = savings_transaction_tbl::where('reference_no', $referenceNo)->first();

        abort_if(! $tx, 404, 'Transaction not found.');

        $receiptAccount = savings_account_tbl::find($tx->savings_account_id);
        $receiptOwnerId = $receiptAccount ? (int) $receiptAccount->user_id : null;

        abort_unless(
            $receiptOwnerId
            && ($receiptOwnerId === (int) auth()->id() || \App\Services\SoDGuard::actingAsStaff()),
            403,
            'You do not have permission to view this receipt.'
        );

        // Receipt is only available for completed transactions
        if (strtolower($tx->status ?? '') !== 'completed') {
            abort(403, 'Receipt is only available for completed transactions.');
        }

        // Get user for the transaction
        $savingsAccount = savings_account_tbl::find($tx->savings_account_id);
        $transactionUser = $savingsAccount ? Users_tbl::find($savingsAccount->user_id) : null;

        if (! $transactionUser) {
            $transactionUser = $user;
        }

        // Type-specific display config
        $typeConfig = [
            'deposit' => ['label' => 'Deposit', 'title' => 'Deposit Successful!', 'color' => 'green'],
            'withdrawal' => ['label' => 'Withdrawal', 'title' => 'Withdrawal Successful!', 'color' => 'red'],
            'interest_credit' => ['label' => 'Interest Credit', 'title' => 'Interest Credited!', 'color' => 'blue'],
        ];

        $cfg = $typeConfig[$tx->type] ?? ['label' => ucfirst(str_replace('_', ' ', $tx->type)), 'title' => 'Transaction Complete', 'color' => 'green'];
        $type = $cfg['label'];
        $date = \Carbon\Carbon::parse($tx->transaction_date)->format('F d, Y');
        $time = \Carbon\Carbon::parse($tx->created_at)->format('h:i A');
        $amount = 'PHP '.number_format($tx->amount, 2);
        $balance = 'PHP '.number_format($tx->balance_after, 2);
        $note = $tx->note ?? 'N/A';
        $member = $transactionUser->first_name.' '.$transactionUser->last_name;
        $isDeposit = $cfg['color'] === 'green';

        // Font paths
        $fontRegular = public_path('Poppins/Poppins-Regular.ttf');
        $fontSemiBold = public_path('Poppins/Poppins-SemiBold.ttf');

        // Canvas size — increased height to fit extra row
        $w = 600;
        $h = 550;
        $img = imagecreatetruecolor($w, $h);

        // Enable antialiasing
        imageantialias($img, true);

        // Colors
        $white = imagecolorallocate($img, 255, 255, 255);
        $green = imagecolorallocate($img, 30, 64, 53);
        $lightGreen = imagecolorallocate($img, 240, 247, 244);
        $red = imagecolorallocate($img, 220, 38, 38);
        $lightRed = imagecolorallocate($img, 254, 242, 242);
        $muted = imagecolorallocate($img, 107, 123, 116);
        $border = imagecolorallocate($img, 226, 232, 229);
        $dark = imagecolorallocate($img, 26, 26, 26);
        $accentClr = $isDeposit ? $green : $red;
        $accentBg = $isDeposit ? $lightGreen : $lightRed;

        $blue = imagecolorallocate($img, 30, 86, 160);
        $lightBlue = imagecolorallocate($img, 235, 244, 255);

        $accentClr = match ($cfg['color']) {
            'green' => $green,
            'red' => $red,
            'blue' => $blue,
            default => $green,
        };
        $accentBg = match ($cfg['color']) {
            'green' => $lightGreen,
            'red' => $lightRed,
            'blue' => $lightBlue,
            default => $lightGreen,
        };

        // Background
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // Top accent bar
        imagefilledrectangle($img, 0, 0, $w, 6, $accentClr);

        // Header background strip
        imagefilledrectangle($img, 0, 6, $w, 140, $accentBg);

        // Circle icon background
        imagefilledellipse($img, $w / 2, 80, 80, 80, $accentClr);
        imagefilledellipse($img, $w / 2, 80, 70, 70, $white);

        // Checkmark inside circle
        imageline($img, $w / 2 - 14, 80, $w / 2 - 4, 92, $accentClr);
        imageline($img, $w / 2 - 13, 80, $w / 2 - 3, 92, $accentClr);
        imageline($img, $w / 2 - 4, 92, $w / 2 + 16, 66, $accentClr);
        imageline($img, $w / 2 - 4, 91, $w / 2 + 16, 65, $accentClr);

        // Helper: centered text
        $centerText = function (int $fontSize, string $fontPath, string $text, int $y, $color) use ($img, $w) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($w - $textWidth) / 2;
            imagettftext($img, $fontSize, 0, (int) $x, $y, $color, $fontPath, $text);
        };

        // Title
        // $title = $isDeposit ? 'Deposit Successful!' : 'Withdrawal Successful!';
        $title = $cfg['title'];
        $centerText(16, $fontSemiBold, $title, 165, $dark);

        // Subtitle
        $sub = 'KMPCATS Cooperative -- Official Receipt';
        $centerText(9, $fontRegular, $sub, 183, $muted);

        // Divider
        imageline($img, 40, 198, $w - 40, 198, $border);

        // Info rows — Time row added after Date
        $rows = [
            ['Reference No.', $referenceNo],
            ['Member', $member],
            ['Date', $date],
            ['Time', $time],       // <-- new row
            ['Type', $type],
            ['Amount', $amount],
            ['Balance After', $balance],
            ['Note', $note],
        ];

        $y = 220;
        foreach ($rows as $row) {
            // Label (left)
            imagettftext($img, 9, 0, 50, $y, $muted, $fontRegular, $row[0]);

            // Value (right-aligned)
            $val = strlen($row[1]) > 38 ? substr($row[1], 0, 38).'...' : $row[1];
            $bbox = imagettfbbox(9, 0, $fontSemiBold, $val);
            $valW = $bbox[2] - $bbox[0];
            $valX = $w - 50 - $valW;
            $valColor = $row[0] === 'Amount' ? $accentClr : $dark;
            imagettftext($img, 9, 0, (int) $valX, $y, $valColor, $fontSemiBold, $val);

            // Draw line BELOW the text
            imageline($img, 50, $y + 10, $w - 50, $y + 10, $border);

            $y += 30;
        }

        // Bottom note
        $note1 = 'This receipt is system-generated and serves as';
        $note2 = 'official proof of your transaction.';
        $centerText(8, $fontRegular, $note1, $h - 38, $muted);
        $centerText(8, $fontRegular, $note2, $h - 24, $muted);

        // Bottom bar
        imagefilledrectangle($img, 0, $h - 20, $w, $h, $accentClr);
        $foot = 'KMPCATS Cooperative Management System';
        $centerText(8, $fontRegular, $foot, $h - 6, $white);

        // Output as JPG
        $filename = "Receipt-{$referenceNo}.jpg";
        ob_start();
        imagejpeg($img, null, 95);
        $imageData = ob_get_clean();
        imagedestroy($img);

        // Check if request is for inline view (for admin modal display)
        if ($request->query('view') === 'inline') {
            $base64 = 'data:image/jpeg;base64,'.base64_encode($imageData);

            return response()->json(['image' => $base64]);
        }

        return response($imageData, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Handle admin savings transaction (deposit/withdrawal for any member).
     */
    public function adminStoreSavings(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|string|in:deposit,withdrawal',
            'payment_method' => ['nullable', 'string', 'in:cash,gcash'],
            'gcash_number' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:255',
        ]);

        // Segregation of duties: an Allied Worker may not process their own
        // member account (self-processing block). Only the GM may do so.
        if (\App\Services\SoDGuard::actingAsStaff() && ! \App\Services\SoDGuard::isGeneralManager()
            && (int) Auth::id() === (int) $request->member_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot process a transaction for your own member account (self-processing is blocked).',
            ], 422);
        }

        // Get member's savings account
        $savingsAccount = savings_account_tbl::where('user_id', $request->member_id)->first();

        // If no savings account exists, create one
        if (! $savingsAccount) {
            $savingsAccount = savings_account_tbl::create([
                'user_id' => $request->member_id,
                'balance' => 0,
                'status' => 'active',
                'opened_at' => Carbon::today(),
            ]);
        }

        $amount = $request->amount;
        $type = $request->type;

        // Handle withdrawal - check balance
        if ($type === 'withdrawal') {
            if ($savingsAccount->balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Available: ₱'.number_format($savingsAccount->balance, 2),
                ], 422);
            }
            $newBalance = $savingsAccount->balance - $amount;

            $settings = SavingsInterestSetting::getOrCreate();
            if ($settings->maintaining_balance > 0 && $newBalance < $settings->maintaining_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'This withdrawal would bring balance below the maintaining balance of ₱'.number_format($settings->maintaining_balance, 2).'. Minimum required: ₱'.number_format($settings->maintaining_balance, 2),
                ], 422);
            }
        } else {
            $newBalance = $savingsAccount->balance + $amount;
        }

        // Update balance
        $savingsAccount->update(['balance' => $newBalance]);

        // Generate reference number
        $referenceNo = $this->generateReferenceNo($type);

        // Create transaction record
        $transaction = savings_transaction_tbl::create([
            'savings_account_id' => $savingsAccount->id,
            'type' => $type,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'gcash_number' => $request->gcash_number,
            'balance_after' => $newBalance,
            'note' => $request->note,
            'reference_no' => $referenceNo,
            'transaction_date' => Carbon::today(),
            'status' => 'Completed',
            'created_by' => Auth::id(),
            'approved_by' => Auth::id(),
        ]);

        $member = Users_tbl::find($request->member_id);
        AuditLog::log(
            'Admin '.ucfirst($type).' Savings',
            ucfirst($type)." of ₱{$amount} to/from {$member?->first_name} {$member?->last_name} (Ref: {$referenceNo})",
            'savings',
            $savingsAccount->id
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($type).' of ₱'.number_format($amount, 2).' successful!',
            'reference_no' => $referenceNo,
            'new_balance' => $newBalance,
        ]);
    }

    /**
     * Get member's current savings balance.
     */
    public function getMemberBalance($memberId)
    {
        $savingsAccount = savings_account_tbl::where('user_id', $memberId)->first();
        $balance = $savingsAccount ? $savingsAccount->balance : 0;
        $member = Users_tbl::with('otherinfo')->find($memberId);

        return response()->json([
            'balance' => $balance,
            'contact_no' => $member?->otherinfo?->contact_no,
        ]);
    }

    /**
     * Get member share capital balance for AJAX.
     */
    public function getMemberShareCapitalBalance($memberId)
    {
        $account = share_capital_account_tbl::where('user_id', $memberId)->first();
        $balance = $account ? $account->total_amount : 0;

        return response()->json(['balance' => $balance]);
    }

    /**
     * Convert/transfer a portion of a member's Savings into Share Capital.
     *
     * Atomic: savings balance, share capital balance, and BOTH ledger records
     * change together or not at all. Idempotent via idempotency_key (used to
     * build the shared SCP-CONV- reference on both sides of the ledger).
     */
    public function convertToShareCapital(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'amount' => 'required|numeric|min:1',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $memberId = $request->member_id;
        $amount = (float) $request->amount;
        $amountPerShare = ShareCapital::PAR_VALUE;
        $conversionType = ShareCapital::CONVERSION_TYPE;

        $savingsAccount = savings_account_tbl::where('user_id', $memberId)->first();

        if (! $savingsAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Member does not have a savings account.',
            ], 422);
        }

        if ($amount > $savingsAccount->balance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient savings balance. Available: ₱'.number_format($savingsAccount->balance, 2),
            ], 422);
        }

        $settings = SavingsInterestSetting::getOrCreate();
        $remainingBalance = $savingsAccount->balance - $amount;
        if ($settings->maintaining_balance > 0 && $remainingBalance < $settings->maintaining_balance) {
            return response()->json([
                'success' => false,
                'message' => 'This conversion would bring savings balance below the maintaining balance of ₱'.number_format($settings->maintaining_balance, 2).'. Minimum required: ₱'.number_format($settings->maintaining_balance, 2),
            ], 422);
        }

        $shares = (int) ($amount / $amountPerShare);
        $convertedAmount = $shares * $amountPerShare;

        if ($shares < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum conversion amount is ₱'.number_format($amountPerShare, 2).' (1 share).',
            ], 422);
        }

        if ($convertedAmount < $amount) {
            $remainder = $amount - $convertedAmount;
        } else {
            $remainder = 0;
        }

        $now = Carbon::now();

        // Shared reference on BOTH ledger records so the two sides trace to one operation.
        // When an idempotency key is supplied, reuse it to build the reference so a retry
        // resolves to the same reference and is detected as already processed.
        $referenceNo = $request->idempotency_key
            ? 'SCP-CONV-'.strtoupper($request->idempotency_key)
            : 'SCP-CONV-'.strtoupper(bin2hex(random_bytes(8)));

        // Idempotency guard: a previous successful run already wrote both ledger rows
        // with this reference. Replay without touching balances again.
        $alreadyProcessed = share_capital_transaction_tbl::where('reference_no', $referenceNo)->exists()
            || savings_transaction_tbl::where('reference_no', $referenceNo)->exists();

        if ($alreadyProcessed) {
            $existingSc = share_capital_transaction_tbl::where('reference_no', $referenceNo)->first();

            return response()->json([
                'success' => true,
                'message' => 'This conversion was already processed (Ref: '.$referenceNo.'). No changes were made.',
                'converted_amount' => $convertedAmount,
                'shares' => $shares,
                'reference_no' => $referenceNo,
                'remainder' => $remainder,
                'already_processed' => true,
                'share_capital_account_id' => $existingSc ? $existingSc->share_capital_account_id : null,
            ]);
        }

        DB::beginTransaction();

        try {
            $savingsNewBalance = $savingsAccount->balance - $convertedAmount;
            $savingsAccount->update(['balance' => $savingsNewBalance]);

            savings_transaction_tbl::create([
                'savings_account_id' => $savingsAccount->id,
                'type' => $conversionType,
                'amount' => $convertedAmount,
                'payment_method' => 'Internal Transfer',
                'balance_after' => $savingsNewBalance,
                'note' => 'Transferred to Share Capital',
                'reference_no' => $referenceNo,
                'transaction_date' => $now->toDateString(),
                'status' => 'Completed',
            ]);

            $scAccount = share_capital_account_tbl::where('user_id', $memberId)->first();

            if ($scAccount) {
                $scAccount->update([
                    'total_shares' => $scAccount->total_shares + $shares,
                    'total_amount' => $scAccount->total_amount + $convertedAmount,
                    'status' => 'Active',
                ]);
                $scAccountId = $scAccount->id;
            } else {
                $scAccount = share_capital_account_tbl::create([
                    'user_id' => $memberId,
                    'total_shares' => $shares,
                    'total_amount' => $convertedAmount,
                    'status' => 'Active',
                ]);
                $scAccountId = $scAccount->id;
            }

            share_capital_transaction_tbl::create([
                'share_capital_account_id' => $scAccountId,
                'type' => $conversionType,
                'shares' => $shares,
                'amount_per_share' => $amountPerShare,
                'total_amount' => $convertedAmount,
                'payment_method' => 'Internal Transfer',
                'reference_no' => $referenceNo,
                'note' => 'Converted from Savings',
                'status' => 'Completed',
                'transaction_date' => $now->toDateString(),
            ]);

            DB::commit();

            $member = Users_tbl::find($memberId);

            return response()->json([
                'success' => true,
                'message' => 'Successfully converted ₱'.number_format($convertedAmount, 2).' ('.$shares.' share(s)) to Share Capital.',
                'converted_amount' => $convertedAmount,
                'shares' => $shares,
                'reference_no' => $referenceNo,
                'remainder' => $remainder,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Conversion failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
