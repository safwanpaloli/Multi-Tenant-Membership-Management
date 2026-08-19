<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResult;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use InvalidArgumentException;

class StripePaymentProvider implements PaymentProviderInterface
{
    protected array $credentials;
    protected StripeClient $stripe;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
        
        if (empty($credentials['secret_key'])) {
            throw new InvalidArgumentException('Stripe secret_key is missing from credentials.');
        }

        $this->stripe = new StripeClient($credentials['secret_key']);
    }

    public function createPayment(array $data): PaymentResult
    {
        if (empty($data['token'])) {
            return new PaymentResult(success: false, error: 'Payment token is required for Stripe.');
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($data['amount'] * 100), // Stripe expects cents
                'currency' => 'usd',
                'payment_method' => $data['token'],
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            return new PaymentResult(
                success: $paymentIntent->status === 'succeeded',
                paymentId: $paymentIntent->id,
                status: $paymentIntent->status,
                error: $paymentIntent->status === 'succeeded' ? null : 'Payment intent not succeeded.'
            );
        } catch (ApiErrorException $e) {
            return new PaymentResult(
                success: false,
                error: $e->getMessage()
            );
        }
    }

    public function verifyPayment(string $paymentId): PaymentResult
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentId);
            
            return new PaymentResult(
                success: $paymentIntent->status === 'succeeded',
                paymentId: $paymentIntent->id,
                status: $paymentIntent->status
            );
        } catch (ApiErrorException $e) {
            return new PaymentResult(
                success: false,
                paymentId: $paymentId,
                error: $e->getMessage()
            );
        }
    }

    public function refund(string $paymentId, ?float $amount = null): PaymentResult
    {
        try {
            $refundParams = ['payment_intent' => $paymentId];
            if ($amount !== null) {
                $refundParams['amount'] = (int) ($amount * 100);
            }
            
            $refund = $this->stripe->refunds->create($refundParams);
            
            return new PaymentResult(
                success: $refund->status === 'succeeded',
                paymentId: $paymentId,
                status: 'refunded'
            );
        } catch (ApiErrorException $e) {
            return new PaymentResult(
                success: false,
                paymentId: $paymentId,
                error: $e->getMessage()
            );
        }
    }
}
