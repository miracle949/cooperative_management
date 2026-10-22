<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allied_worker_assignments', function (Blueprint $table) {
            $table->string('previous_membership_category', 100)->nullable()->after('previous_role');
        });

        // Backfill active assignments: snapshot the member's current category,
        // then mark the account's stored category as "Allied Workers".
        $assignments = DB::table('allied_worker_assignments')->where('status', 'active')->get();

        foreach ($assignments as $assignment) {
            $otherinfo = DB::table('otherinfo_tbls')->where('user_id', $assignment->user_id)->first();
            $previousCategory = $otherinfo->membership_category ?? null;

            DB::table('allied_worker_assignments')
                ->where('id', $assignment->id)
                ->update(['previous_membership_category' => $previousCategory]);

            if ($otherinfo) {
                DB::table('otherinfo_tbls')
                    ->where('user_id', $assignment->user_id)
                    ->update(['membership_category' => 'Allied Workers']);
            }
        }
    }

    public function down(): void
    {
        // Restore each assignment's snapshot back onto the member account.
        $assignments = DB::table('allied_worker_assignments')
            ->whereNotNull('previous_membership_category')
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('otherinfo_tbls')
                ->where('user_id', $assignment->user_id)
                ->update(['membership_category' => $assignment->previous_membership_category]);
        }

        Schema::table('allied_worker_assignments', function (Blueprint $table) {
            $table->dropColumn('previous_membership_category');
        });
    }
};