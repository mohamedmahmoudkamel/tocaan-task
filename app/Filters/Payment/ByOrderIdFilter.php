<?php

namespace App\Filters\Payment;

class ByOrderIdFilter
{
    /**
     * @param $query
     * @param $key
     * @param $value
     * @return mixed
     */
    public function apply($query, $key, $value): mixed
    {
        return $query->where('order_id', $value);
    }
}
