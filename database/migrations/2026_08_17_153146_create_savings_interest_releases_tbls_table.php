<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_interest_releases_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_account_id')->constrained('savings_account_tbls')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_label');
            $table->decimal('amount', 12, 2);
            $table->string('reference_no', 64)->unique();
            $table->timestamps();

            $table->unique(['savings_account_id', 'period_start', 'period_end'], 'sir_acct_period_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_interest_releases_tbls');
    }
};
