<?php

namespace App\Services\PaymentGateways;

use Illuminate\Support\Str;

class MockGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function charge(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        // Simulate an API call delay
        usleep(500000); // 0.5 seconds

        // Simulate success
        return [
            'success' => true,
            'transaction_id' => 'mock_txn_' . Str::random(16),
            'error' => null,
        ];
    }
}
