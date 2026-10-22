<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Http\Controllers\ShareCapital;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CapitalizeSavingsInterest extends Command
{
    /**
     * php artisan savings:capitalize-backfill
     *
     * One-time backfill: fold each account's already-accrued interest
     * (interest_accrued_balance) into its main savings balance, and repair the
     * historical `interest_credit` transaction rows so their balance_after
     * snapshots reflect the correct capitalized running balance.
     *
     * Idempotent and safe to re-run: completed balance-affecting rows are
     * authoritative anchors, and interest rows are recomputed deterministically.
     */
    protected $signature = 'savings:capitalize-backfill';

    protected $description = 'Capitalize previously-accrued savings interest into the main balance and repair interest_credit history';

    public function handle(): int
    {
        $conversionType = ShareCapital::CONVERSION_TYPE;

        $accounts = savings_account_tbl::query()->get();

        $count = 0;
        foreach ($accounts as $account) {
            $charged = $this->capitalizeAccount($account, $conversionType);
            if ($charged) {
                $count++;
            }
        }

        $this->info('Capitalized accrued interest for '.$count.' savings account(s).');

        return self::SUCCESS;
    }

    private function capitalizeAccount(savings_account_tbl $account, string $conversionType): bool
    {
        $txs = savings_transaction_tbl::where('savings_account_id', $account->id)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($txs->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($account, $txs, $conversionType) {
            $running = 0.0;
            $interestRowsSeen = 0;

            foreach ($txs as $tx) {
                $type = $tx->type;
                $status = strtolower((string) $tx->status);

                if ($type === 'interest_credit') {
                    $running += (float) $tx->amount;
                    $interestRowsSeen++;

                    $tx->balance_after = round($running, 2);
                    $tx->status = 'completed';
                    $tx->save();
                    continue;
                }

                // Non-interest rows: only completed balance-affecting rows move
                // the running ledger and resync it to their authoritative snapshot.
                $isBalanceAffecting = in_array($type, ['deposit', 'withdrawal', $conversionType], true);
                if (! $isBalanceAffecting || $status !== 'completed' || $tx->balance_after === null) {
                    continue;
                }

                $running = (float) $tx->balance_after;
            }

            if ($interestRowsSeen === 0) {
                return false;
            }

            // Target balance is derived purely from the replayed ledger (which is
            // reconstructed from authoritative completed-row balance_after and the
            // capitalized interest). This is idempotent: identical whether or not
            // interest was already capitalized on a prior run.
            $newBalance = round($running, 2);
            $account->update(['balance' => $newBalance]);

            AuditLog::log(
                'Savings Interest Capitalized',
                "Capitalized interest into savings account #{$account->id} via replay. New balance: ₱{$newBalance}",
                'savings',
                $account->id
            );

            return true;
        });
    }
}