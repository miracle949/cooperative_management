<?php

namespace App\Console\Commands;

use App\Services\SavingsInterestService;
use Illuminate\Console\Command;

class ProcessSavingsInterestAndMaturities extends Command
{
    /**
     * php artisan savings:process
     *
     * Runs daily via the scheduler.
     * Auto-releases interest if a new period boundary is reached.
     */
    protected $signature = 'savings:process';

    protected $description = 'Auto-release savings interest (if due)';

    public function handle(SavingsInterestService $service): int
    {
        $this->autoReleaseInterest($service);

        $this->info('Savings processing complete.');

        return self::SUCCESS;
    }

    private function autoReleaseInterest(SavingsInterestService $service): void
    {
        $results = $service->autoRelease();

        if (empty($results)) {
            $this->comment('No interest release due at this time.');

            return;
        }

        foreach ($results as $r) {
            $this->line("Credited ₱{$r['interest']} interest to account #{$r['account_id']} for {$r['period_label']} (Ref: {$r['reference_no']})");
        }

        $this->info('Interest credited to '.count($results).' account(s).');
    }
}
