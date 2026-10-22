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

        Schema::create('lending_repayments_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lending_id')
                ->constrained('lending_program_tbls')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->integer('payment_number');
            $table->decimal('amount_due', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->date('due_date')->nullable();
            $table->date('payment_date');
            $table->decimal('late_fee', 10, 2)->nullable();
            $table->date('penalty_applied_at')->nullable();
            $table->string('payment_method');
            $table->string('payment_type');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lending_repayments_tbls');
    }
};
