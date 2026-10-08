<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetAssignmentResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetHistoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);
        $history = $asset->assignments()->with(['user', 'assigner', 'returner'])->latest('assigned_at')->get();

        return response()->json([
            'success' => true,
            'data' => AssetAssignmentResource::collection($history),
        ]);
    }
}
