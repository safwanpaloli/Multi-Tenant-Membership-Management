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

        return response()->json($purchases);
    }
}
