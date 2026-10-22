<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the obsolete member-side two_factor_enabled preference column.
     * It is only dropped when present so fresh installs (which never create
     * the column) and upgraded environments both migrate safely.
     */
    public function up(): void
    {
        if (Schema::hasColumn('account_settings_tbls', 'two_factor_enabled')) {
            Schema::table('account_settings_tbls', function (Blueprint $table) {
                $table->dropColumn('two_factor_enabled');
            });
        }
    }

    /**
     * Restore the boolean column if a rollback is ever required.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('account_settings_tbls', 'two_factor_enabled')) {
            Schema::table('account_settings_tbls', function (Blueprint $table) {
                $table->boolean('two_factor_enabled')->default(false);
            });
        }
    }
};