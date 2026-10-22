<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('account_settings_tbls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('loan_reminders')->default(true);
            $table->boolean('savings_updates')->default(true);
            $table->boolean('email_digest')->default(false);
            $table->boolean('announcements')->default(true);
            $table->boolean('login_alerts')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users_tbls')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_settings_tbls');
    }
};
