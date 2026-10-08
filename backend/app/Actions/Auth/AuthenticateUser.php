<?php

namespace App\Actions\Auth;

use App\AuditAction;
use App\Models\User;
use App\Support\AuditLogger;
use App\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @throws ValidationException
     */
    public function handle(string $email, string $password, bool $remember): User
    {
        $authenticated = Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
            'status' => UserStatus::Active->value,
        ], $remember);

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => ['Email or password is incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();
        $user->forceFill(['last_login_at' => now()])->save();
        $this->auditLogger->log(AuditAction::UserLogin, $user, $user);

        return $user;
    }
}
