<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add base_role to users_tbls to mark member-based accounts.
        Schema::table('users_tbls', function (Blueprint $table) {
            $table->string('base_role', 20)->nullable()->after('role');
        });

        // Backfill: any current user whose role is a member category is member-based.
        DB::table('users_tbls')
            ->whereIn(DB::raw('LOWER(role)'), ['member', 'pending', 'inactive'])
            ->update(['base_role' => 'member']);

        // 2. Allied Worker assignment history (supports promotion, revocation, audit).
        // NOTE: no foreign key constraints - users_tbls is MyISAM and cannot be
        // referenced by InnoDB foreign keys (consistent with the rest of the schema).
        Schema::create('allied_worker_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('previous_role', 100)->nullable();
            $table->string('new_role', 100);
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->string('id_document_path', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // 3. Segregation-of-duties / GM-review columns on the three transaction tables.
        $transactionTables = [
            'savings_transaction_tbls',
            'share_capital_transaction_tbls',
            'lending_repayments_tbls',
        ];

        foreach ($transactionTables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('created_by')->nullable()->after('id');
                $t->unsignedBigInteger('approved_by')->nullable()->after('created_by');
                $t->boolean('needs_gm_review')->default(false)->after('approved_by');
                $t->unsignedBigInteger('reviewed_by')->nullable()->after('needs_gm_review');
                $t->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $t->string('review_note', 255)->nullable()->after('reviewed_at');
            });
        }
    }

    public function down(): void
    {
        $transactionTables = [
            'savings_transaction_tbls',
            'share_capital_transaction_tbls',
            'lending_repayments_tbls',
        ];

        foreach ($transactionTables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['created_by', 'approved_by', 'needs_gm_review', 'reviewed_by', 'reviewed_at', 'review_note']);
            });
        }

        Schema::dropIfExists('allied_worker_assignments');

        Schema::table('users_tbls', function (Blueprint $table) {
            $table->dropColumn('base_role');
        });
    }
};
