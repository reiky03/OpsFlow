<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Users\AssignUserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\AssignUserRoleRequest;
use App\Http\Resources\UserAdminResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AssignUserRoleRequest $request, User $user, AssignUserRole $assignUserRole): JsonResponse
    {
        $user = $assignUserRole->handle($user, $request->user(), $request->validated('role_ids'));

        return response()->json(['success' => true, 'message' => 'User roles updated successfully.', 'data' => new UserAdminResource($user->load(['department', 'roles']))]);
    }
}
