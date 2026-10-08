<?php

namespace App\Actions\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\TicketActivityType;
use Illuminate\Support\Facades\DB;

class UpdateTicket
{
    /** @param array<string, mixed> $data */
    public function handle(Ticket $ticket, User $actor, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $actor, $data): Ticket {
            $oldValues = $ticket->only(array_keys($data));
            $ticket->update($data);

            $ticket->activities()->create([
                'actor_id' => $actor->id,
                'type' => TicketActivityType::Updated,
                'description' => "Ticket details updated by {$actor->name}.",
                'old_values' => $oldValues,
                'new_values' => $ticket->only(array_keys($data)),
            ]);

            return $ticket;
        }, 3);
    }
}
