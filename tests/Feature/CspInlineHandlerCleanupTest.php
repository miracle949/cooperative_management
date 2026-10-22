<?php

use Illuminate\Support\Facades\File;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php. These are pure static sweeps over the view
// and asset sources; no database tables are required.

// ────────────────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────────────────

function cspInlineAllBladeFiles(): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $files[] = $file->getRealPath();
        }
    }

    sort($files);

    return $files;
}

function cspInlineCommentStripped(string $content): string
{
    foreach (['{{--', '<!--'] as $open) {
        $close = $open === '{{--' ? '--}}' : '-->';
        $i = 0;
        while (($s = strpos($content, $open, $i)) !== false) {
            $e = strpos($content, $close, $s + strlen($open));
            if ($e === false) {
                $i = $s + strlen($open);
                continue;
            }
            $content = substr_replace($content, '', $s, $e + strlen($close) - $s);
            $i = $s;
        }
    }

    return $content;
}

/**
 * Locate all inline event-handler attributes in a document. The value must be
 * quoted (as produced by both real HTML attributes and JS string builders),
 * and the token must be a lowercase on-* attribute name — this excludes JS
 * variable declarations such as "online =" or property assignments such as
 * "el.onchange =".
 */
function cspInlineHandlerMatches(string $content): array
{
    preg_match_all('/\son[a-z]+\s*=\s*["\']/', $content, $matches, PREG_OFFSET_CAPTURE);

    return $matches[0];
}

// ────────────────────────────────────────────────────────────────────────────
// Static sweep: no inline handlers in live views
// ────────────────────────────────────────────────────────────────────────────

it('leaves zero live inline event-handler attributes in the view tree', function () {
    $files = cspInlineAllBladeFiles();
    expect($files)->not->toBeEmpty();

    $offenders = [];

    foreach ($files as $file) {
        if (str_contains($file, 'practice_folder')) {
            continue; // dead folder, not routed anywhere
        }

        $stripped = cspInlineCommentStripped(File::get($file));
        $matches = cspInlineHandlerMatches($stripped);

        if (! empty($matches)) {
            $relative = str_replace(base_path('resources/views/'), '', $file);
            foreach ($matches as [$value, $offset]) {
                $line = substr_count(substr($stripped, 0, $offset), "\n") + 1;
                $offenders[] = sprintf('%s:%d → %s', $relative, $line, trim($value));
            }
        }
    }

    expect($offenders)->toBeEmpty(implode("\n", $offenders));
});

// ────────────────────────────────────────────────────────────────────────────
// Coverage: every live view that relies on data-action must load the dispatcher
// ────────────────────────────────────────────────────────────────────────────

