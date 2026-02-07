<?php

namespace App\Services;

use App\Enums\{OrderStatus, PaymentStatus};
use App\Models\{Payment};
use App\DTOs\PaymentData;
use Exception;

class PaymentService
{
    public function processPayment(PaymentData $paymentData): Payment
    {
        if ($paymentData->order->status !== OrderStatus::CONFIRMED) {
            throw new Exception('Order must be confirmed to process payment');
        }

        $gateway = GatewayRegistry::resolve($paymentData->method);

        $result = $gateway->process($paymentData);

        return Payment::create([
            'user_id' => $paymentData->order->user_id,
            'order_id' => $paymentData->order->id,
            'payment_method' => $paymentData->method,
            'status' => PaymentStatus::from($result['status']),
            'amount' => $paymentData->amount,
            'metadata' => $paymentData->metadata,
            'gateway_reference' => $result['reference'] ?? null,
        ]);
    }
}
