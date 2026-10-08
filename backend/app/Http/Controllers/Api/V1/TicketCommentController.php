<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\AddTicketComment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\StoreTicketCommentRequest;
use App\Http\Resources\TicketCommentResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class TicketCommentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreTicketCommentRequest $request, Ticket $ticket, AddTicketComment $addComment): JsonResponse
    {
        $comment = $addComment->handle(
            $ticket,
            $request->user(),
            $request->string('message')->toString(),
            $request->boolean('is_internal'),
        );
        $comment->load(['user', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'data' => new TicketCommentResource($comment),
        ], 201);
    }
}
