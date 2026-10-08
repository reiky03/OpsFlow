<?php

namespace App\Actions\Users;

use App\AuditAction;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class AssignUserRole
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param list<int> $roleIds */
    public function handle(User $target, User $actor, array $roleIds): User
    {
        return DB::transaction(function () use ($target, $actor, $roleIds): User {
            $oldRoleIds = $target->roles()->pluck('roles.id')->all();
            $target->roles()->sync($roleIds);
            $this->auditLogger->log(
                AuditAction::UserRoleChanged,
                $target,
                $actor,
                ['role_ids' => $oldRoleIds],
                ['role_ids' => $roleIds],
            );

            return $target;
        }, 3);
    }
}
