<?php

namespace App\Actions\Tickets;

use App\AuditAction;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use App\Support\AuditLogger;
use App\TicketActivityType;
use App\TicketPriority;
use Illuminate\Support\Facades\DB;

class ChangeTicketPriority
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Ticket $ticket, TicketPriority $priority, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $priority, $actor): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $oldPriority = $ticket->priority;
            $ticket->update(['priority' => $priority]);

            $ticket->activities()->create([
                'actor_id' => $actor->id,
                'type' => TicketActivityType::PriorityChanged,
                'description' => "Priority changed from {$oldPriority->value} to {$priority->value} by {$actor->name}.",
                'old_values' => ['priority' => $oldPriority->value],
                'new_values' => ['priority' => $priority->value],
            ]);

            $this->auditLogger->log(
                AuditAction::TicketPriorityChanged,
                $ticket,
                $actor,
                ['priority' => $oldPriority->value],
                ['priority' => $priority->value],
            );

            $ticket->loadMissing('assignedTechnician');
            if ($ticket->assignedTechnician && $ticket->assigned_technician_id !== $actor->id) {
                $ticket->assignedTechnician->notify(new TicketEventNotification(
                    'Ticket priority changed',
                    "{$ticket->ticket_number} priority is now {$priority->value}.",
                    ['ticket_id' => $ticket->id, 'priority' => $priority->value],
                ));
            }

            return $ticket;
        }, 3);
    }
}
