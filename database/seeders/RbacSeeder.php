<?php

namespace Database\Seeders;

use App\Enums\PermissionSlug;
use App\Enums\RoleSlug;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Seed the permission catalog and per-tenant roles/assignments.
     */
    public function run(): void
    {
        $permissions = [
            PermissionSlug::MembershipView->value => 'View memberships',
            PermissionSlug::MembershipCreate->value => 'Create memberships',
            PermissionSlug::MembershipUpdate->value => 'Update memberships',
            PermissionSlug::MembershipDelete->value => 'Delete memberships',
            PermissionSlug::PurchaseView->value => 'View membership purchases',
            PermissionSlug::TenantSettingsView->value => 'View tenant settings',
            PermissionSlug::TenantSettingsUpdate->value => 'Update tenant settings',
        ];

        $permissionModels = [];
        foreach ($permissions as $slug => $name) {
            $permissionModels[$slug] = Permission::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        }

        foreach (Tenant::all() as $tenant) {
            $superAdmin = Role::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => RoleSlug::SuperAdmin->value],
                ['name' => 'Super Admin', 'description' => 'Full access to every administrative operation.']
            );
            $superAdmin->permissions()->sync(array_values($permissionModels));

            $membershipManager = Role::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => RoleSlug::MembershipManager->value],
                ['name' => 'Membership Manager', 'description' => 'Manages memberships only.']
            );
            $membershipManager->permissions()->sync([
                $permissionModels[PermissionSlug::MembershipView->value],
                $permissionModels[PermissionSlug::MembershipCreate->value],
                $permissionModels[PermissionSlug::MembershipUpdate->value],
                $permissionModels[PermissionSlug::MembershipDelete->value],
            ]);

            $this->assignSuperAdmin($tenant, $superAdmin);
            $this->createMembershipManager($tenant, $membershipManager);
        }
    }

    private function assignSuperAdmin(Tenant $tenant, Role $role): void
    {
        $admin = User::where('email', "admin@{$tenant->slug}.test")->firstOrFail();
        $admin->roles()->syncWithoutDetaching([$role->id]);
    }

    private function createMembershipManager(Tenant $tenant, Role $role): void
    {
        $manager = User::firstOrCreate(
            ['email' => "membership_manager@{$tenant->slug}.test"],
            [
                'tenant_id' => $tenant->id,
                'role' => UserRole::Admin,
                'name' => $tenant->name.' Membership Manager',
                'password' => 'password',
            ]
        );

        $manager->roles()->syncWithoutDetaching([$role->id]);
    }
}
