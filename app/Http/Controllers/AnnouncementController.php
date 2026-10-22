<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementComments_tbl;
use App\Models\AnnouncementLikes_tbl;
use App\Models\AnnouncementPoll_tbl;
use App\Models\AnnouncementPollVote_tbl;
use App\Models\Announcements_tbl;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement = Announcements_tbl::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        AuditLog::log(
            'Created Announcement',
            "Created announcement: {$request->title}",
            'announcement',
            $announcement->id
        );

        return redirect()->route('dashboard')->with('success', 'Announcement published successfully.');
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = AnnouncementComments_tbl::create([
            'announcement_id' => $id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        AuditLog::log(
            'Posted Comment',
            "Posted comment on announcement #{$id}",
            'announcement_comment',
            $comment->id
        );

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'first_name' => $comment->user->first_name,
                    'last_name' => $comment->user->last_name,
                    'role' => $comment->user->role,
                ],
            ],
        ]);
    }

    public function deleteComment(Request $request, $id, $commentId)
    {
        $comment = AnnouncementComments_tbl::where('announcement_id', $id)
            ->where('id', $commentId)
            ->firstOrFail();

        $comment->delete();

        AuditLog::log(
            'Deleted Comment',
            "Deleted comment #{$commentId} from announcement #{$id}",
            'announcement_comment',
            $commentId
        );

        $count = AnnouncementComments_tbl::where('announcement_id', $id)->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function deleteAnnouncement(Request $request, $id)
    {
        $announcement = Announcements_tbl::findOrFail($id);
        $announcement->comments()->delete();
        $announcement->likes()->delete();
        $announcement->delete();

        AuditLog::log(
            'Deleted Announcement',
            "Deleted announcement #{$id}: {$announcement->title}",
            'announcement',
            $id
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function toggleLike(Request $request, $id)
    {
        $userId = Auth::id();
        $existing = AnnouncementLikes_tbl::where('announcement_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
            AuditLog::log(
                'Unliked Announcement',
                "Unliked announcement #{$id}",
                'announcement_like',
                $id
            );
        } else {
            AnnouncementLikes_tbl::create([
                'announcement_id' => $id,
                'user_id' => $userId,
            ]);
            $liked = true;
            AuditLog::log(
                'Liked Announcement',
                "Liked announcement #{$id}",
                'announcement_like',
                $id
            );
        }

        $count = AnnouncementLikes_tbl::where('announcement_id', $id)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'count' => $count,
        ]);
    }

    public function storePoll(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $poll = AnnouncementPoll_tbl::create([
            'user_id' => Auth::id(),
            'question' => $request->question,
            'options' => $request->options,
            'expires_at' => $request->expires_at,
        ]);

        AuditLog::log(
            'Created Poll',
            "Created poll: {$request->question}",
            'announcement_poll',
            $poll->id
        );

        return response()->json([
            'success' => true,
            'poll' => [
                'id' => $poll->id,
                'question' => $poll->question,
                'options' => $poll->options,
                'expires_at' => $poll->expires_at?->format('M d, Y h:i A'),
                'is_expired' => $poll->isExpired(),
                'results' => $poll->results,
                'total_votes' => $poll->votes()->count(),
                'user_id' => $poll->user_id,
            ],
        ]);
    }

    public function votePoll(Request $request, $pollId)
    {
        $poll = AnnouncementPoll_tbl::findOrFail($pollId);

        if ($poll->isExpired()) {
            return response()->json(['success' => false, 'message' => 'This poll has expired.'], 422);
        }

        $request->validate([
            'option_index' => 'required|integer|min:0|max:'.(count($poll->options) - 1),
        ]);

        $userId = Auth::id();

        AnnouncementPollVote_tbl::updateOrCreate(
            ['poll_id' => $pollId, 'user_id' => $userId],
            ['option_index' => $request->option_index]
        );

        $poll->load('votes');
        $results = $poll->results;

        return response()->json([
            'success' => true,
            'results' => $results,
            'total_votes' => count($poll->votes),
            'voted_index' => $request->option_index,
        ]);
    }

    public function deletePoll(Request $request, $pollId)
    {
        $poll = AnnouncementPoll_tbl::findOrFail($pollId);
        $poll->votes()->delete();
        $poll->delete();

        AuditLog::log(
            'Deleted Poll',
            "Deleted poll #{$pollId}: {$poll->question}",
            'announcement_poll',
            $pollId
        );

        return response()->json([
            'success' => true,
        ]);
    }
}
