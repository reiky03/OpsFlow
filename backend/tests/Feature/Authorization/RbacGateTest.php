<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RbacGateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_with_permission_is_allowed_by_matching_gate(): void
    {
        $permission = Permission::factory()->create(['name' => PermissionName::TicketCreate->value]);
        $role = Role::factory()->create(['name' => SystemRole::Employee->value]);
        $role->permissions()->attach($permission);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->assertTrue(Gate::forUser($user)->allows(PermissionName::TicketCreate->value));
        $this->assertFalse(Gate::forUser($user)->allows(PermissionName::UserCreate->value));
    }

    public function test_admin_role_is_allowed_for_every_registered_permission(): void
    {
        $adminRole = Role::factory()->create(['name' => SystemRole::Admin->value]);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        foreach (PermissionName::cases() as $permission) {
            $this->assertTrue(Gate::forUser($admin)->allows($permission->value));
        }
    }
}
