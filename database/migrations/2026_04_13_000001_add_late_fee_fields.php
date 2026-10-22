<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_settings_tbls', function (Blueprint $table) {
            $table->decimal('late_fee_percentage', 5, 2)->default(2.00)->after('max_amount');
            $table->integer('grace_period_months')->default(1)->after('late_fee_percentage');
        });

        // Schema::table('lending_program_tbls', function (Blueprint $table) {
        //     $table->date('due_date')->nullable()->after('total_interest');
        //     $table->decimal('late_fee', 10, 2)->default(0)->after('due_date');
        //     $table->timestamp('penalty_applied_at')->nullable()->after('late_fee');
        // });

        $lateFeeSetting = DB::table('loan_settings_tbls')->first();
        if ($lateFeeSetting) {
            DB::table('loan_settings_tbls')->update([
                'late_fee_percentage' => 2.00,
                'grace_period_months' => 1,
            ]);
        }
    }

    public function down(): void
    {
        // Schema::table('lending_program_tbls', function (Blueprint $table) {
        //     $table->dropColumn(['due_date', 'late_fee', 'penalty_applied_at']);
        // });

        Schema::table('loan_settings_tbls', function (Blueprint $table) {
            $table->dropColumn(['late_fee_percentage', 'grace_period_months']);
        });
    }
};
