<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Assets\ReturnAsset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Asset\ReturnAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;

class AssetReturnController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ReturnAssetRequest $request, Asset $asset, ReturnAsset $returnAsset): JsonResponse
    {
        $asset = $returnAsset->handle($asset, $request->user(), $request->string('notes')->toString() ?: null);
        $asset->load('assignedUser');

        return response()->json([
            'success' => true,
            'message' => 'Asset returned successfully.',
            'data' => new AssetResource($asset),
        ]);
    }
}
