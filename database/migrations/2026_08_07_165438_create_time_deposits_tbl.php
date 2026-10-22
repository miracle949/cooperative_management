<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('time_deposits_tbl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('savings_account_id');
            $table->decimal('goal_amount', 12, 2);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->date('opened_at');
            $table->date('maturity_date');
            $table->enum('status', ['active', 'claimed'])->default('active');
            $table->string('reference_no', 191)->unique();
            $table->string('claim_reference_no')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->foreign('savings_account_id')
                ->references('id')->on('savings_account_tbls')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_deposits_tbl');
    }
};
