<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_forgot_password_uses_generic_response_for_known_and_unknown_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('message', 'If the email exists, a password reset link has been sent.');
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'unknown@opsflow.local'])
            ->assertOk()
            ->assertJsonPath('message', 'If the email exists, a password reset link has been sent.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_password_with_valid_broker_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'old-password']);
        $token = null;

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertOk()->assertJsonPath('message', 'Password reset successfully.');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }
}
