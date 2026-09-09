<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

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
                'name'      => 'Auth Test Agent',
                'username'  => 'authagent',
                'password'  => bcrypt('password123'),
                'role'      => 'agent',
                'status'    => 'offline',
            ]
        );
    }

    /**
     * Test login using EMAIL via API
     */
    public function testLoginWithEmailSucceeds()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'authtest@indraco.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => [
                        'email'    => 'authtest@indraco.com',
                        'username' => 'authagent',
                        'role'     => 'agent',
                    ]
                ]
            ]);
    }

    /**
     * Test login using USERNAME via API
     */
    public function testLoginWithUsernameSucceeds()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'authagent',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => [
                        'email'    => 'authtest@indraco.com',
                        'username' => 'authagent',
                        'role'     => 'agent',
                    ]
                ]
            ]);
    }

    /**
     * Test login using explicit 'username' JSON key
     */
    public function testLoginWithExplicitUsernameKeySucceeds()
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
                    ]
                ]
            ]);
    }

    /**
     * Test login with incorrect password returns 401
     */
    public function testLoginWithWrongPasswordFails()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'authagent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error'   => [
                    'code' => 'INVALID_CREDENTIALS',
                ]
            ]);
    }

    /**
     * Test login with non-existent username returns 401
     */
    public function testLoginWithNonExistentUserFails()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'nonexistent_user',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error'   => [
                    'code' => 'INVALID_CREDENTIALS',
                ]
            ]);
    }

    /**
     * Test Web Session Login with username
     */
    public function testWebLoginWithUsernameRedirects()
    {
        $response = $this->post('/login', [
            'login'    => 'authagent',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/preview.html');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test Web Session Login with email
     */
    public function testWebLoginWithEmailRedirects()
    {
        $response = $this->post('/login', [
            'login'    => 'authtest@indraco.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/preview.html');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test GET /api/v1/auth/me returns authenticated user
     */
    public function testGetMeProfile()
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-User-Id', $this->user->id)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => [
                        'id'       => $this->user->id,
                        'username' => 'authagent',
                        'email'    => 'authtest@indraco.com',
                    ]
                ]
            ]);
    }
}