it('loads the CSP dispatcher in every standalone view that uses data-action', function () {
    // Context-owned files inherit the dispatcher from their host page:
    //   * admin_components/*        → @extends('layouts.admin') or are partials
    //   * components/navbar2        → rendered inside layouts/admin
    //   * membership_components/…   → @include'd by register.blade.php
    $inherited = [
        base_path('resources/views/admin_components') . '/',
        base_path('resources/views/components/navbar2.blade.php'),
        base_path('resources/views/membership_components/personal_data.blade.php'),
        base_path('resources/views/membership_components/review_submit.blade.php'),
    ];

    $inherited = array_map(fn ($path) => str_replace('\\', '/', $path), $inherited);

    $shouldInclude = collect(cspInlineAllBladeFiles())
        ->reject(fn ($file) => str_contains($file, 'practice_folder'))
        ->reject(function ($file) use ($inherited) {
            $file = str_replace('\\', '/', $file);
            foreach ($inherited as $owner) {
                if (str_ends_with($owner, '/')) {
                    if (str_starts_with($file, $owner)) {
                        return true;
                    }
                } elseif ($file === $owner) {
                    return true;
                }
            }

            return false;
        })
        ->filter(fn ($file) => str_contains(File::get($file), 'data-action='))
        ->values();

    expect($shouldInclude)->not->toBeEmpty();

    foreach ($shouldInclude as $file) {
        expect(File::get($file), $file)->toContain("asset('js/csp-events.js')");
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Dispatcher asset: the loaded script actually provides the delegation primitives
// ────────────────────────────────────────────────────────────────────────────

it('ships a dispatcher asset with the full delegation contract', function () {
    $path = public_path('js/csp-events.js');

    expect(File::exists($path))->toBeTrue();

    $source = File::get($path);

    expect($source)->toContain('CSP_actions.register')
        ->toContain('data-action')
        ->toContain('data-arg')
        ->toContain('data-trigger')
        ->toContain('data-numeric-guard')
        ->toContain('data-error-mark')
        ->not->toContain('unsafe-inline');
});

// ────────────────────────────────────────────────────────────────────────────
// Loading order: the dispatcher must be fetched before any glue registration
// that depends on the CSP_actions API
// ────────────────────────────────────────────────────────────────────────────

it('loads the dispatcher before page-specific glue registration', function () {
    $standalone = [
        'members_components/member_portal.blade.php',
        'members_components/loan_status.blade.php',
        'members_components/savings.blade.php',
        'members_components/Financial.blade.php',
        'members_components/share_capital.blade.php',
        'members_components/transactions.blade.php',
        'members_components/loan_application.blade.php',
        'register.blade.php',
        'login.blade.php',
    ];

    foreach ($standalone as $view) {
        $content = File::get(base_path('resources/views/' . $view));

        $includePos = strpos($content, 'csp-events.js');
        $gluePos = strpos($content, 'CSP_actions.register');
        if ($gluePos === false) {
            $gluePos = strpos($content, 'A.register');
        }

        if ($gluePos !== false) {
            expect($includePos, $view)->not->toBeFalse();
            expect($includePos, $view)->toBeLessThan($gluePos);
        }
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Dispatcher contract: registered generics receive (event, element) even when
// the element declares no data-arg. Registered actions are written against the
// documented (event, element) signature and read their own attributes off the
// element; invoking them with zero arguments leaves both undefined and the
// action throws (e.g. remove-element on the admin receipt Close).
// ────────────────────────────────────────────────────────────────────────────

it('defaults registered actions without data-arg to (event, element)', function () {
    $source = File::get(public_path('js/csp-events.js'));

    expect($source)->toContain("if (el.getAttribute('data-arg') === null) args = [e, el];");

    $runHandlerStart = strpos($source, 'function runHandler');
    $runHandler = substr($source, $runHandlerStart, strpos($source, 'function dispatch', $runHandlerStart) - $runHandlerStart);
    $actionLookup = strpos($runHandler, 'var handler = actions[name];');

    expect($runHandler)->toContain("handler.apply(el, args)");
    expect(strpos($runHandler, "[e, el];"))->toBeGreaterThan($actionLookup);
});

// ────────────────────────────────────────────────────────────────────────────
// Encoding: no UTF-8→Windows-1252 mojibake tokens in view/controller sources.
// Each token is the double-encoded form of a char corrupted by a one-round
// CP1252 re-encode; literals are byte-escaped so this test file itself can
// never reintroduce the corrupted bytes.
// ────────────────────────────────────────────────────────────────────────────

function cspMojibakeTokens(): array
{
    return [
        "\xC3\xA2\xE2\x80\x9A\xC2\xB1", // â‚± → ₱
        "\xC3\x82\xC2\xB7",             // Â·  → ·
        "\xC3\xA2\xE2\x82\xAC\xE2\x80\x9D", // â€" → —
        "\xC3\xA2\xE2\x80\x9D\xE2\x82\xAC", // â"€ → ─
        "\xC3\xA2\xE2\x80\xA0\xE2\x80\x99", // â†' → →
    ];
}

function cspMojibakeSourceFiles(): array
{
    $files = [];

    foreach (['app' => '.php', 'resources/views' => '.blade.php'] as $dir => $extension) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path($dir), FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $extension)) {
                $files[] = $file->getRealPath();
            }
        }
    }

    sort($files);

    return $files;
}

it('contains zero mojibake tokens in view and controller sources', function () {
    $files = cspMojibakeSourceFiles();
    expect($files)->not->toBeEmpty();

    $offenders = [];

    foreach ($files as $file) {
        $content = File::get($file);
        foreach (cspMojibakeTokens() as $token) {
            if (str_contains($content, $token)) {
                $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $line = substr_count(substr($content, 0, strpos($content, $token)), "\n") + 1;
                $offenders[] = sprintf('%s:%d contains a mojibake token', $relative, $line);
            }
        }
    }

    expect($offenders)->toBeEmpty(implode("\n", $offenders));
});

it('renders the peso sign correctly in the previously corrupted sources', function () {
    $files = [
        'resources/views/admin_components/partials/finance_savings_modals.blade.php',
        'resources/views/admin_components/partials/finance_sharecapital_modals.blade.php',
        'app/Http/Controllers/UserController.php',
    ];

    foreach ($files as $file) {
        expect(File::get(base_path($file)), $file)->toContain("\xE2\x82\xB1"); // ₱
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Admin loan detail: the "Attached Documents" section must render every
// attachment column the member loan-application form can populate, not just
// the legacy valid_id / proof_of_income pair.
// ────────────────────────────────────────────────────────────────────────────

it('renders every loan document column in the admin detail modal', function () {
    $source = File::get(base_path('resources/views/admin_components/lending.blade.php'));

    $labels = [
        "'Valid ID'",
        "'Proof of Income'",
        "'Proof of Emergency'",
        "'Business Permit'",
        "'Financial Statement'",
        "'School ID'",
        "'Certificate of Registration (COR)'",
        "'Certificate of Good Standing (COG)'",
    ];

    foreach ($labels as $label) {
        expect($source)->toContain($label);
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Loan detail modal: the Payment Progress must reflect completion. Once a loan
// is fully paid (status Completed, set server-side when payments_made reaches
// total_payments), the modal shows 100% (total of total) even if the repayment
// row count is below the term (bulk settlement, legacy loans, overdue sweep).
// ────────────────────────────────────────────────────────────────────────────

it('reports full payment progress for loans already marked Completed', function () {
    $source = File::get(base_path('resources/views/admin_components/lending.blade.php'));

    expect($source)
        ->toContain("const isCompleted = String(loan.status || '').toLowerCase() === 'completed';")
        ->toContain('const displayed = isCompleted ? totalPaymentsCount : paymentsMade;')
        ->toContain("document.getElementById('modalPaymentProgress').textContent = displayed + ' of ' + totalPaymentsCount;")
        ->toContain("Math.round((displayed / totalPaymentsCount) * 100)");
});

// ────────────────────────────────────────────────────────────────────────────
// Admin Disbursal Management (financial_activity.blade.php): bulk Approve All
// for both dividends and patronage, and the patronage "Disburse All" button
// styled consistently with the dividend card. Pure static sweeps over the
// route/controller/view sources; no database tables are required.
// ────────────────────────────────────────────────────────────────────────────

it('wires the bulk dividend approve-all route to the controller method', function () {
    $routes = File::get(base_path('routes/web.php'));

    expect($routes)
        ->toContain('"/admin/dividends/approve-all"')
        ->toContain('"approveAll"')
        ->toContain('dividends.approve-all');
});

it('keeps the bulk approve-all controller method and pending counts', function () {
    $controller = File::get(base_path('app/Http/Controllers/DividendController.php'));
    $viewController = File::get(base_path('app/Http/Controllers/UserController.php'));

    expect($controller)
        ->toContain('function approveAll(')
        ->toContain('approvedCount')
        ->toContain('$pendingCount')
        ->toContain('$patronagePendingCount');

    expect($viewController)->toContain('$pendingCount')
        ->toContain('$patronagePendingCount')
        ->toContain("'pendingCount'")
        ->toContain("'patronagePendingCount'");
});

it('registers bulk Approve All actions on the Disbursal Management page', function () {
    $view = File::get(base_path('resources/views/admin_components/financial_activity.blade.php'));

    expect($view)
        ->toContain('data-action="approveAllDividends"')
        ->toContain('id="approve-all-btn"')
        ->toContain('function approveAllDividends()')
        ->toContain('data-action="approveAllPatronage"')
        ->toContain('id="approve-all-patronage-btn"')
        ->toContain('function approveAllPatronage()');
});

it('labels the patronage bulk disbursement as Disburse All like the dividend card', function () {
    $view = File::get(base_path('resources/views/admin_components/financial_activity.blade.php'));

    expect($view)
        ->toContain('id="disburse-patronage-btn" class="btn btn-primary btn-lg"')
        ->toContain('id="disburse-all-btn" class="btn btn-primary btn-lg"');
});

it('hides the dividend Disburse All button when nothing is left to disburse', function () {
    $view = File::get(base_path('resources/views/admin_components/financial_activity.blade.php'));

    expect($view)->toContain(
        '@if($approvedCount > 0)' . "\n" .
        '                            <button data-action="disburseAllDividends" id="disburse-all-btn" class="btn btn-primary btn-lg">'
    );
});

// ────────────────────────────────────────────────────────────────────────────
// Record Payment member field: TomSelect needs its stylesheet allowed by the
// CSP style-src, and the member select must render as one searchable dropdown
// rather than a plain "Select member" option plus a detached search.
// ────────────────────────────────────────────────────────────────────────────

it('allows the TomSelect stylesheet host in the CSP style-src', function () {
    $csp = File::get(base_path('app/Http/Middleware/SetSecurityHeaders.php'));

    expect($csp)
        ->toContain("style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net")
        ->toContain('script-src')
        ->toContain('https://cdn.jsdelivr.net');
});

it('renders the record-payment member field as a single searchable dropdown', function () {
    $view = File::get(base_path('resources/views/admin_components/payments.blade.php'));

    expect($view)
        ->toContain('id="rpMemberSelect"')
        ->toContain('data-action="loadMemberLoans"')
        ->toContain('new TomSelect(\'#rpMemberSelect\'')
        ->toContain("placeholder: 'Search for a member...'")
        ->not->toContain('option value="">Select member');
});

// ────────────────────────────────────────────────────────────────────────────
// Members page: the Account Status select must reflect the stored role.
// ────────────────────────────────────────────────────────────────────────────

it('shows the stored member role in the Account Status dropdown', function () {
    $view = File::get(base_path('resources/views/admin_components/members.blade.php'));

    expect($view)
        ->toContain('id="detail-role"')
        ->toContain('<option value="Pending">Pending</option>')
        ->toContain('<option value="Member">Member</option>')
        ->toContain('<option value="inactive">Inactive</option>')
        ->toContain("String(member.role || '').toLowerCase()")
        ->not->toContain("document.getElementById('detail-role').value = member.role");
});

it('disables the Account Status dropdown for admin records to prevent demotion', function () {
    $view = File::get(base_path('resources/views/admin_components/members.blade.php'));

    expect($view)
        ->toContain('roleSelect.disabled = true')
        ->toContain('document.createElement(\'option\')');
});

// ────────────────────────────────────────────────────────────────────────────
// Members page table: the Actions column is gone and pending members open a
// dedicated application-review modal carrying the Accept / Decline actions.
// ────────────────────────────────────────────────────────────────────────────

it('removes the Actions column from the member table', function () {
    $view = File::get(base_path('resources/views/admin_components/members.blade.php'));

    expect($view)
        ->toContain(
            '<th>Role</th>' . "\n" .
            '                        <th>Status</th>' . "\n" .
            '                    </tr>'
        )
        ->not->toContain('js-confirm-delete-admin px-3')
        ->not->toContain('route(\'approve.user\', $member->id)')
        ->toContain('colspan="6" class="text-center py-12"');
});

it('routes pending members to the application review modal', function () {
    $view = File::get(base_path('resources/views/admin_components/members.blade.php'));

    expect($view)
        ->toContain("data-action=\"{{ strtolower(\$member->role ?? '') === 'pending' ? 'openMemberReviewModal' : 'openMemberDetailModal' }}\"")
        ->toContain('id="pendingDetailModal"')
        ->toContain('function openMemberReviewModal(memberId)')
        ->toContain("review-accept-form').action = '/approve-user/'")
        ->toContain("review-decline-form').action = '/dashboard-members/decline/'")
        ->toContain('Accept Application')
        ->toContain('Decline');
});

// ────────────────────────────────────────────────────────────────────────────
// Members page: the "Send Capital Email" triggers were removed from the member
// detail modal (hero button and Account Settings "Email Actions" block). The
// JS href assignments must be gone as well, or openMemberDetailModal would
// throw a TypeError against the removed elements.
// ────────────────────────────────────────────────────────────────────────────

it('removes the Send Capital Email buttons and their JS href wiring from the member detail modal', function () {
    $view = File::get(base_path('resources/views/admin_components/members.blade.php'));

    expect($view)
        ->not->toContain('detail-send-sc-email')
        ->not->toContain("Send Capital Email")
        ->not->toContain('Email Actions')
        ->not->toContain('/dashboard-members/send-share-email/');
});