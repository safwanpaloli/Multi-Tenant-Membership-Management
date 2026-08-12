<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setupAdmin(Tenant $tenant): User
    {
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        $role = Role::factory()->create(['tenant_id' => $tenant->id]);
        $perms = ['membership.view', 'membership.create', 'membership.update', 'membership.delete'];
        $ids = [];
        foreach ($perms as $p) {
            $ids[] = Permission::firstOrCreate(['slug' => $p])->id;
        }
        $role->permissions()->sync($ids);
        $admin->roles()->sync([$role->id]);

        return $admin;
    }

    public function test_membership_crud_works(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = $this->setupAdmin($tenant);

        // CREATE
        $response = $this->actingAs($admin)->postJson('/api/admin/memberships', [
            'name' => 'Gold',
            'price' => 100,
            'free_membership_limit' => 10,
            'status' => 'active',
        ]);
        $response->assertStatus(201);
        $id = $response->json('data.membership.id');

        // READ
        $readResponse = $this->actingAs($admin)->getJson('/api/admin/memberships/' . $id);
        $readResponse->assertStatus(200)->assertJsonPath('data.membership.name', 'Gold');

        // UPDATE
        $updateResponse = $this->actingAs($admin)->putJson('/api/admin/memberships/' . $id, [
            'name' => 'Platinum',
        ]);
        $updateResponse->assertStatus(200)->assertJsonPath('data.membership.name', 'Platinum');

        // DELETE
        $deleteResponse = $this->actingAs($admin)->deleteJson('/api/admin/memberships/' . $id);
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('memberships', ['id' => $id]);
    }

    public function test_validation_works(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = $this->setupAdmin($tenant);

        // Missing name and price
        $response = $this->actingAs($admin)->postJson('/api/admin/memberships', [
            'status' => 'active',
        ]);
        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_inactive_memberships_cannot_be_purchased(): void
    {
        $tenant = Tenant::factory()->create();
        $membership = Membership::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'inactive',
        ]);

        $consumer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'consumer',
        ]);

        $response = $this->actingAs($consumer)->postJson('/api/consumer/memberships/' . $membership->id . '/purchase', [
            'billing_cycle' => 'monthly',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'This membership is not active.');
    }
}
