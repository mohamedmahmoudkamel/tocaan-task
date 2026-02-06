<?php

namespace App\Contracts;

use App\DTOs\PaymentData;

interface PaymentGatewayInterface
{
    public function process(PaymentData $data): array;
}
