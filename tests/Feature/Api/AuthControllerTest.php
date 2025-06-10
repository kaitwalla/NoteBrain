<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user',
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422);
    }

    public function test_logout_revokes_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_user_returns_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_user_requires_authentication()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
