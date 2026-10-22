<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Officer', 'slug' => 'officer', 'description' => 'Limited sidebar access', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
