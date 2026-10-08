<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CurrentUserControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_active_user_is_returned_with_200(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');
    }

    public function test_authenticated_inactive_user_is_forbidden_with_403(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your account is inactive.');
    }
}
