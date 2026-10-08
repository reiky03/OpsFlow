<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\AssignTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\AssignTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TicketAssignmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AssignTicketRequest $request, Ticket $ticket, AssignTicket $assignTicket): JsonResponse
    {
        $technician = User::query()->findOrFail($request->integer('technician_id'));
        $ticket = $assignTicket->handle($ticket, $technician, $request->user());
        $ticket->load(['category', 'reporter', 'assignedTechnician', 'asset']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket assigned successfully.',
            'data' => new TicketResource($ticket),
        ]);
    }
}
