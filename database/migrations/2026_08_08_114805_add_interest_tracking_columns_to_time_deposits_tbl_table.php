<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_deposits_tbl', function (Blueprint $table) {
            $table->decimal('interest_accrued_balance', 12, 2)->default(0.00)->after('balance');
            $table->date('last_interest_credited_at')->nullable()->after('interest_accrued_balance');
        });
    }

    public function down(): void
    {
        Schema::table('time_deposits_tbl', function (Blueprint $table) {
            $table->dropColumn(['interest_accrued_balance', 'last_interest_credited_at']);
        });
    }
};
