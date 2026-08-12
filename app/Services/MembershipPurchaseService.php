<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentGateways\PaymentGatewayFactory;
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
     * @return Subscription
     * @throws \Exception
     */
    public function purchase(User $user, Membership $membership, string $billingCycle): Subscription
    {
        if (! in_array($billingCycle, ['monthly', 'yearly'])) {
            throw new InvalidArgumentException("Invalid billing cycle.");
        }

        if ($membership->tenant_id !== $user->tenant_id) {
            throw new InvalidArgumentException("Membership does not belong to your tenant.");
        }

        if ($membership->status->value !== 'active') { // Assuming MembershipStatus is an enum
            throw new InvalidArgumentException("This membership is not active.");
        }

        return DB::transaction(function () use ($user, $membership, $billingCycle) {
            // Lock the membership row for update to prevent concurrent free limit bypass
            $lockedMembership = Membership::where('id', $membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Count existing free allocations
            $allocatedFreeCount = Subscription::where('membership_id', $lockedMembership->id)
                ->where('is_free_allocation', true)
                ->count();

            $isFreeAllocation = $allocatedFreeCount < $lockedMembership->free_membership_limit;
            $price = 0.00;

            if (! $isFreeAllocation) {
                $price = $billingCycle === 'monthly' 
                    ? (float) $lockedMembership->monthly_price 
                    : (float) $lockedMembership->yearly_price;
            }

            // If it's a paid transaction, process payment
            $transactionId = null;
            if ($price > 0) {
                // Resolve the tenant's gateway
                $tenant = $lockedMembership->tenant;
                $gateway = PaymentGatewayFactory::makeForTenant($tenant);

                // Charge the customer
                $paymentResult = $gateway->charge($price, 'USD', [
                    'user_id' => $user->id,
                    'membership_id' => $lockedMembership->id,
                ]);

                if (! $paymentResult['success']) {
                    throw new RuntimeException("Payment failed: " . ($paymentResult['error'] ?? 'Unknown error'));
                }

                $transactionId = $paymentResult['transaction_id'];

                // Record the payment
                Payment::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'membership_id' => $lockedMembership->id,
                    'amount' => $price,
                    'currency' => 'USD',
                    'gateway' => $tenant->payment_gateway_config['provider'] ?? 'mock',
                    'transaction_id' => $transactionId,
                    'status' => 'success',
                ]);
            }

            // Create the subscription
            $expiresAt = $billingCycle === 'monthly' ? now()->addMonth() : now()->addYear();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'membership_id' => $lockedMembership->id,
                'status' => 'active',
                'billing_cycle' => $billingCycle,
                'price' => $price,
                'is_free_allocation' => $isFreeAllocation,
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            return $subscription;
        });
    }
}
