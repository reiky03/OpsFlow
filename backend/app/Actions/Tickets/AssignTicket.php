<?php

namespace App\Actions\Tickets;

use App\AuditAction;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use App\Support\AuditLogger;
use App\TicketActivityType;
use App\TicketStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignTicket
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Ticket $ticket, User $technician, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $technician, $actor): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);

            if (! in_array($ticket->status, [TicketStatus::Open, TicketStatus::Assigned], true)) {
                throw ValidationException::withMessages([
                    'technician_id' => 'Only open or assigned tickets can be assigned.',
                ]);
            }

            $oldValues = [
                'assigned_technician_id' => $ticket->assigned_technician_id,
                'status' => $ticket->status->value,
            ];

            $ticket->update([
                'assigned_technician_id' => $technician->id,
                'assigned_by' => $actor->id,
                'assigned_at' => now(),
                'status' => TicketStatus::Assigned,
            ]);

            $ticket->activities()->create([
                'actor_id' => $actor->id,
                'type' => TicketActivityType::Assigned,
                'description' => "Assigned to {$technician->name} by {$actor->name}.",
                'old_values' => $oldValues,
                'new_values' => [
                    'assigned_technician_id' => $technician->id,
                    'status' => TicketStatus::Assigned->value,
                ],
            ]);

            $this->auditLogger->log(AuditAction::TicketAssigned, $ticket, $actor, $oldValues, [
                'assigned_technician_id' => $technician->id,
                'status' => TicketStatus::Assigned->value,
            ]);

            $ticket->loadMissing('reporter');
            $technician->notify(new TicketEventNotification(
                'Ticket assigned',
                "{$ticket->ticket_number} has been assigned to you.",
                ['ticket_id' => $ticket->id],
            ));
            $ticket->reporter->notify(new TicketEventNotification(
                'Technician assigned',
                "{$technician->name} is now handling {$ticket->ticket_number}.",
                ['ticket_id' => $ticket->id],
            ));

            return $ticket;
        }, 3);
    }
}
