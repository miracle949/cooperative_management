<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->string('net_proceeds_adjustment_type')->nullable()->after('net_proceeds');
            $table->decimal('net_proceeds_adjustment_amount', 10, 2)->nullable()->default(0)->after('net_proceeds_adjustment_type');
            $table->decimal('base_net_proceeds', 10, 2)->nullable()->after('net_proceeds_adjustment_amount');
        });

        DB::table('lending_program_tbls')
            ->whereNull('base_net_proceeds')
            ->update(['base_net_proceeds' => DB::raw('net_proceeds')]);
    }

    public function down(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->dropColumn(['base_net_proceeds', 'net_proceeds_adjustment_amount', 'net_proceeds_adjustment_type']);
        });
    }
};