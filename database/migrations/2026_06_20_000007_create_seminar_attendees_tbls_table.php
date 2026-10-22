<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_attendees_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->constrained('seminars_tbls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users_tbls')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, attended, absent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_attendees_tbls');
    }
};
