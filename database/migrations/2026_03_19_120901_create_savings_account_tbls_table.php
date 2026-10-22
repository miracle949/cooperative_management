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
        Schema::create('savings_account_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->decimal('balance', 12, 2)->default('0.00');
            $table->enum('status', ['active', 'frozen', 'closed'])->default('active');
            $table->date('opened_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_account_tbls');
    }
};
