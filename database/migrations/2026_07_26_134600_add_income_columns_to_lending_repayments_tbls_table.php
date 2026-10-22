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
            if (! Schema::hasColumn('lending_repayments_tbls', 'principal_paid')) {
                $table->decimal('principal_paid', 10, 2)->default(0)->after('amount_paid');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'interest_paid')) {
                $table->decimal('interest_paid', 10, 2)->default(0)->after('principal_paid');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'service_fee_paid')) {
                $table->decimal('service_fee_paid', 10, 2)->default(0)->after('interest_paid');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'late_fee')) {
                $table->decimal('late_fee', 10, 2)->nullable()->after('service_fee_paid');
            }
        });

        // Idempotent backfill: compute income breakdown for existing records
        DB::statement('
            UPDATE lending_repayments_tbls r
            INNER JOIN lending_program_tbls p ON r.lending_id = p.id
            SET
                r.interest_paid = CASE
                    WHEN p.total_payment > 0 THEN ROUND(r.amount_paid * (p.total_interest / p.total_payment), 2)
                    ELSE 0
                END,
                r.principal_paid = CASE
                    WHEN p.total_payment > 0 THEN ROUND(r.amount_paid - ROUND(r.amount_paid * (p.total_interest / p.total_payment), 2), 2)
                    ELSE r.amount_paid
                END
            WHERE r.principal_paid = 0 AND r.interest_paid = 0
        ');
    }

    public function down(): void
    {
        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $columns = ['principal_paid', 'interest_paid', 'service_fee_paid', 'late_fee'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('lending_repayments_tbls', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
