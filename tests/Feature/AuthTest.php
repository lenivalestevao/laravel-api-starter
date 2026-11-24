<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonPath('user.email', 'ada@example.com');

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    }

    public function test_register_validates_input(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
