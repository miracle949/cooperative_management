<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patronage_refund_distributions_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->integer('year');
            $table->decimal('total_patronage', 12, 2)->default(0);
            $table->decimal('allocation_ratio', 8, 4)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->index('year');
            $table->index(['user_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patronage_refund_distributions_tbls');
    }
};
