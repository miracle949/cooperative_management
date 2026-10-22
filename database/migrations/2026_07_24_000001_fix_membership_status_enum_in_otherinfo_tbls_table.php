<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->string('membership_status', 50)->default('Unofficial')->change();
        });
    }

    public function down(): void
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->enum('membership_status', ['Unofficial', 'Active', 'Not Active'])->default('Unofficial')->change();
        });
    }
};
