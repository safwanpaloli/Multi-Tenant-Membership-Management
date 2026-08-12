<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResult;
use Illuminate\Support\Str;

class StripePaymentProvider implements PaymentProviderInterface
{
    protected array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
        // In a real app, initialize Stripe SDK with $this->credentials['secret_key']
    }

    public function createPayment(array $data): PaymentResult
    {
        // Mocking Stripe API call
        return new PaymentResult(
            success: true,
            paymentId: 'pi_' . Str::random(24),
            status: 'paid'
        );
    }

    public function verifyPayment(string $paymentId): PaymentResult
    {
        return new PaymentResult(success: true, paymentId: $paymentId, status: 'paid');
    }

    public function refund(string $paymentId, ?float $amount = null): PaymentResult
    {
        return new PaymentResult(success: true, paymentId: $paymentId, status: 'refunded');
    }
}
