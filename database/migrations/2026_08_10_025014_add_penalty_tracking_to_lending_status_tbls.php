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
        Schema::table('lending_status_tbls', function (Blueprint $table) {
            $table->decimal('penalty_amount', 12, 2)->default(0)->after('remaining_balance');
            $table->date('last_penalty_date')->nullable()->after('penalty_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lending_status_tbls', function (Blueprint $table) {
            //
        });
    }
};
