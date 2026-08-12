<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setupRolesAndPermissions(Tenant $tenant): array
    {
        $viewPerm = Permission::firstOrCreate(['slug' => 'membership.view']);
        $createPerm = Permission::firstOrCreate(['slug' => 'membership.create']);
        $deletePerm = Permission::firstOrCreate(['slug' => 'membership.delete']);

        $managerRole = Role::firstOrCreate([
            'tenant_id' => $tenant->id, 
            'slug' => RoleSlug::MembershipManager->value,
            'name' => 'Manager'
        ]);
        $managerRole->permissions()->sync([$viewPerm->id, $createPerm->id]); // No delete

        $basicRole = Role::firstOrCreate([
            'tenant_id' => $tenant->id,
            'slug' => 'basic-admin',
            'name' => 'Basic'
        ]);
        $basicRole->permissions()->sync([$viewPerm->id]); // Only view

        return [$managerRole, $basicRole];
    }

    public function test_authorized_admin_can_create_membership(): void
    {
        $tenant = Tenant::factory()->create();
        [$managerRole, $basicRole] = $this->setupRolesAndPermissions($tenant);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);
        $admin->roles()->sync([$managerRole->id]);

        $response = $this->actingAs($admin)->postJson('/api/admin/memberships', [
            'name' => 'New Premium',
            'price' => 10,
            'free_membership_limit' => 5,
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('memberships', ['name' => 'New Premium']);
    }

    public function test_unauthorized_admin_cannot_create_membership(): void
    {
        $tenant = Tenant::factory()->create();
        [$managerRole, $basicRole] = $this->setupRolesAndPermissions($tenant);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);
        $admin->roles()->sync([$basicRole->id]);

        $response = $this->actingAs($admin)->postJson('/api/admin/memberships', [
            'name' => 'New Premium',
            'price' => 10,
            'free_membership_limit' => 5,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_admin_cannot_delete_membership(): void
    {
        $tenant = Tenant::factory()->create();
        [$managerRole, $basicRole] = $this->setupRolesAndPermissions($tenant);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);
        $admin->roles()->sync([$managerRole->id]); // Manager can create, but not delete in our setup

        $membership = \App\Models\Membership::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($admin)->deleteJson('/api/admin/memberships/' . $membership->id);

        $response->assertStatus(403);
    }
}
