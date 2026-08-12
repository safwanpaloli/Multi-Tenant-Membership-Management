<?php

namespace App\Services\PaymentGateways;

use App\Models\Tenant;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function makeForTenant(Tenant $tenant): PaymentGatewayInterface
    {
        $config = $tenant->payment_gateway_config ?? [];
        $provider = $config['provider'] ?? 'mock';

        return match ($provider) {
            'mock' => new MockGateway($config),
            // 'stripe' => new StripeGateway($config['api_key']),
            // 'paypal' => new PayPalGateway($config['client_id'], $config['secret']),
            default => throw new InvalidArgumentException("Unsupported payment gateway provider: {$provider}"),
        };
    }
}
