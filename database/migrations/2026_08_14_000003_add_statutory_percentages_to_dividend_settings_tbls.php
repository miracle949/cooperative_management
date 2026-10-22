<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dividend_settings_tbls', function (Blueprint $table) {
            $table->decimal('reserve_fund_percentage', 5, 2)->default(10.00)->after('patronage_basis');
            $table->decimal('cetf_percentage', 5, 2)->default(10.00)->after('reserve_fund_percentage');
            $table->decimal('cdf_percentage', 5, 2)->default(3.00)->after('cetf_percentage');
            $table->decimal('optional_fund_percentage', 5, 2)->default(7.00)->after('cdf_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('dividend_settings_tbls', function (Blueprint $table) {
            $table->dropColumn(['reserve_fund_percentage', 'cetf_percentage', 'cdf_percentage', 'optional_fund_percentage']);
        });
    }
};
