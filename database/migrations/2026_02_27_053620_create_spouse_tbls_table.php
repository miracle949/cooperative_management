<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('family_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->string('spouse_name')->nullable();
            $table->date('spouse_date_birth')->nullable();
            $table->string('spouse_place_birth')->nullable();
            $table->integer('number_son')->nullable();
            $table->integer('number_daughter')->nullable();
            $table->string('other_spec')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spouse_tbls');
    }
};
