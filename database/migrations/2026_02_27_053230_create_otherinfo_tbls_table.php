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
        Schema::create('otherinfo_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->string('membership_category')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('place_of_birth')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('present_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('skills')->nullable();
            $table->longText('profile_picture')->nullable();
            $table->longText('signature')->nullable();
            $table->enum('approval_status', ['Pending', 'Approved', 'Declined'])
                ->default('Pending');
            $table->enum('membership_status', ['Unofficial', 'Active', 'Not Active'])
                ->default('Unofficial');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otherinfo_tbls');
    }
};
