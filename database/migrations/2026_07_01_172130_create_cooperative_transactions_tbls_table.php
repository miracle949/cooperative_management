<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_transactions_tbls', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->enum('category', ['Vehicle Purchase', 'Bank Investment', 'Office Equipment', 'Utilities', 'Maintenance', 'Other']);
            $table->enum('transaction_type', ['expense', 'investment']);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_transactions_tbls');
    }
};
