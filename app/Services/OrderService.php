<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\DTOs\OrderUpdateData;
use Exception;

class OrderService
{
    public function deleteOrder(Order $order): array
    {
        if ($order->payments()->exists()) {
            throw new Exception('Order can not be deleted');
        }

        $order->items()->delete();
        $order->delete();

        return [
            'order_id' => $order->id,
            'message' => 'Order deleted successfully',
        ];
    }


    public function updateOrder(Order $order, OrderUpdateData $data): array
    {
        if ($order->payments()->exists()) {
            throw new Exception('Orders with payments cannot be updated');
        }

        if ($data->hasItemsUpdate()) {
            $order->items()->delete();

            foreach ($data->items as $item) {
                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $totalAmount = collect($data->items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });
            $order->total_amount = $totalAmount;
        }

        $order->status = OrderStatus::PENDING->value;

        $order->save();

        return [
            'order' => $order,
            'message' => 'Order updated successfully',
        ];
    }
}
