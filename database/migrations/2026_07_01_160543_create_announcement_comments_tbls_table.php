<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_comments_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements_tbls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users_tbls');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_comments_tbls');
    }
};
