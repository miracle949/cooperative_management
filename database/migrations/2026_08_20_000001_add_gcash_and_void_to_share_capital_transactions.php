<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_capital_transaction_tbls', function (Blueprint $table) {
            $table->string('gcash_number', 20)->nullable()->after('reference_no');
            $table->string('gcash_reference_no', 20)->nullable()->unique()->after('gcash_number');
            $table->string('void_reason', 100)->nullable()->after('status');
            $table->unsignedBigInteger('voided_by')->nullable()->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });

        DB::statement("
            ALTER TABLE share_capital_transaction_tbls
            MODIFY status ENUM(
                'Pending', 'Completed', 'Approved', 'Rejected', 'voided'
            ) DEFAULT 'Pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE share_capital_transaction_tbls
            MODIFY status ENUM(
                'Pending', 'Completed', 'Approved', 'Rejected'
            ) DEFAULT 'Pending'
        ");

        Schema::table('share_capital_transaction_tbls', function (Blueprint $table) {
            $table->dropColumn(['gcash_number', 'gcash_reference_no', 'void_reason', 'voided_by', 'voided_at']);
        });
    }
};
