<?php

namespace App\Models;

use App\PermissionName;
use App\SystemRole;
use App\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'employee_number', 'department_id', 'job_title', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function reportedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'reporter_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_technician_id');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function assignedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_user_id');
    }

    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function hasRole(SystemRole|string $role): bool
    {
        $roleName = $role instanceof SystemRole ? $role->value : $role;

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermissionTo(PermissionName|string $permission): bool
    {
        $permissionName = $permission instanceof PermissionName ? $permission->value : $permission;

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('permissions.name', $permissionName))
            ->exists();
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'last_login_at' => 'datetime',
        ];
    }
}
