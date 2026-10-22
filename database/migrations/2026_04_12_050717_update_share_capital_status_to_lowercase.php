<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE share_capital_transaction_tbls SET status = LOWER(status)');
    }

    public function down(): void
    {
        DB::statement("UPDATE share_capital_transaction_tbls SET status = 'Completed' WHERE status = 'completed'");
    }
};
