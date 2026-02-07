<?php

namespace Tests\Feature\Payments;

use App\Models\{User, Order, Payment};
use App\Enums\{OrderStatus, PaymentStatus, PaymentMethod};
use Tests\TestCase;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class ListPaymentsTest extends TestCase
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

    public function test_user_can_list_his_payments(): void
    {
        $order = Order::factory()
            ->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CONFIRMED,
            ]);

        Payment::factory()
            ->count(3)
            ->create([
                'order_id' => $order->id,
                'user_id' => $this->user->id,
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_user_can_view_single_payment(): void
    {
        $order = Order::factory()
            ->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_method' => $payment->payment_method->value,
                'status' => $payment->status->value,
                'amount' => $payment->amount,
                'gateway_reference' => $payment->gateway_reference,
                'created_at' => $payment->created_at->toISOString(),
                'metadata' => $payment->metadata,
            ]);
    }

    public function test_user_can_view_payments_for_specific_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        Payment::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$order->id}/payments");

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(2, $response->json('pagination.total'));
    }

    public function test_user_cannot_access_another_users_payment(): void
    {
        $otherUser = User::factory()->create();
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        $payment = Payment::factory()->create(['order_id' => $otherOrder->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson([
                'error' => 'Access denied',
                'message' => 'You can only view your own payments',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_payments(): void
    {
        $response = $this->getJson('/api/payments');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_payment_list_filters_by_status(): void
    {
        $order = Order::factory()
            ->create([
                'user_id' => $this->user->id,
                'status' => OrderStatus::CONFIRMED->value,
            ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::SUCCESSFUL->value,
            'user_id' => $this->user->id,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::FAILED->value,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments?status=successful');

        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(PaymentStatus::SUCCESSFUL->value, $data[0]['status']);
    }

    public function test_payment_list_filters_by_payment_method(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'user_id' => $this->user->id,
        ]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'payment_method' => PaymentMethod::PAYPAL,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments?payment_method=credit_card');

        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('credit_card', $data[0]['payment_method']);
    }

    public function test_payment_list_pagination_works(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED->value,
        ]);

        Payment::factory()
            ->count(25)
            ->create([
                'order_id' => $order->id,
                'user_id' => $this->user->id,
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments?per_page=10');

        $response->assertStatus(Response::HTTP_OK);
        $pagination = $response->json('pagination');
        $this->assertEquals(10, $pagination['per_page']);
        $this->assertEquals(3, $pagination['total_pages'] ?? $pagination['last_page']);
    }
}
