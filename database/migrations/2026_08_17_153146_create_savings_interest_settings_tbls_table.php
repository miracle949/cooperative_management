<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_interest_settings_tbls', function (Blueprint $table) {
            $table->id();

            $table->decimal('annual_rate', 5, 2)->default(2.00);
            $table->enum('release_frequency', ['monthly', 'quarterly', 'semi-annual', 'annual'])->default('quarterly');
            $table->decimal('min_balance_for_interest', 12, 2)->default(0.00);
            $table->decimal('maintaining_balance', 12, 2)->default(0.00);

            $table->timestamps();
        });

        DB::table('savings_interest_settings_tbls')->insert([
            [
                'annual_rate' => 2.00,
                'release_frequency' => 'quarterly',
                'min_balance_for_interest' => 0.00,
                'maintaining_balance' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_interest_settings_tbls');
    }
};
