<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['department', 'roles.permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Current user retrieved successfully.',
            'data' => new UserResource($user),
        ]);
    }
}
