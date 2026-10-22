<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Faq_tbl;
use App\Models\Notifications_tbl;
use App\Models\SupportTicket_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSupportController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // MEMBER REPORTS (support tickets)
    // ─────────────────────────────────────────────────────────────

    public function reportsIndex(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = trim((string) $request->get('search', ''));

        $query = SupportTicket_tbl::with('user');

        if (in_array($status, ['open', 'in_progress', 'resolved'], true)) {
            $query->where('status', $status);
        } else {
            $status = 'all';
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
                    });
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $counts = [
            'all' => SupportTicket_tbl::count(),
            'open' => SupportTicket_tbl::where('status', 'open')->count(),
            'in_progress' => SupportTicket_tbl::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket_tbl::where('status', 'resolved')->count(),
        ];

        return view('admin_components.support_reports', compact('reports', 'counts', 'status', 'search'));
    }

    public function reportsUpdate(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
            'admin_reply' => 'nullable|string|max:2000',
        ]);

        $ticket = SupportTicket_tbl::with('user')->findOrFail($id);

        $oldStatus = $ticket->status;
        $newReply = trim((string) ($data['admin_reply'] ?? ''));
        $replyChanged = $newReply !== '' && $newReply !== trim((string) $ticket->admin_reply);

        $ticket->status = $data['status'];

        if ($replyChanged) {
            $ticket->admin_reply = $newReply;
            $ticket->replied_by = Auth::id();
            $ticket->replied_at = now();
        }

        $ticket->save();

        // Let the member know something happened with their report.
        if ($replyChanged || ($oldStatus !== $ticket->status && $ticket->status === 'resolved')) {
            $message = $replyChanged
                ? "Our team replied to your report \"{$ticket->subject}\": {$newReply}"
                : "Your report \"{$ticket->subject}\" has been marked as resolved.";

            Notifications_tbl::create([
                'user_id' => $ticket->user_id,
                'title' => $replyChanged ? 'Reply to Your Report' : 'Report Resolved',
                'message' => $message,
                'category' => 'inbox',
                'is_important' => true,
            ]);
        }

        AuditLog::log(
            'Updated Support Report',
            "Report #{$ticket->id} ({$ticket->subject}) status: {$oldStatus} → {$ticket->status}" . ($replyChanged ? ' with a reply' : ''),
            'support_ticket',
            $ticket->id
        );

        return response()->json(['success' => true, 'message' => 'Report updated.']);
    }

    // ─────────────────────────────────────────────────────────────
    // FAQ MANAGEMENT
    // ─────────────────────────────────────────────────────────────

    public function faqsIndex()
    {
        $faqs = Faq_tbl::orderBy('sort_order')->orderBy('id')->get();
        $grouped = $faqs->groupBy('category');
        $categories = $faqs->pluck('category')->unique()->values();

        return view('admin_components.faqs_manage', compact('faqs', 'grouped', 'categories'));
    }

    public function faqsStore(Request $request)
    {
        $data = $this->validateFaq($request);

        if (!isset($data['sort_order'])) {
            $data['sort_order'] = ((int) Faq_tbl::max('sort_order')) + 1;
        }

        $faq = Faq_tbl::create($data);

        AuditLog::log('Created FAQ', "Added FAQ \"{$faq->question}\" under {$faq->category}", 'faq', $faq->id);

        return response()->json(['success' => true, 'message' => 'FAQ added.']);
    }

    public function faqsUpdate(Request $request, $id)
    {
        $faq = Faq_tbl::findOrFail($id);
        $data = $this->validateFaq($request);

        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $faq->sort_order;
        }

        $faq->update($data);

        AuditLog::log('Updated FAQ', "Updated FAQ \"{$faq->question}\" (ID: {$faq->id})", 'faq', $faq->id);

        return response()->json(['success' => true, 'message' => 'FAQ updated.']);
    }

    public function faqsToggle($id)
    {
        $faq = Faq_tbl::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        AuditLog::log(
            $faq->is_active ? 'Published FAQ' : 'Hid FAQ',
            "FAQ \"{$faq->question}\" is now " . ($faq->is_active ? 'visible' : 'hidden') . ' to members',
            'faq',
            $faq->id
        );

        return response()->json([
            'success' => true,
            'message' => $faq->is_active ? 'FAQ is now visible to members.' : 'FAQ is now hidden from members.',
        ]);
    }

    public function faqsDestroy($id)
    {
        $faq = Faq_tbl::findOrFail($id);
        $question = $faq->question;
        $faq->delete();

        AuditLog::log('Deleted FAQ', "Deleted FAQ \"{$question}\"", 'faq', $id);

        return response()->json(['success' => true, 'message' => 'FAQ deleted.']);
    }

    private function validateFaq(Request $request): array
    {
        $data = $request->validate([
            'category' => 'required|string|max:100',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['category'] = trim($data['category']);
        $data['is_active'] = $request->boolean('is_active', true);

        if (($data['sort_order'] ?? null) === null) {
            unset($data['sort_order']);
        }

        return $data;
    }
}