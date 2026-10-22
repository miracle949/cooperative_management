<?php

use App\Http\Controllers\lendingController;
use App\Http\Controllers\ShareCapital;
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsersHandle;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlliedWorkerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

Route::get("/", [UserController::class, "UserDirection"]);

Route::get("/index", [UserController::class, "index"])->name("index");

Route::get('/proof/{path}', function ($path) {
    $path = ltrim($path, '/');
    abort_if(str_contains($path, '..'), 404);

    foreach (['public', 'local'] as $diskName) {
        $disk = Storage::disk($diskName);
        if ($disk->exists($path)) {
            return $disk->response($path);
        }
    }

    foreach ([
        public_path($path),
        public_path('storage/' . $path),
        storage_path('app/' . $path),
        storage_path('app/private/' . $path),
        storage_path('app/public/' . $path),
    ] as $candidate) {
        if (is_file($candidate)) {
            return response()->file($candidate);
        }
    }

    abort(404);
})->where('path', '.*')->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::post('/forgot-password', [UserController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', fn($token) => view('auth.reset-password', [
        'token' => $token,
        'email' => request('email'),
    ]))->name('password.reset');

    Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('password.update');
});

// ✅ THIS IS THE KEY FIX - named 'login' must point to the login PAGE view
Route::get("/login", [UserController::class, "LoginPage"])->name("login");

// Login page also accessible via /login-page
Route::get("/login-page", [UserController::class, "LoginPage"])->name("LoginPage");

// Login Handle page POST only
Route::post("/login-handle", [UsersHandle::class, "login"])->name("UserLogin")->middleware("throttle:login");

// Forgot / reset password (Laravel password broker)
Route::get("/forgot-password", [PasswordResetController::class, "showLinkRequestForm"])->name("password.request");
Route::post("/forgot-password", [PasswordResetController::class, "sendResetLinkEmail"])->name("password.email")->middleware("throttle:password-reset");
Route::get("/reset-password/{token}", [PasswordResetController::class, "showResetForm"])->name("password.reset");
Route::post("/reset-password", [PasswordResetController::class, "reset"])->name("password.update")->middleware("throttle:password-reset");

Route::get('/mail-debug', function () {
    abort_unless(request('key') === 'kp-debug-7391', 404);
    set_time_limit(120);
    $out = [];

    // 1. Can the server reach Gmail at all?
    foreach ([587 => 'tcp://', 465 => 'ssl://'] as $port => $scheme) {
        $fp = @fsockopen($scheme . 'smtp.gmail.com', $port, $errno, $errstr, 10);
        $out["port_$port"] = $fp ? 'reachable' : "BLOCKED ($errstr)";
        if ($fp)
            fclose($fp);
    }

    // 2. What config is Laravel actually using?
    $out['config'] = [
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'queue' => config('queue.default'),
    ];

    // 3. Send a plain test email
    try {
        \Illuminate\Support\Facades\Mail::raw('SMTP test from cooperative site', function ($m) {
            $m->to('rogelioamoyan768@gmail.com')->subject('SMTP test');
        });
        $out['smtp'] = 'sent OK';
    } catch (\Throwable $e) {
        $out['smtp'] = 'FAILED: ' . $e->getMessage();
    }

    // 4. Stuck / failed queue jobs
    try {
        $out['jobs_waiting'] = \DB::table('jobs')->count();
    } catch (\Throwable $e) {
        $out['jobs_waiting'] = 'n/a: ' . $e->getMessage();
    }
    try {
        $f = \DB::table('failed_jobs')->latest('id')->first();
        $out['last_failed_job'] = $f ? substr($f->exception, 0, 400) : 'none';
    } catch (\Throwable $e) {
        $out['last_failed_job'] = 'n/a: ' . $e->getMessage();
    }

    return response()->json($out);
})->middleware('throttle:5,1');

// Two-factor authentication (TOTP) — General Manager / Main Admin only.
// Enrollment, confirmation, regeneration and disable require an authenticated
// session; eligibility is enforced inside TwoFactorController. The challenge
// endpoint is pre-verification and throttled by a dedicated limiter.
Route::get("/2fa/challenge", [TwoFactorController::class, "challenge"])->name("2fa.challenge");
Route::post("/2fa/challenge", [TwoFactorController::class, "verifyChallenge"])->name("2fa.challenge.attempt")->middleware("throttle:2fa-challenge");

