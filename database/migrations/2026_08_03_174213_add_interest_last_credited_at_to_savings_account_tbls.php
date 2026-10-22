<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->decimal('interest_accrued_balance', 12, 2)->default(0)->after('balance');
            $table->date('interest_last_credited_at')->nullable()->after('interest_accrued_balance');
        });
    }

    public function down(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->dropColumn('interest_last_credited_at');
        });
    }
};
