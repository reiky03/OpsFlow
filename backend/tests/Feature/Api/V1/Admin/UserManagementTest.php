<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_admin_can_create_assign_role_and_deactivate_user(): void
    {
        $adminRole = Role::factory()->create(['name' => SystemRole::Admin->value]);
        $employeeRole = Role::factory()->create(['name' => SystemRole::Employee->value]);
        $technicianRole = Role::factory()->create(['name' => SystemRole::Technician->value]);
        $permissions = collect([PermissionName::UserCreate, PermissionName::UserUpdate, PermissionName::UserAssignRole, PermissionName::UserView])
            ->map(fn (PermissionName $permission) => Permission::query()->create(['name' => $permission->value]));
        $adminRole->permissions()->attach($permissions);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $created = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'New Employee',
            'email' => 'new.employee@opsflow.local',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertCreated()->assertJsonPath('data.roles.0.name', SystemRole::Employee->value);

        $userId = $created->json('data.id');
        $this->actingAs($admin)->postJson("/api/v1/users/{$userId}/role", ['role_ids' => [$technicianRole->id]])
            ->assertOk()->assertJsonPath('data.roles.0.name', SystemRole::Technician->value);
        $this->actingAs($admin)->postJson("/api/v1/users/{$userId}/deactivate")
            ->assertOk()->assertJsonPath('data.status', 'INACTIVE');

        $this->assertDatabaseHas('audit_logs', ['action' => 'USER_ROLE_CHANGED', 'entity_id' => $userId]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'USER_DEACTIVATED', 'entity_id' => $userId]);
        $this->assertNotNull($employeeRole);
    }
}
