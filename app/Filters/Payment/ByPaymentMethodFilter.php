<?php

namespace App\Filters\Payment;

class ByPaymentMethodFilter
{
    /**
     * @param $query
     * @param $key
     * @param $value
     * @return mixed
     */
    public function apply($query, $key, $value): mixed
    {
        return $query->where('payment_method', $value);
    }
}
