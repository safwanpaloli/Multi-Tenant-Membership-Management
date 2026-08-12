<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConsumerMembershipResource;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    protected MembershipService $membershipService;

    public function __construct(MembershipService $membershipService)
    {
        $this->membershipService = $membershipService;
    }

    /**
     * List available memberships for the consumer's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $memberships = $this->membershipService->getMembershipsForTenant($request->user()->tenant_id);

        return response()->json([
            'data' => ConsumerMembershipResource::collection($memberships->items()),
        ]);
    }
}
