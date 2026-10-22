<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('savings_settings_tbls')
            ->where('savings_type', 'like', 'Time Deposit%')
            ->update(['interest_rate' => 4.00]);
    }

    public function down(): void
    {
        // Restore the original tiered rates if rolled back
        DB::table('savings_settings_tbls')
            ->where('savings_type', 'Time Deposit 12mo')
            ->update(['interest_rate' => 4.00]);
    }
};
