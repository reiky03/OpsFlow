<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\PermissionName;
use App\SystemRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $rolePermissions = [
            SystemRole::Employee->value => [
                PermissionName::TicketCreate,
                PermissionName::TicketViewOwn,
                PermissionName::TicketComment,
                PermissionName::AssetViewOwn,
            ],
            SystemRole::Technician->value => [
                PermissionName::TicketViewAssigned,
                PermissionName::TicketViewUnassigned,
                PermissionName::TicketUpdateStatus,
                PermissionName::TicketUpdatePriority,
                PermissionName::TicketComment,
                PermissionName::TicketInternalNote,
                PermissionName::AssetViewAll,
            ],
            SystemRole::ItManager->value => [
                PermissionName::TicketCreate,
                PermissionName::TicketViewOwn,
                PermissionName::TicketViewAssigned,
                PermissionName::TicketViewUnassigned,
                PermissionName::TicketViewAll,
                PermissionName::TicketAssign,
                PermissionName::TicketUpdateStatus,
                PermissionName::TicketUpdatePriority,
                PermissionName::TicketComment,
                PermissionName::TicketInternalNote,
                PermissionName::AssetViewOwn,
                PermissionName::AssetViewAll,
                PermissionName::AssetCreate,
                PermissionName::AssetUpdate,
                PermissionName::AssetAssign,
                PermissionName::UserView,
                PermissionName::TicketCategoryManage,
                PermissionName::AuditView,
            ],
            SystemRole::Admin->value => PermissionName::cases(),
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::query()->where('name', $roleName)->firstOrFail();
            $permissionIds = Permission::query()
                ->whereIn('name', array_map(
                    fn (PermissionName $permission): string => $permission->value,
                    $permissions,
                ))
                ->pluck('id');

            $role->permissions()->sync($permissionIds);
        }
    }
}
