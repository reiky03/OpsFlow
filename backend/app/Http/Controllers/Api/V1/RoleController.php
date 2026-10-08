<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\PermissionName;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        Gate::authorize(PermissionName::UserView->value);

        return response()->json([
            'success' => true,
            'data' => RoleResource::collection(Role::query()->with('permissions')->withCount('users')->orderBy('display_name')->get()),
        ]);
    }
}
