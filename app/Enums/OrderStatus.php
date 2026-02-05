<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PENDING => __('orders.status.pending'),
            self::PROCESSING => __('orders.status.processing'),
            self::COMPLETED => __('orders.status.completed'),
            self::CANCELLED => __('orders.status.cancelled'),
        };
    }
}