Route::middleware(['auth'])->group(function () {
    Route::get("/2fa/manage", [TwoFactorController::class, "manage"])->name("2fa.manage");
    Route::post("/2fa/enroll", [TwoFactorController::class, "enroll"])->name("2fa.enroll");
    Route::post("/2fa/confirm", [TwoFactorController::class, "confirm"])->name("2fa.confirm");
    Route::post("/2fa/disable", [TwoFactorController::class, "disable"])->name("2fa.disable");
    Route::post("/2fa/recovery-regenerate", [TwoFactorController::class, "regenerateRecoveryCodes"])->name("2fa.recovery.regenerate");
});

// Register page GET
Route::get("/register-page", [UserController::class, "RegisterPage"])->name("RegisterPage");

// About us page GET
Route::get("/about-us", [UserController::class, "AboutUs"])->name("AboutUs");

// Services page GET
Route::get("/services", [UserController::class, "ServicesPage"])->name("ServicesPage");

// Blogs page GET
Route::get("/blogs", [UserController::class, "BlogsPage"])->name("BlogsPage");

// Contact page GET
Route::get("/contact", [UserController::class, "ContactPage"])->name("ContactPage");

Route::get("/navbar", [UserController::class, "Navbar"]);

// Static page GET (internal admin preview — contains member records + action links)
Route::get("/static-page", [UserController::class, "StaticPage"])->name("StaticPage")->middleware("admin");

Route::post('/check-email', [UsersHandle::class, 'checkEmail'])->name('check.email')->middleware('throttle:30,1');

// Member Portal page GET
Route::get("/member-portal", [UsersHandle::class, "MemberPortal"])->name("MemberPortal")->middleware("auth", "member.active");

// Lending Program page GET
Route::get("/loan_application", [lendingController::class, "index"])->name("LoanApplication")->middleware("auth", "member.active");

Route::post('/savings/{id}/update-status', [UserController::class, 'updateSavingsStatus'])->name('savings.updateStatus')->middleware('admin');
Route::post('/savings/{id}/disburse', [UserController::class, 'disburseSavingsWithdrawal'])->name('savings.disburse')->middleware('admin');
Route::post('/savings/{id}/complete-deposit', [UserController::class, 'completeSavingsDeposit'])->name('savings.complete-deposit')->middleware('admin');
Route::post('/savings/{id}/void-deposit', [UserController::class, 'voidSavingsDeposit'])->name('savings.void-deposit')->middleware('admin');

// Route::get("/savings", [UsersHandle::class, "Savings"])->name("savings");

// Savings routes
Route::get("/savings-page", [SavingsController::class, "index"])->name("savings.index")->middleware("auth", "member.active");

Route::post("/savings/deposit", [SavingsController::class, "deposit"])->name("savings.deposit")->middleware("auth", "member.active");

Route::post("/savings/withdraw", [SavingsController::class, "withdraw"])->name("savings.withdraw")->middleware("auth", "member.active");


// Share Capital page GET
Route::get('/share-capital', [ShareCapital::class, 'memberIndex'])
    ->name("ShareCapitalMember")
    ->middleware('auth', 'member.active');

// Profile Member page GET
Route::get("/profile-member", [UsersHandle::class, "ProfileMember"])
    ->name("ProfileMember")
    ->middleware("auth", "resigned.redirect", "member.active");

Route::get('/profile-member/soa', [UsersHandle::class, 'DownloadSoa'])->name('DownloadSoa');

Route::get('/member/status-ping', [UsersHandle::class, 'AccountStatusPing'])
    ->name('member.statusPing')
    ->middleware('auth');   // ← no member-status / inactive-guard middleware

Route::get('/faqs', [UsersHandle::class, 'Faqs'])->name('Faqs');
Route::post('/support/report', [UsersHandle::class, 'SubmitReport'])->name('support.report.store');

// Member reports (support tickets)
Route::get("/admin/support-reports", [AdminSupportController::class, "reportsIndex"])->name("admin.support-reports.index")->middleware("admin");
Route::post("/admin/support-reports/{id}/update", [AdminSupportController::class, "reportsUpdate"])->name("admin.support-reports.update")->middleware("admin");

