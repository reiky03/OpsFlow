<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Roles\UpdateRolePermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Role\UpdateRolePermissionsRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RolePermissionController extends Controller
{
    public function __invoke(UpdateRolePermissionsRequest $request, Role $role, UpdateRolePermissions $updateRolePermissions): JsonResponse
    {
        $role = $updateRolePermissions->handle($role, $request->user(), $request->validated('permission_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Role permissions updated successfully.',
            'data' => new RoleResource($role),
        ]);
    }
}
