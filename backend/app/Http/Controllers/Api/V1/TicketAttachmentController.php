<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tickets\StoreTicketAttachment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\StoreTicketAttachmentRequest;
use App\Http\Resources\TicketAttachmentResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class TicketAttachmentController extends Controller
{
    public function store(StoreTicketAttachmentRequest $request, Ticket $ticket, StoreTicketAttachment $storeAttachment): JsonResponse
    {
        $attachment = $storeAttachment->handle(
            $ticket,
            $request->user(),
            $request->file('file'),
            $request->filled('ticket_comment_id') ? $request->integer('ticket_comment_id') : null,
        )->load('uploader');

        return response()->json([
            'success' => true,
            'message' => 'Attachment uploaded successfully.',
            'data' => new TicketAttachmentResource($attachment),
        ], 201);
    }
}
