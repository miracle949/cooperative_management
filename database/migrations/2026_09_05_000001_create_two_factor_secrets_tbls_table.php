<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_secrets_tbls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users_tbls')->onDelete('cascade');
            $table->text('secret')->nullable();
            $table->text('recovery_codes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_secrets_tbls');
    }
};