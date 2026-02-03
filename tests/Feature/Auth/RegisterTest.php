<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};

class RegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_register_successfully(): void
    {
        $password = $this->faker->password(8, 20);
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'user' => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                ],
                'token_type' => 'Bearer',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    public function test_registration_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create();
        $password = $this->faker->password(8, 20);

        $userData = [
            'name' => $this->faker->name(),
            'email' => $existingUser->email,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);

        $this->assertEquals(1, User::where('email', $existingUser->email)->count());
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $password = $this->faker->password(8, 20);
        $userData = [
            'name' => $this->faker->name(),
            'email' => 'invalid-email',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password(): void
    {
        $shortPassword = $this->faker->password(1, 6);
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $shortPassword,
            'password_confirmation' => $shortPassword,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_password_confirmation_mismatch(): void
    {
        $password = $this->faker->password(8, 20);
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password . 'different',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_empty_name(): void
    {
        $password = $this->faker->password(8, 20);
        $userData = [
            'name' => '',
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
    }
}
