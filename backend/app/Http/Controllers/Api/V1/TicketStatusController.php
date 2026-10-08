<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\ChangeTicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\UpdateTicketStatusRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\TicketStatus;
use Illuminate\Http\JsonResponse;

class TicketStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTicketStatusRequest $request, Ticket $ticket, ChangeTicketStatus $changeStatus): JsonResponse
    {
        $ticket = $changeStatus->handle(
            $ticket,
            TicketStatus::from($request->string('status')->toString()),
            $request->user(),
        );
        $ticket->load(['category', 'reporter', 'assignedTechnician', 'asset']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully.',
            'data' => new TicketResource($ticket),
        ]);
    }
}
