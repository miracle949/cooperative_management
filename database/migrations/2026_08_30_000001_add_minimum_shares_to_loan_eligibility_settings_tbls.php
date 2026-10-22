<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_eligibility_settings_tbls', function (Blueprint $table) {
            $table->decimal('minimum_shares', 10, 2)->default(10)->after('savings_to_loan_ratio');
        });

        DB::table('loan_eligibility_settings_tbls')->update(['minimum_shares' => 10.00]);
    }

    public function down(): void
    {
        Schema::table('loan_eligibility_settings_tbls', function (Blueprint $table) {
            $table->dropColumn('minimum_shares');
        });
    }
};