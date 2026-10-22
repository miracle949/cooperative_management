<?php

use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Http\Request;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php. These tests only exercise config, middleware,
// cookies and git ignores, so no tables are required.

function sessionCookieFrom($response)
{
    $httpResponse = $response instanceof TestResponse ? $response->baseResponse : $response;

    return collect($httpResponse->headers->getCookies())
        ->first(fn ($cookie) => str_contains($cookie->getName(), '-session'));
}

function isGitIgnored(string $path): bool
{
    $code = 0;
    exec('git check-ignore -q ' . escapeshellarg($path) . ' 2>&1', $output, $code);

    return $code === 0;
}

// ────────────────────────────────────────────────────────────────────────────
// HTTPS enforcement (production only)
// ────────────────────────────────────────────────────────────────────────────

it('does not redirect local HTTP in non-production environments', function () {
    config()->set('app.env', 'testing');

    $this->get('/login-page')->assertOk();
});

it('redirects HTTP to HTTPS with a 301 when APP_ENV is production', function () {
    config()->set('app.env', 'production');

    $response = $this->get('/login-page');

    $response->assertStatus(301);
    expect($response->headers->get('Location'))->toStartWith('https://');
});

it('does not redirect HTTPS requests in production', function () {
    config()->set('app.env', 'production');
    $request = Request::create('https://localhost/login-page', 'GET');

    $response = (new ForceHttps)->handle($request, fn () => response('ok'));

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('ok');
});

// ────────────────────────────────────────────────────────────────────────────
// HSTS
// ────────────────────────────────────────────────────────────────────────────

it('sends HSTS over HTTPS', function () {
    config()->set('app.env', 'production');
    $request = Request::create('https://localhost/login-page', 'GET');

    $response = (new SetSecurityHeaders)->handle($request, fn () => response('ok'));

    expect($response->headers->get('Strict-Transport-Security'))->toContain('max-age=')
        ->and($response->headers->get('Strict-Transport-Security'))->toContain('includeSubDomains');
});

it('never sends HSTS over plain HTTP', function () {
    $request = Request::create('http://localhost/login-page', 'GET');

    $response = (new SetSecurityHeaders)->handle($request, fn () => response('ok'));

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

// ────────────────────────────────────────────────────────────────────────────
// Session cookie flags
// ────────────────────────────────────────────────────────────────────────────

it('keeps httpOnly and lax SameSite on the session cookie for local HTTP', function () {
    config()->set('session.secure', false);

    $cookie = sessionCookieFrom($this->get('/login-page'));

    expect($cookie)->not->toBeNull()
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax')
        ->and($cookie->isSecure())->toBeFalse();
});

it('marks the session cookie Secure when secure cookies are enabled (production)', function () {
    config()->set('session.secure', true);

    $cookie = sessionCookieFrom($this->get('/login-page'));

    expect($cookie)->not->toBeNull()
        ->and($cookie->isSecure())->toBeTrue()
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax');
});

it('defaults the session Secure flag off for non-production environments', function () {
    config()->set('app.env', 'testing');

    expect(env('SESSION_SECURE_COOKIE', config('app.env') === 'production'))->toBeFalse()
        ->and(config('session.secure'))->toBeFalse();
});

it('defaults the session Secure flag on for production', function () {
    config()->set('app.env', 'production');

    expect(env('SESSION_SECURE_COOKIE', config('app.env') === 'production'))->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Secrets hygiene
// ────────────────────────────────────────────────────────────────────────────

it('git-ignores .env, backups and log files', function () {
    expect(isGitIgnored('.env'))->toBeTrue();
    expect(isGitIgnored('.env.backup'))->toBeTrue();
    expect(isGitIgnored('.env.production'))->toBeTrue();
    expect(isGitIgnored('storage/logs/laravel.log'))->toBeTrue();
});

it('keeps .env.example free of real secrets', function () {
    $content = file_get_contents(base_path('.env.example'));

    foreach (['MAIL_PASSWORD=', 'DB_PASSWORD=', 'REDIS_PASSWORD=', 'AWS_SECRET_ACCESS_KEY='] as $key) {
        expect((bool) preg_match('/^' . preg_quote($key, '/') . '\s*=\s*\S+/m', $content))
            ->toBeFalse("{$key} must not carry a non-empty value in .env.example");
    }
});