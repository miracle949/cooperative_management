<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dividend_distributions', function (Blueprint $table) {
            $table->string('patronage_basis', 50)
                ->nullable()
                ->after('patronage_refund_pool');
        });

        // Backfill existing distributions with the patronage basis from the
        // DividendSetting belonging to the same distribution year. Fall back to
        // the default 'total_repayment' only when no matching historical
        // setting exists for that year.
        DB::table('dividend_distributions')->get()->each(function ($distribution) {
            $basis = DB::table('dividend_settings_tbls')
                ->where('year', $distribution->year)
                ->value('patronage_basis');

            DB::table('dividend_distributions')
                ->where('id', $distribution->id)
                ->update(['patronage_basis' => $basis ?: 'total_repayment']);
        });
    }

    public function down(): void
    {
        Schema::table('dividend_distributions', function (Blueprint $table) {
            $table->dropColumn('patronage_basis');
        });
    }
};
