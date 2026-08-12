<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipPurchase;
use App\Models\User;
use App\Services\PaymentGateways\PaymentProviderFactory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class MembershipPurchaseService
{
    /**
     * Process a membership purchase for a consumer.
     *
     * @param User $user
     * @param Membership $membership
     * @param string $billingCycle
     * @return MembershipPurchase
     * @throws \Exception
     */
    public function purchase(User $user, Membership $membership, string $billingCycle): MembershipPurchase
    {
        if (! in_array($billingCycle, ['monthly', 'yearly'])) {
            throw new InvalidArgumentException("Invalid billing cycle.");
        }

        if ($membership->tenant_id !== $user->tenant_id) {
            abort(404, "Membership not found.");
        }

        if ($membership->status->value !== 'active') { // Assuming MembershipStatus is an enum
            abort(400, "This membership is not active.");
        }

        return DB::transaction(function () use ($user, $membership, $billingCycle) {
            // Lock the membership row for update to prevent concurrent free limit bypass
            $lockedMembership = Membership::where('id', $membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Count existing free allocations
            $allocatedFreeCount = MembershipPurchase::where('membership_id', $lockedMembership->id)
                ->where('amount', 0) // Free allocations have 0 price
                ->count();

            $isFreeAllocation = $allocatedFreeCount < $lockedMembership->free_membership_limit;
            $price = 0.00;

            if (! $isFreeAllocation) {
                $price = $billingCycle === 'monthly' 
                    ? (float) $lockedMembership->monthly_price 
                    : (float) $lockedMembership->yearly_price;
            }

            $paymentId = null;
            $paymentProvider = null;
            $status = 'paid';

            // If it's a paid transaction, process payment
            if ($price > 0) {
                // Resolve the tenant's gateway
                $tenant = $lockedMembership->tenant;
                $gateway = PaymentProviderFactory::make($tenant);

                // Charge the customer
                $paymentResult = $gateway->createPayment([
                    'user_id' => $user->id,
                    'membership_id' => $lockedMembership->id,
                    'amount' => $price,
                ]);

                if (! $paymentResult->success) {
                    throw new RuntimeException("Payment failed: " . ($paymentResult->error ?? 'Unknown error'));
                }

                $paymentId = $paymentResult->paymentId;
                
                // Get provider name
                $config = $tenant->paymentConfigs()->where('is_active', true)->first();
                $paymentProvider = $config ? $config->provider : null;
                $status = $paymentResult->status;
            }

            // Record the purchase
            $purchase = MembershipPurchase::create([
                'tenant_id' => $lockedMembership->tenant_id,
                'consumer_id' => $user->id,
                'membership_id' => $lockedMembership->id,
                'payment_id' => $paymentId,
                'payment_provider' => $paymentProvider,
                'billing_cycle' => $billingCycle,
                'amount' => $price,
                'currency' => 'USD',
                'status' => $status,
                'purchased_at' => now(),
            ]);

            return $purchase;
        });
    }

    /**
     * Get paginated purchases for an admin.
     */
    public function getAdminPurchases(int $tenantId, array $filters = [], int $perPage = 15)
    {
        $query = MembershipPurchase::where('tenant_id', $tenantId)->with(['consumer', 'membership']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('consumer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['membership_id'])) {
            $query->where('membership_id', $filters['membership_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('purchased_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('purchased_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }
}
