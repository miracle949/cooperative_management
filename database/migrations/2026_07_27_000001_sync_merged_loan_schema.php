<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lending_program_tbls: loan charge / net proceeds columns
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            if (! Schema::hasColumn('lending_program_tbls', 'processing_fee_rate')) {
                $table->decimal('processing_fee_rate', 10, 2)->default(0)->after('lending_type_term');
            }
            if (! Schema::hasColumn('lending_program_tbls', 'service_fee_rate')) {
                $table->decimal('service_fee_rate', 10, 2)->default(0)->after('processing_fee_rate');
            }
            if (! Schema::hasColumn('lending_program_tbls', 'loan_protection_fee')) {
                $table->decimal('loan_protection_fee', 10, 2)->default(0)->after('service_fee_rate');
            }
            if (! Schema::hasColumn('lending_program_tbls', 'retention_paid_rate')) {
                $table->decimal('retention_paid_rate', 10, 2)->default(0)->after('loan_protection_fee');
            }
            if (! Schema::hasColumn('lending_program_tbls', 'retention_unpaid_rate')) {
                $table->decimal('retention_unpaid_rate', 10, 2)->default(0)->after('retention_paid_rate');
            }
            if (! Schema::hasColumn('lending_program_tbls', 'net_proceeds')) {
                $table->decimal('net_proceeds', 10, 2)->default(0)->after('retention_unpaid_rate');
            }
        });

        // loan_settings_tbls: limits, interest type, fees, retention, penalty, status
        Schema::table('loan_settings_tbls', function (Blueprint $table) {
            if (! Schema::hasColumn('loan_settings_tbls', 'min_amount')) {
                $table->decimal('min_amount', 12, 2)->nullable()->after('loan_type');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'min_term')) {
                $table->integer('min_term')->nullable()->after('max_amount');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'max_term')) {
                $table->integer('max_term')->nullable()->after('min_term');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'interest_type')) {
                $table->enum('interest_type', ['Flat', 'Declining'])->default('Declining')->after('interest_rate');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'processing_fee_rate')) {
                $table->decimal('processing_fee_rate', 5, 2)->default(2.00)->after('interest_type');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'service_fee_rate')) {
                $table->decimal('service_fee_rate', 5, 2)->default(2.00)->after('processing_fee_rate');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'loan_protection_fee')) {
                $table->decimal('loan_protection_fee', 10, 2)->default(2.00)->after('service_fee_rate');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'retention_paid_rate')) {
                $table->decimal('retention_paid_rate', 5, 2)->default(3.00)->after('loan_protection_fee');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'retention_unpaid_rate')) {
                $table->decimal('retention_unpaid_rate', 5, 2)->default(6.00)->after('retention_paid_rate');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'late_fee')) {
                $table->decimal('late_fee', 10, 2)->default(0)->after('retention_unpaid_rate');
            }
            if (! Schema::hasColumn('loan_settings_tbls', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('late_fee');
            }
        });

        // lending_repayments_tbls: amount due, due date, penalty, payment type
        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            if (! Schema::hasColumn('lending_repayments_tbls', 'amount_due')) {
                $table->decimal('amount_due', 10, 2)->nullable()->after('payment_number');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'due_date')) {
                $table->date('due_date')->nullable()->after('amount_paid');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'penalty_applied_at')) {
                $table->date('penalty_applied_at')->nullable()->after('late_fee');
            }
            if (! Schema::hasColumn('lending_repayments_tbls', 'payment_type')) {
                $table->string('payment_type')->default('monthly')->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->dropColumn([
                'processing_fee_rate',
                'service_fee_rate',
                'loan_protection_fee',
                'retention_paid_rate',
                'retention_unpaid_rate',
                'net_proceeds',
            ]);
        });

        Schema::table('loan_settings_tbls', function (Blueprint $table) {
            $table->dropColumn([
                'min_amount',
                'min_term',
                'max_term',
                'interest_type',
                'processing_fee_rate',
                'service_fee_rate',
                'loan_protection_fee',
                'retention_paid_rate',
                'retention_unpaid_rate',
                'late_fee',
                'is_active',
            ]);
        });

        Schema::table('lending_repayments_tbls', function (Blueprint $table) {
            $table->dropColumn([
                'amount_due',
                'due_date',
                'penalty_applied_at',
                'payment_type',
            ]);
        });
    }
};
