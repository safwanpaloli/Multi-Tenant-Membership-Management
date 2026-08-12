<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResult;
use Illuminate\Support\Str;

class PayPalPaymentProvider implements PaymentProviderInterface
{
    protected array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function createPayment(array $data): PaymentResult
    {
        return new PaymentResult(
            success: true,
            paymentId: 'PAYID-' . Str::random(20),
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
