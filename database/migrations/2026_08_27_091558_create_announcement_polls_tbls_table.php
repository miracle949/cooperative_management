<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_polls_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls');
            $table->string('question');
            $table->json('options');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_polls_tbls');
    }
};
