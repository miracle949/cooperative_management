<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('category', ['inbox', 'spam', 'social'])->default('inbox');
            $table->boolean('is_important')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_tbls');
    }
};
