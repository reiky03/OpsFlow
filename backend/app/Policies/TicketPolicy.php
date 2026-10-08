<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\PermissionName;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAnyViewPermission($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketViewAll)
            || ($user->hasPermissionTo(PermissionName::TicketViewOwn) && $ticket->reporter_id === $user->id)
            || ($user->hasPermissionTo(PermissionName::TicketViewAssigned) && $ticket->assigned_technician_id === $user->id)
            || ($user->hasPermissionTo(PermissionName::TicketViewUnassigned) && $ticket->assigned_technician_id === null);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return ($ticket->reporter_id === $user->id && $ticket->status->value === 'OPEN')
            || $user->hasPermissionTo(PermissionName::TicketViewAll);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketAssign);
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        $canOperateTicket = $user->hasPermissionTo(PermissionName::TicketUpdateStatus)
            && ($ticket->assigned_technician_id === $user->id || $user->hasPermissionTo(PermissionName::TicketViewAll));
        $canConfirmResolution = $ticket->reporter_id === $user->id
            && $ticket->status->value === 'RESOLVED';

        return $canOperateTicket || $canConfirmResolution;
    }

    public function updatePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketUpdatePriority) && $this->view($user, $ticket);
    }

    public function comment(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketComment) && $this->view($user, $ticket);
    }

    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketInternalNote) && $this->view($user, $ticket);
    }

    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo(PermissionName::TicketInternalNote) && $this->view($user, $ticket);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    private function hasAnyViewPermission(User $user): bool
    {
        return collect([
            PermissionName::TicketViewOwn,
            PermissionName::TicketViewAssigned,
            PermissionName::TicketViewUnassigned,
            PermissionName::TicketViewAll,
        ])->contains(fn (PermissionName $permission): bool => $user->hasPermissionTo($permission));
    }
}
