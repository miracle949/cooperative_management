<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_requests_tbls', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('token', 64)->unique();
            $t->string('status', 20)->default('pending'); // pending|approved|denied|expired|used
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_requests_tbls');
    }
};