// FAQ management
Route::get("/admin/faqs", [AdminSupportController::class, "faqsIndex"])->name("admin.faqs.index")->middleware("admin");
Route::post("/admin/faqs", [AdminSupportController::class, "faqsStore"])->name("admin.faqs.store")->middleware("admin");
Route::post("/admin/faqs/{id}/update", [AdminSupportController::class, "faqsUpdate"])->name("admin.faqs.update")->middleware("admin");
Route::post("/admin/faqs/{id}/toggle", [AdminSupportController::class, "faqsToggle"])->name("admin.faqs.toggle")->middleware("admin");
Route::delete("/admin/faqs/{id}", [AdminSupportController::class, "faqsDestroy"])->name("admin.faqs.destroy")->middleware("admin");

// Edit Profile Member page GET
Route::get("/edit-profile-member", [UsersHandle::class, "EditProfileMember"])->name("EditProfileMember")->middleware("auth", "member.active");

// Edit Profile Member page POST
Route::post("/edit-profile-member", [UsersHandle::class, "UpdateProfileMember"])->name("UpdateProfileMember")->middleware("auth", "member.active");

Route::get("/settings", [UsersHandle::class, "Settings"])->name("Settings")->middleware("auth", "member.active");

Route::get("/notifications", [UsersHandle::class, "Notifications"])->name("Notifications")->middleware("auth", "member.active");

Route::post('/settings/toggle', [App\Http\Controllers\UsersHandle::class, 'UpdateSetting'])->name('settings.toggle')->middleware('auth', 'member.active');
Route::post('/settings/change-password', [App\Http\Controllers\UsersHandle::class, 'ChangePassword'])->name('settings.changePassword')->middleware('auth', 'member.active');
Route::post('/settings/request-deactivation', [App\Http\Controllers\UsersHandle::class, 'RequestDeactivation'])->name('settings.requestDeactivation')->middleware('auth', 'member.active');
Route::get('/settings/export', [App\Http\Controllers\UsersHandle::class, 'ExportData'])->name('settings.export')->middleware('auth', 'member.active');

Route::get("/Seminars", [UsersHandle::class, "Seminars"])->name("Seminars");

Route::post("/seminars/verify-passcode", [UsersHandle::class, "verifySeminarPasscode"])->name("Seminars.verifyPasscode")->middleware("auth", "member.active");

Route::post('/notifications/mark-all-read', [App\Http\Controllers\UsersHandle::class, 'MarkAllRead'])
    ->name('notifications.markAllRead')->middleware('auth', 'member.active');

Route::get("/transactions", [UsersHandle::class, "Transactions"])->name("transactions")->middleware("auth", "member.active");

// Driver Portal page GET
Route::get("/driver-portal", [UserController::class, "DriverPortal"])->name("DriverPortal");

// User Handle page GET
Route::get("/user-handle", [UsersHandle::class, "UserHandle"])->name("UserHandle")->middleware("auth");

// Registration page POST
Route::post("/registration", [UsersHandle::class, "registration"])->name("registration")->middleware("throttle:registration");

Route::post('/approve-user/{id}', [UserController::class, 'approveUser'])->name('approve.user')->middleware('admin');

Route::post('/messageAboutShare/{id}', [UserController::class, "messageAboutShare"])->name("message.user")->middleware('admin');

Route::middleware(['auth'])->group(function () {
    Route::get('/share-capital-form', [ShareCapital::class, 'index'])->name('share_capital.index');
    Route::post('/share-capital-form', [ShareCapital::class, 'store'])->name('share_capital.member.store');

});

Route::post('/loan-disburse', [lendingController::class, 'disburseLoan'])->name('loan.disburse')->middleware('admin');

Route::post('/admin/loans/apply-penalties', [lendingController::class, 'adminApplyOverduePenalties'])
    ->name('admin.loans.applyPenalties')
    ->middleware('admin');

Route::post('/share-capital/store', [ShareCapital::class, 'store'])->name('share_capital.store.admin')->middleware('admin');

// Share Capital form via email link
Route::get('/share-capital-form/{id}', [ShareCapital::class, 'showForMember'])->name('share_capital.show');

// Application form
Route::get('/application-form/{id}', [UserController::class, 'applicationForm'])->name('applicationForm');

Route::post('/application-form/{id}', [UsersHandle::class, 'applicationFormButton'])->name('applicationFormButton');

