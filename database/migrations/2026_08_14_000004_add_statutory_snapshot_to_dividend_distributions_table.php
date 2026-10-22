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
            $table->decimal('reserve_fund_percentage', 5, 2)->default(10.00)->after('optional_fund');
            $table->decimal('cetf_percentage', 5, 2)->default(10.00)->after('reserve_fund_percentage');
            $table->decimal('cdf_percentage', 5, 2)->default(3.00)->after('cetf_percentage');
            $table->decimal('optional_fund_percentage', 5, 2)->default(7.00)->after('cdf_percentage');
            $table->decimal('statutory_total_percentage', 5, 2)->default(30.00)->after('optional_fund_percentage');
            $table->decimal('remaining_surplus', 15, 2)->default(0)->after('statutory_total_percentage');
        });

        // Backfill existing distributions: default statutory percentages and the
        // remaining surplus derived from the already-snapshotted statutory amounts.
        DB::table('dividend_distributions')->get()->each(function ($distribution) {
            $statutoryTotal = round(
                (float) $distribution->reserve_fund
                + (float) $distribution->education_fund
                + (float) $distribution->community_fund
                + (float) $distribution->optional_fund,
                2
            );

            DB::table('dividend_distributions')->where('id', $distribution->id)->update([
                'reserve_fund_percentage' => 10.00,
                'cetf_percentage' => 10.00,
                'cdf_percentage' => 3.00,
                'optional_fund_percentage' => 7.00,
                'statutory_total_percentage' => 30.00,
                'remaining_surplus' => round((float) $distribution->net_surplus - $statutoryTotal, 2),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('dividend_distributions', function (Blueprint $table) {
            $table->dropColumn([
                'reserve_fund_percentage',
                'cetf_percentage',
                'cdf_percentage',
                'optional_fund_percentage',
                'statutory_total_percentage',
                'remaining_surplus',
            ]);
        });
    }
};
