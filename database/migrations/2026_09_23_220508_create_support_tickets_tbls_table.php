<?php
// database/migrations/2026_09_23_000000_create_support_tickets_tbls_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_tickets_tbls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users_tbls')->cascadeOnDelete();
            $table->string('category');           // Balance Discrepancy, Loan Requirement, Payment Reflection, Other
            $table->string('subject');
            $table->text('message');
            $table->string('proof_path')->nullable();
            $table->string('status')->default('open'); // open, in_progress, resolved
            $table->text('admin_response')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets_tbls');
    }
};