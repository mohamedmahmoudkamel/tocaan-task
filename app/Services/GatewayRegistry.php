<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Exception;

class GatewayRegistry
{
    private static array $gateways = [];

    public static function register(string $name, PaymentGatewayInterface $gateway): void
    {
        self::$gateways[$name] = $gateway;
    }

    public static function resolve(string $name): PaymentGatewayInterface
    {
        return self::$gateways[$name] ?? throw new Exception("Gateway {$name} not found");
    }
}
