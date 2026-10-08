<?php

namespace App\Actions\Users;

use App\AuditAction;
use App\Models\User;
use App\Support\AuditLogger;
use App\UserStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeactivateUser
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(User $target, User $actor): User
    {
        if ($target->is($actor)) {
            throw ValidationException::withMessages(['user' => 'You cannot deactivate your own account.']);
        }

        return DB::transaction(function () use ($target, $actor): User {
            $target->update(['status' => UserStatus::Inactive]);
            DB::table('sessions')->where('user_id', $target->id)->delete();
            $this->auditLogger->log(
                AuditAction::UserDeactivated,
                $target,
                $actor,
                ['status' => UserStatus::Active->value],
                ['status' => UserStatus::Inactive->value],
            );

            return $target;
        }, 3);
    }
}
