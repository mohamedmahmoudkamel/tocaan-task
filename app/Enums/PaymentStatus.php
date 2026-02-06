<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';

    public function getDisplayName(): string
    {
        return match($this) {
            self::PENDING => __('payments.status.pending'),
            self::SUCCESSFUL => __('payments.status.successful'),
            self::FAILED => __('payments.status.failed'),
        };
    }
}
