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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 191)->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key', 191)->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->dateTime('disbursed_at')->nullable()->after('status');
            $table->foreignId('disbursed_by')
                ->nullable()
                ->after('disbursed_at')
                ->constrained('users_tbls')
                ->nullOnDelete();
            $table->string('disbursement_method', 50)->nullable()->after('disbursed_by');
            $table->string('disbursement_reference', 100)->nullable()->after('disbursement_method');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');

        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn([
                'disbursed_at',
                'disbursed_by',
                'disbursement_method',
                'disbursement_reference',
            ]);
        });
    }
};
