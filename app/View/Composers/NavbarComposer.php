<?php

namespace App\View\Composers;

use App\Http\Controllers\UsersHandle;
use App\Models\Membergovern_ids_tbl;
use App\Models\Otherinfo_tbl;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view)
    {
        if (! Auth::check()) {
            $view->with([
                'navMissingCount' => 0,
                'navNotifications' => collect(),
            ]);

            return;
        }

        $userId = Auth::id();

        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $membergovernIds = Membergovern_ids_tbl::where('user_id', $userId)->first();

        $navMissingCount = 0;
        if (empty($otherinfo->contact_no)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->present_address)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->permanent_address)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->date_of_birth)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->place_of_birth)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->sex)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->civil_status)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->citizenship)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->blood_type)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->height)) {
            $navMissingCount++;
        }
        if (empty($otherinfo->weight)) {
            $navMissingCount++;
        }
        if (empty($membergovernIds->sss_id)) {
            $navMissingCount++;
        }
        if (empty($membergovernIds->philhealth_id)) {
            $navMissingCount++;
        }
        if (empty($membergovernIds->pagibig_id)) {
            $navMissingCount++;
        }
        if (empty($membergovernIds->tin_id)) {
            $navMissingCount++;
        }

        $view->with([
            'navMissingCount' => $navMissingCount,
            'navNotifications' => app(UsersHandle::class)->buildMemberNotifications($userId),
        ]);
    }
}
