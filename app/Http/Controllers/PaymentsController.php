<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentData;
use App\Http\Resources\{PaymentResource, PaginationResource};
use App\Models\{Order, Payment};
use App\Http\Requests\Payment\{PaymentRequest, ListPaymentsRequest, ShowPaymentRequest, OrderPaymentsRequest};
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

    public function index(ListPaymentsRequest $request): JsonResponse
    {
        $payments = Payment::search([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => PaymentResource::collection($payments->items()),
            'pagination' => new PaginationResource($payments),
        ]);
    }

    public function show(Payment $payment, ShowPaymentRequest $request): JsonResponse
    {
        return response()->json(new PaymentResource($payment));
    }

    public function orderPayments(Order $order, OrderPaymentsRequest $request): JsonResponse
    {
        $payments = Payment::search([
            'order_id' => $order->id,
            ...$request->validated(),
        ])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => PaymentResource::collection($payments->items()),
            'pagination' => new PaginationResource($payments),
        ]);
    }
}
