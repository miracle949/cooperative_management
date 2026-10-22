<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_passcodes_tbls', function (Blueprint $table) {
            $table->id();
            $table->string('seminar_type', 100)->unique();
            $table->string('passcode', 64);
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_passcodes_tbls');
    }
};
