<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Http\Requests\Membership\UpdateMembershipRequest;
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
     * List memberships belonging to the authenticated admin's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('membership.view');

        $filters = $request->only(['search', 'status', 'sort_by', 'sort_dir']);
        $perPage = (int) $request->input('per_page', 15);

        $memberships = $this->membershipService->getMembershipsForTenant($request->user()->tenant_id, $filters, $perPage);

        return response()->json($memberships);
    }

    /**
     * Create a membership for the authenticated admin's tenant.
     */
    public function store(StoreMembershipRequest $request): JsonResponse
    {
        $this->authorize('membership.create');

        $membership = $this->membershipService->createMembership(
            $request->user()->tenant_id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Membership created.',
            'membership' => $membership,
        ], 201);
    }

    /**
     * Show a single membership scoped to the admin's tenant.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorize('membership.view');

        $membership = $this->membershipService->getMembershipForTenant($request->user()->tenant_id, $id);

        return response()->json(['membership' => $membership]);
    }

    /**
     * Update a membership scoped to the admin's tenant.
     */
    public function update(UpdateMembershipRequest $request, int $id): JsonResponse
    {
        $this->authorize('membership.update');

        $membership = $this->membershipService->updateMembership(
            $request->user()->tenant_id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Membership updated.',
            'membership' => $membership,
        ]);
    }

    /**
     * Delete a membership scoped to the admin's tenant.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('membership.delete');

        $this->membershipService->deleteMembership($request->user()->tenant_id, $id);

        return response()->json(['message' => 'Membership deleted.']);
    }
}
