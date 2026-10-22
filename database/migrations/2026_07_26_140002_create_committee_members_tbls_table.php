<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_members_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('committees_tbls')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users_tbls')->onDelete('cascade');
            $table->boolean('is_chair')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_members_tbls');
    }
};
