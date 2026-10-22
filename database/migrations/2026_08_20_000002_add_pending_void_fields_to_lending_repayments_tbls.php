<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->default('Completed')->after('recorded_by');
            $table->string('gcash_number', 20)->nullable()->after('status');
            $table->string('gcash_reference_no', 20)->nullable()->unique()->after('gcash_number');
            $table->string('void_reason', 100)->nullable()->after('gcash_reference_no');
            $table->unsignedBigInteger('voided_by')->nullable()->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });

        DB::statement("
            ALTER TABLE lending_repayments_tbls
            MODIFY status ENUM(
                'Completed', 'Pending', 'voided'
            ) DEFAULT 'Completed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE lending_repayments_tbls
            MODIFY status ENUM(
                'Completed', 'Pending', 'voided'
            ) DEFAULT 'Completed'
        ");

        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $table->dropColumn(['gcash_number', 'gcash_reference_no', 'void_reason', 'voided_by', 'voided_at']);
        });
    }
};
