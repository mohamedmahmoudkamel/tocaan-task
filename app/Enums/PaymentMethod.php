<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';

    public function getDisplayName(): string
    {
        return match($this) {
            self::CREDIT_CARD => __('payments.methods.credit_card'),
            self::PAYPAL => __('payments.methods.paypal'),
        };
    }
}
