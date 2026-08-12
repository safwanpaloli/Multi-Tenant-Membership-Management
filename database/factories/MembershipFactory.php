<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'benefits' => [fake()->sentence(), fake()->sentence(), fake()->sentence()],
            'price' => fake()->randomFloat(2, 5, 200),
            'monthly_price' => fake()->randomFloat(2, 5, 100),
            'yearly_price' => fake()->randomFloat(2, 50, 1000),
            'free_membership_limit' => fake()->numberBetween(0, 10),
            'status' => MembershipStatus::Active,
        ];
    }
}
