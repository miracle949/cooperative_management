<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Mail\ApprovedMail;
use App\Mail\DeclinedMail;
use App\Mail\ShareCapital as ShareCapitalMail;
use App\Models\AuditLog;
use App\Models\CooperativeTransaction;
use App\Models\Dividend;
use App\Models\educational_tbl;
use App\Models\Family_tbl;
use App\Models\lending_program_tbl;
use App\Models\lending_repayments_tbl;
use App\Models\lending_status_tbl;
use App\Models\Loan_settings_tbl;
use App\Models\LoanEligibilitySetting;
use App\Models\Membergovern_ids_tbl;
use App\Models\Membervehi_tbl;
use App\Models\Otherinfo_tbl;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\SavingsInterestSetting;
use App\Models\share_capital_account_tbl;
use App\Models\share_capital_transaction_tbl;
use App\Models\Spouse_tbl;
use App\Models\system_settings_tbl;
use App\Models\Users_tbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public $getUser;

    public function __construct()
    {
        $this->getUser = new Users_tbl;
    }

    public function importMembers(Request $request)
    {
        $actor = auth()->user();
        if (!($actor->isMainAdmin() || \App\Services\SoDGuard::isGeneralManager())) {
            return redirect()->back()->with('error', 'Only the Main Admin or General Manager can import members.');
        }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        @set_time_limit(300);

        try {
            $book = IOFactory::load($request->file('import_file')->getRealPath());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not read the Excel file: ' . $e->getMessage());
        }

        $summary = ['members_created' => 0, 'members_existing' => 0, 'share_capital' => 0, 'savings' => 0, 'loans' => 0, 'skipped' => 0];
        $errors = [];

        $credentials = [];

        foreach ($this->importRows($book, 'Members') as $rowNo => $r) {
            try {
                $email = strtolower(trim($r['email'] ?? ''));
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Invalid or missing email.');
                }
                foreach (['first name', 'last name', 'membership category', 'civil status'] as $req) {
                    if (trim((string) ($r[$req] ?? '')) === '') {
                        throw new \RuntimeException(ucwords($req) . ' is required.');
                    }
                }
                $dob = $this->importDate($r['date of birth'] ?? null);
                if (!$dob) {
                    throw new \RuntimeException('Date of Birth is missing or not a valid date.');
                }

                if ($this->importFindUser($email)) {
                    $summary['members_existing']++;
                    continue;
                }

                DB::transaction(function () use ($r, $email, $dob, &$credentials) {
                    $base = trim($r['username'] ?? '') ?: strstr($email, '@', true);
                    $username = $base;
                    $i = 1;
                    while (Users_tbl::where('username', $username)->exists()) {
                        $username = $base . $i++;
                    }

                    $password = trim((string) ($r['password'] ?? ''));
                    if ($password !== '' && strlen($password) < 8) {
                        throw new \RuntimeException('Password must be at least 8 characters.');
                    }
                    $generated = $password === '';
                    if ($generated) {
                        // FirstName@123456  (e.g. Alfredo@123456)
                        $firstName = preg_replace('/[^A-Za-z0-9]/', '', trim($r['first name']));
                        $password = ucfirst($firstName) . '@123456';
                    }

                    $user = Users_tbl::create([
                        'first_name' => trim($r['first name']),
                        'middle_name' => trim($r['middle name'] ?? '') ?: null,
                        'last_name' => trim($r['last name']),
                        'username' => $username,
                        'email' => $email,
                        'password' => bcrypt($password),
                        'role' => 'member',
                        'status' => 'active',
                    ]);

                    if ($joined = $this->importDate($r['date joined'] ?? null)) {
                        $user->forceFill(['created_at' => $joined])->save();
                    }

                    $sex = ucfirst(strtolower(trim((string) ($r['sex'] ?? ''))));
                    $sex = in_array($sex, ['Male', 'Female'], true) ? $sex : null;

                    $contact = trim((string) ($r['contact no'] ?? ''));
                    if ($contact !== '' && ctype_digit($contact) && strlen($contact) === 10) {
                        $contact = '0' . $contact; // Excel drops the leading 0
                    }

                    Otherinfo_tbl::create([
                        'user_id' => $user->id,
                        'membership_category' => trim($r['membership category']),
                        'date_of_birth' => $dob->format('Y-m-d'),
                        'place_of_birth' => ($r['place of birth'] ?? '') ?: null,
                        'sex' => $sex,
                        'civil_status' => trim($r['civil status']),
                        'citizenship' => ($r['citizenship'] ?? '') ?: 'Filipino',
                        'contact_no' => $contact ?: null,
                        'present_address' => ($r['present address'] ?? '') ?: null,
                        'permanent_address' => ($r['permanent address'] ?? '') ?: null,
                        'blood_type' => ($r['blood type'] ?? '') ?: null,
                        'height' => ($r['height'] ?? '') ?: null,
                        'weight' => ($r['weight'] ?? '') ?: null,
                        'skills' => ($r['skills'] ?? '') ?: null,
                        'approval_status' => 'Approved',
                        'membership_status' => 'Active',
                        'email_verified' => 1,
                    ]);

                    $spouseDob = $this->importDate($r['spouse date of birth'] ?? null);
                    Family_tbl::create([
                        'user_id' => $user->id,
                        'spouse_name' => ($r['spouse name'] ?? '') ?: null,
                        'spouse_date_birth' => $spouseDob?->format('Y-m-d'),
                        'spouse_place_birth' => ($r['spouse place of birth'] ?? '') ?: null,
                        'number_son' => (int) ($r['number of sons'] ?? 0),
                        'number_daughter' => (int) ($r['number of daughters'] ?? 0),
                    ]);

                    Membergovern_ids_tbl::create(['user_id' => $user->id]);

                    if ($generated) {
                        $credentials[] = [
                            'name' => trim($r['first name'] . ' ' . $r['last name']),
                            'email' => $email,
                            'username' => $username,
                            'password' => $password,
                        ];
                    }
                });

                $summary['members_created']++;
            } catch (\Throwable $e) {
                $errors[] = "Members row {$rowNo}: " . $e->getMessage();
            }
        }

        // ── 2) SHARE CAPITAL (opening balance) ───────────────────────────
        foreach ($this->importRows($book, 'Share Capital') as $rowNo => $r) {
            try {
                $member = $this->importFindUser($r['email'] ?? '');
                if (!$member) {
                    throw new \RuntimeException('No member found with that email.');
                }
                $shares = (float) ($r['shares'] ?? 0);
                if ($shares <= 0) {
                    throw new \RuntimeException('Shares must be greater than 0.');
                }
                $date = $this->importDate($r['date subscribed'] ?? null) ?: now();
                $ref = 'IMP-SC-' . $member->id;

                if (share_capital_transaction_tbl::where('reference_no', $ref)->exists()) {
                    $summary['skipped']++;
                    $errors[] = "Share Capital row {$rowNo}: already imported for this member (skipped).";
                    continue;
                }

                DB::transaction(function () use ($member, $shares, $date, $ref, $r, $actor) {
                    $par = ShareCapital::PAR_VALUE;
                    $total = round($shares * $par, 2);

                    $account = share_capital_account_tbl::firstOrCreate(
                        ['user_id' => $member->id],
                        ['total_shares' => 0, 'total_amount' => 0, 'status' => 'Active', 'acquired_date' => $date]
                    );
                    $account->update([
                        'total_shares' => $account->total_shares + $shares,
                        'total_amount' => $account->total_amount + $total,
                    ]);

                    share_capital_transaction_tbl::create([
                        'share_capital_account_id' => $account->id,
                        'user_id' => $member->id,
                        'type' => 'Subscription',
                        'shares' => $shares,
                        'amount_per_share' => $par,
                        'total_amount' => $total,
                        'payment_method' => 'Opening Balance',
                        'reference_no' => $ref,
                        'transaction_date' => $date->format('Y-m-d'),
                        'status' => 'Completed',
                        'note' => ($r['note'] ?? '') ?: 'Opening balance (imported)',
                        'created_by' => $actor->id,
                        'approved_by' => $actor->id,
                    ]);
                });

                $summary['share_capital']++;
            } catch (\Throwable $e) {
                $errors[] = "Share Capital row {$rowNo}: " . $e->getMessage();
            }
        }

        // ── 3) SAVINGS (opening balance) ─────────────────────────────────
        foreach ($this->importRows($book, 'Savings') as $rowNo => $r) {
            try {
                $member = $this->importFindUser($r['email'] ?? '');
                if (!$member) {
                    throw new \RuntimeException('No member found with that email.');
                }
                $amount = round((float) ($r['savings balance'] ?? 0), 2);
                if ($amount <= 0) {
                    throw new \RuntimeException('Savings Balance must be greater than 0.');
                }
                $date = $this->importDate($r['as of date'] ?? null) ?: now();
                $ref = 'IMP-SV-' . $member->id;

                if (savings_transaction_tbl::where('reference_no', $ref)->exists()) {
                    $summary['skipped']++;
                    $errors[] = "Savings row {$rowNo}: already imported for this member (skipped).";
                    continue;
                }

                DB::transaction(function () use ($member, $amount, $date, $ref, $r, $actor) {
                    $account = savings_account_tbl::firstOrCreate(
                        ['user_id' => $member->id],
                        ['balance' => 0, 'status' => 'active', 'opened_at' => $date->format('Y-m-d')]
                    );
                    $newBalance = round((float) $account->balance + $amount, 2);
                    $account->update(['balance' => $newBalance]);

                    savings_transaction_tbl::create([
                        'savings_account_id' => $account->id,
                        'type' => 'deposit',
                        'amount' => $amount,
                        'balance_after' => $newBalance,
                        'reference_no' => $ref,
                        'payment_method' => 'Opening Balance',
                        'transaction_date' => $date->format('Y-m-d'),
                        'status' => 'Completed',
                        'note' => ($r['note'] ?? '') ?: 'Opening balance (imported)',
                        'created_by' => $actor->id,
                        'approved_by' => $actor->id,
                    ]);
                });

                $summary['savings']++;
            } catch (\Throwable $e) {
                $errors[] = "Savings row {$rowNo}: " . $e->getMessage();
            }
        }

        // ── 4) ACTIVE LOANS ──────────────────────────────────────────────
        foreach ($this->importRows($book, 'Loans') as $rowNo => $r) {
            try {
                $member = $this->importFindUser($r['email'] ?? '');
                if (!$member) {
                    throw new \RuntimeException('No member found with that email.');
                }
                $type = trim((string) ($r['loan type'] ?? ''));
                $amount = round((float) ($r['loan amount'] ?? 0), 2);
                $term = (int) filter_var($r['loan term'] ?? '', FILTER_SANITIZE_NUMBER_INT);
                $released = $this->importDate($r['date released'] ?? null);
                $paymentsMade = max(0, (int) ($r['payments made'] ?? 0));

                if ($type === '' || $amount <= 0 || $term <= 0 || !$released) {
                    throw new \RuntimeException('Loan Type, Loan Amount, Loan Term and Date Released are required.');
                }
                if ($paymentsMade > $term) {
                    throw new \RuntimeException('Payments Made cannot be more than the loan term.');
                }

                $dup = lending_program_tbl::where('user_id', $member->id)
                    ->where('lending_type', $type)
                    ->where('lending_amount', $amount)
                    ->whereDate('created_at', $released->format('Y-m-d'))
                    ->exists();
                if ($dup) {
                    $summary['skipped']++;
                    $errors[] = "Loans row {$rowNo}: this loan was already imported (skipped).";
                    continue;
                }

                DB::transaction(function () use ($member, $type, $amount, $term, $released, $paymentsMade, $r, $actor) {
                    $settings = DB::table('loan_settings_tbls')
                        ->where('loan_type', $type)->where('is_active', true)->first();
                    if (!$settings) {
                        throw new \RuntimeException("Loan type '{$type}' is not configured/active in Settings.");
                    }

                    $calc = new \App\Services\LoanCalculationService;
                    $schedule = $calc->buildSchedule($amount, (float) $settings->interest_rate, $term);

                    $latest = lending_program_tbl::orderBy('reference_no', 'desc')->first();
                    $next = $latest ? intval(substr($latest->reference_no, 4)) + 1 : 1;
                    $referenceNo = 'LN-' . str_pad($next, 4, '0', STR_PAD_LEFT);

                    $processingFee = round($amount * ($settings->processing_fee_rate / 100), 2);
                    $serviceFee = round($amount * ($settings->service_fee_rate / 100), 2);
                    $protectionFee = round($settings->loan_protection_fee * $term, 2);
                    $retention = round($amount * ($settings->retention_unpaid_rate / 100), 2);
                    $netProceeds = round($amount - $processingFee - $serviceFee - $protectionFee - $retention, 2);

                    $loan = lending_program_tbl::create([
                        'user_id' => $member->id,
                        'reference_no' => $referenceNo,
                        'lending_type' => $type,
                        'lending_amount' => $amount,
                        'lending_type_term' => $term . ' months',
                        'monthly_income' => (float) ($r['monthly income'] ?? 0),
                        'monthly_payment' => $calc->averageMonthlyPayment($schedule),
                        'total_payment' => $schedule['total_payment'],
                        'total_interest' => $schedule['total_interest'],
                        'processing_fee_rate' => $processingFee,
                        'service_fee_rate' => $serviceFee,
                        'loan_protection_fee' => $protectionFee,
                        'retention_paid_rate' => 0,
                        'retention_unpaid_rate' => $retention,
                        'net_proceeds' => $netProceeds,
                        'purpose_loan' => ($r['purpose'] ?? '') ?: 'Imported loan',
                        'status' => 'Approved',
                        'due_date' => $released->copy()->addMonths($term)->format('Y-m-d'),
                    ]);

                    // Backdate so schedules, the Transactions page and "Member since" logic line up
                    $loan->forceFill([
                        'created_at' => $released,
                        'updated_at' => $released,
                        'disbursed_at' => $released,
                        'disbursement_method' => 'Opening Balance Import',
                    ])->save();

                    $calc->persistSchedule($loan->id, $schedule);

                    $first = $calc->currentInstallment($loan->id);
                    $status = lending_status_tbl::create([
                        'lending_id' => $loan->id,
                        'user_id' => $member->id,
                        'remaining_balance' => $loan->total_payment,
                        'total_paid' => 0,
                        'payments_made' => 0,
                        'total_payments' => $term,
                        'interest_rate' => $settings->interest_rate,
                        'due_date' => $first->due_date ?? $released->copy()->addMonth()->format('Y-m-d'),
                        'status' => 'Active',
                    ]);

                    // Replay the payments already made
                    for ($n = 1; $n <= $paymentsMade; $n++) {
                        $inst = $calc->currentInstallment($loan->id);
                        if (!$inst) {
                            break;
                        }
                        $pay = (float) $inst->amount_due;
                        $alloc = $calc->allocatePayment($loan->id, $pay);

                        lending_repayments_tbl::create([
                            'lending_id' => $loan->id,
                            'user_id' => $member->id,
                            'payment_number' => (int) $inst->payment_number,
                            'payment_sequence' => 1,
                            'amount_due' => $pay,
                            'amount_paid' => $pay,
                            'principal_paid' => $alloc['principal_paid'],
                            'interest_paid' => $alloc['interest_paid'],
                            'service_fee_paid' => 0,
                            'payment_date' => $released->copy()->addMonths($n)->format('Y-m-d'),
                            'payment_method' => 'Opening Balance',
                            'reference_no' => 'IMP-RP-' . $loan->id . '-' . $n,
                            'payment_type' => 'monthly',
                            'status' => 'Completed',
                            'notes' => 'Imported (already paid before system go-live)',
                            'recorded_by' => $actor->id,
                            'created_by' => $actor->id,
                        ]);

                        $status->total_paid = round((float) $status->total_paid + (float) $alloc['amount_paid'], 2);
                        $status->remaining_balance = max(0, round((float) $status->remaining_balance - $pay, 2));
                        $status->payments_made = (int) $status->payments_made + (int) $alloc['installments_settled'];

                        if ($alloc['fully_paid_now'] || $status->remaining_balance <= 0) {
                            $status->status = 'Completed';
                            $status->payments_made = $status->total_payments;
                            $status->remaining_balance = 0;
                            $loan->update(['status' => 'Completed']);
                        } else {
                            $nextUnpaid = $calc->currentInstallment($loan->id);
                            if ($nextUnpaid) {
                                $status->due_date = $nextUnpaid->due_date;
                            }
                        }
                        $status->save();
                    }
                });

                $summary['loans']++;
            } catch (\Throwable $e) {
                $errors[] = "Loans row {$rowNo}: " . $e->getMessage();
            }
        }

        AuditLog::log(
            'Imported Members',
            "Bulk import: {$summary['members_created']} new member(s), {$summary['members_existing']} existing, "
            . "{$summary['share_capital']} share capital, {$summary['savings']} savings, {$summary['loans']} loan(s); "
            . count($errors) . ' issue(s).',
            'user',
            null
        );

        $msg = "Import finished: {$summary['members_created']} member(s) created"
            . ($summary['members_existing'] ? " ({$summary['members_existing']} already existed)" : '')
            . ", {$summary['share_capital']} share capital, {$summary['savings']} savings, {$summary['loans']} loan(s).";

        return redirect()->back()
            ->with($errors ? 'import_warning' : 'success', $msg)
            ->with('import_errors', array_slice($errors, 0, 100))
            ->with('import_credentials', $credentials);
    }

    /** Reads a sheet into [excelRowNumber => [lowercased header => value]], skipping empty rows. */
    private function importRows($book, string $sheetName): array
    {
        $ws = $book->getSheetByName($sheetName);
        if (!$ws) {
            return [];
        }

        $data = $ws->toArray(null, true, false, false); // raw values (dates stay numeric)
        $headers = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($data) ?? []);

        $out = [];
        foreach ($data as $i => $row) {
            if (count(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '')) === 0) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $c => $h) {
                if ($h !== '') {
                    $assoc[$h] = is_string($row[$c] ?? null) ? trim($row[$c]) : ($row[$c] ?? null);
                }
            }
            $out[$i + 2] = $assoc; // +2 = header row + 1-based index
        }

        return $out;
    }

    /** Accepts real Excel dates or text like 2024-03-15. Returns Carbon or null. */
    private function importDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Carbon::instance(XlDate::excelToDateTimeObject((float) $value))->startOfDay();
            }

            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function importFindUser(?string $email): ?Users_tbl
    {
        $email = strtolower(trim((string) $email));

        return $email === '' ? null : Users_tbl::whereRaw('LOWER(email) = ?', [$email])->first();
    }

    public function convertSavingsToShareCapital(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:' . self::PAR_VALUE,
        ]);

        $user = Auth::user();
        $amount = round((float) $request->amount, 2);
        $price = self::PAR_VALUE;

        // Only whole shares can be converted
        $shares = (int) floor(($amount / $price) + 1e-9);
        $cost = $shares * $price;

        if ($shares < 1) {
            return response()->json([
                'success' => false,
                'message' => 'The minimum conversion is ₱' . $price . ' (1 share).',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($user, $shares, $cost, $price) {
                // Lock the savings row to prevent double-spend
                $savings = savings_account_tbl::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$savings || (float) $savings->balance < $cost) {
                    throw new \RuntimeException(
                        'That is more than your savings balance (₱' . number_format($savings->balance ?? 0, 2) . ').'
                    );
                }

                $scAccount = share_capital_account_tbl::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$scAccount) {
                    $scAccount = share_capital_account_tbl::create([
                        'user_id' => $user->id,
                        'total_shares' => 0,
                        'total_amount' => 0,
                        'status' => 'Active',
                        'acquired_date' => now(),
                    ]);
                }

                $ref = 'CNV-' . now()->format('YmdHis') . rand(10, 99);

                // 1) Debit savings
                $newSavings = round((float) $savings->balance - $cost, 2);
                $savings->update(['balance' => $newSavings]);

                savings_transaction_tbl::create([
                    'savings_account_id' => $savings->id,
                    'type' => 'withdrawal',
                    'amount' => $cost,
                    'balance_after' => $newSavings,
                    'reference_no' => $ref,
                    'payment_method' => 'Conversion',
                    'transaction_date' => now(),
                    'status' => 'Completed',
                    'created_by' => $user->id,
                    'approved_by' => $user->id,
                ]);

                // 2) Credit share capital
                $scAccount->update([
                    'total_shares' => $scAccount->total_shares + $shares,
                    'total_amount' => $scAccount->total_amount + $cost,
                ]);

                share_capital_transaction_tbl::create([
                    'share_capital_account_id' => $scAccount->id,
                    'user_id' => $user->id,
                    'type' => self::CONVERSION_TYPE,
                    'shares' => $shares,
                    'amount_per_share' => $price,
                    'total_amount' => $cost,
                    'payment_method' => 'Savings',
                    'reference_no' => $ref,
                    'transaction_date' => now(),
                    'status' => 'Completed',
                    'created_by' => $user->id,
                    'approved_by' => $user->id,
                ]);

                AuditLog::log(
                    'Converted Savings to Share Capital',
                    "Converted ₱{$cost} from savings into {$shares} share(s) (Ref: {$ref})",
                    'share_capital',
                    $scAccount->id
                );

                return ['shares' => $shares, 'amount' => $cost, 'reference_no' => $ref];
            });

            return response()->json(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Log::error('Savings → SC conversion failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'The conversion could not be completed.'], 500);
        }
    }

    public function sharingCapital()
    {
        return view('ShareCapitalForm.share_capital_form');
    }

    public function shareCapitalForm($id)
    {
        return view('ShareCapitalForm.share_capital_form');
    }

    public function applicationForm($id)
    {
        $id = (int) $id;
        $user = Users_tbl::findOrFail($id);

        abort_unless(
            (int) auth()->id() === $id
            || in_array(strtolower((string) $user->role), ['pending', 'inactive'], true)
            || \App\Services\SoDGuard::actingAsStaff(),
            403
        );

        $vehicles = Membervehi_tbl::where('user_id', $id)->get()->groupBy('vehicle_type');
        $spouse = \App\Models\Family_tbl::where('user_id', $id)->first();
        $other = Otherinfo_tbl::where('user_id', $id)->first();
        $education = educational_tbl::where('user_id', $id)->get();
        $governmentIds = Membergovern_ids_tbl::where('user_id', $id)->first();

        // Check if already submitted (has contact_no or any filled data)
        $alreadySubmitted = $other && !empty($other->contact_no);

        return view('members_components.application_form', compact(
            'user',
            'vehicles',
            'spouse',
            'other',
            'education',
            'governmentIds',
            'alreadySubmitted'
        ));
    }

    public function approveUser($id)
    {
        $user = Users_tbl::findOrFail($id);
        $user->role = 'Member';
        $user->save();

        $otherInfo = DB::table('otherinfo_tbls')->where('user_id', $id)->first();

        if ($otherInfo) {
            DB::table('otherinfo_tbls')
                ->where('user_id', $id)
                ->update([
                    'membership_status' => 'Active',
                    'approval_status' => 'Approved',
                ]);
        } else {
            DB::table('otherinfo_tbls')->insert([
                'user_id' => $id,
                'membership_category' => 'Investor Associate',
                'signature' => '',
                'membership_status' => 'Active',
                'approval_status' => 'Approved',
            ]);
        }

        AuditLog::log(
            'Approved Member',
            "Approved member {$user->first_name} {$user->last_name} (ID: {$id})",
            'user',
            $id
        );

        Mail::to($user->email)->send(new ApprovedMail($user));

        return redirect()->back()->with('success', 'Member approved and email sent!');
    }

    public function messageAboutShare($id)
    {
        $user = Users_tbl::findOrFail($id);
        $user->role = 'Member';
        $user->save();

        $otherInfo = DB::table('otherinfo_tbls')->where('user_id', $id)->first();

        if ($otherInfo) {
            DB::table('otherinfo_tbls')
                ->where('user_id', $id)
                ->update(['approval_status' => 'Approved']);
        } else {
            DB::table('otherinfo_tbls')->insert([
                'user_id' => $id,
                'membership_category' => 'Investor Associate',
                'signature' => '',
                'approval_status' => 'Approved',
            ]);
        }

        AuditLog::log(
            'Sent Share Capital Invitation',
            "Sent share capital invitation email to {$user->first_name} {$user->last_name} (ID: {$id})",
            'user',
            $id
        );

        Mail::to($user->email)->sendNow(new ShareCapitalMail($user));

        return redirect()->back()->with('success', 'Share capital invitation sent!');
    }

    public function declineUser($id)
    {
        $user = Users_tbl::findOrFail($id);

        AuditLog::log(
            'Declined Member',
            "Declined and removed member {$user->first_name} {$user->last_name} (ID: {$id})",
            'user',
            $id
        );

        Mail::to($user->email)->send(new DeclinedMail($user));

        $user->delete();

        return redirect()->back()->with('error', 'Member request declined and removed.');
    }

    public function approveReactivation($id)
    {
        $user = Users_tbl::findOrFail($id);

        if (strtolower((string) $user->status) !== 'reactivation_pending') {
            return redirect()->back()->with('error', 'This member has no pending reactivation request.');
        }

        $user->status = 'active';
        if (strtolower((string) $user->role) === 'inactive') {
            $user->role = 'member';
        }
        $user->save();

        $otherInfo = \App\Models\Otherinfo_tbl::where('user_id', $id)->first();
        if ($otherInfo) {
            $otherInfo->membership_status = 'Active';
            $otherInfo->save();
        }

        $scAccount = share_capital_account_tbl::where('user_id', $id)->first();
        if ($scAccount && strtolower((string) $scAccount->status) !== 'closed') {
            $scAccount->status = 'Active';
            $scAccount->save();
        }

        AuditLog::log(
            'Approved Reactivation',
            "Approved reactivation for {$user->first_name} {$user->last_name} (ID: {$id}). Share capital, savings, loans and historical records untouched.",
            'member',
            $id
        );

        return redirect()->back()->with('success', 'Reactivation approved. Member is active again.');
    }

    public function rejectReactivation($id)
    {
        $user = Users_tbl::findOrFail($id);

        if (strtolower((string) $user->status) !== 'reactivation_pending') {
            return redirect()->back()->with('error', 'This member has no pending reactivation request.');
        }

        $user->status = 'inactive';
        $user->save();

        AuditLog::log(
            'Rejected Reactivation',
            "Rejected reactivation for {$user->first_name} {$user->last_name} (ID: {$id}). Member remains inactive.",
            'member',
            $id
        );

        return redirect()->back()->with('success', 'Reactivation request rejected. Member remains inactive.');
    }

    public function approveResignation(Request $request, $id)
    {
        $rr = \App\Models\ResignationRequest_tbl::findOrFail($id);

        if ($rr->status !== 'pending') {
            return redirect()->back()->with('error', 'This resignation request has already been processed.');
        }

        $rr->status = 'approved';
        $rr->approved_at = now();

        if ($rr->withdraw_share_capital) {
            $rr->release_date = now()->addDays(60)->toDateString();
        }

        $rr->save();

        $user = Users_tbl::find($rr->user_id);
        if ($user) {
            $user->status = $rr->withdraw_share_capital ? 'awaiting_release' : 'resigned';
            $user->save();
        }

        \App\Models\Notifications_tbl::create([
            'user_id' => $rr->user_id,
            'title' => 'Resignation Request Approved',
            'message' => $rr->withdraw_share_capital
                ? 'Your resignation has been approved. Your share capital will be released after the 60-day holding period.'
                : 'Your resignation has been approved. Your share capital will remain with the cooperative.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Approved Resignation',
            "Approved resignation request for {$user?->first_name} {$user?->last_name} (ID: {$rr->user_id})",
            'resignation',
            $rr->id
        );

        return redirect()->back()->with('success', 'Resignation request approved.');
    }

    public function rejectResignation(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rr = \App\Models\ResignationRequest_tbl::findOrFail($id);

        if ($rr->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This resignation request has already been processed.'], 422);
        }

        $rr->status = 'rejected';
        $rr->rejection_reason = $request->rejection_reason;
        $rr->save();

        $user = Users_tbl::find($rr->user_id);
        if ($user && strtolower((string) $user->status) === 'resignation_pending') {
            $user->status = 'active';
            $user->save();
        }

        \App\Models\Notifications_tbl::create([
            'user_id' => $rr->user_id,
            'title' => 'Resignation Request Rejected',
            'message' => 'Your resignation request was not approved. Reason: ' . $request->rejection_reason,
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Rejected Resignation',
            "Rejected resignation request for {$user?->first_name} {$user?->last_name} (ID: {$rr->user_id}). Reason: {$request->rejection_reason}",
            'resignation',
            $rr->id
        );

        return response()->json(['success' => true, 'message' => 'Resignation request rejected.']);
    }

    public function releaseResignationShareCapital(Request $request, $id)
    {
        $rr = \App\Models\ResignationRequest_tbl::findOrFail($id);

        if ($rr->status !== 'approved' || !$rr->withdraw_share_capital) {
            return redirect()->back()->with('error', 'This request is not eligible for share capital release.');
        }

        if ($rr->is_released) {
            return redirect()->back()->with('error', 'Share capital has already been released for this request.');
        }

        $rr->is_released = true;
        $rr->released_at = now();
        $rr->save();

        $user = Users_tbl::find($rr->user_id);
        if ($user) {
            $user->status = 'inactive';
            $user->save();
        }

        \App\Models\Notifications_tbl::create([
            'user_id' => $rr->user_id,
            'title' => 'Share Capital Released',
            'message' => 'Your share capital has been released following your approved resignation.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Released Resigned Member Share Capital',
            "Released share capital for {$user?->first_name} {$user?->last_name} (ID: {$rr->user_id})",
            'resignation',
            $rr->id
        );

        return redirect()->back()->with('success', 'Share capital released successfully.');
    }

    public function updateMember(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $user = Users_tbl::findOrFail($request->id);
            $previousRole = $user->role;

            if ($request->has('role')) {
                $requestedRole = strtolower((string) $request->role);
                $isMemberRole = in_array($requestedRole, ['member', 'pending', 'inactive'], true);
                $isKnownRole = \App\Models\Role::where('slug', $requestedRole)->exists();

                if (in_array($requestedRole, ['admin', 'officer'], true)) {
                    return response()->json(['success' => false, 'message' => 'The admin and officer roles no longer exist. Use General Manager or a custom role instead.'], 422);
                }

                if (!$isMemberRole && !$isKnownRole) {
                    return response()->json(['success' => false, 'message' => "Unknown role '{$request->role}'."], 422);
                }

                if ($requestedRole === 'general-manager' && !auth()->user()->isMainAdmin()) {
                    return response()->json(['success' => false, 'message' => 'Only the main admin can assign the General Manager role.'], 403);
                }
            }

            $fillable = ['first_name', 'middle_name', 'last_name', 'email', 'contact_no', 'date_of_birth', 'present_address', 'permanent_address', 'sex', 'civil_status', 'citizenship', 'place_of_birth', 'blood_type', 'height', 'weight', 'role'];

            $data = $request->only($fillable);
            $data = array_filter($data, fn($value) => $value !== null && $value !== '');

            $user->update($data);

            if (isset($request->role)) {
                $otherInfo = DB::table('otherinfo_tbls')->where('user_id', $request->id)->first();

                if ($request->role === 'Member') {
                    if ($otherInfo) {
                        DB::table('otherinfo_tbls')
                            ->where('user_id', $request->id)
                            ->update([
                                'membership_status' => 'Active',
                                'approval_status' => 'Approved',
                                'signature' => null,
                            ]);
                    } else {
                        DB::table('otherinfo_tbls')->insert([
                            'user_id' => $request->id,
                            'membership_category' => 'Investor Associate',
                            'membership_status' => 'Active',
                            'approval_status' => 'Approved',
                        ]);
                    }
                } elseif ($request->role === 'inactive' && $otherInfo) {
                    DB::table('otherinfo_tbls')
                        ->where('user_id', $request->id)
                        ->update([
                            'membership_status' => 'Not Active',
                        ]);
                }
            }

            AuditLog::log(
                'Updated Member',
                "Updated member {$user->first_name} {$user->last_name} (ID: {$request->id})",
                'user',
                $request->id
            );

            return response()->json(['success' => true, 'message' => 'Member updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeMember(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users_tbls,email',
                'username' => 'required|unique:users_tbls,username',
                'password' => 'required|min:6',
                'membership_category' => 'required',
                'date_of_birth' => 'required|date',
                'civil_status' => 'required',
            ]);

            $user = Users_tbl::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'member',
            ]);

            Otherinfo_tbl::create([
                'user_id' => $user->id,
                'membership_category' => $request->membership_category,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'citizenship' => $request->citizenship,
                'contact_no' => $request->contact_no,
                'present_address' => $request->present_address,
                'skills' => $request->skills,
                'approval_status' => 'Approved',
                'membership_status' => 'Active',
            ]);

            Family_tbl::create([
                'user_id' => $user->id,
                'spouse_name' => $request->spouse_name,
                'spouse_date_birth' => $request->spouse_date_birth,
                'spouse_place_birth' => $request->spouse_place_birth,
                'number_son' => $request->number_son ?? 0,
                'number_daughter' => $request->number_daughter ?? 0,
            ]);

            Membergovern_ids_tbl::create([
                'user_id' => $user->id,
            ]);

            AuditLog::log(
                'Added Member',
                "Added new member {$request->first_name} {$request->last_name} (ID: {$user->id})",
                'user',
                $user->id
            );

            return redirect()->back()->with('success', 'Member added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding member: ' . $e->getMessage());
        }
    }

    public function sendShareCapitalEmail($id)
    {
        $user = Users_tbl::findOrFail($id);
        AuditLog::log(
            'Sent Share Capital Email',
            "Sent share capital email to {$user->first_name} {$user->last_name} (ID: {$id})",
            'user',
            $id
        );
        Mail::to($user->email)->sendNow(new ShareCapitalMail($user));

        return redirect()->back()->with('success', 'Share capital email sent to member!');
    }

    public function StaticPage()
    {

        $getTheUsers = $this->getUser->getAllUser();

        return view('landingpage_components.static', compact('getTheUsers'));
    }

    // public function applicationForm()
    // {
    //     return view("members_components.application_form");
    // }

    public function UserDirection()
    {
        $officers = \App\Models\officer_tbl::with('user')->orderBy('sort_order')->get();

        $loansApprovedThisYear = lending_program_tbl::whereIn('status', ['Approved', 'Completed'])
            ->whereYear('created_at', now()->year)
            ->count();

        return view('landingpage_components.index', compact('officers', 'loansApprovedThisYear'));
    }

    public function LoginPage()
    {
        return view('login');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            // Mail/SMTP problems show up here. Check storage/logs/laravel.log
            \Log::error('Password reset mail failed', ['error' => $e->getMessage()]);
        }

        // Same message whether or not the email exists (no account enumeration).
        return back()->with('status', 'If that email is registered, a password reset link has been sent.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->password_changed_at = now();
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => 'This reset link is invalid or has expired. Please request a new one.']);
    }

    public function index()
    {
        $officers = \App\Models\officer_tbl::with('user')->orderBy('sort_order')->get();

        $loansApprovedThisYear = lending_program_tbl::whereIn('status', ['Approved', 'Completed'])
            ->whereYear('created_at', now()->year)
            ->count();

        return view("landingpage_components.index", compact('officers', 'loansApprovedThisYear'));
    }

    public function RegisterPage()
    {
        return view('register');
    }

    public function AboutUs()
    {
        $officers = \App\Models\officer_tbl::with('user')->orderBy('sort_order')->get();
        return view("landingpage_components.about", compact('officers'));
    }

    public function ServicesPage()
    {
        return view('landingpage_components.services');
    }

    public function BlogsPage()
    {
        return view('landingpage_components.blogs');
    }

    public function ContactPage()
    {
        return view('landingpage_components.contact');
    }

    public function Navbar()
    {
        return view('navbar');
    }

    public function LoanApplication()
    {
        return view('members_components.loan_application');
    }

    public function Savings()
    {
        return view('members_components.savings');
    }

    public function ShareCapital()
    {
        return view('members_components.share_capital');
    }

    public function LoanStatus()
    {
        return view('members_components.loan_status');
    }

    public function ProfileMember()
    {
        return view('members_components.profile');
    }

    public function DriverPortal()
    {
        return view('members_components.driver_portal');
    }

    public function dashboard_admin()
    {
        // Stats Cards
        $totalMembers = Users_tbl::where('role', 'member')->count();
        $totalSavings = savings_account_tbl::sum('balance') ?? 0;
        $totalShareCapital = \App\Models\share_capital_account_tbl::sum('total_amount') ?? 0;
        $activeLoans = lending_program_tbl::where('status', 'Approved')->sum('lending_amount') ?? 0;
        $earnedInterests = lending_program_tbl::where('status', 'Completed')->sum('total_interest') ?? 0;

        // Savings Activity counts
        $totalDeposits = savings_transaction_tbl::where('type', 'deposit')->count();
        $totalWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')->count();

        // Pending counts
        $pendingMembersCount = Users_tbl::where('role', 'pending')->count();
        $pendingLoansCount = lending_program_tbl::where('status', 'Pending')->count();
        $pendingWithdrawalsCount = share_capital_transaction_tbl::where('type', 'Withdrawal')
            ->whereIn('status', ['Pending', 'pending'])->count();

        // Pending Resignations
        $pendingResignationsCount = \App\Models\ResignationRequest_tbl::where('status', 'pending')->count();
        $pendingResignationsList = \App\Models\ResignationRequest_tbl::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'name' => $req->user ? $req->user->first_name . ' ' . $req->user->last_name : 'Unknown',
                    'withdraw' => $req->withdraw_share_capital,
                    'time' => $req->created_at ? $req->created_at->diffForHumans() : 'N/A',
                    'created_at' => $req->created_at,
                ];
            });

        // Upcoming Seminars
        $upcomingSeminars = \App\Models\Seminars_tbl::where('schedule_datetime', '>=', now())
            ->orderBy('schedule_datetime')
            ->take(5)
            ->get()
            ->map(function ($seminar) {
                return [
                    'id' => $seminar->id,
                    'type' => $seminar->seminar_type,
                    'schedule' => $seminar->schedule_datetime ? $seminar->schedule_datetime->format('M d, Y g:i A') : 'N/A',
                    'delivery' => $seminar->delivery_type,
                    'venue' => $seminar->delivery_type === 'online' ? $seminar->online_link : ($seminar->exact_venue ?? $seminar->meetup_place ?? 'N/A'),
                ];
            });
        $upcomingSeminarsCount = $upcomingSeminars->count();

        // === TO-DO LISTS ===

        // 1. Member Approvals (pending registrations)
        $pendingMembersList = Users_tbl::where('role', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'initials' => strtoupper(substr($user->first_name, 0, 1)),
                    'type' => 'Member Registration',
                    'amount' => null,
                    'time' => $user->created_at ? $user->created_at->diffForHumans() : 'N/A',
                    'created_at' => $user->created_at,
                    'route' => 'dashboard.members',
                ];
            });

        // 2. Loan Applications (pending loans)
        $pendingLoansList = lending_program_tbl::with('user')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => $loan->id,
                    'name' => ($loan->user->first_name ?? 'Unknown') . ' ' . ($loan->user->last_name ?? ''),
                    'initials' => strtoupper(substr($loan->user->first_name ?? 'U', 0, 1)),
                    'type' => 'Loan Application',
                    'amount' => $loan->lending_amount,
                    'time' => $loan->created_at ? $loan->created_at->diffForHumans() : 'N/A',
                    'created_at' => $loan->created_at,
                    'route' => 'lendings',
                ];
            });

        // 3. Withdraw Share Capital (pending withdrawals)
        $pendingWithdrawalsList = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('type', 'Withdrawal')
            ->whereIn('status', ['Pending', 'pending'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $user = $tx->shareCapitalAccount && $tx->shareCapitalAccount->user ? $tx->shareCapitalAccount->user : null;

                return [
                    'id' => $tx->id,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'type' => 'Withdraw Share Capital',
                    'amount' => $tx->total_amount,
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                    'route' => 'sharecapitals',
                ];
            });

        // 4. Savings Deposits
        $pendingSavingsDepositsCount = savings_transaction_tbl::where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();

        $pendingSavingsDepositsList = savings_transaction_tbl::with('savingsAccount.user')
            ->where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $user = $tx->savingsAccount && $tx->savingsAccount->user ? $tx->savingsAccount->user : null;

                return [
                    'id' => $tx->id,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'type' => 'Savings Deposit',
                    'amount' => $tx->amount,
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                    'route' => 'savings',
                ];
            });

        // 5. Savings Withdrawals
        $pendingSavingsWithdrawalsCount = savings_transaction_tbl::where('type', 'withdrawal')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->count();

        $pendingSavingsWithdrawalsList = savings_transaction_tbl::with('savingsAccount.user')
            ->where('type', 'withdrawal')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $user = $tx->savingsAccount && $tx->savingsAccount->user ? $tx->savingsAccount->user : null;

                return [
                    'id' => $tx->id,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'type' => 'Savings Withdrawal',
                    'amount' => $tx->amount,
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                    'route' => 'savings',
                ];
            });

        // 6. Share Capital Deposits
        $pendingShareCapitalDepositsCount = share_capital_transaction_tbl::where('type', 'Deposit')
            ->where('status', 'Pending')
            ->count();

        $pendingShareCapitalDepositsList = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('type', 'Deposit')
            ->where('status', 'Pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $user = $tx->shareCapitalAccount && $tx->shareCapitalAccount->user ? $tx->shareCapitalAccount->user : null;

                return [
                    'id' => $tx->id,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'type' => 'Share Capital Deposit',
                    'amount' => $tx->total_amount,
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                    'route' => 'sharecapitals',
                ];
            });

        // 7. Loan Payments
        $pendingLoanPaymentsCount = lending_repayments_tbl::where('status', 'Pending')
            ->count();

        $pendingLoanPaymentsList = lending_repayments_tbl::with(['lending.user', 'user'])
            ->where('status', 'Pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tx) {
                $user = $tx->user ?? ($tx->lending && $tx->lending->user ? $tx->lending->user : null);

                return [
                    'id' => $tx->id,
                    'name' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'type' => 'Loan Payment',
                    'amount' => $tx->amount_paid,
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                    'route' => 'payments',
                ];
            });

        // Counts for tab badges

        // === RECENT ACTIVITY - SEPARATE BY TYPE ===

        // 4. Transactions (savings + share capital - completed/approved)
        $recentSavingsTx = savings_transaction_tbl::with('savingsAccount.user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($tx) {
                return [
                    'type' => 'savings',
                    'subtype' => $tx->type,
                    'title' => $tx->type === 'deposit' ? 'Savings Deposit' : 'Savings Withdrawal',
                    'user' => ($tx->savingsAccount->user->first_name ?? 'Unknown') . ' ' . ($tx->savingsAccount->user->last_name ?? ''),
                    'initials' => strtoupper(substr($tx->savingsAccount->user->first_name ?? 'U', 0, 1)),
                    'amount' => $tx->amount,
                    'status' => 'Completed',
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                ];
            });

        $recentShareCapitalTx = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->whereIn('status', ['Completed', 'Approved', 'completed', 'approved'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($tx) {
                $user = $tx->shareCapitalAccount && $tx->shareCapitalAccount->user ? $tx->shareCapitalAccount->user : null;

                return [
                    'type' => 'share_capital',
                    'subtype' => strtolower($tx->type),
                    'title' => ucfirst($tx->type) . ' Share Capital',
                    'user' => $user ? $user->first_name . ' ' . $user->last_name : 'Unknown',
                    'initials' => $user ? strtoupper(substr($user->first_name, 0, 1)) : 'U',
                    'amount' => $tx->total_amount,
                    'status' => ucfirst($tx->status),
                    'time' => $tx->created_at ? $tx->created_at->diffForHumans() : 'N/A',
                    'created_at' => $tx->created_at,
                ];
            });

        $recentTransactions = $recentSavingsTx->concat($recentShareCapitalTx)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // 5. Member Approvals (registrations + activations)
        $recentMemberApprovals = Users_tbl::whereIn('role', ['member', 'pending'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'member',
                    'title' => $user->role === 'pending' ? 'New Member Registration' : 'Member Activated',
                    'user' => $user->first_name . ' ' . $user->last_name,
                    'initials' => strtoupper(substr($user->first_name, 0, 1)),
                    'status' => $user->role === 'pending' ? 'Pending' : 'Active',
                    'time' => $user->created_at ? $user->created_at->diffForHumans() : 'N/A',
                    'created_at' => $user->created_at,
                ];
            });

        // Savings by Month (last 6 months) - deposits minus withdrawals
        $savingsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('M');
            $monthNum = now()->subMonths($i)->format('n');
            $year = now()->subMonths($i)->format('Y');

            $deposits = savings_transaction_tbl::whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->where('type', 'deposit')
                ->sum('amount') ?? 0;

            $withdrawals = savings_transaction_tbl::whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNum)
                ->where('type', 'withdrawal')
                ->sum('amount') ?? 0;

            $savingsByMonth[$month] = $deposits - $withdrawals;
        }

        // Member Activity (for pie chart)
        $activeMembers = Users_tbl::where('role', 'member')->count();
        $pendingMembers = Users_tbl::where('role', 'pending')->count();
        $totalUsers = $activeMembers + $pendingMembers;

        $memberActivity = [
            'active' => $totalUsers > 0 ? round(($activeMembers / $totalUsers) * 100) : 0,
            'new' => $totalUsers > 0 ? round(($pendingMembers / $totalUsers) * 100) : 0,
            'inactive' => 0,
        ];

        // Loans by Type (for loan distribution) — dynamic from loan_settings_tbls
        $allLoans = lending_program_tbl::whereIn('status', ['Approved', 'Completed'])->with('user')->get();

        $loanSettingsList = Loan_settings_tbl::where('is_active', true)->orderBy('loan_type')->get();
        $loanTypes = $loanSettingsList->pluck('loan_type')->toArray();

        $loanTypeCounts = array_fill_keys($loanTypes, 0);

        $loansByTypeDetails = array_fill_keys($loanTypes, []);

        foreach ($allLoans as $loan) {
            $loanTypeValue = $loan->lending_type ?? '';
            foreach ($loanTypes as $lt) {
                if ($lt && (stripos($loanTypeValue, $lt) !== false || stripos($lt, $loanTypeValue) !== false || strtolower($loanTypeValue) === strtolower($lt))) {
                    $loanTypeCounts[$lt] = ($loanTypeCounts[$lt] ?? 0) + 1;
                    $loansByTypeDetails[$lt][] = $loan;
                    break;
                }
            }
        }

        // Audit Logs
        $auditLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent Savings Transactions (for modal)
        $recentSavingsTransactions = savings_transaction_tbl::with('savingsAccount.user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'amount' => $tx->amount,
                    'total_amount' => $tx->total_amount,
                    'type' => $tx->type,
                    'reference_no' => $tx->reference_no,
                    'created_at' => $tx->created_at,
                    'user_name' => $tx->savingsAccount && $tx->savingsAccount->user ? $tx->savingsAccount->user->first_name . ' ' . $tx->savingsAccount->user->last_name : 'Unknown',
                ];
            });

        // Announcements
        $announcements = \App\Models\Announcements_tbl::with(['user', 'comments.user', 'likes'])
            ->withCount('likes', 'comments')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        $currentUser = Auth::user();

        // Polls
        $polls = \App\Models\AnnouncementPoll_tbl::with(['user', 'votes'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin_components.dashboard', compact(
            'totalMembers',
            'totalSavings',
            'totalShareCapital',
            'activeLoans',
            'earnedInterests',
            'totalDeposits',
            'totalWithdrawals',
            'pendingMembersList',
            'pendingLoansList',
            'pendingWithdrawalsList',
            'pendingMembersCount',
            'pendingLoansCount',
            'pendingWithdrawalsCount',
            'pendingSavingsDepositsCount',
            'pendingSavingsDepositsList',
            'pendingSavingsWithdrawalsCount',
            'pendingSavingsWithdrawalsList',
            'pendingShareCapitalDepositsCount',
            'pendingShareCapitalDepositsList',
            'pendingLoanPaymentsCount',
            'pendingLoanPaymentsList',
            'pendingResignationsCount',
            'pendingResignationsList',
            'upcomingSeminars',
            'upcomingSeminarsCount',
            'recentTransactions',
            'recentMemberApprovals',
            'savingsByMonth',
            'memberActivity',
            'loanTypeCounts',
            'loansByTypeDetails',
            'loanTypes',
            'auditLogs',
            'announcements',
            'polls',
            'currentUser'
        ));
    }

    public function memberSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = \App\Models\Users_tbl::query()
            ->whereIn('role', ['member', 'pending'])
            ->select('id', 'first_name', 'last_name', 'email', 'role');

        // An empty query is allowed: it returns the suggested list of members.
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$q}%"]);
            });
        }

        return response()->json(
            $query->orderBy('first_name')
                ->orderBy('last_name')
                ->limit($q === '' ? 15 : 20)
                ->get()
        );
    }

    public function dashboard_members(Request $request)
    {
        // Auto-transition resignation_pending → inactive when release_date has passed
        Users_tbl::where('status', 'resignation_pending')
            ->whereIn('id', function ($q) {
                $q->select('user_id')
                    ->from('resignation_requests_tbls')
                    ->where('status', 'approved')
                    ->where('withdraw_share_capital', true)
                    ->where('is_released', false)
                    ->where('release_date', '<=', now()->toDateString());
            })
            ->update(['status' => 'inactive']);

        $query = Users_tbl::query();

        if (!auth()->user()?->isMainAdmin()) {
            $query->whereIn('role', ['member', 'pending', 'inactive']);
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            // Support searching by the displayed ID too, e.g. "MEM-0004" or "ADM-0002"
            $numericId = null;
            if (preg_match('/^(?:MEM|ADM)-0*(\d+)$/i', $search, $m)) {
                $numericId = (int) $m[1];
            } elseif (ctype_digit($search)) {
                $numericId = (int) $search;
            }

            $query->where(function ($q) use ($search, $numericId) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);

                if ($numericId !== null) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        $status = $request->get('filter', 'all');
        if ($status === 'pending') {
            $query->where('role', 'pending');
        } elseif ($status === 'active') {
            $query->where(function ($q) {
                $q->where('role', 'member')
                    ->orWhereNotIn('role', ['member', 'pending', 'inactive']);
            });
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('role', 'inactive')
                    ->orWhere('status', 'inactive');
            });
        }

        // ---------------------------------------------------------------
        // Advanced filters: category, role, sex, age group, address
        // (profile details live in otherinfo_tbls)
        // ---------------------------------------------------------------
        $usersTable = $query->getModel()->getTable();
        $memberRoles = ['member', 'pending', 'inactive'];

        $whereOtherInfo = function ($q, callable $conditions, bool $exists = true) use ($usersTable) {
            $method = $exists ? 'whereExists' : 'whereNotExists';
            $q->{$method}(function ($sub) use ($conditions, $usersTable) {
                $sub->select(DB::raw(1))
                    ->from('otherinfo_tbls')
                    ->whereColumn('otherinfo_tbls.user_id', $usersTable . '.id');
                $conditions($sub);
            });
        };

        $categoryFilter = trim((string) $request->get('category', ''));
        $roleFilter = trim((string) $request->get('role', ''));
        $sexFilter = trim((string) $request->get('sex', ''));
        $ageFilter = trim((string) $request->get('age_group', ''));
        $addressFilter = trim((string) $request->get('address', ''));

        // Membership category (matches what the table's "Category" column shows)
        if ($categoryFilter === 'staff') {
            $query->whereNotIn('role', $memberRoles);
        } elseif ($categoryFilter === 'Investor Associate') {
            // Members with no category saved are shown as Investor Associate
            $query->whereIn('role', $memberRoles);
            $whereOtherInfo($query, function ($s) {
                $s->whereNotNull('membership_category')
                    ->where('membership_category', '!=', '')
                    ->where('membership_category', '!=', 'Investor Associate');
            }, false);
        } elseif ($categoryFilter !== '') {
            $query->whereIn('role', $memberRoles);
            $whereOtherInfo($query, fn($s) => $s->where('membership_category', $categoryFilter));
        }

        // Account role (member, admin, general-manager, ...)
        if ($roleFilter !== '') {
            $query->where('role', $roleFilter);
        }

        // Sex
        if (in_array($sexFilter, ['Male', 'Female'], true)) {
            $whereOtherInfo($query, fn($s) => $s->where('sex', $sexFilter));
        } elseif ($sexFilter === 'unspecified') {
            $whereOtherInfo($query, fn($s) => $s->whereIn('sex', ['Male', 'Female']), false);
        }

        // Age group (computed from date_of_birth)
        $ageGroups = [
            '18-25' => [18, 25],
            '26-35' => [26, 35],
            '36-45' => [36, 45],
            '46-59' => [46, 59],
            '60+' => [60, null],
        ];
        if (isset($ageGroups[$ageFilter])) {
            [$minAge, $maxAge] = $ageGroups[$ageFilter];
            $whereOtherInfo($query, function ($s) use ($minAge, $maxAge) {
                $s->whereNotNull('date_of_birth')
                    ->whereDate('date_of_birth', '<=', now()->subYears($minAge)->toDateString());
                if ($maxAge !== null) {
                    $s->whereDate('date_of_birth', '>', now()->subYears($maxAge + 1)->toDateString());
                }
            });
        } elseif ($ageFilter === 'under-18') {
            $whereOtherInfo($query, fn($s) => $s->whereNotNull('date_of_birth')
                ->whereDate('date_of_birth', '>', now()->subYears(18)->toDateString()));
        } elseif ($ageFilter === 'unknown') {
            $whereOtherInfo($query, fn($s) => $s->whereNotNull('date_of_birth'), false);
        }

        // Address (present or permanent)
        if ($addressFilter !== '') {
            $whereOtherInfo($query, function ($s) use ($addressFilter) {
                $s->where(function ($w) use ($addressFilter) {
                    $w->where('present_address', 'like', "%{$addressFilter}%")
                        ->orWhere('permanent_address', 'like', "%{$addressFilter}%");
                });
            });
        }

        // Suggestions for the address box (distinct saved addresses)
        $addressOptions = DB::table('otherinfo_tbls')
            ->whereNotNull('present_address')
            ->where('present_address', '!=', '')
            ->distinct()
            ->orderBy('present_address')
            ->limit(100)
            ->pluck('present_address');

        $activeFilterCount = collect([$categoryFilter, $roleFilter, $sexFilter, $ageFilter, $addressFilter])
            ->filter(fn($v) => $v !== '')
            ->count();

        // Counts for the "Filtered Results" card (respects every filter above)
        $resultStats = ['total' => (clone $query)->count()];
        foreach (['male' => 'Male', 'female' => 'Female'] as $key => $sexValue) {
            $countQuery = clone $query;
            $whereOtherInfo($countQuery, fn($s) => $s->where('sex', $sexValue));
            $resultStats[$key] = $countQuery->count();
        }
        $resultStats['unspecified'] = max(0, $resultStats['total'] - $resultStats['male'] - $resultStats['female']);

        $members = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $pendingRequests = Users_tbl::where('role', 'pending')->orderBy('id', 'asc')->get();

        $adminList = Users_tbl::whereNotIn('role', ['member', 'pending', 'inactive'])
            ->orderBy('id')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'status' => $user->status,
                    'sidebar_permissions' => $user->sidebar_permissions,
                    'is_main' => $user->isMainAdmin(),
                ];
            });

        $resignationRequests = \App\Models\ResignationRequest_tbl::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $inProcessResignations = \App\Models\ResignationRequest_tbl::with('user')
            ->where('status', 'approved')
            ->where('is_released', false)
            ->where('withdraw_share_capital', true)
            ->orderBy('release_date', 'asc')
            ->get();

        $resignees = \App\Models\ResignationRequest_tbl::with('user')
            ->where(function ($q) {
                $q->where('is_released', true)
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'approved')
                            ->where('withdraw_share_capital', false);
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedResignations = \App\Models\ResignationRequest_tbl::with('user')
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        $rejectedResignations = \App\Models\ResignationRequest_tbl::with('user')
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        $reactivationRequests = Users_tbl::where('status', 'reactivation_pending')
            ->orderBy('updated_at', 'desc')
            ->get();

        $memberCategoryCounts = DB::table('otherinfo_tbls')
            ->join('users_tbls', 'otherinfo_tbls.user_id', '=', 'users_tbls.id')
            ->where('users_tbls.role', 'member')
            ->select('otherinfo_tbls.membership_category', DB::raw('COUNT(*) as count'))
            ->groupBy('otherinfo_tbls.membership_category')
            ->pluck('count', 'membership_category');

        $adminCategoryCounts = DB::table('otherinfo_tbls')
            ->join('users_tbls', 'otherinfo_tbls.user_id', '=', 'users_tbls.id')
            ->whereIn('users_tbls.role', ['admin', 'general-manager'])
            ->select('otherinfo_tbls.membership_category', DB::raw('COUNT(*) as count'))
            ->groupBy('otherinfo_tbls.membership_category')
            ->pluck('count', 'membership_category');

        $roles = \App\Models\Role::orderBy('name')->get();
        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role->slug] = Users_tbl::where('role', $role->slug)->count();
        }

        $memberIds = $members->pluck('id')->toArray();

        $resignationRequestsByUser = \App\Models\ResignationRequest_tbl::whereIn('user_id', $memberIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id');

        $shareCapitals = DB::table('share_capital_account_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $scAccountIds = $shareCapitals->pluck('id')->filter()->values()->toArray();
        $paidUpByAccount = \App\Http\Controllers\ShareCapital::paidUpForAccounts($scAccountIds);

        $otherInfo = DB::table('otherinfo_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $spouseInfo = DB::table('family_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $govIds = DB::table('membergovern_ids_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $vehicles = DB::table('membervehi_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->groupBy('user_id');

        $savingsAccounts = DB::table('savings_account_tbls')
            ->whereIn('user_id', $memberIds)
            ->get()
            ->keyBy('user_id');

        $activeLoans = DB::table('lending_program_tbls')
            ->whereIn('user_id', $memberIds)
            ->where('status', 'Approved')
            ->get()
            ->groupBy('user_id');

        $members->getCollection()->transform(function ($member) use ($shareCapitals, $otherInfo, $spouseInfo, $govIds, $vehicles, $savingsAccounts, $activeLoans, $paidUpByAccount, $resignationRequestsByUser) {
            $sc = $shareCapitals->get($member->id);
            $scLedger = $sc ? ($paidUpByAccount[$sc->id] ?? ['shares' => 0, 'amount' => 0]) : ['shares' => 0, 'amount' => 0];
            $member->sc_total_amount = $scLedger['amount'];
            $member->sc_total_shares = $scLedger['shares'];
            $member->sc_status = $sc ? ($sc->status ?? 'No Account') : 'No Account';

            $other = $otherInfo->get($member->id);
            $member->profile_picture = $other->profile_picture ?? null;
            $member->skills = $other->skills ?? null;
            $member->membership_category = $other->membership_category ?? 'Investor Associate';
            $member->signature = $other->signature ?? null;

            // Personal details the member fills out (stored in otherinfo_tbls)
            foreach ([
                'date_of_birth',
                'place_of_birth',
                'sex',
                'civil_status',
                'citizenship',
                'contact_no',
                'present_address',
                'permanent_address',
                'blood_type',
                'height',
                'weight',
                'monthly_income',
                'membership_status',
            ] as $field) {
                $member->{$field} = $other->{$field} ?? $member->{$field} ?? null;
            }

            $spouse = $spouseInfo->get($member->id);
            $member->spouse_name = $spouse->spouse_name ?? null;
            $member->spouse_date_birth = $spouse->spouse_date_birth ?? null;
            $member->spouse_place_birth = $spouse->spouse_place_birth ?? null;
            $member->number_son = $spouse->number_son ?? 0;
            $member->number_daughter = $spouse->number_daughter ?? 0;

            $gov = $govIds->get($member->id);
            $member->sss_id = $gov->sss_id ?? null;
            $member->philhealth_id = $gov->philhealth_id ?? null;
            $member->pagibig_id = $gov->pagibig_id ?? null;
            $member->tin_id = $gov->tin_id ?? null;

            $memberVehicles = $vehicles->get($member->id, collect());
            $member->vehicles = $memberVehicles->map(function ($v) {
                return [
                    'vehicle_type' => $v->vehicle_type,
                    'plate_no' => $v->plate_no,
                    'quantity' => $v->quantity ?? 1,
                ];
            })->values()->toArray();

            $sa = $savingsAccounts->get($member->id);
            $member->savings_balance = $sa ? (new \App\Http\Controllers\SavingsController)->computeSavingsBalance($sa->id) : 0;
            $member->savings_status = $sa ? ($sa->status ?? 'No Account') : 'No Account';
            $member->savings_interest = $sa ? ($sa->interest_accrued_balance ?? 0) : 0;

            $loans = $activeLoans->get($member->id, collect());
            $member->active_loans = $loans->map(function ($l) {
                return [
                    'id' => $l->id,
                    'reference_no' => $l->reference_no,
                    'lending_type' => $l->lending_type,
                    'lending_amount' => $l->lending_amount,
                    'monthly_payment' => $l->monthly_payment,
                    'total_payment' => $l->total_payment,
                ];
            })->values()->toArray();
            $member->active_loans_count = $loans->count();

            $latestResignation = $resignationRequestsByUser->get($member->id)?->first();
            $member->resignation_request = $latestResignation ? [
                'id' => $latestResignation->id,
                'status' => $latestResignation->status,
                'withdraw_share_capital' => (bool) $latestResignation->withdraw_share_capital,
                'created_at' => $latestResignation->created_at,
                'release_date' => $latestResignation->release_date,
                'is_released' => (bool) $latestResignation->is_released,
                'rejection_reason' => $latestResignation->rejection_reason ?? null,
            ] : null;

            return $member;
        });

        return view('admin_components.members', compact('members', 'pendingRequests', 'adminList', 'memberCategoryCounts', 'adminCategoryCounts', 'resignationRequests', 'inProcessResignations', 'resignees', 'rejectedResignations', 'reactivationRequests', 'roles', 'roleCounts', 'addressOptions', 'activeFilterCount', 'resultStats'));
    }

    public function dashboard_savings(Request $request)
    {
        $search = $request->get('search', '');
        $typeFilter = $request->get('type', 'all');
        $statusFilter = $request->get('status', 'all');

        $query = savings_transaction_tbl::with('savingsAccount.user');

        if ($search) {
            $query->whereHas('savingsAccount.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        $currentBalance = (savings_transaction_tbl::where('type', 'deposit')->sum('amount') ?? 0) - (savings_transaction_tbl::where('type', 'withdrawal')->sum('amount') ?? 0);

        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();
        $monthlyAvg = savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->sum('amount') / 6;

        $monthlyWithdrawalAvg = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->sum('amount') / 6;

        $thisMonth = now()->startOfMonth();
        $monthlyDeposits = (savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount') ?? 0)
            - (savings_transaction_tbl::where('type', 'withdrawal')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount') ?? 0);

        $monthlyWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount') ?? 0;

        $monthlyWithdrawalsCount = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $thisMonth)
            ->count() ?? 0;

        $today = now()->startOfDay();
        $todayDeposits = savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $lastContribution = savings_transaction_tbl::orderByDesc('created_at')->first();

        $totalDeposits = savings_transaction_tbl::where('type', 'deposit')->sum('amount') ?? 0;
        $totalWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')->sum('amount') ?? 0;

        $totalSavingsBalance = savings_account_tbl::sum('balance') ?? 0;
        $savingsAccounts = savings_account_tbl::with('user')->orderByDesc('balance')->get();

        $sirSettings = SavingsInterestSetting::getOrCreate();
        $minBalanceForInterest = (float) $sirSettings->min_balance_for_interest;

        $eligibleAccounts = [];
        $notEligibleAccounts = [];

        foreach ($savingsAccounts as $account) {
            $reasons = [];
            if ($account->status !== 'active') {
                $reasons[] = 'Account is not active';
            }
            if ((float) $account->balance < $minBalanceForInterest) {
                $reasons[] = 'Balance below minimum (₱' . number_format($minBalanceForInterest, 2) . ' required)';
            }
            if (empty($reasons)) {
                $eligibleAccounts[] = $account;
            } else {
                $notEligibleAccounts[] = ['account' => $account, 'reasons' => $reasons];
            }
        }

        $eligibleCount = count($eligibleAccounts);
        $notEligibleCount = count($notEligibleAccounts);
        $activeSavingsAccounts = count($eligibleAccounts);

        $allMembers = Users_tbl::memberCandidates()
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        $monthlyData = [];
        $maxAmount = 0;
        $processedMonths = [];

        // Use simple sequential months from current month backward
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');

            // Skip if we've already processed this month
            if (in_array($monthKey, $processedMonths)) {
                continue;
            }
            $processedMonths[] = $monthKey;

            $amount = (float) savings_transaction_tbl::where('type', 'deposit')
                ->whereYear('created_at', $monthDate->format('Y'))
                ->whereMonth('created_at', $monthDate->format('m'))
                ->sum('amount');

            $monthlyData[] = [
                'name' => $monthDate->format('F'),
                'year' => $monthDate->format('Y'),
                'amount' => $amount,
            ];
            if ($amount > $maxAmount) {
                $maxAmount = $amount;
            }
        }

        // Ensure we have exactly 6 months
        if (count($monthlyData) > 6) {
            $monthlyData = array_slice($monthlyData, -6);
        }

        foreach ($monthlyData as &$data) {
            $data['bar_height'] = $maxAmount > 0 ? ($data['amount'] / $maxAmount) * 150 : 0;
        }

        $highestMonth = [
            'name' => 'N/A',
            'amount' => 0,
        ];
        foreach ($monthlyData as $data) {
            if ($data['amount'] > $highestMonth['amount']) {
                $highestMonth = $data;
            }
        }

        $monthlyWithdrawalData = [];
        $maxWithdrawalAmount = 0;
        $processedMonths = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');

            // Skip if we've already processed this month
            if (in_array($monthKey, $processedMonths)) {
                continue;
            }
            $processedMonths[] = $monthKey;

            $amount = (float) savings_transaction_tbl::where('type', 'withdrawal')
                ->whereYear('created_at', $monthDate->format('Y'))
                ->whereMonth('created_at', $monthDate->format('m'))
                ->sum('amount');

            $monthlyWithdrawalData[] = [
                'name' => $monthDate->format('F'),
                'year' => $monthDate->format('Y'),
                'amount' => $amount,
            ];
            if ($amount > $maxWithdrawalAmount) {
                $maxWithdrawalAmount = $amount;
            }
        }

        // Ensure we have exactly 6 months
        if (count($monthlyWithdrawalData) > 6) {
            $monthlyWithdrawalData = array_slice($monthlyWithdrawalData, -6);
        }

        foreach ($monthlyWithdrawalData as &$data) {
            $data['bar_height'] = $maxWithdrawalAmount > 0 ? ($data['amount'] / $maxWithdrawalAmount) * 150 : 0;
        }

        \Log::info('Monthly Withdrawal Data (' . count($monthlyWithdrawalData) . ' months): ' . json_encode($monthlyWithdrawalData));

        $highestWithdrawalMonth = [
            'name' => 'N/A',
            'amount' => 0,
        ];
        foreach ($monthlyWithdrawalData as $data) {
            if ($data['amount'] > $highestWithdrawalMonth['amount']) {
                $highestWithdrawalMonth = $data;
            }
        }

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        $pendingWithdrawals = savings_transaction_tbl::with('savingsAccount.user.otherinfo')
            ->where('type', 'withdrawal')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest()
            ->get();

        $pendingDeposits = savings_transaction_tbl::with('savingsAccount.user.otherinfo')
            ->where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->latest()
            ->get();

        return view('admin_components.savings', compact(
            'transactions',
            'currentBalance',
            'monthlyAvg',
            'lastContribution',
            'totalDeposits',
            'totalWithdrawals',
            'allMembers',
            'highestMonth',
            'monthlyData',
            'monthlyDeposits',
            'todayDeposits',
            'monthlyWithdrawals',
            'monthlyWithdrawalsCount',
            'totalSavingsBalance',
            'activeSavingsAccounts',
            'monthlyWithdrawalAvg',
            'paymentMethods',
            'eligibleCount',
            'notEligibleCount',
            'eligibleAccounts',
            'notEligibleAccounts',
            'sirSettings',
            'savingsAccounts',
            'pendingWithdrawals',
            'pendingDeposits',
        ));
    }

    public function updateSavingsStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|string|in:accept,reject',
        ]);

        $transaction = savings_transaction_tbl::findOrFail($id);

        // Segregation of duties: the creator cannot be the approver unless GM.
        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if (!in_array($transaction->type, ['deposit', 'withdrawal'])) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction type cannot be approved/rejected here.',
            ], 400);
        }

        if (strtolower($transaction->status) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been processed.',
            ], 400);
        }

        $action = $request->action;
        $savingsAccount = savings_account_tbl::findOrFail($transaction->savings_account_id);

        if ($action === 'accept') {
            if ($transaction->type === 'withdrawal' && $savingsAccount->balance < $transaction->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance to approve this withdrawal. Available: ₱' . number_format($savingsAccount->balance, 2),
                ], 400);
            }

            $newBalance = $transaction->type === 'deposit'
                ? $savingsAccount->balance + $transaction->amount
                : $savingsAccount->balance - $transaction->amount;

            $savingsAccount->update(['balance' => $newBalance]);

            $transaction->approved_by = Auth::id();
            $transaction->status = 'Completed';
            $transaction->balance_after = $newBalance;
            $transaction->save();

            AuditLog::log(
                'Approved Savings ' . ucfirst($transaction->type),
                "Approved {$transaction->type} of ₱{$transaction->amount} (Ref: {$transaction->reference_no})",
                'savings',
                $id
            );

            return response()->json([
                'success' => true,
                'message' => ucfirst($transaction->type) . ' approved successfully.',
                'new_balance' => $newBalance,
            ]);
        } else {
            $transaction->status = 'Rejected';
            $transaction->save();

            AuditLog::log(
                'Rejected Savings ' . ucfirst($transaction->type),
                "Rejected {$transaction->type} of ₱{$transaction->amount} (Ref: {$transaction->reference_no})",
                'savings',
                $id
            );

            return response()->json([
                'success' => true,
                'message' => ucfirst($transaction->type) . ' request rejected.',
            ]);
        }
    }

    public function disburseSavingsWithdrawal(Request $request, $id)
    {
        $request->validate([
            'admin_receipt' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $transaction = savings_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'withdrawal') {
            return response()->json([
                'success' => false,
                'message' => 'Only withdrawal transactions can be disbursed.',
            ], 400);
        }

        if (strtolower($transaction->status) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been processed.',
            ], 400);
        }

        $savingsAccount = savings_account_tbl::findOrFail($transaction->savings_account_id);

        if ($savingsAccount->balance < $transaction->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance to disburse this withdrawal. Available: ₱' . number_format($savingsAccount->balance, 2),
            ], 400);
        }

        $receiptPath = $request->file('admin_receipt')->store('admin_receipts', 'public');

        $newBalance = $savingsAccount->balance - $transaction->amount;

        $savingsAccount->update(['balance' => $newBalance]);

        $transaction->status = 'Completed';
        $transaction->balance_after = $newBalance;
        $transaction->gcash_proof_path = $receiptPath;
        $transaction->save();

        $memberId = $savingsAccount->user_id;
        \App\Models\Notifications_tbl::create([
            'user_id' => $memberId,
            'title' => 'Savings Withdrawal Disbursed',
            'message' => 'Your savings withdrawal of ₱' . number_format($transaction->amount, 2) . ' has been disbursed via GCash (Ref: ' . $transaction->reference_no . '). Your new savings balance is ₱' . number_format($newBalance, 2) . '.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Disbursed Savings Withdrawal',
            "Disbursed withdrawal of ₱{$transaction->amount} (Ref: {$transaction->reference_no}). New balance: ₱{$newBalance}",
            'savings',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal disbursed successfully. Member has been notified.',
            'new_balance' => $newBalance,
        ]);
    }

    public function completeSavingsDeposit(Request $request, $id)
    {
        $transaction = savings_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'deposit') {
            return response()->json([
                'success' => false,
                'message' => 'Only deposit transactions can be completed here.',
            ], 400);
        }

        if (strtolower($transaction->status) !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been processed.',
            ], 400);
        }

        $savingsAccount = savings_account_tbl::findOrFail($transaction->savings_account_id);

        $newBalance = $savingsAccount->balance + $transaction->amount;

        $savingsAccount->update(['balance' => $newBalance]);

        $transaction->status = 'Completed';
        $transaction->balance_after = $newBalance;
        $transaction->save();

        $memberId = $savingsAccount->user_id;
        \App\Models\Notifications_tbl::create([
            'user_id' => $memberId,
            'title' => 'Savings Deposit Confirmed',
            'message' => 'Your savings deposit of ₱' . number_format($transaction->amount, 2) . ' has been confirmed. Your new savings balance is ₱' . number_format($newBalance, 2) . '. (Ref: ' . $transaction->reference_no . ')',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Completed Savings Deposit',
            "Confirmed deposit of ₱{$transaction->amount} (Ref: {$transaction->reference_no}). New balance: ₱{$newBalance}",
            'savings',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit confirmed successfully. Member has been notified.',
            'new_balance' => $newBalance,
        ]);
    }

    public function voidSavingsDeposit(Request $request, $id)
    {
        $request->validate([
            'void_reason' => 'required|string|in:wrong_amount,duplicate_payment,fraudulent,other_member,technical_error,other',
        ]);

        $transaction = savings_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'deposit') {
            return response()->json(['success' => false, 'message' => 'Only deposit transactions can be returned.'], 400);
        }

        if (strtolower($transaction->status) !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending deposits can be returned.'], 400);
        }

        $transaction->update([
            'status' => 'voided',
            'void_reason' => $request->void_reason,
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        $savingsAccount = savings_account_tbl::findOrFail($transaction->savings_account_id);
        $reasonText = $this->returnReasonLabel($request->void_reason);

        \App\Models\Notifications_tbl::create([
            'user_id' => $savingsAccount->user_id,
            'title' => 'Savings Deposit Returned',
            'message' => 'Your savings deposit of ₱' . number_format($transaction->amount, 2) . " has been returned by the admin. Reason: {$reasonText}. (Ref: {$transaction->reference_no})",
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Returned Savings Deposit',
            "Returned deposit of ₱{$transaction->amount} (Ref: {$transaction->reference_no}). Reason: {$reasonText}",
            'savings',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit marked as returned. Member has been notified.',
        ]);
    }

    public function dashboard_lendings(Request $request)
    {
        $statusFilter = $request->get('status', 'pending');
        $search = $request->get('search', '');

        $query = lending_program_tbl::with([
            'user',
            'repayments' => function ($q) {
                $q->select('id', 'lending_id', 'payment_number');
            }
        ]);

        if ($statusFilter !== 'all') {
            $query->where('status', ucfirst($statusFilter));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('lending_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                    });
            });
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(10);

        $allMembers = Users_tbl::memberCandidates()
            ->orderBy('first_name')
            ->get();

        $loanSettings = Loan_settings_tbl::pluck('interest_rate', 'loan_type')->toArray();

        $loanChargeSettings = Loan_settings_tbl::where('is_active', true)->get()->keyBy('loan_type');

        $lateFeeSettings = Loan_settings_tbl::first();
        $lateFeePercentage = $lateFeeSettings->late_fee_percentage ?? 2.00;
        $gracePeriodMonths = $lateFeeSettings->grace_period_months ?? 1;

        $penalizedLoans = $this->calculatePenalties($lateFeePercentage, $gracePeriodMonths);

        return view('admin_components.lending', compact('loans', 'statusFilter', 'search', 'allMembers', 'loanSettings', 'loanChargeSettings', 'lateFeePercentage', 'gracePeriodMonths', 'penalizedLoans'));
    }

    private function calculatePenalties($lateFeePercentage, $gracePeriodMonths)
    {
        $today = now();
        $penalizedLoans = [];

        $approvedLoans = lending_program_tbl::with('user')
            ->where('status', 'Approved')
            ->get();

        foreach ($approvedLoans as $loan) {
            $termMonths = (int) filter_var($loan->lending_type_term, FILTER_SANITIZE_NUMBER_INT);

            if (!$loan->due_date && $loan->created_at) {
                $dueDate = $loan->created_at->addMonths($termMonths);
                $loan->due_date = $dueDate->format('Y-m-d');
                $loan->save();
            }

            if (!$loan->due_date) {
                continue;
            }

            $dueDate = \Carbon\Carbon::parse($loan->due_date);
            $penaltyStartDate = $dueDate->copy()->addMonths($gracePeriodMonths);

            if ($today->gte($penaltyStartDate)) {
                $monthsOverdue = $dueDate->diffInMonths($today) - $gracePeriodMonths;
                $monthsOverdue = max(0, $monthsOverdue);

                if ($monthsOverdue > 0) {
                    $lateFee = $loan->lending_amount * ($lateFeePercentage / 100) * $monthsOverdue;

                    $penalizedLoans[] = [
                        'id' => $loan->id,
                        'member_name' => ($loan->user->first_name ?? 'Unknown') . ' ' . ($loan->user->last_name ?? ''),
                        'lending_amount' => $loan->lending_amount,
                        'due_date' => $loan->due_date,
                        'months_overdue' => $monthsOverdue,
                        'late_fee' => $lateFee,
                        'status' => 'Overdue',
                    ];
                }
            }
        }

        return $penalizedLoans;
    }

    public function updateLoanSettings(Request $request)
    {
        $request->validate([
            'late_fee_percentage' => 'required|numeric|min:0|max:100',
            'grace_period_months' => 'required|integer|min:0|max:12',
        ]);

        Loan_settings_tbl::query()->update([
            'late_fee_percentage' => $request->late_fee_percentage,
            'grace_period_months' => $request->grace_period_months,
        ]);

        AuditLog::log(
            'Updated Loan Settings',
            "Updated loan penalty settings: late fee {$request->late_fee_percentage}%, grace period {$request->grace_period_months} month(s)",
            'settings',
            null
        );

        return redirect()->back()->with('success', 'Loan penalty settings updated successfully.');
    }

    public function createLoanAdmin(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'lending_type' => 'required|string',
            'lending_amount' => 'required|numeric|min:1',
            'lending_type_term' => 'required|string',
            'monthly_income' => 'required|numeric|min:0',
            'purpose_loan' => 'required|string',
        ]);

        $member = Users_tbl::findOrFail($request->member_id);

        // Server-authoritative calculation: never trust the client-computed
        // monthly_payment / total_payment / total_interest hidden fields.
        $settings = DB::table('loan_settings_tbls')
            ->where('loan_type', $request->lending_type)
            ->where('is_active', true)
            ->first();

        if (!$settings) {
            return redirect()->back()
                ->with('error', 'Loan settings are not configured for this loan type.');
        }

        $loanCalc = new \App\Services\LoanCalculationService;
        $schedule = $loanCalc->buildSchedule(
            (float) $request->lending_amount,
            (float) $settings->interest_rate,
            (int) filter_var($request->lending_type_term, FILTER_SANITIZE_NUMBER_INT)
        );

        $latestLoan = lending_program_tbl::orderBy('reference_no', 'desc')->first();
        $newRefNumber = $latestLoan ? intval(substr($latestLoan->reference_no, 4)) + 1 : 1;
        $referenceNo = 'LN-' . str_pad($newRefNumber, 4, '0', STR_PAD_LEFT);

        // Compute charges from the configured loan settings — mirrors the member
        // application path (lendingController@lendingProgram) so admin-created
        // loans carry the same fee breakdown.
        $principal = (float) $request->lending_amount;
        $termMonths = (int) filter_var($request->lending_type_term, FILTER_SANITIZE_NUMBER_INT);
        $processingFee = round($principal * ($settings->processing_fee_rate / 100), 2);
        $serviceFee = round($principal * ($settings->service_fee_rate / 100), 2);
        $loanProtectionFee = round($settings->loan_protection_fee * $termMonths, 2);
        $retentionRateApplied = $settings->retention_unpaid_rate;
        $retentionAmount = round($principal * ($retentionRateApplied / 100), 2);
        $netProceeds = round($principal - $processingFee - $serviceFee - $loanProtectionFee - $retentionAmount, 2);

        $loan = lending_program_tbl::create([
            'user_id' => $request->member_id,
            'reference_no' => $referenceNo,
            'lending_type' => $request->lending_type,
            'lending_amount' => $request->lending_amount,
            'lending_type_term' => $request->lending_type_term,
            'monthly_income' => $request->monthly_income,
            'monthly_payment' => $loanCalc->averageMonthlyPayment($schedule),
            'total_payment' => $schedule['total_payment'],
            'total_interest' => $schedule['total_interest'],
            'processing_fee_rate' => $processingFee,
            'service_fee_rate' => $serviceFee,
            'loan_protection_fee' => $loanProtectionFee,
            'retention_paid_rate' => $retentionRateApplied == $settings->retention_paid_rate ? $retentionAmount : 0,
            'retention_unpaid_rate' => $retentionRateApplied == $settings->retention_unpaid_rate ? $retentionAmount : 0,
            'net_proceeds' => $netProceeds,
            'purpose_loan' => $request->purpose_loan,
            'status' => 'Approved',
            'due_date' => now()->addMonths((int) filter_var($request->lending_type_term, FILTER_SANITIZE_NUMBER_INT))->format('Y-m-d'),
        ]);

        // Persist the installment schedule so repayments allocate per-installment.
        $loanCalc->persistSchedule($loan->id, $schedule);

        if ($request->hasFile('valid_id')) {
            $loan->valid_id = $request->file('valid_id')->store('loan_documents', 'public');
        }
        if ($request->hasFile('proof_of_income')) {
            $loan->proof_of_income = $request->file('proof_of_income')->store('loan_documents', 'public');
        }
        $loan->save();

        AuditLog::log(
            'Admin Created Loan',
            "Created {$request->lending_type} loan of ₱{$request->lending_amount} for {$member->first_name} {$member->last_name} (Ref: {$referenceNo})",
            'loan',
            $loan->id
        );

        return redirect()->back()->with('success', 'Loan created successfully for ' . $member->first_name . ' ' . $member->last_name);
    }

    public function approveLoan(Request $request, $id)
    {
        $loan = lending_program_tbl::findOrFail($id);
        $loan->status = 'Approved';
        $loan->save();

        $user = $loan->user;
        AuditLog::log(
            'Approved Loan',
            "Approved loan (Ref: {$loan->reference_no}) for {$user->first_name} {$user->last_name} - ₱" . number_format($loan->lending_amount, 2),
            'loan',
            $id
        );

        return redirect()->back()->with('success', 'Loan application approved successfully.');
    }

    public function declineLoan(Request $request, $id)
    {
        $request->validate([
            'decline_reason' => 'required|string|max:500',
        ]);

        $loan = lending_program_tbl::findOrFail($id);
        $loan->status = 'Declined';
        $loan->decline_reason = $request->decline_reason;
        $loan->save();

        $user = $loan->user;
        AuditLog::log(
            'Declined Loan',
            "Declined loan (Ref: {$loan->reference_no}) for {$user->first_name} {$user->last_name} - Reason: {$request->decline_reason}",
            'loan',
            $id
        );

        return redirect()->back()->with('error', 'Loan application declined.');
    }

    public function dashboard_sharecapitals(Request $request)
    {
        $search = $request->get('search', '');
        $typeFilter = $request->get('type', 'all');
        $statusFilter = $request->get('status', 'all');

        $query = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('status', '!=', 'failed');

        if ($search) {
            $query->whereHas('shareCapitalAccount.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        $totalContributions = share_capital_transaction_tbl::whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
            ->where('status', 'Completed')
            ->sum('total_amount') ?? 0;

        $totalContributions = $totalContributions - (
            share_capital_transaction_tbl::where('type', 'Withdrawal')
                ->whereIn('status', ['Approved', 'approved'])
                ->sum('total_amount') ?? 0
        );

        $perShareValue = ShareCapital::PAR_VALUE;

        $accounts = share_capital_account_tbl::with('user')->get();

        $paidUp = ShareCapital::paidUpForAccounts($accounts->pluck('id')->all());

        $eligibleAccounts = collect($paidUp)
            ->filter(fn($stats) => $stats['shares'] >= ShareCapital::ELIGIBLE_SHARES)
            ->count();

        $shareCapitalAccounts = $accounts
            ->each(function ($account) use ($paidUp) {
                $stats = $paidUp[$account->id] ?? ['shares' => 0.0, 'amount' => 0.0];
                $account->paid_up_shares = $stats['shares'];
                $account->paid_up_amount = $stats['amount'];
            })
            ->sortByDesc('paid_up_amount')
            ->values();

        $allMembers = Users_tbl::memberCandidates()
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        $pendingReleases = \App\Models\ResignationRequest_tbl::with('user.shareCapitalAccount')
            ->where('status', 'approved')
            ->where('withdraw_share_capital', true)
            ->where('is_released', false)
            ->orderBy('release_date', 'asc')
            ->get();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        $pendingSCDeposits = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('type', 'Deposit')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin_components.sharecapitals', compact(
            'transactions',
            'totalContributions',
            'perShareValue',
            'eligibleAccounts',
            'shareCapitalAccounts',
            'allMembers',
            'pendingReleases',
            'paymentMethods',
            'pendingSCDeposits'
        ));
    }

    public function adminStoreShareCapital(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'shares' => 'required|numeric|min:0.5',
            'type' => 'required|string|in:Deposit,Withdrawal',
            'payment_method' => ['nullable', 'string', Rule::requiredIf($request->type === 'Deposit')],
            'note' => 'nullable|string|max:255',
        ]);

        // Segregation of duties: an Allied Worker may not process their own
        // member account (self-processing block). Only the GM may do so.
        if (
            \App\Services\SoDGuard::actingAsStaff() && !\App\Services\SoDGuard::isGeneralManager()
            && (int) Auth::id() === (int) $request->member_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot process a transaction for your own member account (self-processing is blocked).',
            ], 422);
        }

        $memberId = $request->member_id;
        $shares = (float) $request->shares;
        $amountPerShare = ShareCapital::PAR_VALUE;
        $totalAmount = $shares * $amountPerShare;
        $type = $request->type;
        $perShareValue = ShareCapital::PAR_VALUE;

        $account = share_capital_account_tbl::where('user_id', $memberId)->first();

        if (!$account) {
            $account = share_capital_account_tbl::create([
                'user_id' => $memberId,
                'total_shares' => 0,
                'total_amount' => 0,
                'status' => 'Active',
                'acquired_date' => Carbon::now(),
            ]);
        }

        if ($type === 'Withdrawal') {
            if ($account->total_shares < $shares) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient shares. Available: ' . $account->total_shares . ' shares',
                ], 422);
            }

            $newShares = $account->total_shares - $shares;
            $newAmount = $account->total_amount - $totalAmount;

            // Full withdrawal via admin → auto-resignation
            if ($newShares <= 0) {
                $existing = \App\Models\ResignationRequest_tbl::where('user_id', $memberId)
                    ->whereIn('status', ['pending'])
                    ->first();

                if (!$existing) {
                    \App\Models\ResignationRequest_tbl::create([
                        'user_id' => $memberId,
                        'withdraw_share_capital' => true,
                        'status' => 'pending',
                    ]);

                    Users_tbl::where('id', $memberId)->update(['status' => 'resignation_pending']);
                }

                AuditLog::log(
                    'Admin Withdrew All Share Capital',
                    "Full withdrawal of {$account->total_shares} shares for member (ID: {$memberId}) triggered auto-resignation",
                    'share_capital',
                    $account->id
                );

                return response()->json([
                    'success' => true,
                    'warning' => true,
                    'message' => 'Full withdrawal processed. An auto-resignation request has been created for this member, subject to the 60-day release rule.',
                    'reference_no' => 'N/A',
                    'new_shares' => 0,
                    'new_amount' => 0,
                ]);
            }
        } else {
            $newShares = $account->total_shares + $shares;
            $newAmount = $account->total_amount + $totalAmount;
        }

        $account->update([
            'total_shares' => $newShares,
            'total_amount' => $newAmount,
        ]);

        $referenceNo = 'SC-' . date('YmdHis') . rand(10, 99);

        share_capital_transaction_tbl::create([
            'share_capital_account_id' => $account->id,
            'user_id' => $memberId,
            'type' => $type,
            'shares' => $shares,
            'amount_per_share' => $amountPerShare,
            'total_amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'reference_no' => $referenceNo,
            'transaction_date' => Carbon::today(),
            'status' => 'Completed',
            'note' => $request->note,
            'created_by' => Auth::id(),
            'approved_by' => Auth::id(),
        ]);

        $member = Users_tbl::find($memberId);
        AuditLog::log(
            'Admin ' . ucfirst($type) . ' Share Capital',
            ucfirst($type) . " of {$shares} shares (₱{$totalAmount}) for {$member?->first_name} {$member?->last_name} (Ref: {$referenceNo})",
            'share_capital',
            $account->id
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' of ' . $shares . ' shares (₱' . number_format($totalAmount, 2) . ') successful!',
            'reference_no' => $referenceNo,
            'new_shares' => $newShares,
            'new_amount' => $newAmount,
        ]);
    }

    public function getMemberShareCapitalBalance($memberId)
    {
        $account = share_capital_account_tbl::where('user_id', $memberId)->first();
        $member = Users_tbl::with('otherinfo')->find($memberId);

        $stats = $account ? ShareCapital::paidUpForAccount($account->id) : ['shares' => 0.0, 'amount' => 0.0];

        return response()->json([
            'total_shares' => $stats['shares'],
            'total_amount' => $stats['amount'],
            'contact_no' => $member?->otherinfo?->contact_no,
        ]);
    }

    public function updateWithdrawalStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|string|in:accept,reject',
        ]);

        $transaction = share_capital_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'Withdrawal') {
            return response()->json([
                'success' => false,
                'message' => 'This is not a withdrawal transaction.',
            ], 400);
        }

        if ($transaction->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This withdrawal has already been processed.',
            ], 400);
        }

        $action = $request->action;

        if ($action === 'accept') {
            $account = DB::table('share_capital_account_tbls')
                ->where('id', $transaction->share_capital_account_id)
                ->first();

            if (!$account || $account->total_shares < $transaction->shares) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient shares to process this withdrawal.',
                ], 400);
            }

            DB::table('share_capital_account_tbls')
                ->where('id', $transaction->share_capital_account_id)
                ->decrement('total_shares', $transaction->shares);

            DB::table('share_capital_account_tbls')
                ->where('id', $transaction->share_capital_account_id)
                ->decrement('total_amount', $transaction->total_amount);

            $transaction->status = 'Approved';
            $transaction->approved_by = Auth::id();
            $transaction->save();

            AuditLog::log(
                'Approved Share Capital Withdrawal',
                "Approved withdrawal of {$transaction->shares} shares (Transaction ID: {$id})",
                'share_capital',
                $id
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request accepted successfully.',
            ]);
        } else {
            $transaction->status = 'Rejected';
            $transaction->save();

            AuditLog::log(
                'Rejected Share Capital Withdrawal',
                "Rejected withdrawal of {$transaction->shares} shares (Transaction ID: {$id})",
                'share_capital',
                $id
            );

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request rejected.',
            ]);
        }
    }

    public function completeShareCapitalDeposit(Request $request, $id)
    {
        $transaction = share_capital_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'Deposit') {
            return response()->json([
                'success' => false,
                'message' => 'Only deposit transactions can be completed.',
            ], 400);
        }

        if ($transaction->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been processed.',
            ], 400);
        }

        DB::table('share_capital_account_tbls')
            ->where('id', $transaction->share_capital_account_id)
            ->increment('total_shares', $transaction->shares);

        DB::table('share_capital_account_tbls')
            ->where('id', $transaction->share_capital_account_id)
            ->increment('total_amount', $transaction->total_amount);

        $transaction->status = 'Completed';
        $transaction->save();

        $scAccount = share_capital_account_tbl::findOrFail($transaction->share_capital_account_id);
        $memberId = $scAccount->user_id;

        \App\Models\Notifications_tbl::create([
            'user_id' => $memberId,
            'title' => 'Share Capital Deposit Confirmed',
            'message' => 'Your share capital deposit of ' . $transaction->shares . ' shares (₱' . number_format($transaction->total_amount, 2) . ') has been confirmed. (Ref: ' . $transaction->reference_no . ')',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Completed Share Capital Deposit',
            "Confirmed deposit of {$transaction->shares} shares / ₱{$transaction->total_amount} (Ref: {$transaction->reference_no})",
            'share_capital',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit confirmed successfully. Member has been notified.',
        ]);
    }

    public function voidShareCapitalDeposit(Request $request, $id)
    {
        $request->validate([
            'void_reason' => 'required|string|in:wrong_amount,duplicate_payment,fraudulent,other_member,technical_error,other',
        ]);

        $transaction = share_capital_transaction_tbl::findOrFail($id);

        if (!\App\Services\SoDGuard::canFinalize($transaction)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        if ($transaction->type !== 'Deposit') {
            return response()->json(['success' => false, 'message' => 'Only deposit transactions can be returned.'], 400);
        }

        if ($transaction->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Only pending deposits can be returned.'], 400);
        }

        $transaction->update([
            'status' => 'voided',
            'void_reason' => $request->void_reason,
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        $scAccount = share_capital_account_tbl::findOrFail($transaction->share_capital_account_id);
        $reasonText = $this->returnReasonLabel($request->void_reason);

        \App\Models\Notifications_tbl::create([
            'user_id' => $scAccount->user_id,
            'title' => 'Share Capital Deposit Returned',
            'message' => 'Your share capital deposit of ' . $transaction->shares . ' shares (₱' . number_format($transaction->total_amount, 2) . ") has been returned. Reason: {$reasonText}. (Ref: {$transaction->reference_no})",
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Returned Share Capital Deposit',
            "Returned deposit of {$transaction->shares} shares / ₱{$transaction->total_amount} (Ref: {$transaction->reference_no}). Reason: {$reasonText}",
            'share_capital',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Deposit marked as returned. Member has been notified.',
        ]);
    }

    public function dashboard_reports(Request $request)
    {
        $fromDate = $this->validDateParam($request, 'from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $this->validDateParam($request, 'to_date', now()->endOfMonth()->format('Y-m-d'));
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

    public function dashboard_settings(Request $request)
    {
        // Route-level authorization: only Full-Access / Settings-permission staff
        // (plus GM and Main Admin) may open this page directly.
        $user = Auth::user();
        $allowed = false;
        if ($user) {
            if ($user->isMainAdmin() || $user->isGeneralManager()) {
                $allowed = true;
            } else {
                // Access comes from the assigned role's permissions. Member-based
                // staff (Allied Workers) are included; plain members without a
                // staff role row are not.
                $settingsRole = \App\Models\Role::where('slug', $user->role)->first();
                $settingsPerms = $settingsRole?->sidebar_permissions;
                $allowed = $settingsRole !== null
                    && (is_null($settingsPerms) || in_array('settings', (array) $settingsPerms, true));
            }
        }

        if (!$allowed) {
            if ($request->isMethod('POST')) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to change settings.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'You are not authorized to access settings.');
        }

        $adminUser = Auth::user();
        $adminOther = DB::table('otherinfo_tbls')->where('user_id', $adminUser->id)->first();
        $companySettings = [
            'company_name' => system_settings_tbl::getValue('company_name', 'CoopAdmin Savings and Loan Cooperative'),
            'registration_number' => system_settings_tbl::getValue('registration_number', 'RN-2024-001234'),
            'company_address' => system_settings_tbl::getValue('company_address', ''),
            'company_phone' => system_settings_tbl::getValue('company_phone', ''),
            'company_email' => system_settings_tbl::getValue('company_email', ''),
        ];

        // Show ALL non-member users (admin, officer, secretary, treasurer, etc.)
        $adminList = Users_tbl::whereNotIn('role', ['member', 'pending', 'inactive'])
            ->orderBy('id')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'status' => $user->status,
                    'sidebar_permissions' => $user->sidebar_permissions,
                    'is_main' => $user->isMainAdmin(),
                ];
            });

        $roles = \App\Models\Role::orderBy('name')->get();

        // Count users per role for dynamic admin categories
        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role->slug] = Users_tbl::where('role', $role->slug)->count();
        }

        if ($request->isMethod('POST')) {
            // Finance settings may only be updated by the Main Admin / GM.
            if (!(auth()->user()->isMainAdmin() || auth()->user()->isGeneralManager())) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to update finance settings.'], 403);
            }
            $this->saveFinanceSettingsFromRequest($request);
        }

        $financeData = $this->loadFinanceSettingsData();

        $backupDir = storage_path('app/backups');
        $backups = collect();
        if (is_dir($backupDir)) {
            $backups = collect(scandir($backupDir))
                ->filter(fn($f) => str_ends_with($f, '.sql'))
                ->map(function ($file) use ($backupDir) {
                    return [
                        'name' => $file,
                        'size' => round(filesize($backupDir . '/' . $file) / 1024 / 1024, 2) . ' MB',
                        'created_at' => Carbon::createFromTimestamp(filemtime($backupDir . '/' . $file)),
                    ];
                })
                ->sortByDesc('created_at')
                ->values();
        }

        $faqs = \App\Models\Faq_tbl::orderBy('category')->orderBy('sort_order')->get();
        $grouped = $faqs->groupBy('category');
        $categories = $faqs->pluck('category')->unique()->values();

        return view('admin_components.settings', compact('adminUser', 'companySettings', 'adminList', 'roles', 'roleCounts', 'backups', 'grouped', 'categories', 'adminOther') + $financeData);

        // return view('admin_components.settings', compact('adminUser', 'companySettings', 'adminList', 'roles', 'roleCounts') + $financeData);
    }

    public function createBackup(Request $request)
    {
        if (!(auth()->user()->isMainAdmin() || auth()->user()->isGeneralManager())) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to create backups.'], 403);
        }

        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . '/' . $filename;

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump');

        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --quick %s > %s 2>&1',
            $mysqldumpPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            \Log::error('Backup failed', [
                'return_var' => $returnVar,
                'output' => implode("\n", $output),
                'command' => preg_replace('/--password=\S+/', '--password=***', $command),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . (implode(' ', $output) ?: 'mysqldump may not be installed or reachable on this server.'),
            ], 500);
        }

        AuditLog::log(
            'Created System Backup',
            "Created database backup file {$filename} (" . round(filesize($filePath) / 1024 / 1024, 2) . ' MB)',
            'system_backup',
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Backup created successfully.',
            'backup' => [
                'name' => $filename,
                'size' => round(filesize($filePath) / 1024 / 1024, 2) . ' MB',
                'created_at' => now()->format('M d, Y g:i A'),
            ],
        ]);
    }

    public function downloadBackup($filename)
    {
        if (!(auth()->user()->isMainAdmin() || auth()->user()->isGeneralManager())) {
            abort(403, 'You are not authorized to download backups.');
        }

        $filename = basename($filename); // prevent path traversal
        $filePath = storage_path('app/backups/' . $filename);

        if (!str_ends_with($filename, '.sql') || !file_exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        AuditLog::log(
            'Downloaded System Backup',
            "Downloaded database backup file {$filename}",
            'system_backup',
            null
        );

        return response()->download($filePath);
    }

    public function restoreBackup(Request $request, $filename)
    {
        if (!(auth()->user()->isMainAdmin() || auth()->user()->isGeneralManager())) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to restore backups.'], 403);
        }

        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password.'], 422);
        }

        $filename = basename($filename);
        $backupDir = storage_path('app/backups');
        $filePath = $backupDir . '/' . $filename;

        if (!str_ends_with($filename, '.sql') || !file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Backup file not found.'], 404);
        }

        @set_time_limit(0);

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump');
        $mysqlPath = env('MYSQL_PATH', 'mysql');

        // 1) Safety backup of the current database
        $safetyName = 'pre_restore_' . now()->format('Y_m_d_His') . '.sql';
        $safetyPath = $backupDir . '/' . $safetyName;

        $dumpCmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --quick %s > %s 2>&1',
            $mysqldumpPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($safetyPath)
        );

        exec($dumpCmd, $dumpOut, $dumpRet);

        if ($dumpRet !== 0 || !file_exists($safetyPath) || filesize($safetyPath) === 0) {
            if (file_exists($safetyPath)) {
                unlink($safetyPath);
            }
            return response()->json([
                'success' => false,
                'message' => 'Restore cancelled: could not create a safety backup first. ' . implode(' ', $dumpOut),
            ], 500);
        }

        // 2) Restore the chosen file
        $restoreCmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
            $mysqlPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        exec($restoreCmd, $restoreOut, $restoreRet);

        if ($restoreRet !== 0) {
            \Log::error('Restore failed', [
                'return_var' => $restoreRet,
                'output' => implode("\n", $restoreOut),
                'file' => $filename,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . (implode(' ', $restoreOut) ?: 'mysql client not found.')
                    . " Your previous data was saved as {$safetyName}.",
            ], 500);
        }

        try {
            AuditLog::log(
                'Restored System Backup',
                "Restored database from {$filename}. Safety backup: {$safetyName}",
                'system_backup',
                null
            );
        } catch (\Throwable $e) {
            // audit table may not exist in the restored data; ignore
        }

        return response()->json([
            'success' => true,
            'message' => "Database restored from {$filename}. Safety backup saved as {$safetyName}.",
        ]);
    }

    public function deleteBackup(Request $request, $filename)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can delete backups.'], 403);
        }

        $filename = basename($filename); // prevent path traversal
        $filePath = storage_path('app/backups/' . $filename);

        if (!str_ends_with($filename, '.sql') || !file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Backup file not found.'], 404);
        }

        unlink($filePath);

        AuditLog::log(
            'Deleted System Backup',
            "Deleted database backup file {$filename}",
            'system_backup',
            null
        );

        return response()->json(['success' => true, 'message' => 'Backup deleted successfully.']);
    }

    private function loadFinanceSettingsData()
    {
        $loanSettingsList = Loan_settings_tbl::orderBy('loan_type')->get();
        $loanSettings = $loanSettingsList->pluck('interest_rate', 'loan_type')->toArray();

        $lateFeeSettings = Loan_settings_tbl::first();
        $lateFeePercentage = $lateFeeSettings->late_fee_percentage ?? 2.00;
        $gracePeriodMonths = $lateFeeSettings->grace_period_months ?? 1;

        $currentYear = now()->year;
        $year = now()->year;
        $years = DB::table('dividend_distributions')->orderByDesc('year')->pluck('year');

        $dividendSetting = \App\Models\DividendSetting::where('year', $year)->first();
        $dividendFundPercentage = $dividendSetting ? $dividendSetting->dividend_fund_percentage : 60.00;
        $patronageFundPercentage = $dividendSetting ? $dividendSetting->patronage_fund_percentage : 40.00;
        $reserveFundPercentage = $dividendSetting ? $dividendSetting->reserve_fund_percentage : 10.00;
        $cetfPercentage = $dividendSetting ? $dividendSetting->cetf_percentage : 10.00;
        $cdfPercentage = $dividendSetting ? $dividendSetting->cdf_percentage : 3.00;
        $optionalFundPercentage = $dividendSetting ? $dividendSetting->optional_fund_percentage : 7.00;
        $statutoryTotalPercentage = $reserveFundPercentage + $cetfPercentage + $cdfPercentage + $optionalFundPercentage;
        $remainingSurplusPercentage = 100 - $statutoryTotalPercentage;

        $savingsInterestSettings = SavingsInterestSetting::getOrCreate();
        $loanEligibilitySettings = LoanEligibilitySetting::getOrCreate();
        $paymentMethods = \App\Models\PaymentMethod::orderBy('id')->get();

        return compact(
            'loanSettingsList',
            'loanSettings',
            'lateFeePercentage',
            'gracePeriodMonths',
            'currentYear',
            'year',
            'years',
            'dividendFundPercentage',
            'patronageFundPercentage',
            'reserveFundPercentage',
            'cetfPercentage',
            'cdfPercentage',
            'optionalFundPercentage',
            'statutoryTotalPercentage',
            'remainingSurplusPercentage',
            'savingsInterestSettings',
            'loanEligibilitySettings',
            'paymentMethods'
        );
    }

    private function saveFinanceSettingsFromRequest(Request $request)
    {
        $loanSettingsList = Loan_settings_tbl::orderBy('loan_type')->get();

        // Dynamic interest updates for any loan types
        $inputs = $request->all();

        // Global loan charges — one set of values applied to every loan type
        $globalFields = [
            'global_interest' => 'interest_rate',
            'global_processing' => 'processing_fee_rate',
            'global_service' => 'service_fee_rate',
            'global_protection' => 'loan_protection_fee',
            'global_retention_paid' => 'retention_paid_rate',
            'global_retention_unpaid' => 'retention_unpaid_rate',
        ];

        $update = [];
        foreach ($globalFields as $field => $column) {
            if (array_key_exists($field, $inputs) && is_numeric($inputs[$field])) {
                $update[$column] = $inputs[$field];
            }
        }
        if ($update) {
            Loan_settings_tbl::query()->update($update);
        }

        // Save dividend/patronage percentages if provided
        if ($request->has('dividend_fund_percentage') || $request->has('patronage_fund_percentage')) {
            $year = $request->get('dividend_year', $request->get('year', now()->year));
            $ds = \App\Models\DividendSetting::getForYear($year);
            if ($request->has('dividend_fund_percentage')) {
                $ds->dividend_fund_percentage = $request->dividend_fund_percentage;
            }
            if ($request->has('patronage_fund_percentage')) {
                $ds->patronage_fund_percentage = $request->patronage_fund_percentage;
            }
            if ($request->has('patronage_basis')) {
                $ds->patronage_basis = $request->patronage_basis;
            }
            $ds->updated_by = auth()->id();
            $ds->save();

            $distributionForYear = DB::table('dividend_distributions')->where('year', $year)->first();
            if ($distributionForYear) {
                $dividendPct = (float) $ds->dividend_fund_percentage;
                $patronagePct = (float) $ds->patronage_fund_percentage;
                $netSurplus = (float) $distributionForYear->net_surplus;
                $totalStatutory = (float) $distributionForYear->reserve_fund
                    + (float) $distributionForYear->education_fund
                    + (float) $distributionForYear->community_fund
                    + (float) $distributionForYear->optional_fund;
                $remainingSurplus = isset($distributionForYear->remaining_surplus)
                    ? (float) $distributionForYear->remaining_surplus
                    : round($netSurplus - $totalStatutory, 2);
                $newDividendPool = round($remainingSurplus * ($dividendPct / 100), 2);
                $newPatronageRefund = round($remainingSurplus * ($patronagePct / 100), 2);

                DB::table('dividend_distributions')->where('year', $year)->update([
                    'dividend_pool' => $newDividendPool,
                    'patronage_refund_pool' => $newPatronageRefund,
                    'updated_at' => now(),
                ]);
            }

            session()->now('success', 'Dividend and patronage fund percentages updated successfully.');
        }

        // Save statutory fund allocation percentages if provided
        if ($request->hasAny(['reserve_fund_percentage', 'cetf_percentage', 'cdf_percentage', 'optional_fund_percentage'])) {
            $statYear = $request->get('statutory_year', $request->get('dividend_year', $request->get('year', now()->year)));

            $reserve = round((float) $request->reserve_fund_percentage, 2);
            $cetf = round((float) $request->cetf_percentage, 2);
            $cdf = round((float) $request->cdf_percentage, 2);
            $optional = round((float) $request->optional_fund_percentage, 2);
            $statutoryTotal = $reserve + $cetf + $cdf + $optional;

            $statutoryValues = [$reserve, $cetf, $cdf, $optional];
            $hasInvalid = count(array_filter($statutoryValues, fn($value) => $value < 0 || $value > 100)) > 0;

            if ($hasInvalid) {
                session()->now('error', 'Each statutory fund percentage must be between 0 and 100.');
            } elseif ($statutoryTotal > 100) {
                session()->now('error', 'Statutory fund allocations cannot exceed 100% of net surplus.');
            } else {
                $statDs = \App\Models\DividendSetting::getForYear($statYear);
                $statDs->reserve_fund_percentage = $reserve;
                $statDs->cetf_percentage = $cetf;
                $statDs->cdf_percentage = $cdf;
                $statDs->optional_fund_percentage = $optional;
                $statDs->updated_by = auth()->id();
                $statDs->save();

                session()->now('success', 'Statutory fund allocation percentages updated successfully. These settings apply to newly generated annual distributions.');

                AuditLog::log(
                    'Updated Statutory Fund Percentages',
                    "Changed statutory allocations for year {$statYear}: Reserve {$reserve}%, CETF {$cetf}%, CDF {$cdf}%, Optional {$optional}%.",
                    'settings',
                    null
                );
            }
        }

        // Save savings interest settings if provided
        if ($request->hasAny(['annual_rate', 'release_frequency', 'min_balance_for_interest', 'maintaining_balance'])) {
            $sirSettings = SavingsInterestSetting::getOrCreate();
            $sirSettings->fill($request->only([
                'annual_rate',
                'release_frequency',
                'min_balance_for_interest',
                'maintaining_balance',
            ]));
            $sirSettings->save();

            session()->now('success', 'Savings interest settings updated successfully.');

            AuditLog::log(
                'Updated Savings Interest Settings',
                "Rate: {$sirSettings->annual_rate}%, Frequency: {$sirSettings->release_frequency}, Min balance: ₱{$sirSettings->min_balance_for_interest}, Maintaining: ₱{$sirSettings->maintaining_balance}",
                'settings',
                null
            );
        }

        // Save loan eligibility settings if provided
        if ($request->hasAny(['savings_to_loan_enabled', 'savings_to_loan_ratio', 'minimum_shares'])) {
            $loanEligSettings = LoanEligibilitySetting::getOrCreate();
            $loanEligSettings->savings_to_loan_enabled = $request->boolean('savings_to_loan_enabled', true);
            if ($request->has('savings_to_loan_ratio')) {
                $loanEligSettings->savings_to_loan_ratio = round((float) $request->savings_to_loan_ratio, 2);
            }
            if ($request->has('minimum_shares')) {
                $loanEligSettings->minimum_shares = round((float) $request->minimum_shares, 2);
            }
            $loanEligSettings->save();

            session()->now('success', 'Loan eligibility settings updated successfully.');

            AuditLog::log(
                'Updated Loan Eligibility Settings',
                'Savings holdback enabled: ' . ($loanEligSettings->savings_to_loan_enabled ? 'Yes' : 'No')
                . ', Holdback: ₱' . number_format($loanEligSettings->savings_to_loan_ratio, 2)
                . ', Minimum shares: ' . $loanEligSettings->minimum_shares,
                'settings',
                null
            );
        }

        AuditLog::log(
            'Updated Financial Settings',
            'Updated loan interest rates and dividend/patronage settings',
            'settings',
            null
        );
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_tbls,email,' . $user->id,
            'contact_no' => 'nullable|string|max:50',
            'present_address' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
            ]);

            $info = [
                'contact_no' => $validated['contact_no'] ?? null,
                'present_address' => $validated['present_address'] ?? null,
                'updated_at' => now(),
            ];

            if (DB::table('otherinfo_tbls')->where('user_id', $user->id)->exists()) {
                DB::table('otherinfo_tbls')->where('user_id', $user->id)->update($info);
            } else {
                DB::table('otherinfo_tbls')->insert($info + [
                    'user_id' => $user->id,
                    'membership_category' => 'Admin',
                    'signature' => null,
                    'approval_status' => 'Approved',
                    'created_at' => now(),
                ]);
            }
        });

        AuditLog::log('Updated Profile', 'Updated own profile details', 'user', $user->id);

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        AuditLog::log(
            'Changed Password',
            'Admin changed their password',
            'user',
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function updateAdmin(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can update admin accounts.'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|exists:users_tbls,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_tbls,email,' . $request->id,
            'role' => 'required|string|exists:roles,slug',
        ]);

        $user = Users_tbl::findOrFail($validated['id']);

        if ($user->isMainAdmin() && auth()->id() !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Cannot modify the main admin account.'], 403);
        }

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        AuditLog::log(
            'Updated Admin',
            "Updated admin {$validated['first_name']} {$validated['last_name']} (ID: {$validated['id']})",
            'user',
            $validated['id']
        );

        return response()->json(['success' => true, 'message' => 'Admin account updated successfully.']);
    }

    public function deleteAdmin(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can delete admin accounts.'], 403);
        }

        $request->validate(['id' => 'required|exists:users_tbls,id']);

        $user = Users_tbl::findOrFail($request->id);

        if ($user->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete the main admin account.'], 403);
        }

        AuditLog::log(
            'Deactivated Admin',
            "Deactivated admin {$user->first_name} {$user->last_name} (ID: {$request->id})",
            'user',
            $request->id
        );

        $user->update(['role' => 'inactive', 'status' => 'inactive']);

        return response()->json(['success' => true, 'message' => 'Admin account deactivated successfully.']);
    }

    public function toggleAdminStatus(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can change admin status.'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|exists:users_tbls,id',
            'status' => 'required|in:active,inactive,pending',
        ]);

        $user = Users_tbl::findOrFail($validated['id']);

        if ($user->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Cannot modify the main admin status.'], 403);
        }

        $user->update(['status' => $validated['status']]);

        AuditLog::log(
            'Changed Admin Status',
            "Changed admin {$user->first_name} {$user->last_name} (ID: {$validated['id']}) status to {$validated['status']}",
            'user',
            $validated['id']
        );

        return response()->json(['success' => true, 'message' => 'Admin status updated to ' . ucfirst($validated['status']) . '.']);
    }

    public function storeAdmin(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            abort(403, 'Only the main admin can create admin or officer accounts.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_tbls,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,slug',
        ]);

        $base = strtolower(preg_replace('/[^a-z0-9]/', '', $validated['first_name'] . '.' . $validated['last_name']));
        $username = $base;
        $counter = 1;
        while (Users_tbl::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        $user = Users_tbl::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'username' => $username,
            'status' => 'active',
        ]);

        DB::table('otherinfo_tbls')->insert([
            'user_id' => $user->id,
            'membership_category' => 'Admin',
            'signature' => null,
            'approval_status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLog::log(
            'Created Admin',
            "Created admin account for {$validated['first_name']} {$validated['last_name']} (ID: {$user->id}) with role {$validated['role']}",
            'user',
            $user->id
        );

        return redirect()->route('settings', ['tab' => 'admin-management'])
            ->with('admin_created', true);
    }

    public function storeRole(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can create roles.'], 403);
        }

        $customRoleCount = \App\Models\Role::where('is_system', false)->count();
        if ($customRoleCount >= \App\Models\Role::MAX_CUSTOM_ROLES) {
            return response()->json([
                'success' => false,
                'message' => 'Role limit reached (' . \App\Models\Role::MAX_CUSTOM_ROLES . '). Delete an existing custom role before creating another.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'sidebar_permissions' => 'nullable|array',
            'sidebar_permissions.*' => 'string',
        ]);

        // Protected permissions (Settings, Finance) can only be granted by
        // the Main Admin or the General Manager. Other
        // role creators cannot delegate them to custom roles.
        $protected = \App\Services\SoDGuard::protectedPermissions();
        $canGrantProtected = auth()->user()->isMainAdmin() || \App\Services\SoDGuard::isGeneralManager();
        $granted = collect($validated['sidebar_permissions'] ?? [])
            ->when(
                !$canGrantProtected,
                fn($c) => $c->reject(fn($p) => in_array($p, $protected, true))
            )
            ->reject(fn($p) => $p === 'audit-logs')
            ->values();

        $slug = \Illuminate\Support\Str::slug($validated['name'] ?? '');
        if (empty($slug)) {
            $slug = 'role';
        }
        $originalSlug = $slug;
        $counter = 1;
        while (\App\Models\Role::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $role = \App\Models\Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'],
            'is_system' => false,
            'sidebar_permissions' => $granted->all(),
        ]);

        AuditLog::log(
            'Created Role',
            "Created role '{$role->name}' ({$role->slug})",
            'role',
            $role->id
        );

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' created successfully.",
            'role' => $role,
        ]);
    }

    public function updateRole(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can update roles.'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|exists:roles,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'sidebar_permissions' => 'nullable|array',
            'sidebar_permissions.*' => 'string',
        ]);

        $role = \App\Models\Role::findOrFail($validated['id']);

        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'System roles cannot be edited.'], 403);
        }

        if (\App\Models\Role::where('name', $validated['name'])->where('id', '!=', $role->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Another role is already named '{$validated['name']}'.",
            ], 409);
        }

        // Protected permissions can only be granted by the Main Admin / GM.
        $protected = \App\Services\SoDGuard::protectedPermissions();
        $canGrantProtected = auth()->user()->isMainAdmin() || \App\Services\SoDGuard::isGeneralManager();
        $granted = collect($validated['sidebar_permissions'] ?? [])
            ->when(
                !$canGrantProtected,
                fn($c) => $c->reject(fn($p) => in_array($p, $protected, true))
            )
            ->reject(fn($p) => $p === 'audit-logs')
            ->values();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'sidebar_permissions' => $granted->all(),
        ]);

        AuditLog::log(
            'Updated Role',
            "Updated role '{$role->name}' ({$role->slug}) with " . count($granted) . " sidebar permission(s)",
            'role',
            $role->id
        );

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' updated successfully.",
            'role' => $role->fresh(),
        ]);
    }

    public function deleteRole(Request $request)
    {
        if (!auth()->user()->isMainAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the main admin can delete roles.'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|exists:roles,id',
        ]);

        $role = \App\Models\Role::findOrFail($validated['id']);

        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'System roles cannot be deleted.'], 403);
        }

        $usersCount = Users_tbl::where('role', $role->slug)->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete '{$role->name}' — {$usersCount} user(s) are assigned this role.",
            ], 409);
        }

        AuditLog::log(
            'Deleted Role',
            "Deleted role '{$role->name}' ({$role->slug})",
            'role',
            $validated['id']
        );

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' deleted successfully.",
        ]);
    }

    public function storeCooperativeTransaction(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:Vehicle Purchase,Bank Investment,Office Equipment,Utilities,Maintenance,Other',
            'category_other' => 'required_if:category,Other|nullable|string|max:120',
            'transaction_type' => 'required|string|in:expense,investment',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
        ]);

        $category = $request->category === 'Other'
            ? (trim($request->category_other) ?: 'Other')
            : $request->category;

        try {
            $transaction = CooperativeTransaction::create([
                'description' => $request->description,
                'category' => $category,
                'transaction_type' => $request->transaction_type,
                'amount' => $request->amount,
                'transaction_date' => $request->transaction_date,
            ]);

            AuditLog::log(
                'Recorded Cooperative Transaction',
                "Recorded {$request->transaction_type} of ₱{$request->amount} ({$category}: {$request->description})",
                'cooperative_transaction',
                $transaction->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction recorded successfully!',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function dashboard_financial_activity(Request $request)
    {
        // Dividend data
        $year = $request->get('year', now()->year);
        $distribution = DB::table('dividend_distributions')->where('year', $year)->first();
        $approvedCount = 0;
        $disbursedCount = 0;
        $pendingCount = 0;
        $totalSumShareCapital = 0;
        $totalSumRecommended = 0;
        $totalSumApproved = 0;
        $currentYear = now()->year;
        $years = DB::table('dividend_distributions')
            ->orderByDesc('year')
            ->pluck('year');

        $dividendSetting = \App\Models\DividendSetting::where('year', $year)->first();
        $dividendFundPercentage = $dividendSetting ? $dividendSetting->dividend_fund_percentage : 60.00;

        $dividends = collect();

        if ($distribution) {
            $dividends = Dividend::with('user')
                ->where('year', $year)
                ->orderBy('id')
                ->paginate(10)
                ->appends(['year' => $year]);

            $approvedCount = Dividend::where('year', $year)->where('status', 'approved')->count();
            $disbursedCount = Dividend::where('year', $year)->where('status', 'disbursed')->count();
            $pendingCount = Dividend::where('year', $year)->where('status', 'pending')->count();
            $totalSumShareCapital = Dividend::where('year', $year)->sum('share_capital_amount');
            $totalSumRecommended = Dividend::where('year', $year)->sum('recommended_amount');
            $totalSumApproved = Dividend::where('year', $year)->sum('approved_amount');
        }

        // Patronage data
        $patronageFundPercentage = $dividendSetting ? $dividendSetting->patronage_fund_percentage : 40.00;
        $patronageBasis = ($distribution && !empty($distribution->patronage_basis))
            ? $distribution->patronage_basis
            : ($dividendSetting ? $dividendSetting->patronage_basis : 'total_repayment');
        $reserveFundPercentage = $dividendSetting ? $dividendSetting->reserve_fund_percentage : 10.00;
        $cetfPercentage = $dividendSetting ? $dividendSetting->cetf_percentage : 10.00;
        $cdfPercentage = $dividendSetting ? $dividendSetting->cdf_percentage : 3.00;
        $optionalFundPercentage = $dividendSetting ? $dividendSetting->optional_fund_percentage : 7.00;
        $statutoryTotalPercentage = $reserveFundPercentage + $cetfPercentage + $cdfPercentage + $optionalFundPercentage;
        $remainingSurplusPercentage = 100 - $statutoryTotalPercentage;
        $patronageApprovedCount = 0;
        $patronageDisbursedCount = 0;
        $patronagePendingCount = 0;
        $totalSumPatronageApproved = 0;

        if ($distribution) {
            $patronageApprovedCount = \App\Models\PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->count();
            $patronageDisbursedCount = \App\Models\PatronageRefundDistribution::where('year', $year)->where('status', 'disbursed')->count();
            $patronagePendingCount = \App\Models\PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->count();
            $totalSumPatronageApproved = \App\Models\PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->sum('amount');
        }

        $cooperativeTransactions = CooperativeTransaction::orderBy('created_at', 'desc')->take(10)->get();
        $cooperativeStats = [
            'total_expenses' => CooperativeTransaction::where('transaction_type', 'expense')->sum('amount'),
            'total_investments' => CooperativeTransaction::where('transaction_type', 'investment')->sum('amount'),
        ];

        $search = $request->get('search', '');
        $typeFilter = $request->get('type', 'all');
        $statusFilter = $request->get('status', 'all');

        $alliedPendingFilter = $this->alliedPendingVisibilityClosure();

        $savingsQuery = savings_transaction_tbl::with('savingsAccount.user');

        if ($alliedPendingFilter) {
            $savingsQuery->where($alliedPendingFilter);
        }

        if ($search) {
            $savingsQuery->whereHas('savingsAccount.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($typeFilter !== 'all') {
            $savingsQuery->where('type', $typeFilter);
        }

        if ($statusFilter !== 'all') {
            $savingsQuery->whereRaw('LOWER(status) = ?', [strtolower($statusFilter)]);
        }

        $savingsTransactions = $savingsQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['tab' => 'savings', 'search' => $search, 'type' => $typeFilter, 'status' => $statusFilter]);

        $currentBalance = (savings_transaction_tbl::where('type', 'deposit')->sum('amount') ?? 0) - (savings_transaction_tbl::where('type', 'withdrawal')->sum('amount') ?? 0);

        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();
        $monthlyAvg = savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->sum('amount') / 6;

        $monthlyWithdrawalAvg = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->sum('amount') / 6;

        $thisMonth = now()->startOfMonth();
        $monthlyDeposits = (savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount') ?? 0)
            - (savings_transaction_tbl::where('type', 'withdrawal')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount') ?? 0);

        $monthlyWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount') ?? 0;

        $monthlyWithdrawalsCount = savings_transaction_tbl::where('type', 'withdrawal')
            ->where('created_at', '>=', $thisMonth)
            ->count() ?? 0;

        $today = now()->startOfDay();
        $todayDeposits = savings_transaction_tbl::where('type', 'deposit')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $lastContribution = savings_transaction_tbl::orderByDesc('created_at')->first();

        $totalDeposits = savings_transaction_tbl::where('type', 'deposit')->sum('amount') ?? 0;
        $totalWithdrawals = savings_transaction_tbl::where('type', 'withdrawal')->sum('amount') ?? 0;

        $totalSavingsBalance = savings_account_tbl::sum('balance') ?? 0;
        $savingsAccounts = savings_account_tbl::with('user')->orderByDesc('balance')->get();

        $sirSettings = SavingsInterestSetting::getOrCreate();
        $minBalanceForInterest = (float) $sirSettings->min_balance_for_interest;

        $eligibleAccounts = [];
        $notEligibleAccounts = [];

        foreach ($savingsAccounts as $account) {
            $reasons = [];
            if ($account->status !== 'active') {
                $reasons[] = 'Account is not active';
            }
            if ((float) $account->balance < $minBalanceForInterest) {
                $reasons[] = 'Balance below minimum (₱' . number_format($minBalanceForInterest, 2) . ' required)';
            }
            if (empty($reasons)) {
                $eligibleAccounts[] = $account;
            } else {
                $notEligibleAccounts[] = ['account' => $account, 'reasons' => $reasons];
            }
        }

        $eligibleCount = count($eligibleAccounts);
        $notEligibleCount = count($notEligibleAccounts);
        $activeSavingsAccounts = count($eligibleAccounts);

        $allMembers = Users_tbl::memberCandidates()
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        $monthlyData = [];
        $maxAmount = 0;
        $processedMonths = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');

            if (in_array($monthKey, $processedMonths)) {
                continue;
            }
            $processedMonths[] = $monthKey;

            $amount = (float) savings_transaction_tbl::where('type', 'deposit')
                ->whereYear('created_at', $monthDate->format('Y'))
                ->whereMonth('created_at', $monthDate->format('m'))
                ->sum('amount');

            $monthlyData[] = [
                'name' => $monthDate->format('F'),
                'year' => $monthDate->format('Y'),
                'amount' => $amount,
            ];
            if ($amount > $maxAmount) {
                $maxAmount = $amount;
            }
        }

        if (count($monthlyData) > 6) {
            $monthlyData = array_slice($monthlyData, -6);
        }

        foreach ($monthlyData as &$data) {
            $data['bar_height'] = $maxAmount > 0 ? ($data['amount'] / $maxAmount) * 150 : 0;
        }

        $highestMonth = ['name' => 'N/A', 'amount' => 0];
        foreach ($monthlyData as $data) {
            if ($data['amount'] > $highestMonth['amount']) {
                $highestMonth = $data;
            }
        }

        $monthlyWithdrawalData = [];
        $maxWithdrawalAmount = 0;
        $processedMonths = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');

            if (in_array($monthKey, $processedMonths)) {
                continue;
            }
            $processedMonths[] = $monthKey;

            $amount = (float) savings_transaction_tbl::where('type', 'withdrawal')
                ->whereYear('created_at', $monthDate->format('Y'))
                ->whereMonth('created_at', $monthDate->format('m'))
                ->sum('amount');

            $monthlyWithdrawalData[] = [
                'name' => $monthDate->format('F'),
                'year' => $monthDate->format('Y'),
                'amount' => $amount,
            ];
            if ($amount > $maxWithdrawalAmount) {
                $maxWithdrawalAmount = $amount;
            }
        }

        if (count($monthlyWithdrawalData) > 6) {
            $monthlyWithdrawalData = array_slice($monthlyWithdrawalData, -6);
        }

        foreach ($monthlyWithdrawalData as &$data) {
            $data['bar_height'] = $maxWithdrawalAmount > 0 ? ($data['amount'] / $maxWithdrawalAmount) * 150 : 0;
        }

        $highestWithdrawalMonth = ['name' => 'N/A', 'amount' => 0];
        foreach ($monthlyWithdrawalData as $data) {
            if ($data['amount'] > $highestWithdrawalMonth['amount']) {
                $highestWithdrawalMonth = $data;
            }
        }

        $pendingWithdrawals = savings_transaction_tbl::with('savingsAccount.user.otherinfo')
            ->where('type', 'withdrawal')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->when($alliedPendingFilter, fn($q) => $q->where($alliedPendingFilter))
            ->latest()
            ->get();

        $pendingDeposits = savings_transaction_tbl::with('savingsAccount.user.otherinfo')
            ->where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->when($alliedPendingFilter, fn($q) => $q->where($alliedPendingFilter))
            ->latest()
            ->get();

        // ─── Share Capital tab data ───────────────────────────────────────────
        $scSearch = $request->get('sc_search', '');
        $scTypeFilter = $request->get('sc_type', 'all');
        $scStatusFilter = $request->get('sc_status', 'all');

        $scQuery = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('status', '!=', 'failed');

        if ($alliedPendingFilter) {
            $scQuery->where($alliedPendingFilter);
        }

        if ($scSearch) {
            $scQuery->whereHas('shareCapitalAccount.user', function ($q) use ($scSearch) {
                $q->where('first_name', 'like', "%{$scSearch}%")
                    ->orWhere('last_name', 'like', "%{$scSearch}%");
            });
        }

        if ($scTypeFilter !== 'all') {
            $scQuery->where('type', $scTypeFilter);
        }

        if ($scStatusFilter !== 'all') {
            $scQuery->where('status', $scStatusFilter);
        }

        $scTransactions = $scQuery->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['tab' => 'share-capitals', 'sc_search' => $scSearch, 'sc_type' => $scTypeFilter, 'sc_status' => $scStatusFilter]);

        $totalContributions = share_capital_transaction_tbl::whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
            ->where('status', 'Completed')
            ->sum('total_amount') ?? 0;

        $totalContributions = $totalContributions - (
            share_capital_transaction_tbl::where('type', 'Withdrawal')
                ->whereIn('status', ['Approved', 'approved'])
                ->sum('total_amount') ?? 0
        );

        $perShareValue = ShareCapital::PAR_VALUE;

        $accounts = share_capital_account_tbl::with('user')->get();
        $paidUp = ShareCapital::paidUpForAccounts($accounts->pluck('id')->all());

        $scEligibleCount = collect($paidUp)
            ->filter(fn($stats) => $stats['shares'] >= ShareCapital::ELIGIBLE_SHARES)
            ->count();

        $shareCapitalAccounts = $accounts
            ->each(function ($account) use ($paidUp) {
                $stats = $paidUp[$account->id] ?? ['shares' => 0.0, 'amount' => 0.0];
                $account->paid_up_shares = $stats['shares'];
                $account->paid_up_amount = $stats['amount'];
            })
            ->sortByDesc('paid_up_amount')
            ->values();

        $pendingReleases = \App\Models\ResignationRequest_tbl::with('user.shareCapitalAccount')
            ->where('status', 'approved')
            ->where('withdraw_share_capital', true)
            ->where('is_released', false)
            ->orderBy('release_date', 'asc')
            ->get();

        $pendingSCDeposits = share_capital_transaction_tbl::with('shareCapitalAccount.user')
            ->where('type', 'Deposit')
            ->where('status', 'Pending')
            ->when($alliedPendingFilter, fn($q) => $q->where($alliedPendingFilter))
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin_components.financial_activity', compact(
            'distribution',
            'dividends',
            'year',
            'years',
            'currentYear',
            'approvedCount',
            'disbursedCount',
            'pendingCount',
            'totalSumShareCapital',
            'totalSumRecommended',
            'totalSumApproved',
            'cooperativeTransactions',
            'cooperativeStats',
            'dividendFundPercentage',
            'patronageFundPercentage',
            'patronageBasis',
            'reserveFundPercentage',
            'cetfPercentage',
            'cdfPercentage',
            'optionalFundPercentage',
            'statutoryTotalPercentage',
            'remainingSurplusPercentage',
            'patronageApprovedCount',
            'patronageDisbursedCount',
            'patronagePendingCount',
            'totalSumPatronageApproved',
            'savingsTransactions',
            'currentBalance',
            'monthlyAvg',
            'monthlyWithdrawalAvg',
            'monthlyDeposits',
            'monthlyWithdrawals',
            'monthlyWithdrawalsCount',
            'todayDeposits',
            'lastContribution',
            'totalDeposits',
            'totalWithdrawals',
            'totalSavingsBalance',
            'savingsAccounts',
            'activeSavingsAccounts',
            'eligibleAccounts',
            'notEligibleAccounts',
            'eligibleCount',
            'notEligibleCount',
            'sirSettings',
            'allMembers',
            'monthlyData',
            'highestMonth',
            'monthlyWithdrawalData',
            'highestWithdrawalMonth',
            'pendingWithdrawals',
            'pendingDeposits',
            'scTransactions',
            'totalContributions',
            'perShareValue',
            'scEligibleCount',
            'shareCapitalAccounts',
            'pendingReleases',
            'pendingSCDeposits'
        ));
    }

    /**
     * Single source of truth for a loan's display status (Active / Overdue / Completed).
     * Used by BOTH the loan grid cards and the loan detail hero so they can never
     * disagree on the same loan's status again.
     */
    private function resolveLoanStatusLabel($loan, $status): string
    {
        if ($loan->status === 'Completed') {
            return 'Completed';
        }

        if (!$status) {
            return 'Active';
        }

        $totalPayments = (int) ($status->total_payments ?? 0);
        $paymentsMade = (int) ($status->payments_made ?? 0);

        if ($totalPayments <= 0 || $paymentsMade >= $totalPayments) {
            return 'Active';
        }

        $today = now()->timezone('Asia/Manila');

        $scheduleByNumber = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $loan->id)
            ->get()
            ->keyBy('payment_number');

        $nextInstallmentNumber = $paymentsMade + 1;
        $anchorDueDate = $status->due_date
            ? \Carbon\Carbon::parse($status->due_date)
            : \Carbon\Carbon::parse($loan->created_at)
            ->addMonthsNoOverflow($nextInstallmentNumber * lendingController::PAYMENT_INTERVAL_MONTHS);

        for ($i = 1; $i <= $totalPayments; $i++) {
            $row = $scheduleByNumber[$i] ?? null;
            $isPaid = $row
                ? (float) $row->amount_paid >= (float) $row->amount_due
                : $i <= $paymentsMade;

            if ($isPaid) {
                continue;
            }

            $dueDateForRow = $anchorDueDate->copy()
            ->addMonthsNoOverflow(($i - $nextInstallmentNumber) * lendingController::PAYMENT_INTERVAL_MONTHS);

            return $dueDateForRow->lt($today) ? 'Overdue' : 'Active';
        }

        return 'Active';
    }

    public function dashboard_payments(Request $request)
    {
        $method = $request->get('method', 'all');

        $alliedPendingFilter = $this->alliedPendingVisibilityClosure();

        $query = lending_repayments_tbl::with(['lending.user', 'user']);

        if ($method !== 'all') {
            $query->where('payment_method', $method);
        }

        if ($alliedPendingFilter) {
            $query->where($alliedPendingFilter);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        $pendingRepayments = lending_repayments_tbl::with(['lending.user', 'user'])
            ->where('status', 'Pending')
            ->when($alliedPendingFilter, fn($q) => $q->where($alliedPendingFilter))
            ->latest()
            ->get();

        $allMembers = Users_tbl::memberCandidates()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        return view('admin_components.payments', compact('payments', 'method', 'allMembers', 'paymentMethods', 'pendingRepayments'));
    }

    public function createLoanSetting(Request $request)
    {
        $request->validate([
            'loan_type' => 'required|string|max:191',
        ]);

        $exists = Loan_settings_tbl::where('loan_type', $request->loan_type)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Loan type already exists');
        }

        // New loan types inherit the global loan charges so all types stay in sync.
        $reference = Loan_settings_tbl::orderBy('loan_type')->first();

        $ls = new Loan_settings_tbl;
        $ls->loan_type = $request->loan_type;
        $ls->interest_rate = $reference->interest_rate ?? 2.00;
        $ls->processing_fee_rate = $reference->processing_fee_rate ?? 2.00;
        $ls->service_fee_rate = $reference->service_fee_rate ?? 2.00;
        $ls->loan_protection_fee = $reference->loan_protection_fee ?? 2.00;
        $ls->retention_paid_rate = $reference->retention_paid_rate ?? 3.00;
        $ls->retention_unpaid_rate = $reference->retention_unpaid_rate ?? 6.00;
        $ls->is_active = true;
        $ls->save();

        AuditLog::log('Created Loan Type', "Created loan type {$ls->loan_type} with interest {$ls->interest_rate}%", 'settings', null);

        return redirect()->back()->with('success', 'Loan type created');
    }

    public function deleteLoanSetting(Request $request, $id)
    {
        $setting = Loan_settings_tbl::find($id);
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Loan type not found'], 404);
        }

        $inUse = lending_program_tbl::where('lending_type', $setting->loan_type)->count();
        if ($inUse > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete {$setting->loan_type}. It is currently used by {$inUse} loan(s).",
            ]);
        }

        $name = $setting->loan_type;
        $setting->delete();

        AuditLog::log('Deleted Loan Type', "Deleted loan type {$name}", 'settings', null);

        return response()->json(['success' => true, 'message' => "Loan type \"{$name}\" deleted"]);
    }

    public function getLoanPayable($id)
    {
        $loan = lending_program_tbl::find($id);
        if (!$loan) {
            return response()->json(['success' => false, 'message' => 'Loan not found'], 404);
        }

        $status = lending_status_tbl::where('lending_id', $id)->first();

        $totalPayments = (int) ($status->total_payments ?? $loan->total_payments ?? 0);
        $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);

        $monthlyAmortization = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;

        $remaining = $status ? (float) ($status->remaining_balance ?? $loan->total_payment ?? $loan->lending_amount) : (float) ($loan->total_payment ?? $loan->lending_amount);

        // Scheduled loans: the CURRENT unpaid installment is authoritative
        // (full-installment repayment rule); legacy loans use the flat average.
        $currentInstallment = (new \App\Services\LoanCalculationService)->currentInstallment($id);

        $base = $currentInstallment
            ? (float) $currentInstallment->amount_due
            : min($monthlyAmortization > 0 ? $monthlyAmortization : $remaining, $remaining);

        $today = now()->timezone('Asia/Manila')->toDateString();
        $penalty = 0;
        $lateFeeRate = Loan_settings_tbl::getLateFeeRate($loan->lending_type);
        if ($status && $status->due_date && $status->due_date < $today && $remaining > 0) {
            $penalty = round($base * ($lateFeeRate / 100), 2); // late fee % of current installment / amortization
        }

        $total = round($base + $penalty, 2);

        return response()->json([
            'success' => true,
            'base' => $base,
            'installment' => $currentInstallment ? (float) $currentInstallment->amount_due : $base,
            'has_schedule' => (bool) $currentInstallment,
            'late_fee_percent' => $lateFeeRate,
            'penalty' => $penalty,
            'total' => $total,
            'remaining' => round($remaining, 2),
            'full_total' => round($remaining + $penalty, 2),
        ]);
    }

    public function adminStoreRepayment(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'lending_id' => 'required|exists:lending_program_tbls,id',
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string',
            'reference_no' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'late_fee' => 'nullable|numeric|min:0',
        ]);

        // Segregation of duties: an Allied Worker may not process their own
        // member account (self-processing block). Only the GM may do so.
        if (
            \App\Services\SoDGuard::actingAsStaff() && !\App\Services\SoDGuard::isGeneralManager()
            && (int) auth()->id() === (int) $request->member_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot process a transaction for your own member account (self-processing is blocked).',
            ], 422);
        }

        $loan = lending_program_tbl::findOrFail($request->lending_id);
        $interestRatio = ($loan->total_payment > 0) ? ($loan->total_interest / $loan->total_payment) : 0;

        $loanCalc = new \App\Services\LoanCalculationService;
        $currentInstallment = $loanCalc->currentInstallment($loan->id);

        $status = lending_status_tbl::firstOrCreate(
            ['lending_id' => $request->lending_id],
            [
                'user_id' => $request->member_id,
                'remaining_balance' => $loan->total_payment,
                'total_paid' => 0,
                'payments_made' => 0,
                'total_payments' => max(1, (int) filter_var($loan->lending_type_term ?? '6', FILTER_SANITIZE_NUMBER_INT)),
                'interest_rate' => \App\Models\Loan_settings_tbl::getRate($loan->lending_type),
                'due_date' => now()->addMonth()->format('Y-m-d'),
                'status' => 'Active',
            ]
        );

        DB::beginTransaction();
        try {
            $totalPayments = max(1, (int) ($status->total_payments ?? $loan->total_payments ?? 0));
            $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
            $monthlyAmortization = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;
            $today = now()->timezone('Asia/Manila')->toDateString();
            $lateFee = 0;
            if ($status->due_date && $status->due_date < $today && $status->remaining_balance > 0) {
                $lateFeeRate = Loan_settings_tbl::getLateFeeRate($loan->lending_type);
                $lateFee = round(($currentInstallment ? (float) $currentInstallment->amount_due : $monthlyAmortization) * ($lateFeeRate / 100), 2);
            }

            if ($currentInstallment) {
                // ── SCHEDULE-BASED (new loans) ───────────────────────────────
                // FULL-INSTALLMENT-ONLY RULE: the admin form sends no
                // payment_type, so the mode is auto-detected by amount —
                // matches current installment + late fee → monthly; matches
                // full balance + late fee → full (existing rule). Anything
                // else (partial or excess) is rejected with no writes.
                $amount = round((float) $request->amount_paid, 2);
                $monthlyTotal = round((float) $currentInstallment->amount_due + $lateFee, 2);
                $fullTotal = round((float) $status->remaining_balance + $lateFee, 2);

                if (abs($amount - $monthlyTotal) <= 0.005) {
                    $installmentAmount = (float) $currentInstallment->amount_due;
                } elseif (abs($amount - $fullTotal) <= 0.005) {
                    $installmentAmount = $amount;
                } else {
                    DB::rollBack();
                    $label = '₱' . number_format($monthlyTotal, 2);
                    $msg = $amount < $monthlyTotal
                        ? "Partial payments are not allowed. Please pay the full current installment of {$label}."
                        : "Payment exceeds the current installment. Please enter exactly {$label}.";

                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                $allocation = $loanCalc->allocatePayment($loan->id, $installmentAmount);

                $repayment = lending_repayments_tbl::create([
                    'lending_id' => $request->lending_id,
                    'user_id' => $request->member_id,
                    'payment_number' => (int) $currentInstallment->payment_number,
                    'payment_sequence' => lending_repayments_tbl::where('lending_id', $request->lending_id)
                        ->where('payment_number', $currentInstallment->payment_number)
                        ->where('status', '!=', 'voided')
                        ->count() + 1,
                    'amount_due' => $installmentAmount,
                    'amount_paid' => $installmentAmount,
                    'principal_paid' => $allocation['principal_paid'],
                    'interest_paid' => $allocation['interest_paid'],
                    'service_fee_paid' => 0,
                    'late_fee' => $lateFee > 0 ? $lateFee : null,
                    'penalty_applied_at' => $lateFee > 0 ? now()->timezone('Asia/Manila') : null,
                    'payment_date' => $request->payment_date ?: now()->toDateString(),
                    'payment_method' => $request->payment_method ?: 'Cash',
                    'reference_no' => $request->reference_no ?: 'ADMIN-' . now()->format('YmdHis'),
                    'payment_type' => abs($amount - $monthlyTotal) <= 0.005 ? 'monthly' : 'full',
                    'notes' => 'Recorded by admin',
                    'recorded_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]);

                if ($status) {
                    $status->total_paid = round((float) $status->total_paid + (float) $allocation['amount_paid'], 2);
                    $status->remaining_balance = max(0, round((float) $status->remaining_balance - $installmentAmount, 2));
                    $status->payments_made = (int) $status->payments_made + (int) $allocation['installments_settled'];

                    if ($allocation['fully_paid_now'] || $status->remaining_balance <= 0) {
                        $status->status = 'Completed';
                        $status->payments_made = $status->total_payments;
                        $status->remaining_balance = 0;
                        $loan->update(['status' => 'Completed']);
                    } else {
                        $nextUnpaid = $loanCalc->currentInstallment($loan->id);
                        if ($nextUnpaid) {
                            $status->due_date = $nextUnpaid->due_date;
                        }
                    }

                    $status->save();
                }
            } else {
                // ── LEGACY (existing loans without schedule rows) — flat ratio ────
                $paymentsMade = lending_repayments_tbl::where('lending_id', $request->lending_id)->count();

                $interestPaid = round($request->amount_paid * $interestRatio, 2);
                $principalPaid = round($request->amount_paid - $interestPaid, 2);

                $repayment = lending_repayments_tbl::create([
                    'lending_id' => $request->lending_id,
                    'user_id' => $request->member_id,
                    'payment_number' => $paymentsMade + 1,
                    'payment_sequence' => lending_repayments_tbl::where('lending_id', $request->lending_id)
                        ->where('payment_number', $paymentsMade + 1)
                        ->where('status', '!=', 'voided')
                        ->count() + 1,
                    'amount_paid' => $request->amount_paid,
                    'principal_paid' => $principalPaid,
                    'interest_paid' => $interestPaid,
                    'service_fee_paid' => 0,
                    'late_fee' => $lateFee > 0 ? $lateFee : null,
                    'payment_date' => $request->payment_date ?: now()->toDateString(),
                    'payment_method' => $request->payment_method ?: 'Cash',
                    'reference_no' => $request->reference_no ?: 'ADMIN-' . now()->format('YmdHis'),
                    'payment_type' => 'monthly',
                    'notes' => 'Recorded by admin',
                    'recorded_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]);

                if ($status) {
                    $status->total_paid += $request->amount_paid;
                    $status->remaining_balance = max(0, $status->remaining_balance - $request->amount_paid);
                    $status->payments_made += 1;

                    if ($status->remaining_balance <= 0 || $status->payments_made >= $status->total_payments) {
                        $status->status = 'Completed';
                        $status->payments_made = $status->total_payments;
                        $loan->update(['status' => 'Completed']);
                    }

                    $status->save();
                }
            }

            DB::commit();

            $member = Users_tbl::find($request->member_id);
            AuditLog::log(
                'Admin Recorded Loan Repayment',
                "Recorded loan repayment of ₱{$request->amount_paid} for {$member?->first_name} {$member?->last_name} (Loan ID: {$request->lending_id})",
                'loan',
                $request->lending_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully! Ref: ' . ($repayment->reference_no ?? 'N/A'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function dashboard_officers_committees()
    {
        $officers = \App\Models\officer_tbl::with('user')->orderBy('sort_order')->get();
        $allMembers = Users_tbl::memberCandidates()->orderBy('first_name')->get();
        $positions = \App\Models\OfficerPosition::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin_components.officers_committees', compact('officers', 'allMembers', 'positions'));
    }

    public function storeOfficerPosition(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:officer_positions_tbls,name',
        ]);

        $position = \App\Models\OfficerPosition::create([
            'name' => trim($request->name),
            'is_active' => true,
        ]);

        AuditLog::log(
            'Created Officer Position',
            "Added new officer position \"{$position->name}\"",
            'officer_position',
            $position->id
        );

        return response()->json([
            'success' => true,
            'message' => "Position \"{$position->name}\" added successfully.",
            'position' => $position,
        ]);
    }

    public function deleteOfficerPosition($id)
    {
        $position = \App\Models\OfficerPosition::findOrFail($id);

        $inUse = \App\Models\officer_tbl::where('position', $position->name)->count();
        if ($inUse > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete \"{$position->name}\". It is currently assigned to {$inUse} officer(s).",
            ], 422);
        }

        $name = $position->name;
        $position->delete();

        AuditLog::log('Deleted Officer Position', "Deleted officer position \"{$name}\"", 'officer_position', $id);

        return response()->json(['success' => true, 'message' => "Position \"{$name}\" deleted successfully."]);
    }

    public function storeOfficer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users_tbls,id',
            'position' => 'required|string|max:255',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date|after_or_equal:term_start',
        ]);

        \App\Models\officer_tbl::create($request->only('user_id', 'position', 'term_start', 'term_end'));

        return response()->json(['success' => true, 'message' => 'Officer added successfully.']);
    }

    public function updateOfficer(Request $request, $id)
    {
        $officer = \App\Models\officer_tbl::findOrFail($id);
        $request->validate([
            'position' => 'required|string|max:255',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date|after_or_equal:term_start',
        ]);

        $officer->update($request->only('position', 'term_start', 'term_end'));

        return response()->json(['success' => true, 'message' => 'Officer updated successfully.']);
    }

    public function deleteOfficer($id)
    {
        $officer = \App\Models\officer_tbl::findOrFail($id);
        $officer->delete();

        return response()->json(['success' => true, 'message' => 'Officer removed successfully.']);
    }

    public function auditLogsIndex(Request $request)
    {
        if (!\App\Services\SoDGuard::isGeneralManager()) {
            abort(403, 'Only the General Manager can view audit logs.');
        }

        $query = AuditLog::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('admin_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('target_type', 'like', "%{$search}%")
                    ->orWhere('user_role', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        AuditLog::log(
            'Viewed Audit Logs',
            'Accessed the system audit logs page',
            'audit_logs',
            null
        );

        return view('admin_components.audit_logs', compact('logs'));
    }

    // ─── Additional Patronage Records (Finance Page) ─────────────────────

    public function patronageRecordsPartial(Request $request)
    {
        $year = $request->get('year', now()->year);
        $records = \App\Models\PatronageRecord::with('user', 'recorder')
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['year' => $year]);

        $totalAmount = \App\Models\PatronageRecord::where('year', $year)->sum('amount');
        $recordCount = \App\Models\PatronageRecord::where('year', $year)->count();

        $allMembers = Users_tbl::memberCandidates()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('admin_components.patronage_records_partial', compact(
            'records',
            'year',
            'totalAmount',
            'recordCount',
            'allMembers'
        ));
    }

    public function storePatronageRecord(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_id' => 'required|exists:users_tbls,id',
            'year' => 'required|integer|min:2000|max:2100',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $record = \App\Models\PatronageRecord::create([
            'user_id' => $request->user_id,
            'year' => $request->year,
            'source' => $request->source,
            'description' => $request->description,
            'amount' => $request->amount,
            'recorded_by' => auth()->id(),
        ]);

        $member = Users_tbl::find($request->user_id);
        AuditLog::log(
            'Created Additional Patronage Record',
            "Added patronage record for {$member->first_name} {$member->last_name} (Year: {$request->year}, Source: {$request->source}, Amount: ₱" . number_format($request->amount, 2) . ')',
            'patronage_record',
            $record->id
        );

        $records = \App\Models\PatronageRecord::with('user', 'recorder')
            ->where('year', $request->year)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['year' => $request->year]);

        $totalAmount = \App\Models\PatronageRecord::where('year', $request->year)->sum('amount');
        $recordCount = \App\Models\PatronageRecord::where('year', $request->year)->count();
        $allMembers = Users_tbl::memberCandidates()->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        $html = view('admin_components.patronage_records_partial', compact(
            'records',
            'totalAmount',
            'recordCount',
            'allMembers'
        ))->with('year', $request->year)->render();

        return response()->json([
            'success' => true,
            'message' => 'Patronage record added successfully.',
            'html' => $html,
        ]);
    }

    public function updatePatronageRecord(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $record = \App\Models\PatronageRecord::findOrFail($id);

        $oldAmount = $record->amount;
        $record->update([
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
        ]);

        $member = Users_tbl::find($record->user_id);
        AuditLog::log(
            'Updated Additional Patronage Record',
            "Updated patronage record for {$member->first_name} {$member->last_name} (Year: {$record->year}) from ₱" . number_format($oldAmount, 2) . ' to ₱' . number_format($record->amount, 2),
            'patronage_record',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Patronage record updated successfully.',
        ]);
    }

    public function deletePatronageRecord($id)
    {
        $record = \App\Models\PatronageRecord::with('user')->findOrFail($id);
        $year = $record->year;

        $memberName = $record->user->first_name . ' ' . $record->user->last_name;
        $amount = $record->amount;
        $source = $record->source;

        $record->delete();

        AuditLog::log(
            'Deleted Additional Patronage Record',
            "Deleted patronage record for {$memberName} (Year: {$year}, Source: {$source}, Amount: ₱" . number_format($amount, 2) . ')',
            'patronage_record',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Patronage record deleted successfully.',
        ]);
    }

    public function completeRepayment(Request $request, $id)
    {
        $repayment = lending_repayments_tbl::findOrFail($id);

        if (strtolower($repayment->status) !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This repayment is not pending.']);
        }

        // Segregation of duties: the creator cannot be the one confirming unless GM.
        if (!\App\Services\SoDGuard::canFinalize($repayment)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        $loan = lending_program_tbl::findOrFail($repayment->lending_id);
        $status = lending_status_tbl::where('lending_id', $repayment->lending_id)->first();
        $loanCalc = new \App\Services\LoanCalculationService;
        $currentInstallment = $loanCalc->currentInstallment($loan->id);

        $cashReceived = (float) $repayment->amount_paid;
        $penaltyAmount = (float) ($repayment->late_fee ?? 0);

        DB::beginTransaction();
        try {
            if ($currentInstallment) {
                // ── SCHEDULE-BASED: apply penalty + allocate payment ────────────
                if ($penaltyAmount > 0 && $status) {
                    $status->penalty_amount = (float) ($status->penalty_amount ?? 0) + $penaltyAmount;
                    $status->remaining_balance = (float) $status->remaining_balance + $penaltyAmount;
                    $status->last_penalty_date = $repayment->due_date
                        ? \Carbon\Carbon::parse($repayment->due_date)->format('Y-m-d')
                        : now()->format('Y-m-d');
                    $status->save();

                    AuditLog::log(
                        'Loan Overdue Penalty',
                        "Applied ₱{$penaltyAmount} overdue penalty on loan (ID: {$repayment->lending_id})",
                        'loan',
                        $repayment->lending_id
                    );
                }

                $allocation = $loanCalc->allocatePayment($loan->id, $cashReceived);

                $repayment->update([
                    'status' => 'Completed',
                    'principal_paid' => $allocation['principal_paid'],
                    'interest_paid' => $allocation['interest_paid'],
                ]);

                if ($status) {
                    $status->total_paid = round((float) $status->total_paid + (float) $allocation['amount_paid'], 2);
                    $status->remaining_balance = max(0, round((float) $status->remaining_balance - $cashReceived, 2));
                    $status->payments_made = (int) $status->payments_made + (int) $allocation['installments_settled'];

                    if ($allocation['fully_paid_now'] || $status->remaining_balance <= 0) {
                        $status->status = 'Completed';
                        $status->payments_made = (int) $status->total_payments;
                        $status->remaining_balance = 0;
                        $status->due_date = $loan->created_at
                            ? \Carbon\Carbon::parse($loan->created_at)
                                ->addMonthsNoOverflow(((int) $status->total_payments) * lendingController::PAYMENT_INTERVAL_MONTHS)
                                ->format('Y-m-d')
                            : $status->due_date;

                        lending_program_tbl::where('id', $loan->id)
                            ->update(['status' => 'Completed']);
                    } else {
                        $nextUnpaid = $loanCalc->currentInstallment($loan->id);
                        if ($nextUnpaid) {
                            $status->due_date = $nextUnpaid->due_date;
                        }
                    }

                    $status->save();
                }
            } else {
                // ── LEGACY: flat-ratio allocation ───────────────────────────────
                $interestRatio = ($loan->total_payment > 0) ? ($loan->total_interest / $loan->total_payment) : 0;
                $interestPaid = round($cashReceived * $interestRatio, 2);
                $principalPaid = round($cashReceived - $interestPaid, 2);

                $repayment->update([
                    'status' => 'Completed',
                    'principal_paid' => $principalPaid,
                    'interest_paid' => $interestPaid,
                ]);

                if ($status) {
                    $status->total_paid += $cashReceived;
                    $status->remaining_balance = max(0, $status->remaining_balance - $cashReceived);
                    $status->payments_made += 1;

                    if ($status->remaining_balance <= 0 || $status->payments_made >= $status->total_payments) {
                        $status->status = 'Completed';
                        $status->payments_made = $status->total_payments;

                        lending_program_tbl::where('id', $repayment->lending_id)
                            ->update(['status' => 'Completed']);
                    } else {
                        $status->due_date = \Carbon\Carbon::parse($loan->created_at)
    ->addMonthsNoOverflow(($status->payments_made + 1) * lendingController::PAYMENT_INTERVAL_MONTHS)
    ->format('Y-m-d');
                    }

                    $status->save();
                }
            }

            DB::commit();

            \App\Models\Notifications_tbl::create([
                'user_id' => $repayment->user_id,
                'title' => 'Loan Repayment Confirmed',
                'message' => 'Your loan repayment of ₱' . number_format($repayment->amount_paid, 2) . " has been confirmed. (Ref: {$repayment->reference_no})",
                'category' => 'inbox',
                'is_important' => true,
            ]);

            AuditLog::log(
                'Completed Loan Repayment',
                "Confirmed repayment of ₱{$repayment->amount_paid} (Ref: {$repayment->reference_no}) on loan (ID: {$repayment->lending_id})",
                'loan',
                $repayment->lending_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Repayment confirmed successfully. Member has been notified.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm repayment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function voidRepayment(Request $request, $id)
    {
        $repayment = lending_repayments_tbl::findOrFail($id);

        if (strtolower($repayment->status) !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This repayment is not pending.']);
        }

        // Segregation of duties: the creator cannot be the one voiding unless GM.
        if (!\App\Services\SoDGuard::canFinalize($repayment)) {
            return response()->json([
                'success' => false,
                'message' => \App\Services\SoDGuard::denialMessage()['message'],
            ], 403);
        }

        $request->validate([
            'void_reason' => 'required|string|in:wrong_amount,duplicate_payment,fraudulent,other_member,technical_error,other',
        ]);

        $repayment->update([
            'status' => 'voided',
            'void_reason' => $request->void_reason,
            'voided_by' => Auth::id(),
            'voided_at' => now(),
        ]);

        $reasonLabels = [
            'wrong_amount' => 'Wrong amount entered',
            'duplicate_payment' => 'Duplicate payment',
            'fraudulent' => 'Fraudulent / suspicious transaction',
            'other_member' => 'Sent by wrong member',
            'technical_error' => 'System / technical error',
            'other' => 'Other',
        ];
        $reasonText = $this->returnReasonLabel($request->void_reason);

        \App\Models\Notifications_tbl::create([
            'user_id' => $repayment->user_id,
            'title' => 'Loan Repayment Returned',
            'message' => 'Your loan repayment of ₱' . number_format($repayment->amount_paid, 2) . " has been returned. Reason: {$reasonText}. (Ref: {$repayment->reference_no})",
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Returned Loan Repayment',
            "Returned repayment of ₱{$repayment->amount_paid} (Ref: {$repayment->reference_no}) on loan (ID: {$repayment->lending_id}). Reason: {$reasonText}",
            'loan',
            $repayment->lending_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Repayment marked as returned. Member has been notified.',
        ]);
    }

    /**
     * Query filter hiding in-flight transactions created by Allied Workers
     * from everyone except the General Manager. Returns null for the GM (or
     * when there are no Allied Workers) so the caller skips the filter.
     */
    private function returnReasonLabel(?string $key): string
    {
        $labels = [
            'wrong_amount' => 'Wrong amount entered',
            'duplicate_payment' => 'Duplicate payment',
            'fraudulent' => 'Fraudulent / suspicious transaction',
            'other_member' => 'Sent by wrong member',
            'technical_error' => 'System / technical error',
            'other' => 'Other',
        ];

        return $labels[$key] ?? ($key ?: 'No reason provided');
    }

    private function alliedPendingVisibilityClosure(): ?\Closure
    {
        if (\App\Services\SoDGuard::isGeneralManager()) {
            return null;
        }

        $alliedWorkerIds = \App\Models\Users_tbl::alliedWorkerIds();
        if (empty($alliedWorkerIds)) {
            return null;
        }

        return function ($q) use ($alliedWorkerIds) {
            $q->where(function ($sub) use ($alliedWorkerIds) {
                $sub->whereNotIn('created_by', $alliedWorkerIds)
                    ->orWhereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['completed', 'voided', 'void', 'rejected', 'failed']);
            });
        };
    }
}