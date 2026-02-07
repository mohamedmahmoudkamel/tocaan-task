<?php

namespace Tests\Feature\Orders;

use App\Models\{User, Order, OrderItem, Payment};
use App\Enums\OrderStatus;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class DeleteOrderTest extends TestCase
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

    public function test_user_can_delete_their_order_without_payments(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'order_id' => $order->id,
                'message' => 'Order deleted successfully',
            ]);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_user_cannot_delete_order_with_payments(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

        Payment::factory()->create(['order_id' => $order->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'error' => 'Order deletion failed',
                'message' => 'Order can not be deleted',
            ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_user_cannot_delete_another_users_order(): void
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/orders/{$otherOrder->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('orders', ['id' => $otherOrder->id]);
    }

    public function test_unauthenticated_user_cannot_delete_order(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_order_not_found_returns_404(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/orders/999');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
