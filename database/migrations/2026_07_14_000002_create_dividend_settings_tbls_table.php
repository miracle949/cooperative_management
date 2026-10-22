<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividend_settings_tbls', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->decimal('dividend_fund_percentage', 5, 2)->default(60.00);
            $table->foreignId('updated_by')->nullable()->constrained('users_tbls')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_settings_tbls');
    }
};
