<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resignation_requests_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls')->cascadeOnDelete();
            $table->boolean('withdraw_share_capital')->default(false);
            $table->string('status', 20)->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->date('release_date')->nullable();
            $table->boolean('is_released')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignation_requests_tbls');
    }
};
