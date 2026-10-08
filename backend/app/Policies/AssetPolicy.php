<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use App\PermissionName;

class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetViewOwn)
            || $user->hasPermissionTo(PermissionName::AssetViewAll);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetViewAll)
            || ($user->hasPermissionTo(PermissionName::AssetViewOwn) && $asset->assigned_user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetUpdate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool
    {
        return false;
    }

    public function assign(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetAssign);
    }

    public function return(User $user, Asset $asset): bool
    {
        return $user->hasPermissionTo(PermissionName::AssetAssign);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Asset $asset): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return false;
    }
}
