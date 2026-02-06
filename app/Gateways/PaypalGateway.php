<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentData;
use App\Enums\PaymentMethod;

class PaypalGateway implements PaymentGatewayInterface
{
    public function process(PaymentData $data): array
    {
        $faker = \Faker\Factory::create();
        $success = $faker->boolean();

        logger('paypal payment processing', $data->toArray());

        $result = [
            'status' => $success ? 'successful' : 'failed',
            'reference' => 'PP_' . $faker->uuid(),
            'details' => $success ? 'PayPal transaction completed' : 'PayPal transaction failed',
            'gateway' => PaymentMethod::PAYPAL->value,
        ];

        logger('paypal payment result', $result);

        return $result;
    }
}
