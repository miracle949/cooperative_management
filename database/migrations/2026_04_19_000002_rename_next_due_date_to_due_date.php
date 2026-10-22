<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if next_due_date exists and due_date doesn't
        if (Schema::hasColumn('lending_status_tbls', 'next_due_date') && ! Schema::hasColumn('lending_status_tbls', 'due_date')) {
            Schema::table('lending_status_tbls', function (Blueprint $table) {
                $table->renameColumn('next_due_date', 'due_date');
            });
        }

        // If neither exists, create due_date
        if (! Schema::hasColumn('lending_status_tbls', 'due_date')) {
            Schema::table('lending_status_tbls', function (Blueprint $table) {
                $table->date('due_date')->nullable()->after('interest_rate');
            });
        }
    }

    public function down(): void
    {
        // No rollback needed for this fix
    }
};
