<?php

namespace App\Services\PaymentGateways;

interface PaymentGatewayInterface
{
    /**
     * Process a payment for a given amount.
     *
     * @param float $amount
     * @param string $currency
     * @param array $metadata
     * @return array ['success' => bool, 'transaction_id' => string|null, 'error' => string|null]
     */
    public function charge(float $amount, string $currency = 'USD', array $metadata = []): array;
}
