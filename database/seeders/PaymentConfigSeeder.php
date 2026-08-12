<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantPaymentConfig;
use Illuminate\Database\Seeder;

class PaymentConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $index => $tenant) {
            // Give even-indexed tenants Stripe, odd-indexed PayPal for variety
            if ($index % 2 === 0) {
                TenantPaymentConfig::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'provider' => 'stripe',
                    ],
                    [
                        'is_active' => true,
                        'credentials' => [
                            'public_key' => env('STRIPE_TEST_PUBLIC_KEY', 'pk_test_mock_stripe_key_' . $tenant->slug),
                            'secret_key' => env('STRIPE_TEST_SECRET_KEY', 'sk_test_mock_stripe_secret_' . $tenant->slug),
                            'webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET', 'whsec_mock_secret_' . $tenant->slug),
                        ]
                    ]
                );
            } else {
                TenantPaymentConfig::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'provider' => 'paypal',
                    ],
                    [
                        'is_active' => true,
                        'credentials' => [
                            'client_id' => env('PAYPAL_TEST_CLIENT_ID', 'mock_paypal_client_id_' . $tenant->slug),
                            'client_secret' => env('PAYPAL_TEST_CLIENT_SECRET', 'mock_paypal_secret_' . $tenant->slug),
                            'mode' => 'sandbox',
                        ]
                    ]
                );
            }
        }
    }
}
