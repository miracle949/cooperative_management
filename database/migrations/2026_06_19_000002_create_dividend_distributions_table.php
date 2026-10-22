<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividend_distributions', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->decimal('net_surplus', 15, 2)->default(0);
            $table->decimal('reserve_fund', 15, 2)->default(0);
            $table->decimal('education_fund', 15, 2)->default(0);
            $table->decimal('community_fund', 15, 2)->default(0);
            $table->decimal('optional_fund', 15, 2)->default(0);
            $table->decimal('dividend_pool', 15, 2)->default(0);
            $table->decimal('patronage_refund_pool', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_distributions');
    }
};
