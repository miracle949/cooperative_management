<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ResignationRequest_tbl;
use App\Models\share_capital_account_tbl;
use App\Models\share_capital_transaction_tbl;
use App\Models\Users_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResignationController extends Controller
{
    public function requestResignation(Request $request)
    {
        $request->validate([
            'withdraw_share_capital' => 'required|boolean',
        ]);

        $user = Auth::user();

        $existing = ResignationRequest_tbl::where('user_id', $user->id)
            ->whereIn('status', ['pending'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending resignation request.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            ResignationRequest_tbl::create([
                'user_id' => $user->id,
                'withdraw_share_capital' => $request->withdraw_share_capital,
                'status' => 'pending',
            ]);

            $user->status = 'resignation_pending';
            $user->save();

            DB::commit();

            AuditLog::log(
                'Requested Resignation',
                "User #{$user->id} ({$user->first_name} {$user->last_name}) submitted resignation request".($request->withdraw_share_capital ? ' with share capital withdrawal' : ''),
                'resignation',
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Resignation request submitted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit resignation request.',
            ], 500);
        }
    }

    public function approveResignation(Request $request, $id)
    {
        $resignation = ResignationRequest_tbl::findOrFail($id);
        $user = Users_tbl::findOrFail($resignation->user_id);

        if ($resignation->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $resignation->status = 'approved';
            $resignation->approved_at = now();
            $resignation->save();

            if ($resignation->withdraw_share_capital) {
                $resignation->release_date = now()->addDays(60);
                $resignation->save();

                $user->status = 'awaiting_release';
                $user->save();

                $message = 'Resignation approved. 60-day holding period started. Release date: '.$resignation->release_date->format('M d, Y');
            } else {
                $user->status = 'resigned';
                $user->save();

                $scAccount = share_capital_account_tbl::where('user_id', $user->id)->first();
                if ($scAccount) {
                    $scAccount->status = 'Inactive';
                    $scAccount->save();
                }

                $message = 'Resignation approved. Member set to inactive. Share capital retained.';
            }

            DB::commit();

            AuditLog::log(
                'Approved Resignation',
                "Approved resignation for {$user->first_name} {$user->last_name} (ID: {$id})".($resignation->withdraw_share_capital ? ' - with share capital withdrawal' : ''),
                'resignation',
                $id
            );

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to approve resignation: '.$e->getMessage());
        }
    }

    public function rejectResignation(Request $request, $id)
    {
        $resignation = ResignationRequest_tbl::findOrFail($id);
        $user = Users_tbl::findOrFail($resignation->user_id);

        if ($resignation->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $resignation->status = 'rejected';
            $resignation->save();

            $user->status = 'active';
            $user->save();

            DB::commit();

            AuditLog::log(
                'Rejected Resignation',
                "Rejected resignation for {$user->first_name} {$user->last_name} (ID: {$id})",
                'resignation',
                $id
            );

            return redirect()->back()->with('success', 'Resignation request rejected. Member status restored to active.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to reject resignation: '.$e->getMessage());
        }
    }

    public function releaseShareCapital(Request $request, $id)
    {
        $resignation = ResignationRequest_tbl::with('user')->findOrFail($id);

        if ($resignation->status !== 'approved') {
            return redirect()->back()->with('error', 'Resignation must be approved first.');
        }

        if (! $resignation->withdraw_share_capital) {
            return redirect()->back()->with('error', 'This member opted to leave share capital.');
        }

        if ($resignation->is_released) {
            return redirect()->back()->with('error', 'Share capital already released.');
        }

        if (now()->lt($resignation->release_date)) {
            $daysLeft = now()->diffInDays($resignation->release_date);

            return redirect()->back()->with('error', "60-day holding period not yet over. {$daysLeft} day(s) remaining.");
        }

        $user = $resignation->user;
        $scAccount = share_capital_account_tbl::where('user_id', $user->id)->first();

        if (! $scAccount || $scAccount->total_amount <= 0) {
            return redirect()->back()->with('error', 'No share capital balance to release.');
        }

        DB::beginTransaction();
        try {
            $shares = $scAccount->total_shares;
            $amount = $scAccount->total_amount;

            share_capital_transaction_tbl::create([
                'share_capital_account_id' => $scAccount->id,
                'type' => 'Withdrawal',
                'shares' => $shares,
                'total_amount' => $amount,
                'payment_method' => 'Cash',
                'reference_no' => 'RESIGN-REL-'.now()->format('YmdHis'),
                'note' => 'Full share capital release upon resignation',
                'status' => 'Completed',
                'transaction_date' => now()->format('Y-m-d'),
            ]);

            $scAccount->total_shares = 0;
            $scAccount->total_amount = 0;
            $scAccount->status = 'Closed';
            $scAccount->save();

            $resignation->is_released = true;
            $resignation->save();

            $user->status = 'inactive';
            $user->save();

            DB::commit();

            AuditLog::log(
                'Released Share Capital',
                "Released share capital ₱{$amount} ({$shares} shares) to {$user->first_name} {$user->last_name} upon resignation (ID: {$id})",
                'resignation',
                $id
            );

            return redirect()->back()->with('success', 'Share capital released: ₱'.number_format($amount, 2)." paid out to {$user->first_name} {$user->last_name}.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to release share capital: '.$e->getMessage());
        }
    }

    public function pendingRequests()
    {
        $requests = ResignationRequest_tbl::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function pendingReleases()
    {
        $releases = ResignationRequest_tbl::with('user.shareCapitalAccount')
            ->where('status', 'approved')
            ->where('withdraw_share_capital', true)
            ->where('is_released', false)
            ->orderBy('release_date', 'asc')
            ->get();

        return response()->json($releases);
    }
}
