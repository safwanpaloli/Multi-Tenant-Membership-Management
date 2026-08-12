<?php

namespace Database\Seeders;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    /**
     * Seed sample memberships for every tenant.
     */
    public function run(): void
    {
        foreach (Tenant::all() as $tenant) {
            Membership::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Basic Membership'],
                [
                    'description' => 'Entry-level access for new members.',
                    'benefits' => ['Access to standard content', 'Community support'],
                    'price' => 0,
                    'monthly_price' => null,
                    'yearly_price' => null,
                    'free_membership_limit' => 5,
                    'status' => MembershipStatus::Active,
                ]
            );

            Membership::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Premium Membership'],
                [
                    'description' => 'Full access with extra benefits.',
                    'benefits' => ['Access to premium content', '10% discount', 'Priority support'],
                    'price' => 99.99,
                    'monthly_price' => 9.99,
                    'yearly_price' => 99.00,
                    'free_membership_limit' => 0,
                    'status' => MembershipStatus::Active,
                ]
            );
        }
    }
}
