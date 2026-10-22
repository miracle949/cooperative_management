<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_deposits_tbl', function (Blueprint $table) {
            $table->decimal('claimed_amount', 12, 2)->nullable()->after('balance');
            $table->decimal('claimed_principal', 12, 2)->nullable()->after('claimed_amount');
            $table->decimal('claimed_interest', 12, 2)->nullable()->after('claimed_principal');
        });
    }

    public function down(): void
    {
        Schema::table('time_deposits_tbl', function (Blueprint $table) {
            $table->dropColumn(['claimed_amount', 'claimed_principal', 'claimed_interest']);
        });
    }
};