Route::get("/nav-bar2", [UsersHandle::class, "Navbar2"])->name("Navbar2");

Route::get("/logout", [UsersHandle::class, "logout"])->name("logout");

Route::post("/lending-program", [lendingController::class, "lendingProgram"])
    ->name("lendingProgram")
    ->middleware("auth", "member.active");

Route::get("/Financial", [UsersHandle::class, "Financial"])->name("Financial")->middleware("auth", "member.active");

Route::get('/check-reference', [UsersHandle::class, 'checkReference'])
    ->name('reference.check')
    ->middleware('auth', 'member.active');

Route::get('/savings/receipt/{referenceNo}', [SavingsController::class, 'downloadReceipt'])
    ->name('savings.receipt')->middleware('auth', 'member.active');

Route::post('/savings/admin/store', [SavingsController::class, 'adminStoreSavings'])
    ->name('savings.admin.store')
    ->middleware('admin');

Route::get('/savings/admin/balance/{memberId}', [SavingsController::class, 'getMemberBalance'])
    ->name('savings.admin.balance')
    ->middleware('admin');

Route::get('/savings/admin/sc-balance/{memberId}', [SavingsController::class, 'getMemberShareCapitalBalance'])
    ->name('savings.admin.sc-balance')
    ->middleware('admin');

Route::post('/savings/convert-to-share-capital', [SavingsController::class, 'convertToShareCapital'])
    ->name('savings.convert-to-share-capital')
    ->middleware('admin');

Route::get('/loan-status', [lendingController::class, 'loanStatus'])
    ->name('LoanStatus')
    ->middleware('auth', 'member.active');

Route::post('/repayment/store', [lendingController::class, 'storeRepayment'])
    ->name('repayment.store')
    ->middleware('auth', 'member.active');

Route::post('/savings/member/verify-password', [\App\Http\Controllers\UsersHandle::class, 'VerifyConversionPassword'])
    ->middleware('auth')
    ->name('savings.member.verify_password');

// ✅ Final — just these two lines, no wrapping group needed
Route::post('/otp/send', [OtpController::class, 'send'])->name('otp.send')->middleware('throttle:otp-send');
Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:otp-verify');

// ✅ With web middleware — session persists correctly
// Route::middleware('web')->group(function () {
//     Route::post('/send-otp', [OtpController::class, 'send'])->name('send.otp');
//     Route::post('/verify-otp', [OtpController::class, 'verify'])->name('verify.otp');
// });

Route::post('/change-password', [UsersHandle::class, 'ChangePassword'])->name('ChangePassword');

// Admin routes
Route::get("/dashboard-admin", [UserController::class, "dashboard_admin"])->name("dashboard")->middleware("admin");
Route::get("/dashboard-members", [UserController::class, "dashboard_members"])->name("dashboard.members")->middleware("admin");
Route::put("/dashboard-members/update", [UserController::class, "updateMember"])->name("update.member")->middleware("admin");
Route::post("/dashboard-members/store", [UserController::class, "storeMember"])->name("member.store")->middleware("admin");
Route::get("/dashboard-members/send-share-email/{id}", [UserController::class, "sendShareCapitalEmail"])->name("send.share.capital.email")->middleware("admin");
Route::delete("/dashboard-members/decline/{id}", [UserController::class, "declineUser"])->name("decline.user")->middleware("admin");
Route::redirect("/dashboard-savings", "/dashboard-financial-activity?tab=savings")->name("savings")->middleware("admin");
Route::get("/dashboard-lendings", [UserController::class, "dashboard_lendings"])->name("lendings")->middleware("admin");

Route::post('/resignation/{id}/approve', [UserController::class, 'approveResignation'])->name('resignation.approve');
Route::post('/resignation/{id}/reject', [UserController::class, 'rejectResignation'])->name('resignation.reject');
Route::post('/resignation/{id}/release', [UserController::class, 'releaseResignationShareCapital'])->name('resignation.release');

