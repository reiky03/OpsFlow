<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\ListNotificationRequest;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ListNotificationRequest $request): JsonResponse
    {
        $query = $request->user()->notifications()->when($request->boolean('unread'), fn ($query) => $query->whereNull('read_at'));
        $paginator = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }
}
