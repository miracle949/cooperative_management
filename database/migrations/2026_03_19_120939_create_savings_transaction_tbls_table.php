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
        Schema::create('savings_transaction_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_account_id')
                ->constrained('savings_account_tbls')
                ->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'interest', 'penalty_offset']);
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->decimal('balance_after', 12, 2);
            $table->string('note')->nullable();
            $table->string('reference_no')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_transaction_tbls');
    }
};
