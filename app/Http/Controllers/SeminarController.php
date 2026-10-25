<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SeminarAttendees_tbl;
use App\Models\SeminarCompletions_tbl;
use App\Models\SeminarPasscodes_tbl;
use App\Models\Seminars_tbl;
use App\Models\SeminarTypes_tbl;
use App\Models\Users_tbl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeminarController extends Controller
{
    public const CORE_TYPES = ['pmes', 'fundamentals', 'finance'];

    public function index(Request $request)
    {
        $upcomingSeminars = Seminars_tbl::with(['attendees.user'])
            ->where('schedule_datetime', '>=', now())
            ->orderBy('schedule_datetime', 'asc')
            ->get();

        $pastSeminars = Seminars_tbl::with(['attendees.user'])
            ->where('schedule_datetime', '<', now())
            ->orderBy('schedule_datetime', 'desc')
            ->get();

        $seminarTypes = SeminarTypes_tbl::orderBy('id')->get();
        $passcodes = SeminarPasscodes_tbl::all()->keyBy('seminar_type');

        $search = $request->input('search', '');
        $statusFilter = $request->input('status', 'all');

        $query = Users_tbl::leftJoin('seminar_completions_tbls', 'seminar_completions_tbls.user_id', '=', 'users_tbls.id')
            ->whereNotIn('users_tbls.role', ['admin', 'general-manager'])
            ->select(
                'users_tbls.*',
                'seminar_completions_tbls.pmes_completed',
                'seminar_completions_tbls.fundamentals_completed',
                'seminar_completions_tbls.finance_completed',
                'seminar_completions_tbls.completed_at as completion_completed_at'
            );

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users_tbls.first_name', 'like', "%{$search}%")
                    ->orWhere('users_tbls.last_name', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'completed') {
            $query->whereRaw('(COALESCE(seminar_completions_tbls.pmes_completed,0) + COALESCE(seminar_completions_tbls.fundamentals_completed,0) + COALESCE(seminar_completions_tbls.finance_completed,0)) = 3');
        } elseif ($statusFilter === 'incomplete') {
            $query->whereRaw('(COALESCE(seminar_completions_tbls.pmes_completed,0) + COALESCE(seminar_completions_tbls.fundamentals_completed,0) + COALESCE(seminar_completions_tbls.finance_completed,0)) < 3');
        }

        $query->orderByRaw('(COALESCE(seminar_completions_tbls.pmes_completed,0) + COALESCE(seminar_completions_tbls.fundamentals_completed,0) + COALESCE(seminar_completions_tbls.finance_completed,0)) asc')
            ->orderBy('users_tbls.first_name');

        $users = $query->paginate(15)->withQueryString();

        $userIds = $users->pluck('id');
        $attendedMap = DB::table('seminar_attendees_tbls')
            ->join('seminars_tbls', 'seminars_tbls.id', '=', 'seminar_attendees_tbls.seminar_id')
            ->whereIn('seminar_attendees_tbls.user_id', $userIds)
            ->where('seminar_attendees_tbls.status', 'attended')
            ->get(['seminar_attendees_tbls.user_id', 'seminars_tbls.seminar_type'])
            ->groupBy('user_id')
            ->map(fn($rows) => $rows->pluck('seminar_type')->unique()->values()->all());

        // Upcoming pending seminar each member is scheduled for.
        // Core types require a valid (unexpired) passcode; otherwise the schedule is treated as void.
        $coreTypes = ['pmes', 'fundamentals', 'finance'];
        $validPasscodeTypes = collect($passcodes)
            ->filter(fn($pc) => $pc && (is_null($pc->expires_at) || $pc->expires_at->gte(now())))
            ->keys()
            ->all();
        $typeLabels = SeminarTypes_tbl::pluck('label', 'slug')->toArray();

        // Upcoming pending seminar each member is scheduled for.
// Core types require a valid (unexpired) passcode; otherwise the schedule is treated as void.
        $coreTypes = ['pmes', 'fundamentals', 'finance'];
        $validPasscodeTypes = collect($passcodes)
            ->filter(fn($pc) => $pc && (is_null($pc->expires_at) || $pc->expires_at->gte(now())))
            ->keys()
            ->all();
        $typeLabels = SeminarTypes_tbl::pluck('label', 'slug')->toArray();

        $scheduledRowsByUser = DB::table('seminar_attendees_tbls')
            ->join('seminars_tbls', 'seminars_tbls.id', '=', 'seminar_attendees_tbls.seminar_id')
            ->where('seminar_attendees_tbls.status', 'pending')
            ->where('seminars_tbls.schedule_datetime', '>=', now())
            ->orderBy('seminars_tbls.schedule_datetime', 'asc')
            ->get(['seminar_attendees_tbls.user_id', 'seminars_tbls.seminar_type', 'seminars_tbls.schedule_datetime'])
            ->groupBy('user_id');

        $coreTypes = self::CORE_TYPES;
        $validPasscodeTypes = collect($passcodes)
            ->filter(fn($pc) => $pc && (is_null($pc->expires_at) || $pc->expires_at->gte(now())))
            ->keys()
            ->all();
        $typeLabels = SeminarTypes_tbl::pluck('label', 'slug')->toArray();

        $scheduledRowsByUser = DB::table('seminar_attendees_tbls')
            ->join('seminars_tbls', 'seminars_tbls.id', '=', 'seminar_attendees_tbls.seminar_id')
            ->where('seminar_attendees_tbls.status', 'pending')
            ->where('seminars_tbls.schedule_datetime', '>=', now())
            ->orderBy('seminars_tbls.schedule_datetime', 'asc')
            ->get(['seminar_attendees_tbls.user_id', 'seminars_tbls.seminar_type', 'seminars_tbls.schedule_datetime'])
            ->groupBy('user_id');

        $users->getCollection()->transform(function ($user) use ($attendedMap, $scheduledRowsByUser, $passcodes, $validPasscodeTypes, $typeLabels, $coreTypes) {
            $completion = (object) [
                'pmes_completed' => (bool) $user->pmes_completed,
                'fundamentals_completed' => (bool) $user->fundamentals_completed,
                'finance_completed' => (bool) $user->finance_completed,
                'completed_at' => $user->completion_completed_at,
            ];
            $user->completion = $completion;
            $user->attended_types = $attendedMap[$user->id] ?? [];

            $scheduled = [];
            $rows = $scheduledRowsByUser[$user->id] ?? collect();

            foreach ($rows as $row) {
                $type = $row->seminar_type;

                // Already scheduled this type (keep the earliest one)
                if (isset($scheduled[$type])) {
                    continue;
                }

                // Skip if the member already completed this core seminar
                if (in_array($type, $coreTypes) && ($completion->{$type . '_completed'} ?? false)) {
                    continue;
                }

                // Core types need an unexpired passcode, otherwise the schedule is void
                if (in_array($type, $coreTypes) && !in_array($type, $validPasscodeTypes)) {
                    continue;
                }

                $scheduled[$type] = [
                    'slug' => $type,
                    'label' => $typeLabels[$type] ?? ucwords(str_replace('_', ' ', $type)),
                    'passcode' => $passcodes[$type]->passcode ?? null,
                    'schedule_datetime' => $row->schedule_datetime,
                ];
            }

            // New: every scheduled seminar, each with its own passcode
            $user->scheduled_seminars = array_values($scheduled);

            // Kept for backward compatibility (first one only)
            $user->scheduled_seminar = $user->scheduled_seminars[0]['label'] ?? null;
            $user->scheduled_passcode = $user->scheduled_seminars[0]['passcode'] ?? null;

            return $user;
        });

        return view('admin_components.seminars', compact(
            'upcomingSeminars',
            'pastSeminars',
            'users',
            'seminarTypes',
            'passcodes',
            'validPasscodeTypes',
            'search',
            'statusFilter'
        ));
    }

    /**
     * For the given users and seminar types, returns [user_id => [type_slug => 'completed'|'scheduled']].
     * - completed: attended that seminar, or (core types) the completion flag is set
     * - scheduled: already has an upcoming pending session of that type
     */
    private function blockedTypeMap(array $userIds, array $types): array
    {
        $blocked = [];

        if (empty($userIds) || empty($types)) {
            return $blocked;
        }

        $rows = DB::table('seminar_attendees_tbls')
            ->join('seminars_tbls', 'seminars_tbls.id', '=', 'seminar_attendees_tbls.seminar_id')
            ->whereIn('seminar_attendees_tbls.user_id', $userIds)
            ->whereIn('seminars_tbls.seminar_type', $types)
            ->where(function ($q) {
                $q->where('seminar_attendees_tbls.status', 'attended')
                    ->orWhere(function ($q2) {
                        $q2->where('seminar_attendees_tbls.status', 'pending')
                            ->where('seminars_tbls.schedule_datetime', '>=', now());
                    });
            })
            ->get(['seminar_attendees_tbls.user_id', 'seminars_tbls.seminar_type', 'seminar_attendees_tbls.status']);

        foreach ($rows as $r) {
            $reason = $r->status === 'attended' ? 'completed' : 'scheduled';
            // "completed" wins over "scheduled"
            if (($blocked[$r->user_id][$r->seminar_type] ?? null) !== 'completed') {
                $blocked[$r->user_id][$r->seminar_type] = $reason;
            }
        }

        // Core seminars can also be completed through the member's passcode, with no attendee row
        $coreTicked = array_values(array_intersect($types, self::CORE_TYPES));
        if ($coreTicked) {
            foreach (SeminarCompletions_tbl::whereIn('user_id', $userIds)->get() as $c) {
                foreach ($coreTicked as $type) {
                    if ($c->{$type . '_completed'}) {
                        $blocked[$c->user_id][$type] = 'completed';
                    }
                }
            }
        }

        return $blocked;
    }

    public function memberSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $types = array_values(array_unique(array_filter((array) $request->input('types', []))));

        $query = Users_tbl::whereNotIn('role', ['admin', 'general-manager'])
            ->select('id', 'first_name', 'last_name', 'email', 'role');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$q}%"]);
            });
        }

        $members = $query->orderBy('first_name')->orderBy('last_name')->get();

        $allSlugs = SeminarTypes_tbl::pluck('slug')->all();
        $blockedMap = $this->blockedTypeMap($members->pluck('id')->all(), $allSlugs);

        // Optional filter: hide members blocked for ALL of the given types
        if (!empty($types)) {
            $members = $members->filter(function ($m) use ($blockedMap, $types) {
                $blockedAmongTicked = array_intersect(array_keys($blockedMap[$m->id] ?? []), $types);
                return count($blockedAmongTicked) < count($types);
            })->values();
        }

        if ($q !== '') {
            $members = $members->take(50)->values();
        }

        // Add the seminars each member still needs (not completed, not already scheduled)
        $result = $members->map(function ($m) use ($blockedMap, $allSlugs) {
            $row = $m->toArray();
            $row['needs'] = array_values(array_diff($allSlugs, array_keys($blockedMap[$m->id] ?? [])));
            return $row;
        });

        return response()->json($result);
    }

    public function storeSeminarType(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100|unique:seminar_types_tbls,label',
        ]);

        $slug = Str::slug($validated['label'], '_');

        if (SeminarTypes_tbl::where('slug', $slug)->exists()) {
            return redirect()->back()->with('error', 'A seminar type with a similar name already exists.')->withInput();
        }

        SeminarTypes_tbl::create([
            'slug' => $slug,
            'label' => $validated['label'],
        ]);

        AuditLog::log('Created Seminar Type', "Created seminar type '{$validated['label']}'", 'seminar_type', null);

        return redirect()->route('seminars.index')->with('success', "Seminar type '{$validated['label']}' created.");
    }

    public function scheduleSeminar(Request $request)
    {
        $validated = $request->validate([
            'seminar_types' => 'required|array|min:1',
            'seminar_types.*' => 'required|distinct|exists:seminar_types_tbls,slug',
            'schedule_datetime' => 'required|date|after:now',
            'delivery_type' => 'required|in:online,f2f',
            'online_link' => 'nullable|url|required_if:delivery_type,online',
            'meetup_place' => 'nullable|string|max:255|required_if:delivery_type,f2f',
            'exact_venue' => 'nullable|string|max:255|required_if:delivery_type,f2f',
            'attendees' => 'required|array|min:1',
            'attendees.*' => 'exists:users_tbls,id',
            'passcode' => 'required|string|max:64',
            'valid_days' => 'nullable|integer|min:1|max:365',
        ], [
            'seminar_types.required' => 'Please select at least one seminar type.',
            'seminar_types.min' => 'Please select at least one seminar type.',
        ]);

        $blockedMap = $this->blockedTypeMap($validated['attendees'], $validated['seminar_types']);

        DB::beginTransaction();
        try {
            $createdIds = [];
            $attendeeTotal = 0;
            $skipped = []; // [user_id => [labels]]
            $now = now();
            $typeLabels = SeminarTypes_tbl::pluck('label', 'slug')->toArray();

            foreach ($validated['seminar_types'] as $slug) {
                $eligible = [];
                foreach ($validated['attendees'] as $userId) {
                    $reason = $blockedMap[$userId][$slug] ?? null;
                    if ($reason) {
                        $skipped[$userId][] = ($typeLabels[$slug] ?? $slug) . " ({$reason})";
                    } else {
                        $eligible[] = $userId;
                    }
                }

                // Nobody left for this type, so don't create an empty session
                if (empty($eligible)) {
                    continue;
                }

                $seminar = Seminars_tbl::create([
                    'seminar_type' => $slug,
                    'schedule_datetime' => $validated['schedule_datetime'],
                    'delivery_type' => $validated['delivery_type'],
                    'online_link' => $validated['online_link'] ?? null,
                    'meetup_place' => $validated['meetup_place'] ?? null,
                    'exact_venue' => $validated['exact_venue'] ?? null,
                ]);
                $createdIds[] = $seminar->id;

                $rows = [];
                foreach ($eligible as $userId) {
                    $rows[] = [
                        'seminar_id' => $seminar->id,
                        'user_id' => $userId,
                        'status' => 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                SeminarAttendees_tbl::insert($rows);
                $attendeeTotal += count($rows);

                SeminarPasscodes_tbl::updateOrCreate(
                    ['seminar_type' => $slug],
                    [
                        'passcode' => $validated['passcode'],
                        'expires_at' => now()->addDays((int) ($validated['valid_days'] ?? 1)),
                    ]
                );
            }

            // Build a readable "skipped" note
            $skipNote = '';
            if ($skipped) {
                $names = Users_tbl::whereIn('id', array_keys($skipped))
                    ->get(['id', 'first_name', 'last_name'])
                    ->keyBy('id');
                $parts = [];
                foreach ($skipped as $uid => $labels) {
                    $n = $names[$uid] ?? null;
                    $parts[] = trim(($n->first_name ?? 'Member') . ' ' . ($n->last_name ?? '')) . ': ' . implode(', ', $labels);
                }
                $skipNote = ' Skipped: ' . implode('; ', $parts) . '.';
            }

            if (empty($createdIds)) {
                DB::rollBack();

                return redirect()->back()
                    ->with('error', 'Nothing was scheduled. Every selected member has already completed or is already scheduled for the chosen seminar(s).' . $skipNote)
                    ->withInput();
            }

            DB::commit();

            AuditLog::log(
                'Scheduled Seminar',
                count($createdIds) . ' session(s) [' . implode(', ', $validated['seminar_types']) . "] on {$validated['schedule_datetime']} " .
                "with {$attendeeTotal} attendee slot(s) and a passcode." . $skipNote,
                'seminar',
                $createdIds[0]
            );

            return redirect()->route('seminars.index')->with(
                'success',
                count($createdIds) . " seminar session(s) scheduled ({$attendeeTotal} attendee slot(s))." . $skipNote
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to schedule seminar: ' . $e->getMessage())->withInput();
        }
    }

    public function updateAttendanceAndCompletion(Request $request)
    {
        $validated = $request->validate([
            'seminar_id' => 'required|exists:seminars_tbls,id',
            'user_id' => 'required|exists:users_tbls,id',
            'status' => 'required|in:attended,absent',
        ]);

        $seminar = Seminars_tbl::findOrFail($validated['seminar_id']);
        $attendee = SeminarAttendees_tbl::where('seminar_id', $validated['seminar_id'])
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        $attendee->status = $validated['status'];
        $attendee->save();

        AuditLog::log(
            'Updated Seminar Attendance',
            "Marked user #{$validated['user_id']} as {$validated['status']} for seminar #{$validated['seminar_id']}",
            'seminar_attendance',
            $validated['seminar_id']
        );

        if ($validated['status'] === 'attended' && in_array($seminar->seminar_type, self::CORE_TYPES)) {
            $completion = SeminarCompletions_tbl::firstOrCreate(
                ['user_id' => $validated['user_id']],
                [
                    'pmes_completed' => false,
                    'fundamentals_completed' => false,
                    'finance_completed' => false,
                ]
            );

            $completion->{$seminar->seminar_type . '_completed'} = true;
            $completion->save();

            self::autoUpgradeIfComplete($validated['user_id'], $completion);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked as ' . $validated['status'] . '.',
        ]);
    }

    public static function autoUpgradeIfComplete($userId, $completion)
    {
        if ($completion->pmes_completed && $completion->fundamentals_completed && $completion->finance_completed) {
            $user = Users_tbl::find($userId);

            if (!$completion->completed_at) {
                $completion->completed_at = now();
                $completion->save();
            }

            if ($user && $user->role === 'pending') {
                $user->role = 'member';
                $user->save();

                $otherInfo = DB::table('otherinfo_tbls')->where('user_id', $userId)->first();
                if ($otherInfo) {
                    DB::table('otherinfo_tbls')
                        ->where('user_id', $userId)
                        ->update([
                            'membership_status' => 'Active',
                            'approval_status' => 'Approved',
                        ]);
                }
            }
        }
    }
}
