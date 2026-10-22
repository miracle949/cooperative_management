<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls')->cascadeOnDelete()->unique();
            $table->boolean('mute_inbox')->default(false);
            $table->boolean('mute_spam')->default(false);
            $table->boolean('mute_social')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings_tbls');
    }
};
