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
        Schema::create('lending_program_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');

            $table->string('reference_no', 191)->nullable()->unique();
            $table->string('lending_type')->nullable();

            // Loan Details
            $table->decimal('lending_amount', 10, 2)->nullable();
            $table->string('lending_type_term')->nullable();

            // Loan Charges (Computed Amounts)
            $table->decimal('processing_fee_rate', 10, 2)->default(0);
            $table->decimal('service_fee_rate', 10, 2)->default(0);
            $table->decimal('loan_protection_fee', 10, 2)->default(0);
            $table->decimal('retention_paid_rate', 10, 2)->default(0);
            $table->decimal('retention_unpaid_rate', 10, 2)->default(0);

            // Amount Received by Borrower
            $table->decimal('net_proceeds', 10, 2)->default(0);

            // Payment Information
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->decimal('monthly_payment', 10, 2)->nullable();
            $table->decimal('total_payment', 10, 2)->nullable();
            $table->decimal('total_interest', 10, 2)->nullable();
            $table->date('due_date');

            $table->text('purpose_loan')->nullable();

            // Shared documents
            $table->string('valid_id')->nullable();
            $table->string('proof_of_income')->nullable();

            // Emergency Lending
            $table->string('proof_of_emergency')->nullable();

            // Business Lending
            $table->string('business_permit')->nullable();
            $table->string('financial_statement')->nullable();

            // Education Lending
            $table->string('school_id')->nullable();
            $table->string('cor')->nullable();
            $table->string('cog')->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Completed'])
                ->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lending_program_tbls');
    }
};
