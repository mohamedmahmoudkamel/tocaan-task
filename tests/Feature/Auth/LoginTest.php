<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_user_can_login_successfully(): void
    {
        $password = $this->faker->password(8, 20);
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token_type' => 'Bearer',
            ]);

        $this->assertIsString($response->json('token'));
        $this->assertNotEmpty($response->json('token'));
        $this->assertIsInt($response->json('expires_in'));
    }

    public function test_login_fails_with_non_existing_email(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'error' => 'Invalid credentials',
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $loginData = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'error' => 'Invalid credentials',
                'message' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_login_fails_with_missing_email(): void
    {
        $loginData = [
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_missing_password(): void
    {
        $loginData = [
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }
}
