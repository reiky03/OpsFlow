<?php

namespace App\Actions\Users;

use App\Models\Role;
use App\Models\User;
use App\SystemRole;
use App\UserStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    /** @param array<string, mixed> $data */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $roleIds = Arr::pull($data, 'role_ids');
            $user = User::query()->create([...$data, 'status' => UserStatus::Active]);
            $roleIds ??= [Role::query()->where('name', SystemRole::Employee->value)->value('id')];
            $user->roles()->sync(array_filter($roleIds));

            return $user;
        }, 3);
    }
}
