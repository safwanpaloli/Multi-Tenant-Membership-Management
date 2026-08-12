<?php

namespace Tests\Feature;

use App\Models\MembershipPurchase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_webhooks_are_handled_safely(): void
    {
        $tenant = Tenant::factory()->create();
        
        $purchase = MembershipPurchase::create([
            'tenant_id' => $tenant->id,
            'consumer_id' => 1,
            'membership_id' => 1,
            'payment_id' => 'pay_abc',
            'payment_provider' => 'stripe',
            'status' => 'pending',
            'amount' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
        ]);

        // First webhook
        $response1 = $this->withHeaders(['X-Signature' => 'mock-signature'])
            ->postJson('/api/webhooks/payments/stripe', [
                'payment_id' => 'pay_abc',
                'status' => 'paid',
            ]);
        
        $response1->assertStatus(200);
        $this->assertDatabaseHas('membership_purchases', ['id' => $purchase->id, 'status' => 'paid']);

        // Duplicate webhook
        $response2 = $this->withHeaders(['X-Signature' => 'mock-signature'])
            ->postJson('/api/webhooks/payments/stripe', [
                'payment_id' => 'pay_abc',
                'status' => 'paid',
            ]);

        $response2->assertStatus(200); // Should safely return 200 without side effects
        
        // Assert the state didn't change unexpectedly
        $this->assertDatabaseHas('membership_purchases', ['id' => $purchase->id, 'status' => 'paid']);
    }
}
