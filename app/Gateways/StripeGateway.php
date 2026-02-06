<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentData;
use App\Enums\PaymentMethod;

class StripeGateway implements PaymentGatewayInterface
{
    public function process(PaymentData $data): array
    {
        $faker = \Faker\Factory::create();
        $success = $faker->boolean();

        logger('stripe payment processing', $data->toArray());

        $result = [
            'status' => $success ? 'successful' : 'failed',
            'reference' => 'ST_' . $faker->uuid(),
            'details' => $success ? 'Stripe transaction completed' : 'Stripe transaction failed',
            'gateway' => PaymentMethod::STRIPE->value,
        ];

        logger('stripe payment result', $result);

        return $result;
    }
}
