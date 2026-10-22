<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->decimal('td_goal_amount', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->dropColumn('td_goal_amount');
        });
    }
};
