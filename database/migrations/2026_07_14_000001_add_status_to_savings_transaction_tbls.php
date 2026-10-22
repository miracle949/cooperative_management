<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transaction_tbls', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Completed', 'Failed'])->nullable()->after('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::table('savings_transaction_tbls', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
