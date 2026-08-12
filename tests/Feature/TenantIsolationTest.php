<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An admin from Tenant A cannot view or manage memberships of Tenant B.
     */
    public function test_admin_cannot_access_other_tenant_memberships(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $adminA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'admin',
        ]);

        $membershipB = Membership::factory()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Tenant B Premium',
        ]);

        $response = $this->actingAs($adminA)->getJson('/api/admin/memberships/' . $membershipB->id);

        // ModelNotFoundException caught and converted to 404 by our standard API response handler
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);

        $updateResponse = $this->actingAs($adminA)->putJson('/api/admin/memberships/' . $membershipB->id, [
            'name' => 'Hacked Name',
        ]);

        $updateResponse->assertStatus(404);
    }

    /**
     * A consumer from Tenant A cannot view memberships from Tenant B.
     */
    public function test_consumer_only_sees_own_tenant_memberships(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $consumerA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'consumer',
        ]);

        Membership::factory()->create(['tenant_id' => $tenantA->id, 'name' => 'Tenant A Basic']);
        Membership::factory()->create(['tenant_id' => $tenantB->id, 'name' => 'Tenant B Basic']);

        $response = $this->actingAs($consumerA)->getJson('/api/consumer/memberships');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Tenant A Basic', $response->json('data.0.name'));
    }
    public function test_admin_cannot_view_other_tenant_purchases(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $adminA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'admin',
        ]);
        
        // Give adminA the purchase.view permission via role
        $role = \App\Models\Role::factory()->create(['tenant_id' => $tenantA->id]);
        $perm = \App\Models\Permission::firstOrCreate(['slug' => 'purchase.view']);
        $role->permissions()->sync([$perm->id]);
        $adminA->roles()->sync([$role->id]);

        $membershipB = Membership::factory()->create(['tenant_id' => $tenantB->id]);
        $consumerB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => 'consumer']);

        \App\Models\MembershipPurchase::create([
            'tenant_id' => $tenantB->id,
            'consumer_id' => $consumerB->id,
            'membership_id' => $membershipB->id,
            'billing_cycle' => 'monthly',
            'amount' => 10,
            'currency' => 'USD',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($adminA)->getJson('/api/admin/membership-purchases');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.data'); // It paginate so data.data should be 0
    }
}
