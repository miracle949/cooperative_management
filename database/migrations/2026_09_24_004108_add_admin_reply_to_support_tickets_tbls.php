<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('support_tickets_tbls', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets_tbls', 'admin_reply')) {
                $table->text('admin_reply')->nullable();
            }
            if (!Schema::hasColumn('support_tickets_tbls', 'replied_by')) {
                $table->unsignedBigInteger('replied_by')->nullable();
            }
            if (!Schema::hasColumn('support_tickets_tbls', 'replied_at')) {
                $table->timestamp('replied_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets_tbls', function (Blueprint $table) {
            foreach (['admin_reply', 'replied_by', 'replied_at'] as $col) {
                if (Schema::hasColumn('support_tickets_tbls', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};