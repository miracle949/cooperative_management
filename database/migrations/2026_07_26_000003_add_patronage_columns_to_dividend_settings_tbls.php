<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dividend_settings_tbls', function (Blueprint $table) {
            $table->decimal('patronage_fund_percentage', 5, 2)->default(40.00)->after('dividend_fund_percentage');
            $table->string('patronage_basis', 50)->default('total_repayment')->after('patronage_fund_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('dividend_settings_tbls', function (Blueprint $table) {
            $table->dropColumn(['patronage_fund_percentage', 'patronage_basis']);
        });
    }
};
