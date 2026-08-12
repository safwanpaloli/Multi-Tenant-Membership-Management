<?php

namespace App\Services\PaymentGateways;

use App\Models\Tenant;
use InvalidArgumentException;

class PaymentProviderFactory
{
    public static function make(Tenant $tenant): PaymentProviderInterface
    {
        $config = $tenant->paymentConfigs()->where('is_active', true)->first();

        if (! $config) {
            throw new InvalidArgumentException("No active payment configuration found for this tenant.");
        }

        $provider = $config->provider;
        $credentials = $config->credentials ?? [];

        return match ($provider) {
            'stripe' => new StripePaymentProvider($credentials),
            'paypal' => new PayPalPaymentProvider($credentials),
            default => throw new InvalidArgumentException("Unsupported payment provider: {$provider}"),
        };
    }
}
