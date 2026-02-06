<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\{Order, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(OrderStatus::cases())->value,
        ];
    }
}
