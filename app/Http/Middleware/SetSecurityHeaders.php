<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // One cryptographically-random nonce per request, stable for the
        // entire request lifetime so every rendered inline script can use it.
        $nonce = base64_encode(random_bytes(18));
        app()->instance('csp-nonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'nonce-{$nonce}' blob: http://127.0.0.1:* http://localhost:* https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "style-src 'self' 'unsafe-inline' http://127.0.0.1:* http://localhost:* https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; " .
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: blob:; " .
            "connect-src 'self' ws: wss: http://127.0.0.1:* http://localhost:*;"
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}