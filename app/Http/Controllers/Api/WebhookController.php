<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    public function handlePayment(Request $request, string $provider): JsonResponse
    {
        // Simple mock signature verification
        if ($request->header('X-Signature') !== 'mock-signature') {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $paymentId = $payload['payment_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (! $paymentId || ! $status) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Find the purchase by payment_id and provider
        $purchase = MembershipPurchase::where('payment_id', $paymentId)
            ->where('payment_provider', $provider)
            ->first();

        if (! $purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        // Idempotency: Don't update if already in a final state and event is old, etc.
        // For simplicity, just update the status if it changed
        if ($purchase->status !== $status) {
            $purchase->update([
                'status' => $status,
                'metadata' => array_merge((array) $purchase->metadata, ['webhook_received' => now()->toIso8601String()])
            ]);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}
