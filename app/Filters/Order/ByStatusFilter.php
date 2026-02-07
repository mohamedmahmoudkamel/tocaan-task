<?php

namespace App\Filters\Order;

class ByStatusFilter
{
    /**
     * @param $query
     * @param $key
     * @param $value
     * @return mixed
     */
    public function apply($query, $key, $value): mixed
    {
        return $query->where('status', $value);
    }
}
