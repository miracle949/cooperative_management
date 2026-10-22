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
        Schema::create('savings_settings_tbls', function (Blueprint $table) {
            $table->id();

            // Savings Type
            $table->string('savings_type', 191)->unique(); // e.g. 'Regular Savings', 'Time Deposit 3mo', 'Time Deposit 6mo', 'Time Deposit 12mo'

            // For Time Deposit types only — null for Regular Savings
            $table->unsignedInteger('term_months')->nullable();

            // Interest (what the coop PAYS the member — not to be confused with loan_settings_tbls.interest_rate,
            // which is what a member pays the coop on a loan)
            $table->decimal('interest_rate', 5, 2)->default(4.00); // % p.a.
            $table->enum('crediting_frequency', ['Quarterly', 'At Maturity'])->default('Quarterly');

            // Limits
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Insert default settings: Regular Savings + 3 Time Deposit tiers
        DB::table('savings_settings_tbls')->insert([
            [
                'savings_type' => 'Regular Savings',
                'term_months' => null,
                'interest_rate' => 4.00,
                'crediting_frequency' => 'Quarterly',
                'min_amount' => 1.00,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'savings_type' => 'Time Deposit 3mo',
                'term_months' => 3,
                'interest_rate' => 2.50,
                'crediting_frequency' => 'At Maturity',
                'min_amount' => 1000.00,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'savings_type' => 'Time Deposit 6mo',
                'term_months' => 6,
                'interest_rate' => 3.00,
                'crediting_frequency' => 'At Maturity',
                'min_amount' => 1000.00,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'savings_type' => 'Time Deposit 12mo',
                'term_months' => 12,
                'interest_rate' => 3.50,
                'crediting_frequency' => 'At Maturity',
                'min_amount' => 1000.00,
                'max_amount' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_settings_tbls');
    }
};
