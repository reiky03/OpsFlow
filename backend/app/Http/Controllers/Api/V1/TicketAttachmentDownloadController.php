<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Ticket $ticket, TicketAttachment $ticketAttachment): StreamedResponse
    {
        $this->authorize('view', $ticket);
        $ticketAttachment->loadMissing('comment');
        if ($ticketAttachment->comment?->is_internal) {
            $this->authorize('viewInternalNotes', $ticket);
        }

        abort_unless($ticketAttachment->ticket_id === $ticket->id, 404);

        return Storage::disk($ticketAttachment->disk)->download(
            $ticketAttachment->path,
            $ticketAttachment->original_name,
            ['Content-Type' => $ticketAttachment->mime_type],
        );
    }
}
