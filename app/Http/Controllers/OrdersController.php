<?php

namespace App\Http\Controllers;

use Exception;
use App\DTOs\OrderUpdateData;
use App\Enums\OrderStatus;
use App\Http\Resources\{OrderResource, PaginationResource};
use App\Models\{Order, OrderItem};
use App\Http\Requests\Order\{OrderRequest, ListOrdersRequest, DeleteOrderRequest, ShowOrderRequest, UpdateOrderRequest};
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\{JsonResponse, Response};

class OrdersController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    public function store(OrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $items = $request->validated('items');

            $totalAmount = collect($items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::PENDING->value,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'order_id' => $order->id,
                'message' => __('orders.success'),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => __('orders.failed'),
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function index(ListOrdersRequest $request): JsonResponse
    {
        $orders = Order::search([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'pagination' => new PaginationResource($orders),
        ]);
    }

    public function show(Order $order, ShowOrderRequest $request): JsonResponse
    {
        $order->load('items');

        return response()->json(new OrderResource($order));
    }

    public function update(Order $order, UpdateOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updateData = OrderUpdateData::fromArray($request->validated());
            $result = $this->orderService->updateOrder($order, $updateData);

            DB::commit();

            return response()->json(new OrderResource($result['order']));

        } catch (Exception $e) {
            DB::rollBack();

            logger()->error('Order update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Order update failed',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy(Order $order, DeleteOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $result = $this->orderService->deleteOrder($order);

            DB::commit();

            return response()->json($result, Response::HTTP_OK);

        } catch (Exception $e) {
            DB::rollBack();

            logger()->error('Order deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Order deletion failed',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
