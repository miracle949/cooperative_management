<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE savings_transaction_tbls
            MODIFY status ENUM(
                'pending',
                'completed',
                'approved',
                'rejected',
                'credited',
                'locked',
                'released',
                'deducted'
            ) DEFAULT 'completed'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE savings_transaction_tbls
            MODIFY status ENUM(
                'pending',
                'completed',
                'approved',
                'rejected',
                'credited',
                'locked'
            ) DEFAULT 'completed'
        ");
    }
};
