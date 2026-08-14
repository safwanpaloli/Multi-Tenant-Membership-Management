<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\MembershipPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipPurchaseController extends Controller
{
    protected MembershipPurchaseService $purchaseService;

    public function __construct(MembershipPurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * List membership purchases for the admin's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'membership_id', 'date_from', 'date_to']);
        
        $purchases = $this->purchaseService->getAdminPurchases(
            $request->user()->tenant_id,
            $filters,
            $request->input('per_page', 15)
        );

        return $this->successResponse('Purchases retrieved successfully.', $purchases);
    }

    /**
     * Show a single membership purchase for the admin's tenant.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $purchase = \App\Models\MembershipPurchase::with(['membership', 'consumer'])
            ->where('id', $id)
            ->whereHas('membership', function ($query) use ($request) {
                $query->where('tenant_id', $request->user()->tenant_id);
            })
            ->first();

        if (! $purchase) {
            return $this->errorResponse('Purchase not found.', [], 404);
        }

        return $this->successResponse('Purchase retrieved successfully.', $purchase);
    }
}