// API to get payment count for a loan
Route::get('/loan/{id}/payments-count', function ($id) {
    $count = \DB::table('lending_repayments_tbls')->where('lending_id', $id)->count();
    return response()->json(['payments_made' => $count]);
})->middleware("admin");
Route::post("/loan/approve/{id}", [UserController::class, "approveLoan"])->name("loan.approve")->middleware("admin");
Route::post("/loan/decline/{id}", [UserController::class, "declineLoan"])->name("loan.decline")->middleware("admin");
Route::post("/loan/create-admin", [UserController::class, "createLoanAdmin"])->name("loan.create-admin")->middleware("admin");
Route::post("/loan/settings/update", [UserController::class, "updateLoanSettings"])->name("loan.settings.update")->middleware("admin");
Route::redirect("/dashboard-sharecapitals", "/dashboard-financial-activity?tab=share-capitals")->name("sharecapitals")->middleware("admin");
Route::post("/sharecapital/admin/store", [UserController::class, "adminStoreShareCapital"])->name("sharecapital.admin.store")->middleware("admin");
Route::get("/sharecapital/member/{id}/balance", [UserController::class, "getMemberShareCapitalBalance"])->name("sharecapital.member.balance")->middleware("admin");
Route::post("/sharecapital/withdrawal/{id}/status", [UserController::class, "updateWithdrawalStatus"])->name("sharecapital.withdrawal.status")->middleware("admin");
Route::post("/sharecapital/{id}/complete-deposit", [UserController::class, 'completeShareCapitalDeposit'])->name('sharecapital.complete-deposit')->middleware('admin');
Route::post("/sharecapital/{id}/void-deposit", [UserController::class, 'voidShareCapitalDeposit'])->name('sharecapital.void-deposit')->middleware('admin');
Route::post("/sharecapital/sell", [ShareCapital::class, "sellShares"])->name("sharecapital.sell")->middleware("admin");
Route::get("/dashboard-reports", [ReportController::class, "index"])->name("reports")->middleware("admin");
Route::get("/dashboard-reports/daily", [ReportController::class, "daily"])->name("reports.daily")->middleware("admin");
Route::get("/dashboard-reports/journal-detailed", [ReportController::class, "journalDetailed"])->name("reports.journal.detailed")->middleware("admin");
Route::get("/dashboard-reports/journal-summary", [ReportController::class, "journalSummary"])->name("reports.journal.summary")->middleware("admin");
Route::get("/dashboard-reports/statement-of-operations", [ReportController::class, "statementOfOperations"])->name("reports.statement")->middleware("admin");
Route::get("/dashboard-reports/statement-of-operations/print", [ReportController::class, "statementOfOperationsPrint"])->name("reports.statement.print")->middleware("admin");
Route::get("/dashboard-settings", [UserController::class, "dashboard_settings"])->name("settings")->middleware("admin");
Route::post("/dashboard-settings", [UserController::class, "dashboard_settings"])->name("settings.update")->middleware("admin");
Route::post("/admin/store", [UserController::class, "storeAdmin"])->name("admin.store")->middleware("admin");

Route::post("/admin/change-password", [UserController::class, "changePassword"])->name("admin.change-password")->middleware("admin");

Route::post('/admin/update-profile', [UserController::class, 'updateProfile'])->name('admin.update-profile');

Route::post("/admin/store", [UserController::class, "storeAdmin"])->name("admin.store")->middleware("admin");
Route::post("/admin/change-password", [UserController::class, "changePassword"])->name("admin.change-password")->middleware("admin");
Route::post("/admin/update-profile", [UserController::class, "updateProfile"])->name("admin.update-profile")->middleware("admin");
Route::post("/admin/update", [UserController::class, "updateAdmin"])->name("admin.update")->middleware("admin");

Route::post("/admin/update", [UserController::class, "updateAdmin"])->name("admin.update")->middleware("admin");
Route::post("/admin/delete", [UserController::class, "deleteAdmin"])->name("admin.delete")->middleware("admin");
Route::post("/admin/toggle-status", [UserController::class, "toggleAdminStatus"])->name("admin.toggle-status")->middleware("admin");
Route::post("/roles/store", [UserController::class, "storeRole"])->name("roles.store")->middleware("admin");
Route::post("/roles/update", [UserController::class, "updateRole"])->name("roles.update")->middleware("admin");
Route::post("/roles/delete", [UserController::class, "deleteRole"])->name("roles.delete")->middleware("admin");

Route::middleware(['auth'])->group(function () {
    Route::post('/admin/backups', [App\Http\Controllers\UserController::class, 'createBackup'])->name('admin.backup.create');
    Route::get('/admin/backups/{filename}/download', [App\Http\Controllers\UserController::class, 'downloadBackup'])->name('admin.backup.download');
    Route::delete('/admin/backups/{filename}', [App\Http\Controllers\UserController::class, 'deleteBackup'])->name('admin.backup.delete');
});

