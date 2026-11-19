<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected $tenant;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::firstOrCreate(
            ['slug' => 'auth-test-tenant'],
            ['name' => 'Auth Test Tenant', 'plan' => 'enterprise']
        );

        $this->user = User::firstOrCreate(
            ['email' => 'authtest@indraco.com'],
            [
                'tenant_id' => $this->tenant->id,
                'name' => 'Auth Test Agent',
                'username' => 'authagent',
                'password' => bcrypt('password123'),
                'role' => 'agent',
                'status' => 'offline',
            ]
        );
    }

    /**
     * Test login using EMAIL via API
     */
    public function test_login_with_email_succeeds()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'authtest@indraco.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'authtest@indraco.com',
                        'username' => 'authagent',
                        'role' => 'agent',
                    ],
                ],
            ]);
    }

    /**
     * Test login using USERNAME via API
     */
    public function test_login_with_username_succeeds()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'authagent',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'authtest@indraco.com',
                        'username' => 'authagent',
                        'role' => 'agent',
                    ],
                ],
            ]);
    }

    /**
     * Test login using explicit 'username' JSON key
     */
    public function test_login_with_explicit_username_key_succeeds()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'authagent',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'username' => 'authagent',
                    ],
                ],
            ]);
    }

    /**
     * Test login with incorrect password returns 401
     */
    public function test_login_with_wrong_password_fails()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'authagent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                ],
            ]);
    }

    /**
     * Test login with non-existent username returns 401
     */
    public function test_login_with_non_existent_user_fails()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'nonexistent_user',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                ],
            ]);
    }

    /**
     * Test Web Session Login with username
     */
    public function test_web_login_with_username_redirects()
    {
        $response = $this->post('/login', [
            'login' => 'authagent',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.inbox'));
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test Web Session Login with email
     */
    public function test_web_login_with_email_redirects()
    {
        $response = $this->post('/login', [
            'login' => 'authtest@indraco.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.inbox'));
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test GET /api/v1/auth/me returns authenticated user
     */
    public function test_get_me_profile()
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-User-Id', $this->user->id)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $this->user->id,
                        'username' => 'authagent',
                        'email' => 'authtest@indraco.com',
                    ],
                ],
            ]);
    }
}
