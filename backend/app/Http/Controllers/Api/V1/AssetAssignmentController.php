<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Assets\AssignAsset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Asset\AssignAssetRequest;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AssetAssignmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AssignAssetRequest $request, Asset $asset, AssignAsset $assignAsset): JsonResponse
    {
        $recipient = User::query()->findOrFail($request->integer('user_id'));
        $asset = $assignAsset->handle($asset, $recipient, $request->user(), $request->string('notes')->toString() ?: null);
        $asset->load('assignedUser');

        return response()->json([
            'success' => true,
            'message' => 'Asset assigned successfully.',
            'data' => new AssetResource($asset),
        ]);
    }
}
