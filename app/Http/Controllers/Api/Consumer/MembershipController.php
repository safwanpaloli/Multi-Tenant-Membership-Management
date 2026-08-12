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

    /**
     * Purchase a membership.
     */
    public function purchase(
        \App\Http\Requests\Membership\PurchaseMembershipRequest $request,
        \App\Models\Membership $membership,
        \App\Services\MembershipPurchaseService $purchaseService
    ): JsonResponse {
        try {
            $subscription = $purchaseService->purchase(
                $request->user(),
                $membership,
                $request->validated('billing_cycle')
            );

            return response()->json([
                'message' => 'Membership purchased successfully.',
                'subscription' => $subscription,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to process purchase: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the consumer's purchase history.
     */
    public function purchases(Request $request): JsonResponse
    {
        $purchases = \App\Models\MembershipPurchase::with('membership')
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->get();

        $data = $purchases->map(function ($purchase) {
            return [
                'membership' => [
                    'id' => $purchase->membership->id,
                    'name' => $purchase->membership->name,
                ],
                'billing_cycle' => $purchase->billing_cycle,
                'amount' => (float) $purchase->amount,
                'status' => $purchase->status,
                'purchased_at' => $purchase->purchased_at ? $purchase->purchased_at->toIso8601String() : null,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
