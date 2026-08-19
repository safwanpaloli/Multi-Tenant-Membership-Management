<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResult;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;
use InvalidArgumentException;

class PayPalPaymentProvider implements PaymentProviderInterface
{
    protected array $credentials;
    protected PayPalClient $provider;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
        
        if (empty($credentials['client_id']) || empty($credentials['secret'])) {
            throw new InvalidArgumentException('PayPal client_id or secret is missing from credentials.');
        }

        $this->provider = new PayPalClient;
        $config = [
            'mode'    => $credentials['mode'] ?? 'sandbox',
            'sandbox' => [
                'client_id'         => $credentials['client_id'],
                'client_secret'     => $credentials['secret'],
                'app_id'            => 'APP-80W284485P519543T',
            ],
            'live' => [
                'client_id'         => $credentials['client_id'],
                'client_secret'     => $credentials['secret'],
                'app_id'            => '',
            ],
            'payment_action' => 'Sale',
            'currency'       => 'USD',
            'notify_url'     => '',
            'locale'         => 'en_US',
            'validate_ssl'   => true,
        ];
        
        $this->provider->setApiCredentials($config);
        $this->provider->getAccessToken();
    }

    public function createPayment(array $data): PaymentResult
    {
        // For PayPal REST SDK, the typical flow is that the client creates the order,
        // approves it, and passes the Order ID ($data['token']) to the backend to capture.
        if (empty($data['token'])) {
            return new PaymentResult(success: false, error: 'Payment token (Order ID) is required for PayPal.');
        }

        try {
            $response = $this->provider->capturePaymentOrder($data['token']);
            
            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                return new PaymentResult(
                    success: true,
                    paymentId: $response['id'],
                    status: 'paid'
                );
            }

            return new PaymentResult(
                success: false,
                paymentId: $response['id'] ?? null,
                error: $response['message'] ?? 'Payment capture failed or not completed.'
            );
        } catch (Exception $e) {
            return new PaymentResult(
                success: false,
                error: $e->getMessage()
            );
        }
    }

    public function verifyPayment(string $paymentId): PaymentResult
    {
        try {
            $response = $this->provider->showOrderDetails($paymentId);
            
            if (isset($response['status'])) {
                return new PaymentResult(
                    success: $response['status'] === 'COMPLETED',
                    paymentId: $paymentId,
                    status: $response['status'] === 'COMPLETED' ? 'paid' : $response['status']
                );
            }
            
            return new PaymentResult(success: false, paymentId: $paymentId, error: 'Unable to retrieve order details.');
        } catch (Exception $e) {
            return new PaymentResult(
                success: false,
                paymentId: $paymentId,
                error: $e->getMessage()
            );
        }
    }

    public function refund(string $paymentId, ?float $amount = null): PaymentResult
    {
        // For PayPal, you generally refund a Capture ID, which we would need to extract
        // from the Order details. For simplicity in this integration, assuming $paymentId
        // is the Capture ID if it's not an Order ID.
        try {
            $data = [];
            if ($amount !== null) {
                $data['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => 'USD'
                ];
            }
            
            $response = $this->provider->refundCapturedPayment($paymentId, $data);
            
            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                return new PaymentResult(
                    success: true,
                    paymentId: $response['id'],
                    status: 'refunded'
                );
            }
            
            return new PaymentResult(
                success: false,
                paymentId: $paymentId,
                error: $response['message'] ?? 'Refund failed.'
            );
        } catch (Exception $e) {
            return new PaymentResult(
                success: false,
                paymentId: $paymentId,
                error: $e->getMessage()
            );
        }
    }
}
