<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert the money/ledger tables to InnoDB so DB transactions are actually
     * enforced (MyISAM silently ignores BEGIN/ROLLBACK). users_tbls stays MyISAM,
     * so the account->user foreign keys must be dropped first: InnoDB cannot
     * reference a MyISAM table. Those FKs were never enforced under MyISAM.
     */
    public function up(): void
    {
        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('share_capital_account_tbls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE savings_account_tbls ENGINE = InnoDB');
        DB::statement('ALTER TABLE savings_transaction_tbls ENGINE = InnoDB');
        DB::statement('ALTER TABLE share_capital_account_tbls ENGINE = InnoDB');
        DB::statement('ALTER TABLE share_capital_transaction_tbls ENGINE = InnoDB');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE savings_transaction_tbls ENGINE = MyISAM');
        DB::statement('ALTER TABLE share_capital_transaction_tbls ENGINE = MyISAM');
        DB::statement('ALTER TABLE share_capital_account_tbls ENGINE = MyISAM');
        DB::statement('ALTER TABLE savings_account_tbls ENGINE = MyISAM');

        Schema::table('savings_account_tbls', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users_tbls')->onDelete('cascade');
        });
        Schema::table('share_capital_account_tbls', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users_tbls')->onDelete('cascade');
        });
    }
};
