<?php

namespace App\Actions\Roles;

use App\AuditAction;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\SystemRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateRolePermissions
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param list<int> $permissionIds */
    public function handle(Role $role, User $actor, array $permissionIds): Role
    {
        if ($role->name === SystemRole::Admin->value) {
            throw ValidationException::withMessages([
                'permission_ids' => ['Administrator always has full access and cannot be restricted.'],
            ]);
        }

        return DB::transaction(function () use ($role, $actor, $permissionIds): Role {
            $oldPermissionIds = $role->permissions()->pluck('permissions.id')->sort()->values()->all();
            $newPermissionIds = collect($permissionIds)->unique()->sort()->values()->all();

            $role->permissions()->sync($newPermissionIds);
            $this->auditLogger->log(
                AuditAction::RolePermissionsChanged,
                $role,
                $actor,
                ['permission_ids' => $oldPermissionIds],
                ['permission_ids' => $newPermissionIds],
            );

            return $role->load('permissions')->loadCount('users');
        }, 3);
    }
}
