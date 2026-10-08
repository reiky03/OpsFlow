<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use App\TicketActivityType;
use Illuminate\Support\Facades\DB;

class AddTicketComment
{
    public function handle(Ticket $ticket, User $actor, string $message, bool $isInternal): TicketComment
    {
        return DB::transaction(function () use ($ticket, $actor, $message, $isInternal): TicketComment {
            $comment = $ticket->comments()->create([
                'user_id' => $actor->id,
                'message' => $message,
                'is_internal' => $isInternal,
            ]);

            $ticket->activities()->create([
                'actor_id' => $actor->id,
                'type' => TicketActivityType::CommentAdded,
                'description' => $isInternal
                    ? "Internal note added by {$actor->name}."
                    : "Comment added by {$actor->name}.",
                'metadata' => ['comment_id' => $comment->id, 'is_internal' => $isInternal],
            ]);

            if (! $isInternal) {
                $ticket->loadMissing(['reporter', 'assignedTechnician']);
                $recipient = $actor->id === $ticket->reporter_id
                    ? $ticket->assignedTechnician
                    : $ticket->reporter;

                $recipient?->notify(new TicketEventNotification(
                    'New ticket comment',
                    "{$actor->name} commented on {$ticket->ticket_number}.",
                    ['ticket_id' => $ticket->id, 'comment_id' => $comment->id],
                ));
            }

            return $comment;
        }, 3);
    }
}
