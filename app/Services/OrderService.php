<?php

namespace App\Services;

use App\Models\Order;
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
}
