<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE share_capital_account_tbls MODIFY COLUMN status ENUM('Active','Inactive','Closed','Pending') NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE share_capital_account_tbls MODIFY COLUMN status ENUM('Active','Inactive','Closed') NOT NULL DEFAULT 'Active'");
    }
};
