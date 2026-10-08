<?php

namespace App\Queries;

use App\Models\Ticket;
use App\Models\User;
use App\PermissionName;
use Illuminate\Database\Eloquent\Builder;

class VisibleTickets
{
    /** @return Builder<Ticket> */
    public function for(User $user): Builder
    {
        $query = Ticket::query();
        if ($user->hasPermissionTo(PermissionName::TicketViewAll)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user): void {
            if ($user->hasPermissionTo(PermissionName::TicketViewOwn)) {
                $query->orWhere('reporter_id', $user->id);
            }
            if ($user->hasPermissionTo(PermissionName::TicketViewAssigned)) {
                $query->orWhere('assigned_technician_id', $user->id);
            }
            if ($user->hasPermissionTo(PermissionName::TicketViewUnassigned)) {
                $query->orWhereNull('assigned_technician_id');
            }
        });
    }
}
