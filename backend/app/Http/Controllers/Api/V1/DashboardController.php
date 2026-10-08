<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Queries\VisibleAssets;
use App\Queries\VisibleTickets;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, VisibleTickets $visibleTickets, VisibleAssets $visibleAssets): JsonResponse
    {
        $tickets = $visibleTickets->for($request->user());
        $assets = $visibleAssets->for($request->user());
        $ticketCounts = (clone $tickets)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $priorityCounts = (clone $tickets)->selectRaw('priority, COUNT(*) AS aggregate')->groupBy('priority')->pluck('aggregate', 'priority');
        $assetCounts = (clone $assets)->selectRaw('status, COUNT(*) AS aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $recentTickets = (clone $tickets)->with(['category', 'reporter', 'assignedTechnician', 'asset'])->latest()->limit(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => ['total' => $ticketCounts->sum(), 'by_status' => $ticketCounts, 'by_priority' => $priorityCounts],
                'assets' => ['total' => $assetCounts->sum(), 'by_status' => $assetCounts],
                'notifications' => ['unread' => $request->user()->unreadNotifications()->count()],
                'recent_tickets' => TicketResource::collection($recentTickets),
            ],
        ]);
    }
}
