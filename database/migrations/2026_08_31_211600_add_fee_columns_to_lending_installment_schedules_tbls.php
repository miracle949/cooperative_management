<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lending_installment_schedules_tbls', function (Blueprint $table) {
            $table->decimal('fee_due', 10, 2)->default(0)->after('interest_due');
            $table->decimal('fee_paid', 10, 2)->default(0)->after('fee_due');
        });
    }

    public function down(): void
    {
        Schema::table('lending_installment_schedules_tbls', function (Blueprint $table) {
            $table->dropColumn(['fee_due', 'fee_paid']);
        });
    }
};
