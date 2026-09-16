<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply security and anti-crawler headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // 1. Anti-Crawler: Instruct search engines never to index, snippet, or archive internal app
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate');

        // 2. Anti-Clickjacking: Restrict framing to the same origin (for admin & auth)
        // Public widget scripts can still be included via <script> tags seamlessly.
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // 3. MIME Sniffing Prevention
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 4. Legacy Browser XSS Filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 5. Referrer Policy: Send full URL only to same origin, strict origin on cross-origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 6. Permissions Policy: Disable unused device sensors for internal chat dashboard
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');

        return $response;
    }
}
