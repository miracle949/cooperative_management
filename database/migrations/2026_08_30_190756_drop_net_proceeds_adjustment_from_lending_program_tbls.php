<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->dropColumn([
                'net_proceeds_adjustment_type',
                'net_proceeds_adjustment_amount',
                'base_net_proceeds',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lending_program_tbls', function (Blueprint $table) {
            $table->string('net_proceeds_adjustment_type')->nullable();
            $table->decimal('net_proceeds_adjustment_amount', 10, 2)->nullable()->default(0);
            $table->decimal('base_net_proceeds', 10, 2)->nullable();
        });
    }
};