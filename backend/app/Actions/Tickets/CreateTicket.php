<?php

namespace App\Actions\Tickets;

use App\AuditAction;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use App\Support\AuditLogger;
use App\TicketActivityType;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Support\Facades\DB;

class CreateTicket
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(User $reporter, array $data): Ticket
    {
        return DB::transaction(function () use ($reporter, $data): Ticket {
            $ticket = Ticket::query()->create([
                ...$data,
                'ticket_number' => 'PENDING-'.str()->uuid(),
                'reporter_id' => $reporter->id,
                'priority' => $data['priority'] ?? TicketPriority::Medium,
                'status' => TicketStatus::Open,
            ]);

            $ticket->updateQuietly([
                'ticket_number' => sprintf('TCK-%s-%06d', now()->format('Y'), $ticket->id),
            ]);

            $ticket->activities()->create([
                'actor_id' => $reporter->id,
                'type' => TicketActivityType::Created,
                'description' => "Ticket {$ticket->ticket_number} created by {$reporter->name}.",
                'new_values' => [
                    'status' => TicketStatus::Open->value,
                    'priority' => $ticket->priority->value,
                ],
            ]);

            $this->auditLogger->log(AuditAction::TicketCreated, $ticket, $reporter, newValues: [
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
            ]);

            $reporter->notify(new TicketEventNotification(
                'Ticket created',
                "{$ticket->ticket_number} has been created successfully.",
                ['ticket_id' => $ticket->id],
            ));

            return $ticket;
        }, 3);
    }
}
