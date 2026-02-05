<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User};
use App\Enums\OrderStatus;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class OrderTest extends TestCase
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

    public function test_authenticated_user_can_create_order(): void
    {
        $orderData = [
            'items' => [
                [
                    'product_name' => $this->faker->word(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'price' => $this->faker->randomFloat(2, 10, 100),
                ],
                [
                    'product_name' => $this->faker->word(),
                    'quantity' => $this->faker->numberBetween(1, 10),
                    'price' => $this->faker->randomFloat(2, 10, 100),
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'order_id',
                'message',
            ]);

        $expectedTotal = collect($orderData['items'])->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $orderId = $response->json('order_id');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', count($orderData['items']));

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => $expectedTotal,
            'status' => OrderStatus::PENDING->value,
        ]);

        foreach ($orderData['items'] as $item) {
            $this->assertDatabaseHas('order_items', [
                'order_id' => $orderId,
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    }

    public function test_unauthenticated_user_cannot_create_order(): void
    {
        $this->withHeaders(['Authorization' => ''])
        ->postJson('/api/orders', [
            'items' => [
                [
                    'product_name' => $this->faker->word(),
                    'quantity' => 1,
                    'price' => 10.99,
                ],
            ],
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_order_creation_fails_with_empty_items(): void
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'items' => [],
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_creation_fails_with_missing_items(): void
    {
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_creation_fails_with_invalid_item_data(): void
    {
        $invalidItems = [
            'items' => [
                [
                    'product_name' => '',
                    'quantity' => 0,
                    'price' => -10,
                ],
            ],
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', $invalidItems)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'items.0.product_name',
                'items.0.quantity',
                'items.0.price',
            ]);
    }

    public function test_order_creation_calculates_total_correctly(): void
    {
        $orderData = [
            'items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => 2,
                    'price' => 10.50,
                ],
                [
                    'product_name' => 'Product B',
                    'quantity' => 3,
                    'price' => 5.25,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', $orderData);

        $expectedTotal = (2 * 10.50) + (3 * 5.25);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => $expectedTotal,
            'status' => OrderStatus::PENDING->value,
        ]);

        $this->assertDatabaseCount('order_items', 2);
    }
}