Route::post('/notifications/mark-read', [App\Http\Controllers\UsersHandle::class, 'MarkNotificationRead'])
    ->name('notifications.markRead');

// Allied Worker management (GM / Main Admin only; guards enforced in controller)
Route::get("/allied-workers", [AlliedWorkerController::class, "index"])->name("allied-workers.index")->middleware("admin");
Route::post("/allied-workers/promote", [AlliedWorkerController::class, "promote"])->name("allied-workers.promote")->middleware("admin");
Route::post("/allied-workers/revoke", [AlliedWorkerController::class, "revoke"])->name("allied-workers.revoke")->middleware("admin");
Route::post("/allied-workers/switch-to-member", [AlliedWorkerController::class, "switchToMember"])->name("allied-workers.switch-to-member")->middleware("admin");
Route::post("/allied-workers/switch-to-aw", [AlliedWorkerController::class, "switchToAw"])->name("allied-workers.switch-to-aw")->middleware("admin");
// archives route removed
Route::match(["get", "post"], "/dashboard-financial-activity", [UserController::class, "dashboard_financial_activity"])->name("financial.activity")->middleware("admin");
Route::post('/loan-settings/create', [UserController::class, 'createLoanSetting'])->name('loan.settings.create')->middleware('admin');
Route::post('/loan-settings/update', [UserController::class, 'updateLoanSettings'])->name('loan.settings.update')->middleware('admin');
Route::delete('/loan-settings/{id}', [UserController::class, 'deleteLoanSetting'])->name('loan.settings.delete')->middleware('admin');

Route::post('/savings/member/convert', [ShareCapital::class, 'convertSavingsToShareCapital'])
    ->name('savings.member.convert')
    ->middleware('auth', 'member.active');

Route::get("/loan-stats", [UsersHandle::class, "loanStats"])->name("loan_stats")->middleware("admin");

Route::post("/cooperative-transactions/store", [UserController::class, "storeCooperativeTransaction"])->name("cooperative.transactions.store")->middleware("admin");
Route::get("/dashboard-payments", [UserController::class, "dashboard_payments"])->name("payments")->middleware("admin");
Route::post("/dashboard-payments/record", [UserController::class, "adminStoreRepayment"])->name("payments.record")->middleware("admin");
Route::post('/repayments/{id}/complete', [UserController::class, 'completeRepayment'])->name('repayment.complete')->middleware('admin');
Route::post('/repayments/{id}/void', [UserController::class, 'voidRepayment'])->name('repayment.void')->middleware('admin');
Route::get("/loans/member/{id}/active", function ($id) {
    $loans = \App\Models\lending_program_tbl::where('user_id', $id)
        ->whereIn('status', ['Approved'])
        ->select('id', 'reference_no', 'lending_type', 'monthly_payment', 'total_payment', 'lending_amount')
        ->get();
    return response()->json($loans);
})->name("loans.member.active")->middleware("admin");
Route::get('/loans/{id}/payable', [App\Http\Controllers\UserController::class, 'getLoanPayable'])->name('loans.payable')->middleware('admin');
Route::get("/admin/audit-logs", [UserController::class, "auditLogsIndex"])->name("admin.audit-logs.index")->middleware("admin");
Route::get("/dashboard-officers-committees", [UserController::class, "dashboard_officers_committees"])->name("officers.committees")->middleware("admin");
Route::post("/officers/store", [UserController::class, "storeOfficer"])->name("officers.store")->middleware("admin");
Route::put("/officers/{id}", [UserController::class, "updateOfficer"])->name("officers.update")->middleware("admin");
Route::delete("/officers/{id}", [UserController::class, "deleteOfficer"])->name("officers.delete")->middleware("admin");

Route::post('/officer-positions', [UserController::class, 'storeOfficerPosition'])->name('officer-positions.store');
Route::delete('/officer-positions/{id}', [UserController::class, 'deleteOfficerPosition'])->name('officer-positions.destroy');

