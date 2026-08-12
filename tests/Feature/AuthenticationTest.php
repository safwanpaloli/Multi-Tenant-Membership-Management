<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'email', 'role']
                ]
            ]);
    }

    public function test_consumer_can_register(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'demo-tenant']);

        $response = $this->postJson('/api/consumer/register', [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'tenant_slug' => 'demo-tenant',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.email', 'john@test.com')
            ->assertJsonPath('data.user.role', 'consumer');

        $this->assertDatabaseHas('users', [
            'email' => 'john@test.com',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function test_consumer_can_login(): void
    {
        $tenant = Tenant::factory()->create();
        $consumer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'consumer',
            'email' => 'consumer@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/consumer/login', [
            'email' => 'consumer@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.role', 'consumer');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        // Rate limiting or auth exception -> either 401 or 422 depending on implementation
        // Our customized exception handler turns ValidationExceptions into 422
        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }
}
