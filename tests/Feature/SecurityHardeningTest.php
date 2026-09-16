<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    /**
     * Test 1: robots.txt disallows all search engine crawlers
     */
    public function testRobotsTxtBlocksAllCrawlers()
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /', $content);
        $this->assertStringContainsString('User-agent: Googlebot', $content);
    }

    /**
     * Test 2: Web responses include X-Robots-Tag anti-indexing header
     */
    public function testXRobotsTagHeaderPresentOnWebResponses()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate');
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate">', $response->getContent());
        $this->assertStringContainsString('<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, noimageindex">', $response->getContent());
    }

    /**
     * Test 3: Anti-Clickjacking (X-Frame-Options: SAMEORIGIN)
     */
    public function testAntiClickjackingHeaderPresent()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Test 4: MIME Sniffing & XSS Protection Headers
     */
    public function testMimeSniffingAndXssHeadersPresent()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test 5: Login endpoint has rate limiting (throttle)
     */
    public function testLoginEndpointHasRateLimiting()
    {
        $this->withMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]);

        $response = null;
        for ($i = 0; $i < 18; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'login'    => 'random_unregistered_' . $i . '@example.com',
                'password' => 'wrongpass',
            ]);

            if ($response->status() === 429) {
                break;
            }
        }

        $this->assertEquals(429, $response->status(), 'Login endpoint should enforce rate limiting (HTTP 429 Too Many Requests) after threshold.');
    }
}
