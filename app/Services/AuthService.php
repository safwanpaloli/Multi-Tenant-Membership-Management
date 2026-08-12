<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepository $userRepository;
    protected TenantRepository $tenantRepository;

    public function __construct(UserRepository $userRepository, TenantRepository $tenantRepository)
    {
        $this->userRepository = $userRepository;
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * Authenticate an Admin user and return a token.
     *
     * @throws ValidationException
     */
    public function authenticateAdmin(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Authenticate a Consumer user and return a token.
     *
     * @throws ValidationException
     */
    public function authenticateConsumer(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password) || $user->role !== UserRole::Consumer) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('consumer')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Register a new Consumer user.
     */
    public function registerConsumer(array $data): array
    {
        $tenant = $this->tenantRepository->findBySlugOrFail($data['tenant_slug']);

        $user = $this->userRepository->createConsumer($tenant->id, $data);

        $token = $user->createToken('consumer')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
