<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminars_tbls', function (Blueprint $table) {
            $table->id();
            $table->string('seminar_type'); // pmes, fundamentals, finance
            $table->dateTime('schedule_datetime');
            $table->string('delivery_type'); // online, f2f
            $table->string('online_link')->nullable();
            $table->string('meetup_place')->nullable();
            $table->string('exact_venue')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminars_tbls');
    }
};
