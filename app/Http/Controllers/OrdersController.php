<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\OrderStatus;
use App\Http\Resources\{OrderResource, PaginationResource};
use App\Models\{Order, OrderItem};
use App\Http\Requests\{OrderRequest, ListOrdersRequest};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\{JsonResponse, Response};

class OrdersController extends Controller
{
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

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You can only view your own orders',
            ], Response::HTTP_FORBIDDEN);
        }

        $order->load('items');

        return response()->json(new OrderResource($order));
    }
}
