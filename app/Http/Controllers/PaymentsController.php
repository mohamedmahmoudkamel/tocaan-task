<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentData;
use App\Models\{Order};
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\{JsonResponse, Response};
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentsController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
    }

    public function store(PaymentRequest $request, Order $order): JsonResponse
    {
        try {
            DB::beginTransaction();

            $paymentData = PaymentData::fromArray([
                'amount' => $order->total_amount,
                'method' => $request->validated('payment_method'),
                'metadata' => $request->validated('metadata', []),
                'order' => $order,
            ]);

            $payment = $this->paymentService->processPayment($paymentData);

            DB::commit();

            return response()->json([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_method' => $payment->payment_method->value,
                'payment_status' => $payment->status->value,
                'gateway_reference' => $payment->gateway_reference,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            logger()->error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Payment processing failed',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
