<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentData;
use App\Enums\PaymentMethod;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function process(PaymentData $data): array
    {
        $faker = \Faker\Factory::create();
        $success = $faker->boolean();

        logger('credit card payment processing', $data->toArray());

        $result = [
            'status' => $success ? 'successful' : 'failed',
            'reference' => 'CC_' . $faker->uuid(),
            'details' => $success ? 'Credit card transaction completed' : 'Credit card transaction failed',
            'gateway' => PaymentMethod::CREDIT_CARD->value,
        ];

        logger('credit card payment result', $result);

        return $result;
    }
}
