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

class ChangeTicketStatus
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Ticket $ticket, TicketStatus $nextStatus, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $nextStatus, $actor): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $currentStatus = $ticket->status;

            if (! $currentStatus->canTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => "Status cannot change from {$currentStatus->value} to {$nextStatus->value}.",
                ]);
            }

            $values = ['status' => $nextStatus];
            if ($nextStatus === TicketStatus::Resolved) {
                $values['resolved_at'] = now();
            }
            if ($nextStatus === TicketStatus::Closed) {
                $values['closed_at'] = now();
            }

            $ticket->update($values);

            $ticket->activities()->create([
                'actor_id' => $actor->id,
                'type' => TicketActivityType::StatusChanged,
                'description' => "Status changed from {$currentStatus->value} to {$nextStatus->value} by {$actor->name}.",
                'old_values' => ['status' => $currentStatus->value],
                'new_values' => ['status' => $nextStatus->value],
            ]);

            $this->auditLogger->log(
                AuditAction::TicketStatusChanged,
                $ticket,
                $actor,
                ['status' => $currentStatus->value],
                ['status' => $nextStatus->value],
            );

            $ticket->loadMissing('reporter');
            if ($ticket->reporter_id !== $actor->id) {
                $ticket->reporter->notify(new TicketEventNotification(
                    'Ticket status changed',
                    "{$ticket->ticket_number} is now {$nextStatus->value}.",
                    ['ticket_id' => $ticket->id, 'status' => $nextStatus->value],
                ));
            }

            return $ticket;
        }, 3);
    }
}
