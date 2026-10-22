<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_settings_tbls', function (Blueprint $table) {
            $table->id();

            // Loan Type
            $table->string('loan_type', 191)->unique();

            // Loan Limits
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();

            // Loan Terms
            $table->integer('min_term')->nullable(); // months
            $table->integer('max_term')->nullable(); // months

            // Interest
            $table->decimal('interest_rate', 5, 2)->default(2.00);
            $table->enum('interest_type', [
                'Flat',
                'Declining',
            ])->default('Declining');

            // One-Time Charges
            $table->decimal('processing_fee_rate', 5, 2)->default(2.00);
            $table->decimal('service_fee_rate', 5, 2)->default(2.00);

            // Loan Protection
            $table->decimal('loan_protection_fee', 10, 2)->default(2.00); // ₱2 per month

            // Capital Build-Up (Retention)
            $table->decimal('retention_paid_rate', 5, 2)->default(3.00);
            $table->decimal('retention_unpaid_rate', 5, 2)->default(6.00);

            // Penalty
            $table->decimal('late_fee', 10, 2)->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Insert default interest rates for each loan type
        DB::table('loan_settings_tbls')->insert([
            ['loan_type' => 'Personal Loan', 'interest_rate' => 2.00, 'max_amount' => 25000.00, 'created_at' => now(), 'updated_at' => now()],
            ['loan_type' => 'Emergency Loan', 'interest_rate' => 2.00, 'max_amount' => 25000.00, 'created_at' => now(), 'updated_at' => now()],
            ['loan_type' => 'Business Loan', 'interest_rate' => 2.00, 'max_amount' => 25000.00, 'created_at' => now(), 'updated_at' => now()],
            ['loan_type' => 'Education Loan', 'interest_rate' => 2.00, 'max_amount' => 25000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_settings_tbls');
    }
};
