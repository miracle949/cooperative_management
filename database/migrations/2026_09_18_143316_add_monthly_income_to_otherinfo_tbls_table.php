<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->decimal('monthly_income', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->dropColumn('monthly_income');
        });
    }
};
