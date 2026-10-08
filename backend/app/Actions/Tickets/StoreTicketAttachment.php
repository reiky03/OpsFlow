<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\TicketActivityType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreTicketAttachment
{
    public function handle(Ticket $ticket, User $actor, UploadedFile $file, ?int $commentId): TicketAttachment
    {
        $storedName = str()->uuid().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("ticket-attachments/{$ticket->id}", $storedName, 'local');

        try {
            return DB::transaction(function () use ($ticket, $actor, $file, $commentId, $storedName, $path): TicketAttachment {
                $attachment = $ticket->attachments()->create([
                    'ticket_comment_id' => $commentId,
                    'uploaded_by' => $actor->id,
                    'disk' => 'local',
                    'path' => $path,
                    'stored_name' => $storedName,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                ]);
                $ticket->activities()->create([
                    'actor_id' => $actor->id,
                    'type' => TicketActivityType::AttachmentAdded,
                    'description' => "Attachment added by {$actor->name}.",
                    'metadata' => ['attachment_id' => $attachment->id, 'original_name' => $attachment->original_name],
                ]);

                return $attachment;
            }, 3);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }
}
