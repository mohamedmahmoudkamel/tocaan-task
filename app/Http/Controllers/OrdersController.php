<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\OrderStatus;
use App\Models\{Order, OrderItem};
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
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
}
