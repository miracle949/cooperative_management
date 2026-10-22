<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $table->unsignedInteger('payment_sequence')->default(1)->after('payment_number');
        });
    }

    public function down(): void
    {
        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $table->dropColumn('payment_sequence');
        });
    }
};
