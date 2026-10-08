<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Users\DeactivateUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserAdminResource;
use App\Models\User;
use App\PermissionName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserDeactivationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, User $user, DeactivateUser $deactivateUser): JsonResponse
    {
        Gate::authorize(PermissionName::UserUpdate->value);
        $user = $deactivateUser->handle($user, $request->user());

        return response()->json(['success' => true, 'message' => 'User deactivated successfully.', 'data' => new UserAdminResource($user->load(['department', 'roles']))]);
    }
}
