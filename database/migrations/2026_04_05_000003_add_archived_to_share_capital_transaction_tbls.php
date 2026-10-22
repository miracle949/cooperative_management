<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_capital_transaction_tbls', function (Blueprint $table) {});
    }

    public function down(): void
    {
        Schema::table('share_capital_transaction_tbls', function (Blueprint $table) {});
    }
};
