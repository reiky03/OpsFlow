<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Users\CreateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\ListUserRequest;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\UserAdminResource;
use App\Models\User;
use App\PermissionName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListUserRequest $request): JsonResponse
    {
        $query = User::query()->with(['department', 'roles']);
        $query
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn (Builder $query) => $query
                    ->whereLike('name', "%{$search}%", caseSensitive: false)
                    ->orWhereLike('email', "%{$search}%", caseSensitive: false)
                    ->orWhereLike('employee_number', "%{$search}%", caseSensitive: false));
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('department_id'), fn (Builder $query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('role'), fn (Builder $query) => $query->whereHas('roles', fn (Builder $query) => $query->where('name', $request->string('role'))));

        $paginator = $query->orderBy('name')->paginate($request->integer('per_page', 20))->withQueryString();

        return response()->json([
            'success' => true,
            'data' => UserAdminResource::collection($paginator->getCollection()),
            'meta' => ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, CreateUser $createUser): JsonResponse
    {
        $user = $createUser->handle($request->validated())->load(['department', 'roles']);

        return response()->json(['success' => true, 'message' => 'User created successfully.', 'data' => new UserAdminResource($user)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        Gate::authorize(PermissionName::UserView->value);

        return response()->json(['success' => true, 'data' => new UserAdminResource($user->load(['department', 'roles']))]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json(['success' => true, 'message' => 'User updated successfully.', 'data' => new UserAdminResource($user->load(['department', 'roles']))]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        abort(405, 'Users must be deactivated instead of deleted.');
    }
}
