<?php

namespace App\Providers;

use App\Enums\PaymentMethod;
use App\Services\GatewayRegistry;
use App\Gateways\{CreditCardGateway, PaypalGateway};
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GatewayRegistry::class);

        GatewayRegistry::register(PaymentMethod::CREDIT_CARD->value, new CreditCardGateway());
        GatewayRegistry::register(PaymentMethod::PAYPAL->value, new PaypalGateway());
    }
}