// Announcement routes
Route::post("/announcements", [App\Http\Controllers\AnnouncementController::class, "store"])->name("announcements.store")->middleware("admin");
Route::post("/announcements/{id}/comment", [App\Http\Controllers\AnnouncementController::class, "storeComment"])->name("announcements.comment")->middleware("auth");
Route::post("/announcements/{id}/like", [App\Http\Controllers\AnnouncementController::class, "toggleLike"])->name("announcements.like")->middleware("auth");
Route::post("/announcements/{id}/comment/{commentId}/delete", [App\Http\Controllers\AnnouncementController::class, "deleteComment"])->name("announcements.comment.delete")->middleware("admin");
Route::post("/announcements/{id}/delete", [App\Http\Controllers\AnnouncementController::class, "deleteAnnouncement"])->name("announcements.delete")->middleware("admin");

// Poll routes
Route::post("/polls", [App\Http\Controllers\AnnouncementController::class, "storePoll"])->name("announcements.poll.store")->middleware("admin");
Route::post("/polls/{pollId}/vote", [App\Http\Controllers\AnnouncementController::class, "votePoll"])->name("announcements.poll.vote")->middleware("auth");
Route::post("/polls/{pollId}/delete", [App\Http\Controllers\AnnouncementController::class, "deletePoll"])->name("announcements.poll.delete")->middleware("admin");

// Seminar routes
Route::get("/admin/seminars", [App\Http\Controllers\SeminarController::class, "index"])->name("seminars.index")->middleware("admin");
Route::post("/admin/seminars/schedule", [App\Http\Controllers\SeminarController::class, "scheduleSeminar"])->name("seminars.schedule")->middleware("admin");
Route::post("/admin/seminars/attendance", [App\Http\Controllers\SeminarController::class, "updateAttendanceAndCompletion"])->name("seminars.attendance")->middleware("admin");
Route::post("/admin/seminars/store-type", [App\Http\Controllers\SeminarController::class, "storeSeminarType"])->name("seminars.store-type")->middleware("admin");
Route::get("/admin/seminars/member-search", [App\Http\Controllers\SeminarController::class, "memberSearch"])->name("seminars.member-search")->middleware("admin");

// Resignation routes
Route::post("/member/resign", [App\Http\Controllers\ResignationController::class, "requestResignation"])->name("resignation.request")->middleware("auth");
Route::post("/admin/resignation/{id}/approve", [App\Http\Controllers\ResignationController::class, "approveResignation"])->name("resignation.approve")->middleware("admin");
Route::post("/admin/resignation/{id}/reject", [App\Http\Controllers\ResignationController::class, "rejectResignation"])->name("resignation.reject")->middleware("admin");
Route::post("/admin/resignation/{id}/release", [App\Http\Controllers\ResignationController::class, "releaseShareCapital"])->name("resignation.release")->middleware("admin");

// Account reactivation routes
Route::get("/member/inactive", [App\Http\Controllers\UsersHandle::class, "InactivePage"])->name("member.inactive")->middleware("auth");
Route::post("/member/reactivate", [App\Http\Controllers\UsersHandle::class, "ReactivateAccount"])->name("member.reactivate")->middleware("auth");
Route::post("/admin/member/{id}/reactivate", [App\Http\Controllers\UserController::class, "approveReactivation"])->name("member.reactivation.approve")->middleware("admin");
Route::post("/admin/member/{id}/reactivate/reject", [App\Http\Controllers\UserController::class, "rejectReactivation"])->name("member.reactivation.reject")->middleware("admin");

// Dividend routes
Route::get("/admin/dividends", [DividendController::class, "index"])->name("dividends.index")->middleware("admin");
Route::get("/admin/dividends/partial", [DividendController::class, "tablePartial"])->name("dividends.table-partial")->middleware("admin");
Route::post("/admin/dividends/calculate", [DividendController::class, "calculate"])->name("dividends.calculate")->middleware("admin");
Route::put("/admin/dividends/{id}/update", [DividendController::class, "update"])->name("dividends.update")->middleware("admin");
Route::post("/admin/dividends/{id}/approve", [DividendController::class, "approve"])->name("dividends.approve")->middleware("admin");
Route::post("/admin/dividends/{id}/disburse", [DividendController::class, "disburseOne"])->name("dividends.disburse-one")->middleware("admin");
Route::post("/admin/dividends/disburse", [DividendController::class, "disburseAll"])->name("dividends.disburse")->middleware("admin");
Route::post("/admin/dividends/disburse-all/{year}", [DividendController::class, "disburseAll"])->name("dividends.disburse-all")->middleware("admin");
Route::post("/admin/dividends/approve-all", [DividendController::class, "approveAll"])->name("dividends.approve-all")->middleware("admin");
Route::post("/admin/dividends/fund-percentage", [DividendController::class, "updateFundPercentage"])->name("dividends.update-fund-percentage")->middleware("admin");

