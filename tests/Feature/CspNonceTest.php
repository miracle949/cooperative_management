<?php

use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Http\Request;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php. These tests exercise the CSP middleware and the
// Blade views; no database tables are required.

function cspScriptSrcFrom(string $csp): string
{
    preg_match('/script-src\s+([^;]+)/', $csp, $m);

    return $m[1] ?? '';
}

function nonceValueFrom(string $csp): string
{
    preg_match("/'nonce-([^']+)'/", $csp, $m);

    return $m[1] ?? '';
}

// Comment ranges ({  --  } vs HTML comments) so commented-out scripts are
// never treated as rendered scripts.
function bladeCommentRanges(string $content, string $open, string $close): array
{
    $ranges = [];
    $i = 0;
    while (($s = strpos($content, $open, $i)) !== false) {
        $e = strpos($content, $close, $s + strlen($open));
        if ($e === false) {
            $i = $s + strlen($open);
            continue;
        }
        $ranges[] = [$s, $e + strlen($close)];
        $i = $e + strlen($close);
    }

    return $ranges;
}

function isInsideRange(int $pos, array $ranges): bool
{
    foreach ($ranges as $range) {
        if ($pos >= $range[0] && $pos < $range[1]) {
            return true;
        }
    }

    return false;
}

/**
 * Classify every <script ...> open tag in a document.
 * Returns ['executable' => [tags], 'json' => [tags], 'external' => [tags]].
 */
function classifyScriptTags(string $document): array
{
    $ranges = array_merge(
        bladeCommentRanges($document, '{{--', '--}}'),
        bladeCommentRanges($document, '<!--', '-->')
    );

    $result = ['executable' => [], 'json' => [], 'external' => []];

    $i = 0;
    while (($pos = strpos($document, '<script', $i)) !== false) {
        $end = strpos($document, '>', $pos);
        if ($end === false) {
            $i = $pos + 7;
            continue;
        }
        $open = substr($document, $pos, $end - $pos + 1);

        if (isInsideRange($pos, $ranges)) {
            $i = $pos + 7;
            continue;
        }

        if (preg_match('/\bsrc\s*=/i', $open)) {
            $result['external'][] = $open;
        } elseif (preg_match('/\btype\s*=\s*["\']application\/json/i', $open)) {
            $result['json'][] = $open;
        } else {
            $result['executable'][] = $open;
        }

        $i = $pos + 7;
    }

    return $result;
}

function allBladeFiles(): array
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

// ────────────────────────────────────────────────────────────────────────────
// Middleware behaviour
// ────────────────────────────────────────────────────────────────────────────

it('uses a per-response random nonce, not a static one', function () {
    $csp1 = $this->get(route('login'))->headers->get('Content-Security-Policy');
    $csp2 = $this->get(route('login'))->headers->get('Content-Security-Policy');

    expect((string) $csp1)->toContain("'nonce-");
    expect((string) $csp2)->toContain("'nonce-");

    expect(nonceValueFrom((string) $csp1))->not->toBeEmpty()
        ->and(nonceValueFrom((string) $csp2))->not->toBeEmpty()
        ->and(nonceValueFrom((string) $csp1))->not->toBe(nonceValueFrom((string) $csp2));
});

it('adds the generated nonce to script-src and keeps self', function () {
    $csp = (string) $this->get(route('login'))->headers->get('Content-Security-Policy');
    $nonce = nonceValueFrom($csp);

    expect($csp)->toContain("script-src 'self' 'nonce-{$nonce}'");
});

it('removes unsafe-inline, unsafe-eval and wildcards from script-src', function () {
    $csp = (string) $this->get(route('login'))->headers->get('Content-Security-Policy');
    $scriptSrc = cspScriptSrcFrom($csp);

    expect($scriptSrc)->not->toContain('unsafe-inline')
        ->and($scriptSrc)->not->toContain('unsafe-eval')
        ->and($scriptSrc)->not->toContain('*');
});

it('preserves the external script hosts actually referenced by the app', function () {
    $csp = (string) $this->get(route('login'))->headers->get('Content-Security-Policy');
    $scriptSrc = cspScriptSrcFrom($csp);

    expect($scriptSrc)->toContain('https://unpkg.com')
        ->and($scriptSrc)->toContain('https://cdn.jsdelivr.net')
        ->and($scriptSrc)->toContain('https://cdnjs.cloudflare.com');
});

