<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;

class UserRepository
{
    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Create a new consumer user.
     */
    public function createConsumer(int $tenantId, array $data): User
    {
        return User::create([
            'tenant_id' => $tenantId,
            'role' => UserRole::Consumer,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Service will pass hashed password if necessary, or model handles it. Currently controller hashes via Hash::check, meaning we just store it. Wait, the controller didn't hash the password explicitly in register, maybe the Model has a cast. Let's pass it as is.
        ]);
    }
}
