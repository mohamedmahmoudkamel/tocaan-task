<?php

namespace Database\Factories;

use App\Enums\{PaymentStatus, PaymentMethod};
use App\Models\{Payment, Order, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'payment_method' => $this->faker->randomElement(PaymentMethod::cases())->value,
            'status' => $this->faker->randomElement(PaymentStatus::cases())->value,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'metadata' => null,
            'gateway_reference' => $this->faker->optional()->uuid(),
        ];
    }
}
