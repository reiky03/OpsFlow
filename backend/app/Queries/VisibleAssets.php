<?php

namespace App\Queries;

use App\Models\Asset;
use App\Models\User;
use App\PermissionName;
use Illuminate\Database\Eloquent\Builder;

class VisibleAssets
{
    /** @return Builder<Asset> */
    public function for(User $user): Builder
    {
        $query = Asset::query();

        return $user->hasPermissionTo(PermissionName::AssetViewAll)
            ? $query
            : $query->where('assigned_user_id', $user->id);
    }
}
