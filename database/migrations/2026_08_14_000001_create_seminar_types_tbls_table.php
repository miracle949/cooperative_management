<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_types_tbls', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        DB::table('seminar_types_tbls')->insert([
            ['slug' => 'pmes', 'label' => 'PMES', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'fundamentals', 'label' => 'Fundamentals of Coops', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'finance', 'label' => 'Cooperative Finance', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_types_tbls');
    }
};
