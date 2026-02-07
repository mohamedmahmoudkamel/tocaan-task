<?php

namespace Tests\Feature\Payments;

use App\Models\{User, Order};
use App\Enums\{OrderStatus, PaymentMethod};
use Tests\TestCase;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class PaymentsTest extends TestCase
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

    public function test_authenticated_user_can_process_payment_for_confirmed_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED,
            'total_amount' => 100.00,
        ]);

        $paymentData = [
            'payment_method' => PaymentMethod::CREDIT_CARD->value,
            'metadata' => ['card_last_four' => '1234'],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/orders/{$order->id}/payments", $paymentData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'payment_id',
                'order_id',
                'payment_method',
                'payment_status',
                'gateway_reference',
            ]);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'amount' => 100.00,
        ]);
    }

    public function test_payment_fails_for_non_confirmed_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING,
            'total_amount' => 100.00,
        ]);

        $paymentData = [
            'payment_method' => 'credit_card',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/orders/{$order->id}/payments", $paymentData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_unauthenticated_user_cannot_process_payment(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::CONFIRMED,
        ]);

        $paymentData = [
            'payment_method' => 'credit_card',
        ];

        $response = $this->postJson("/api/orders/{$order->id}/payments", $paymentData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_payment_fails_with_unsupported_method(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

        $paymentData = [
            'payment_method' => 'unsupported_method',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/orders/{$order->id}/payments", $paymentData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('payments', 0);
    }
}
