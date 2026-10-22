<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
    <link rel="stylesheet" href="css_folder/faqs.css?=1">
    <link rel="stylesheet" href="css_folder/loading.css?=12">

    {{-- bootstrap and tailwind link --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- font awesome cdn link --}}
    <link rel="stylesheet" href="font-awesome-icon/css/all.min.css">

    <style nonce="{{ csp_nonce() }}">
        .help-wrap { max-width: 860px; margin-top: 1rem; }
        .help-sub { color: #6b7280; font-size: 13.5px; margin: 4px 0 0; }

        /* Tabs */
        .help-tabs { display: flex; gap: 6px; margin: 1.2rem 0 1.4rem; border-bottom: 1px solid #e5e7eb; }
        .help-tab {
            background: none; border: none; padding: 10px 16px; font-size: 14px; font-weight: 600;
            color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .help-tab:hover { color: #1E2A4A; }
        .help-tab.active { color: #1E2A4A; border-bottom-color: #1E2A4A; }
        .help-count {
            background: #eef0f6; color: #1E2A4A; font-size: 11.5px; font-weight: 700;
            padding: 2px 8px; border-radius: 999px;
        }
        .help-pane { display: none; }
        .help-pane.active { display: block; }

        /* FAQ accordion */
        .faq-cat { margin: 1.6rem 0 .7rem; font-size: 12px; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: #6b7280; }
        .faq-cat:first-child { margin-top: 0; }
        .faq-box { border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; background: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04); }
        .faq-item { border-bottom: 1px solid #f0f1f4; }
        .faq-item:last-child { border-bottom: none; }
        .faq-item summary, .rp-card summary { list-style: none; cursor: pointer; }
        .faq-item summary::-webkit-details-marker, .rp-card summary::-webkit-details-marker { display: none; }
        .faq-item summary {
            padding: 15px 18px; font-weight: 600; font-size: 14px; color: #1a1a1a;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .faq-item summary:hover { background: #fafbfc; }
        .faq-item summary::after, .rp-card summary .rp-chev::after {
            content: ''; width: 8px; height: 8px; flex-shrink: 0;
            border-right: 2px solid #9ca3af; border-bottom: 2px solid #9ca3af;
            transform: rotate(45deg); transition: transform .2s;
        }
        .faq-item[open] summary::after, .rp-card[open] summary .rp-chev::after { transform: rotate(-135deg); }
        .faq-item[open] summary { color: #1E2A4A; background: #f7f8fc; }
        .faq-item p { padding: 4px 18px 18px; margin: 0; font-size: 13.5px; color: #555; line-height: 1.7; background: #f7f8fc; }

        /* Help banner */
        .help-banner {
            margin-top: 1.8rem; padding: 18px 20px; background: linear-gradient(135deg, #f4f6fb, #eef1f9);
            border: 1px solid #e3e7f2; border-radius: 14px; font-size: 13.5px; color: #4b5563;
            display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;
        }
        .btn-report-problem {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #1E2A4A;
            color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer;
            transition: transform .15s, box-shadow .15s;
        }
        .btn-report-problem:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(30, 42, 74, .25); }

        /* My Reports */
        .rp-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
        .rp-filters { display: flex; gap: 6px; flex-wrap: wrap; }
        .rp-filter {
            border: 1px solid #e5e7eb; background: #fff; color: #4b5563; font-size: 12.5px; font-weight: 600;
            padding: 6px 14px; border-radius: 999px; cursor: pointer;
        }
        .rp-filter.active { background: #1E2A4A; border-color: #1E2A4A; color: #fff; }
        .rp-card {
            border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; margin-bottom: 12px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04); overflow: hidden; border-left: 4px solid #d1d5db;
        }
        .rp-card.st-open { border-left-color: #f59e0b; }
        .rp-card.st-in_progress { border-left-color: #3b82f6; }
        .rp-card.st-resolved { border-left-color: #16a34a; }
        .rp-card summary { padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
        .rp-card summary:hover { background: #fafbfc; }
        .rp-main { flex: 1; min-width: 0; }
        .rp-subject { font-weight: 700; font-size: 14.5px; color: #111827; margin: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rp-meta { font-size: 12px; color: #6b7280; margin-top: 3px; }
        .rp-badge { font-size: 11.5px; font-weight: 700; padding: 4px 11px; border-radius: 999px; white-space: nowrap; }
        .rp-badge.open { background: #fef3c7; color: #b45309; }
        .rp-badge.in_progress { background: #dbeafe; color: #1d4ed8; }
        .rp-badge.resolved { background: #dcfce7; color: #15803d; }
        .rp-reply-dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; flex-shrink: 0; }
        .rp-body { padding: 4px 18px 18px; border-top: 1px solid #f0f1f4; }
        .rp-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            color: #9ca3af; margin: 14px 0 6px; }
        .rp-msg { font-size: 13.5px; color: #374151; line-height: 1.65; white-space: pre-line;
            background: #f9fafb; border-radius: 10px; padding: 12px 14px; margin: 0; }
        .rp-reply { border-radius: 10px; padding: 12px 14px; font-size: 13.5px; line-height: 1.65; white-space: pre-line; margin: 0; }
        .rp-reply.has { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; }
        .rp-reply.none { background: #fffbeb; border: 1px dashed #fcd34d; color: #92400e; }
        .rp-proof img { max-height: 160px; border-radius: 10px; border: 1px solid #e5e7eb; }
        .rp-empty { text-align: center; padding: 3rem 1rem; color: #6b7280; background: #fff;
            border: 1px dashed #d1d5db; border-radius: 14px; }
        .rp-empty i { font-size: 30px; color: #cbd5e1; margin-bottom: 10px; display: block; }
    </style>
</head>

<body>
    <div class="container-fluid p-0 m-0">
        @include("components.offcanvas")
        @include("components.sidebar")
        <div class="rightbar">
            @include("components.navbar2")
            @include("components.footer")

            @php
                $openReports = $myReports->where('status', '!=', 'resolved')->count();
                $startOnReports = session('report_success') || request('tab') === 'reports';
                $statusLabels = ['open' => 'New', 'in_progress' => 'Ongoing', 'resolved' => 'Closed'];
            @endphp

            <div class="main-parent">
                <main>
                    <h2>Help Center</h2>
                    <p class="help-sub">Find quick answers, or send our team a message and track its reply here.</p>

                    <div class="help-wrap">
                        {{-- Tabs --}}
                        <div class="help-tabs" role="tablist">
                            <button type="button" class="help-tab {{ $startOnReports ? '' : 'active' }}"
                                data-tab="faqs">
                                <i class="fa-solid fa-circle-question"></i> FAQs
                            </button>
                            <button type="button" class="help-tab {{ $startOnReports ? 'active' : '' }}"
                                data-tab="reports">
                                <i class="fa-solid fa-inbox"></i> My Reports
                                @if ($myReports->count())
                                    <span class="help-count">{{ $myReports->count() }}</span>
                                @endif
                            </button>
                        </div>

                        {{-- ═════════ FAQs TAB ═════════ --}}
                        <div class="help-pane {{ $startOnReports ? '' : 'active' }}" id="pane-faqs">
                            @foreach ($faqs as $section)
                                <h4 class="faq-cat">{{ $section['category'] }}</h4>
                                <div class="faq-box">
                                    @foreach ($section['items'] as $item)
                                        <details class="faq-item">
                                            <summary>{{ $item['q'] }}</summary>
                                            <p>{{ $item['a'] }}</p>
                                        </details>
                                    @endforeach
                                </div>
                            @endforeach

                            <div class="help-banner">
                                <span>Didn't find what you're looking for? Send our team a message directly.</span>
                                <button type="button" class="btn-report-problem" data-bs-toggle="modal"
                                    data-bs-target="#reportProblemModal">
                                    <i class="fa-solid fa-flag"></i>
                                    <span>Report a Problem</span>
                                </button>
                            </div>
                        </div>

                        {{-- ═════════ MY REPORTS TAB ═════════ --}}
                        <div class="help-pane {{ $startOnReports ? 'active' : '' }}" id="pane-reports">
                            <div class="rp-head">
                                <div class="rp-filters">
                                    <button type="button" class="rp-filter active" data-filter="all">All
                                        ({{ $myReports->count() }})</button>
                                    <button type="button" class="rp-filter" data-filter="open">New
                                        ({{ $myReports->where('status', 'open')->count() }})</button>
                                    <button type="button" class="rp-filter" data-filter="in_progress">Ongoing
                                        ({{ $myReports->where('status', 'in_progress')->count() }})</button>
                                    <button type="button" class="rp-filter" data-filter="resolved">Closed
                                        ({{ $myReports->where('status', 'resolved')->count() }})</button>
                                </div>
                                <button type="button" class="btn-report-problem" data-bs-toggle="modal"
                                    data-bs-target="#reportProblemModal">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>New Report</span>
                                </button>
                            </div>

                            @forelse ($myReports as $r)
                                @php $st = $r->status ?: 'open'; @endphp
                                <details class="rp-card st-{{ $st }}" data-status="{{ $st }}"
                                    {{ $r->admin_reply ? 'open' : '' }}>
                                    <summary>
                                        <div class="rp-main">
                                            <p class="rp-subject">{{ $r->subject }}</p>
                                            <div class="rp-meta">
                                                {{ $r->category }} · Submitted
                                                {{ $r->created_at ? $r->created_at->format('M d, Y g:i A') : '' }}
                                            </div>
                                        </div>
                                        @if ($r->admin_reply)
                                            <span class="rp-reply-dot" title="Admin replied"></span>
                                        @endif
                                        <span class="rp-badge {{ $st }}">{{ $statusLabels[$st] ?? 'New' }}</span>
                                        <span class="rp-chev"></span>
                                    </summary>

                                    <div class="rp-body">
                                        <div class="rp-label">Your report</div>
                                        <p class="rp-msg">{{ $r->message }}</p>

                                        @if ($r->proof_path)
                                            <div class="rp-label">Your attachment</div>
                                            <a class="rp-proof" href="{{ asset('storage/' . $r->proof_path) }}"
                                                target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/' . $r->proof_path) }}" alt="Attachment">
                                            </a>
                                        @endif

                                        <div class="rp-label">
                                            Admin reply
                                            @if ($r->admin_reply && $r->updated_at)
                                                · {{ $r->updated_at->diffForHumans() }}
                                            @endif
                                        </div>
                                        @if ($r->admin_reply)
                                            <p class="rp-reply has">{{ $r->admin_reply }}</p>
                                        @elseif ($st === 'resolved')
                                            <p class="rp-reply has">This report has been closed by our team.</p>
                                        @else
                                            <p class="rp-reply none">
                                                {{ $st === 'in_progress'
                                                    ? 'Our team is working on your report. A reply will appear here.'
                                                    : 'Your report was received. Our team will reply here shortly.' }}
                                            </p>
                                        @endif
                                    </div>
                                </details>
                            @empty
                                <div class="rp-empty">
                                    <i class="fa-regular fa-folder-open"></i>
                                    You haven't submitted any reports yet.
                                </div>
                            @endforelse

                            @if ($myReports->count())
                                <div class="rp-empty" id="rpFilterEmpty" style="display:none;">
                                    No reports in this status.
                                </div>
                            @endif
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Report a Problem Modal -->
    <div id="reportProblemModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
                <div class="modal-header" style="border-bottom:1px solid #e5e7eb; padding:1.1rem 1.4rem;">
                    <h5 class="modal-title" style="font-size:15px; font-weight:700; color:#111827;">
                        Report a Problem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('support.report.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body"
                        style="padding:1.2rem 1.4rem; display:flex; flex-direction:column; gap:12px;">

                        @if (session('report_success'))
                            <div
                                style="background:#e7f6ec; border:1px solid #b7e4c3; color:#1a7f37; border-radius:8px; padding:10px 12px; font-size:12.5px;">
                                {{ session('report_success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; border-radius:8px; padding:10px 12px; font-size:12.5px;">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div>
                            <label
                                style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Category</label>
                            <select name="category" id="reportCategorySelect" required class="form-select"
                                style="border-radius:10px; border:1.5px solid #e0e0e0; height:44px; font-size:14px;">
                                <option value="" disabled selected>Select a category</option>
                                <option value="Balance Discrepancy">Balance Discrepancy</option>
                                <option value="Loan Requirement">Loan Requirement</option>
                                <option value="Payment Reflection">Payment Reflection</option>
                                <option value="Rejected / Invalid Deposit">Rejected / Invalid Deposit</option>
                                <option value="Other">Other</option>
                            </select>

                            {{-- Shown only when "Other" is selected --}}
                            <input type="text" name="category_other" id="reportCategoryOther" maxlength="80"
                                placeholder="Please specify your category"
                                style="display:none; width:100%; margin-top:8px; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box;">
                        </div>

                        <div>
                            <label
                                style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Subject</label>
                            <input type="text" name="subject" required maxlength="150"
                                placeholder="Short summary of the issue"
                                style="width:100%; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box;">
                        </div>

                        <div>
                            <label
                                style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Details</label>
                            <textarea name="message" required maxlength="2000" rows="4"
                                placeholder="Describe what happened, including the reference number if it's about a transaction"
                                style="width:100%; padding:10px 12px; border-radius:10px; border:1.5px solid #e0e0e0; font-size:14px; box-sizing:border-box; resize:vertical;"></textarea>
                        </div>

                        <div>
                            <label
                                style="font-size:12px; text-transform:uppercase; font-weight:600; color:#888; display:block; margin-bottom:6px;">Attach
                                Screenshot <span style="color:#bbb; text-transform:none;">(optional)</span></label>
                            <input type="file" name="proof" accept="image/png,image/jpeg,image/jpg" class="form-control"
                                style="border-radius:10px; font-size: 14px;">
                        </div>

                    </div>
                    <div class="modal-footer"
                        style="background:#f8f9fa; border-top:1px solid rgba(0,0,0,0.1); padding:1rem 1.4rem;">
                        <button type="submit"
                            style="width:100%; padding:0.75rem; background:var(--teal, #1E2A4A); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                            <i class="fa fa-paper-plane" style="margin-right:6px;"></i> Submit Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Re-open the modal if the submit failed validation, so the error is visible --}}
    @if ($errors->any())
        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('reportProblemModal');
                if (el && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        </script>
    @endif

    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            // ── "Other" category toggle ──
            var select = document.getElementById('reportCategorySelect');
            var other = document.getElementById('reportCategoryOther');
            if (select && other) {
                select.addEventListener('change', function () {
                    var isOther = select.value === 'Other';
                    other.style.display = isOther ? 'block' : 'none';
                    other.required = isOther;
                    if (isOther) { other.focus(); } else { other.value = ''; }
                });
            }

            // ── Tabs: FAQs / My Reports ──
            var tabs = document.querySelectorAll('.help-tab');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    document.querySelectorAll('.help-pane').forEach(function (p) { p.classList.remove('active'); });
                    tab.classList.add('active');
                    document.getElementById('pane-' + tab.dataset.tab).classList.add('active');
                });
            });

            // ── My Reports status filter ──
            var filters = document.querySelectorAll('.rp-filter');
            var emptyMsg = document.getElementById('rpFilterEmpty');
            filters.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    filters.forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');

                    var shown = 0;
                    document.querySelectorAll('.rp-card').forEach(function (card) {
                        var match = btn.dataset.filter === 'all' || card.dataset.status === btn.dataset.filter;
                        card.style.display = match ? '' : 'none';
                        if (match) shown++;
                    });
                    if (emptyMsg) emptyMsg.style.display = shown === 0 ? 'block' : 'none';
                });
            });
        });
    </script>

    {{-- Toast after submitting a report --}}
    @if (session('report_success'))
        <div class="toast-message" id="reportToast" role="status" style="width:320px;">
            <i class="fa-solid fa-circle-check"></i>
            <p>{{ session('report_success') }}</p>
        </div>

        <script nonce="{{ csp_nonce() }}">
            document.addEventListener('DOMContentLoaded', function () {
                var toast = document.getElementById('reportToast');
                if (!toast) return;
                setTimeout(function () {
                    toast.classList.add('hide');
                    setTimeout(function () { toast.remove(); }, 400);
                }, 4000);
            });
        </script>
    @endif
</body>

</html>