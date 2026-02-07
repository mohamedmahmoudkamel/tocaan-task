<?php

namespace Tests\Feature\Orders;

use App\Models\{User, Order, Payment};
use App\Enums\OrderStatus;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class UpdateOrderTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_user_can_update_their_order_items(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $updateData = [
            'items' => [
                [
                    'product_name' => 'Updated Product 1',
                    'quantity' => 3,
                    'price' => 15,
                ],
                [
                    'product_name' => 'Updated Product 2',
                    'quantity' => 2,
                    'price' => 25,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $order->id,
                'user_id' => $this->user->id,
                'status' => OrderStatus::PENDING->value,
                'total_amount' => 95,
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total_amount' => 95,
            'status' => OrderStatus::PENDING->value,
        ]);

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => 'Updated Product 1',
            'quantity' => 3,
            'price' => 15,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => 'Updated Product 2',
            'quantity' => 2,
            'price' => 25,
        ]);
    }


    public function test_user_cannot_update_order_with_payments(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        Payment::factory()->create(['order_id' => $order->id]);

        $updateData = [
            'status' => OrderStatus::CONFIRMED->value,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PENDING->value,
        ]);
    }

    public function test_user_cannot_update_another_users_order(): void
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => OrderStatus::PENDING,
        ]);

        $updateData = [
            'status' => OrderStatus::CONFIRMED->value,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/orders/{$otherOrder->id}", $updateData);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('orders', [
            'id' => $otherOrder->id,
            'status' => OrderStatus::PENDING->value,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_order(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $updateData = [
            'status' => OrderStatus::CONFIRMED->value,
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
