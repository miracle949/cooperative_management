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
        Schema::create('dividend_histories_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_capital_account_id')
                ->constrained('share_capital_account_tbls')
                ->onDelete('cascade');
            $table->string('period_label'); // e.g., "1st Semester 2024"
            $table->tinyInteger('semester'); // 1 or 2
            $table->integer('year'); // e.g., 2024
            $table->decimal('dividend_rate', 5, 2); // Rate used for calculation (e.g., 8.50)
            $table->decimal('share_capital', 10, 2); // Capital used for calculation
            $table->decimal('dividend_amount', 10, 2); // Amount paid to member
            $table->date('date_paid');
            $table->enum('status', ['Pending', 'Paid'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividend_histories_tbls');
    }
};
