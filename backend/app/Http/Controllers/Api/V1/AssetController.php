<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Assets\CreateAsset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Asset\ListAssetRequest;
use App\Http\Requests\Api\V1\Asset\StoreAssetRequest;
use App\Http\Requests\Api\V1\Asset\UpdateAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\PermissionName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListAssetRequest $request): JsonResponse
    {
        $query = Asset::query()->with('assignedUser');
        if (! $request->user()->hasPermissionTo(PermissionName::AssetViewAll)) {
            $query->where('assigned_user_id', $request->user()->id);
        }

        $query
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('asset_code', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('name', "%{$search}%", caseSensitive: false)
                        ->orWhereLike('serial_number', "%{$search}%", caseSensitive: false)
                        ->orWhereHas('assignedUser', fn (Builder $query) => $query->whereLike('name', "%{$search}%", caseSensitive: false));
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category'), fn (Builder $query) => $query->where('category', $request->string('category')))
            ->when($request->filled('assigned_user_id'), fn (Builder $query) => $query->where('assigned_user_id', $request->integer('assigned_user_id')));

        $paginator = $query
            ->orderBy($request->validated('sort_by', 'created_at'), $request->validated('sort_direction', 'desc'))
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => AssetResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetRequest $request, CreateAsset $createAsset): JsonResponse
    {
        $asset = $createAsset->handle($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Asset created successfully.',
            'data' => new AssetResource($asset),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);
        $asset->load([
            'assignedUser',
            'assignments' => fn ($query) => $query->with(['user', 'assigner', 'returner'])->latest('assigned_at'),
            'tickets' => fn ($query) => $query->with(['category', 'reporter', 'assignedTechnician'])->latest(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asset retrieved successfully.',
            'data' => new AssetResource($asset),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        $asset->update($request->validated());
        $asset->load('assignedUser');

        return response()->json([
            'success' => true,
            'message' => 'Asset updated successfully.',
            'data' => new AssetResource($asset),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset): JsonResponse
    {
        abort(405, 'Assets cannot be deleted through the API.');
    }
}
