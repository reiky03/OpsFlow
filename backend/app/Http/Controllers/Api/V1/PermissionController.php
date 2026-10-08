<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\PermissionName;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Gate::authorize(PermissionName::RoleManage->value);

        return response()->json([
            'success' => true,
            'data' => PermissionResource::collection(Permission::query()->orderBy('name')->get()),
        ]);
    }
}
