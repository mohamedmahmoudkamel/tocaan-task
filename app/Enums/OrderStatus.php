<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case CANCELLED = 'cancelled';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PENDING => __('orders.status.pending'),
            self::CONFIRMED => __('orders.status.confirmed'),
            self::PROCESSING => __('orders.status.processing'),
            self::CANCELLED => __('orders.status.cancelled'),
        };
    }
}
