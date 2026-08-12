<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authenticate a tenant administrator and return an API token.
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $result = $this->authService->authenticateAdmin($request->validated());

        return response()->json([
            'message' => 'Login successful.',
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
                'tenant_id' => $result['user']->tenant_id,
            ],
        ]);
    }
}
