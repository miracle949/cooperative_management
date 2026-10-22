<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Archived', 'Completed'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Archived'])->change();
        });
    }
};
