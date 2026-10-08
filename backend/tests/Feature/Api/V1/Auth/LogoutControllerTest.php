<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_logout_with_200(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => 'http://localhost:8080',
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logout successful.')
            ->assertJsonPath('data', null);

        $this->assertGuest('web');
    }

    public function test_unauthenticated_logout_request_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
