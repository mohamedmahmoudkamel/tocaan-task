<?php

namespace App\Filters\Order;

class ByUserIdFilter
{
    /**
     * @param $query
     * @param $key
     * @param $value
     * @return mixed
     */
    public function apply($query, $key, $value): mixed
    {
        return $query->where('user_id', $value);
    }
}
