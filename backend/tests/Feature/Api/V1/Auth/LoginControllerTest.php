<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_credentials_authenticate_active_user_and_return_200(): void
    {
        $department = Department::factory()->create(['name' => 'IT']);
        $permission = Permission::factory()->create(['name' => PermissionName::TicketCreate->value]);
        $role = Role::factory()->create(['name' => SystemRole::Employee->value]);
        $role->permissions()->attach($permission);
        $user = User::factory()->for($department)->create([
            'email' => 'employee@opsflow.local',
            'password' => 'password',
        ]);
        $user->roles()->attach($role);

        $response = $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => 'http://localhost:8080',
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->postJson('/api/v1/auth/login', [
                'email' => 'employee@opsflow.local',
                'password' => 'password',
                'remember' => true,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.email', 'employee@opsflow.local')
            ->assertJsonPath('data.department.name', 'IT')
            ->assertJsonPath('data.roles.0', SystemRole::Employee->value)
            ->assertJsonPath('data.permissions.0', PermissionName::TicketCreate->value)
            ->assertJsonMissingPath('data.password');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_invalid_credentials_return_generic_422_error(): void
    {
        User::factory()->create([
            'email' => 'employee@opsflow.local',
            'password' => 'password',
        ]);

        $response = $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => 'http://localhost:8080',
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->postJson('/api/v1/auth/login', [
                'email' => 'employee@opsflow.local',
                'password' => 'incorrect-password',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonPath('errors.email.0', 'Email or password is incorrect.');

        $this->assertGuest();
    }

    public function test_inactive_user_receives_same_generic_422_error(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive@opsflow.local',
            'password' => 'password',
        ]);

        $response = $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => 'http://localhost:8080',
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->postJson('/api/v1/auth/login', [
                'email' => 'inactive@opsflow.local',
                'password' => 'password',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Email or password is incorrect.');

        $this->assertGuest();
    }

    public function test_missing_credentials_return_validation_errors(): void
    {
        $response = $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => 'http://localhost:8080',
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->postJson('/api/v1/auth/login');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
