<?php

namespace Tests\Feature;

use App\Models\MembershipPurchase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify unauthenticated users get 401 instead of a crash.
     */
    public function test_authentication_is_enforced(): void
    {
        $response = $this->getJson('/api/admin/memberships');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Verify consumers cannot access admin routes.
     */
    public function test_authorization_prevents_consumers_from_admin_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $consumer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'consumer',
        ]);

        $response = $this->actingAs($consumer)->getJson('/api/admin/memberships');
        
        // EnsureUserIsAdmin middleware might abort with 403 or similar
        $response->assertStatus(403);
    }

    /**
     * Verify input validation format.
     */
    public function test_input_validation_returns_proper_format(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        // Submit empty payload, violating required fields
        $response = $this->actingAs($admin)->postJson('/api/admin/memberships', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
            
        $this->assertFalse($response->json('success'));
        $this->assertEquals('Validation failed.', $response->json('message'));
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Verify webhook signature checking.
     */
    public function test_webhook_signature_verification(): void
    {
        // Without signature
        $response = $this->postJson('/api/webhooks/payments/stripe', [
            'payment_id' => 'pay_123',
            'status' => 'paid'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid signature',
            ]);

        // With mock signature
        $response = $this->withHeaders([
            'X-Signature' => 'mock-signature'
        ])->postJson('/api/webhooks/payments/stripe', [
            'payment_id' => 'pay_123',
            'status' => 'paid'
        ]);

        // Returns 404 because the purchase doesn't exist, but signature passed
        $response->assertStatus(404);
    }
}
