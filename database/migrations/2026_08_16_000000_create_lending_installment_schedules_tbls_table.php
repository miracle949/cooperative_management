<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lending_installment_schedules_tbls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lending_id');
            $table->unsignedInteger('payment_number');
            $table->date('due_date');
            $table->decimal('principal_due', 10, 2)->default(0);
            $table->decimal('interest_due', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('balance_after', 10, 2)->default(0);
            $table->decimal('principal_paid', 10, 2)->default(0);
            $table->decimal('interest_paid', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->index('lending_id');
            $table->unique(['lending_id', 'payment_number'], 'inst_sched_lending_installment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lending_installment_schedules_tbls');
    }
};
