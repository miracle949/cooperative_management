<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payment_methods_tbls', function (Blueprint $table) {
            $table->unsignedTinyInteger('reference_length')->nullable()->after('account_number');
        });
    }

    public function down()
    {
        Schema::table('payment_methods_tbls', function (Blueprint $table) {
            $table->dropColumn('reference_length');
        });
    }
};
