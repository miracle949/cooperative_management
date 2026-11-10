<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->timestamp('application_form_completed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('otherinfo_tbls', function (Blueprint $table) {
            $table->dropColumn('application_form_completed_at');
        });
    }
};
