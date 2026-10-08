<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\ChangeTicketPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\UpdateTicketPriorityRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\TicketPriority;
use Illuminate\Http\JsonResponse;

class TicketPriorityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTicketPriorityRequest $request, Ticket $ticket, ChangeTicketPriority $changePriority): JsonResponse
    {
        $ticket = $changePriority->handle(
            $ticket,
            TicketPriority::from($request->string('priority')->toString()),
            $request->user(),
        );
        $ticket->load(['category', 'reporter', 'assignedTechnician', 'asset']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket priority updated successfully.',
            'data' => new TicketResource($ticket),
        ]);
    }
}
