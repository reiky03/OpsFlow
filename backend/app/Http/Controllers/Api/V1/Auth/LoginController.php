<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUser $authenticateUser): JsonResponse
    {
        $user = $authenticateUser->handle(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->boolean('remember'),
        );

        $request->session()->regenerate();
        $user->load(['department', 'roles.permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => new UserResource($user),
        ]);
    }
}
