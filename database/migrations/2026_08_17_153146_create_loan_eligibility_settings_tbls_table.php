<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_eligibility_settings_tbls', function (Blueprint $table) {
            $table->id();

            $table->boolean('savings_to_loan_enabled')->default(true);
            $table->decimal('savings_to_loan_ratio', 5, 2)->default(100.00);

            $table->timestamps();
        });

        DB::table('loan_eligibility_settings_tbls')->insert([
            [
                'savings_to_loan_enabled' => true,
                'savings_to_loan_ratio' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_eligibility_settings_tbls');
    }
};
