<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_update_non_admin_role_permissions_and_change_is_audited(): void
    {
        $adminRole = Role::factory()->create(['name' => SystemRole::Admin->value]);
        $technicianRole = Role::factory()->create(['name' => SystemRole::Technician->value]);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        $permissions = collect([PermissionName::TicketViewAssigned, PermissionName::TicketComment])
            ->map(fn (PermissionName $permission) => Permission::query()->create(['name' => $permission->value]));

        $this->actingAs($admin)
            ->putJson("/api/v1/roles/{$technicianRole->id}/permissions", [
                'permission_ids' => $permissions->pluck('id')->all(),
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Role permissions updated successfully.')
            ->assertJsonCount(2, 'data.permissions');

        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->all(),
            $technicianRole->permissions()->pluck('permissions.id')->all(),
        );
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ROLE_PERMISSIONS_CHANGED',
            'entity_id' => $technicianRole->id,
        ]);
    }

    public function test_admin_role_permissions_cannot_be_restricted(): void
    {
        $adminRole = Role::factory()->create(['name' => SystemRole::Admin->value]);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin)
            ->putJson("/api/v1/roles/{$adminRole->id}/permissions", ['permission_ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('permission_ids');
    }
}