// Patronage Refund Distribution routes
Route::get("/admin/dividends/patronage-partial", [DividendController::class, "patronageTablePartial"])->name("dividends.patronage-partial")->middleware("admin");
Route::get("/admin/dividends/calculate-patronage", [DividendController::class, "calculatePatronageRefunds"])->name("dividends.calculate-patronage")->middleware("admin");
Route::get("/admin/dividends/reset/{year}", [DividendController::class, "resetDistribution"])->name("dividends.reset")->middleware("admin");
Route::put("/admin/dividends/patronage/{id}/update", [DividendController::class, "updatePatronageRefund"])->name("dividends.patronage.update")->middleware("admin");
Route::post("/admin/dividends/patronage/{id}/approve", [DividendController::class, "approvePatronageRefund"])->name("dividends.patronage.approve")->middleware("admin");
Route::post("/admin/dividends/patronage/approve-all", [DividendController::class, "approveAllPatronageRefunds"])->name("dividends.patronage.approve-all")->middleware("admin");
Route::post("/admin/dividends/patronage/{id}/disburse", [DividendController::class, "disbursePatronageRefundOne"])->name("dividends.patronage.disburse-one")->middleware("admin");
Route::post("/admin/dividends/patronage/disburse-all/{year?}", [DividendController::class, "disburseAllPatronageRefunds"])->name("dividends.patronage.disburse-all")->middleware("admin");
Route::post("/admin/dividends/disburse-both/{year?}", [DividendController::class, "disburseBoth"])->name("dividends.disburse-both")->middleware("admin");
Route::get("/admin/dividends/patronage/{id}/breakdown", [DividendController::class, "patronageBreakdown"])->name("dividends.patronage.breakdown")->middleware("admin");
Route::post("/admin/dividends/update-patronage-basis", [DividendController::class, "updatePatronageBasis"])->name("dividends.update-patronage-basis")->middleware("admin");

// Additional Patronage Records routes
Route::get("/patronage-records/partial", [UserController::class, "patronageRecordsPartial"])->name("patronage-records.partial")->middleware("admin");
Route::post("/patronage-records", [UserController::class, "storePatronageRecord"])->name("patronage-records.store")->middleware("admin");
Route::put("/patronage-records/{id}", [UserController::class, "updatePatronageRecord"])->name("patronage-records.update")->middleware("admin");
Route::delete("/patronage-records/{id}", [UserController::class, "deletePatronageRecord"])->name("patronage-records.delete")->middleware("admin");

// Payment Method routes
Route::get('/admin/payment-methods', [App\Http\Controllers\PaymentMethodController::class, 'index'])->name('payment-methods.index')->middleware('admin');
Route::post('/admin/payment-methods', [App\Http\Controllers\PaymentMethodController::class, 'store'])->name('payment-methods.store')->middleware('admin');
Route::put('/admin/payment-methods/{id}', [App\Http\Controllers\PaymentMethodController::class, 'update'])->name('payment-methods.update')->middleware('admin');
Route::delete('/admin/payment-methods/{id}', [App\Http\Controllers\PaymentMethodController::class, 'destroy'])->name('payment-methods.delete')->middleware('admin');
Route::post('/admin/payment-methods/{id}/toggle', [App\Http\Controllers\PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle')->middleware('admin');
Route::get('/admin/payment-methods/{id}/qr', [App\Http\Controllers\PaymentMethodController::class, 'getQrCode'])->name('payment-methods.qr')->middleware('admin');

Route::post('/dashboard-members/import', [UserController::class, 'importMembers'])
    ->name('members.import');

Route::post('/members/{id}/force-logout', [UserController::class, 'forceLogoutMember'])->name('members.forceLogout');

Route::post('/notifications/mark-all-read', [UsersHandle::class, 'MarkAllNavNotificationsRead'])
    ->name('notifications.markAllRead');
