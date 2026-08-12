<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed the application's tenants and a default admin/consumer per tenant.
     */
    public function run(): void
    {
        $tenants = [
            ['name' => 'Tenant A', 'slug' => 'tenant-a', 'description' => 'First demonstration tenant.'],
            ['name' => 'Tenant B', 'slug' => 'tenant-b', 'description' => 'Second demonstration tenant.'],
            ['name' => 'Tenant C', 'slug' => 'tenant-c', 'description' => 'Third demonstration tenant.'],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::firstOrCreate(['slug' => $data['slug']], $data);

            User::firstOrCreate(
                ['email' => "admin@{$tenant->slug}.test"],
                [
                    'tenant_id' => $tenant->id,
                    'role' => UserRole::Admin,
                    'name' => ucfirst($tenant->slug).' Admin',
                    'password' => 'password',
                ]
            );

            User::firstOrCreate(
                ['email' => "consumer@{$tenant->slug}.test"],
                [
                    'tenant_id' => $tenant->id,
                    'role' => UserRole::Consumer,
                    'name' => ucfirst($tenant->slug).' Consumer',
                    'password' => 'password',
                ]
            );
        }
    }
}
