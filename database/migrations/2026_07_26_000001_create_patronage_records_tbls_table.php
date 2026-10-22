<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patronage_records_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users_tbls')
                ->onDelete('cascade');
            $table->integer('year');
            $table->string('source')->default('other');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users_tbls')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patronage_records_tbls');
    }
};
