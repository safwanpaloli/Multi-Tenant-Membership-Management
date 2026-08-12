<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResult;

interface PaymentProviderInterface
{
    public function createPayment(array $data): PaymentResult;

    public function verifyPayment(string $paymentId): PaymentResult;

    public function refund(string $paymentId, ?float $amount = null): PaymentResult;
}
