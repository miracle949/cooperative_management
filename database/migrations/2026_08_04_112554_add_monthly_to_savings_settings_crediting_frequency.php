<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE savings_settings_tbls MODIFY crediting_frequency ENUM('Monthly', 'Quarterly', 'At Maturity') DEFAULT 'Quarterly'");

        DB::table('savings_settings_tbls')
            ->where('savings_type', 'Regular Savings')
            ->update(['crediting_frequency' => 'Monthly']);
    }

    public function down(): void
    {
        // Move any 'Monthly' rows to a value that will still exist after the enum shrinks
        DB::table('savings_settings_tbls')
            ->where('crediting_frequency', 'Monthly')
            ->update(['crediting_frequency' => 'Quarterly']);

        DB::statement("ALTER TABLE savings_settings_tbls MODIFY crediting_frequency ENUM('Quarterly', 'At Maturity') DEFAULT 'Quarterly'");
    }
};
