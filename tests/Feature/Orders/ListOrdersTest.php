<?php

namespace Tests\Feature\Orders;

use App\Models\{User, Order, OrderItem};
use App\Enums\OrderStatus;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class ListOrdersTest extends TestCase
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

    public function test_user_can_list_his_orders(): void
    {
        Order::factory()
            ->count(3)
            ->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_user_can_view_single_order_with_items(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'id' => $order->id,
            'status' => $order->status->value,
            'total_amount' => $order->total_amount,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
        ])
        ->assertJsonCount(2, 'items');
    }

    public function test_user_cannot_access_another_users_order(): void
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$otherOrder->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'error' => 'Access denied',
                'message' => 'You can only view your own orders',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_order_list_filters_by_status(): void
    {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING->value,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders?status=confirmed');

        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(OrderStatus::CONFIRMED->value, $data[0]['status']);
    }

    public function test_order_list_pagination_works(): void
    {
        Order::factory()
            ->count(25)
            ->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders?per_page=10');

        $response->assertStatus(Response::HTTP_OK);

        $pagination = $response->json('pagination');
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(25, $pagination['total']);
    }
}
