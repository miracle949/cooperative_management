<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relax the UNIQUE constraint on gcash_reference_no to a plain (non-unique)
     * index so that a reference number belonging to a VOIDED transaction can be
     * reused. Cross-table, non-voided uniqueness is enforced at the application
     * layer (see UsersHandle::checkReference and each deposit/repayment store).
     */
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['gcash_reference_no']);
                $t->index('gcash_reference_no');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables()) as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['gcash_reference_no']);
                $t->unique('gcash_reference_no');
            });
        }
    }

    private function tables(): array
    {
        return [
            'savings_transaction_tbls',
            'share_capital_transaction_tbls',
            'lending_repayments_tbls',
        ];
    }
};
