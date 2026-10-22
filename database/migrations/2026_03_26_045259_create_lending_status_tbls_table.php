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
        Schema::create('lending_status_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lending_id')
                ->constrained('lending_program_tbls')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->decimal('remaining_balance', 10, 2);
            $table->decimal('total_paid', 10, 2)->default('0');
            $table->integer('payments_made')->default('0');
            $table->integer('total_payments');
            $table->decimal('interest_rate', 5, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['Active', 'Completed', 'Overdue', 'Defaulted'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lending_status_tbls');
    }
};
