<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->dropColumn([
                'interest_last_credited_at',
                'td_goal_amount',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->date('interest_last_credited_at')->nullable()->after('balance');
            $table->decimal('td_goal_amount', 12, 2)->nullable()->after('interest_last_credited_at');
        });
    }
};
