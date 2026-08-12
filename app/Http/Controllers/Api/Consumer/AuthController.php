<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConsumerLoginRequest;
use App\Http\Requests\Auth\ConsumerRegisterRequest;
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
     * Register a new consumer within the given tenant.
     */
    public function register(ConsumerRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->registerConsumer($request->validated());

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
                'role' => $result['user']->role,
                'tenant_id' => $result['user']->tenant_id,
            ],
        ], 201);
    }

    /**
     * Authenticate a consumer and return an API token.
     */
    public function login(ConsumerLoginRequest $request): JsonResponse
    {
        $result = $this->authService->authenticateConsumer($request->validated());

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
