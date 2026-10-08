<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketActivityResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketActivityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        $activities = $ticket->activities()->with('actor')->oldest()->get();

        return response()->json([
            'success' => true,
            'data' => TicketActivityResource::collection($activities),
        ]);
    }
}