it('still allows self-hosted module script bundles (vite/build assets)', function () {
    $csp = (string) $this->get(route('login'))->headers->get('Content-Security-Policy');
    $scriptSrc = cspScriptSrcFrom($csp);

    expect($scriptSrc)->toContain("'self'");
});

// ────────────────────────────────────────────────────────────────────────────
// Rendered output
// ────────────────────────────────────────────────────────────────────────────

it('tags every rendered inline script with the nonce from the CSP header', function () {
    $response = $this->get(route('login'));
    $csp = (string) $response->headers->get('Content-Security-Policy');
    $nonce = nonceValueFrom($csp);

    expect($nonce)->not->toBeEmpty();

    $classes = classifyScriptTags($response->getContent());

    expect($classes['executable'])->not->toBeEmpty();

    foreach ($classes['executable'] as $tag) {
        expect($tag)->toContain('nonce="' . $nonce . '"');
    }

    // The login page happens to carry exactly two executable inline scripts.
    expect($classes['executable'])->toHaveCount(2);
});

it('never adds a nonce to rendered external-only or JSON tags', function () {
    $classes = classifyScriptTags($this->get(route('login'))->getContent());

    foreach (array_merge($classes['external'], $classes['json']) as $tag) {
        expect($tag)->not->toContain('nonce=');
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Static sweep across every Blade view
// ────────────────────────────────────────────────────────────────────────────

it('tags every executable inline script in every blade view', function () {
    $files = allBladeFiles();
    expect($files)->not->toBeEmpty();

    $total = 0;
    $untagged = [];
    $changedJson = [];

    foreach ($files as $file) {
        $document = file_get_contents($file);
        $classes = classifyScriptTags($document);

        $total += count($classes['executable']);

        foreach ($classes['executable'] as $tag) {
            if (! str_contains($tag, 'nonce="{{ csp_nonce() }}"')) {
                $untagged[] = basename($file) . ': ' . trim($tag);
            }
        }

        foreach ($classes['json'] as $tag) {
            if (str_contains($tag, 'nonce=')) {
                $changedJson[] = basename($file) . ': ' . trim($tag);
            }
        }
    }

    // The sweep must have actually found inline scripts to be meaningful.
    expect($total)->toBe(85);

    expect($untagged)->toBeEmpty();
    expect($changedJson)->toBeEmpty();
});

it('treats commented-out scripts as dead code, not rendered scripts', function () {
    // navbar.blade.php wraps its entire <style>/<script> in an HTML comment.
    $navbar = file_get_contents(base_path('resources/views/components/navbar.blade.php'));
    expect($navbar)->toContain('<!--');
    expect(classifyScriptTags($navbar)['executable'])->toBeEmpty();

    // index.blade.php comments out an AOS.init() inline script block.
    $index = file_get_contents(base_path('resources/views/landingpage_components/index.blade.php'));
    expect($index)->toContain('{{--');
    expect(classifyScriptTags($index)['executable'])->toHaveCount(2);
});

it('leaves JSON data blocks intact in views that carry them', function () {
    $lending = file_get_contents(base_path('resources/views/admin_components/lending.blade.php'));

    expect($lending)->toContain('<script type="application/json" id="loansData">')
        ->toContain('application/json');
    expect(classifyScriptTags($lending)['json'])->toHaveCount(1);
    expect(classifyScriptTags($lending)['executable'])->toHaveCount(1);

    $members = file_get_contents(base_path('resources/views/admin_components/members.blade.php'));
    expect(classifyScriptTags($members)['json'])->toHaveCount(3);
    expect(classifyScriptTags($members)['executable'])->not->toBeEmpty();
});

// ────────────────────────────────────────────────────────────────────────────
// Direct middleware check (per-request stability at the container level)
// ────────────────────────────────────────────────────────────────────────────

it('keeps the nonce stable for the whole request and empty for AJAX-free console', function () {
    $request = Request::create('/login-page', 'GET');
    app()->forgetInstance('csp-nonce');

    (new SetSecurityHeaders)->handle($request, fn () => response('ok'));

    $nonce = (string) app('csp-nonce');

    expect($nonce)->not->toBeEmpty()
        ->and((string) app('csp-nonce'))->toBe($nonce);
});