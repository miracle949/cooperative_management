<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('share_capital_amount', 15, 2)->default(0);
            $table->decimal('recommended_amount', 15, 2)->default(0);
            $table->decimal('approved_amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividends');
    }
};
