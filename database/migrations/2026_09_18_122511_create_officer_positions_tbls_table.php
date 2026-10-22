<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('officer_positions_tbls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Preserve the positions that used to be hardcoded
        $defaults = ['Chairperson', 'Vice Chairperson', 'Secretary', 'Treasurer', 'Auditor', 'P.R.O.', 'Sergeant-at-Arms'];
        foreach ($defaults as $i => $name) {
            DB::table('officer_positions_tbls')->insert([
                'name' => $name,
                'sort_order' => $i,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_positions_tbls');
    }
};