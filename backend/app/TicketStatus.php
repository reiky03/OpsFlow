<?php

namespace App;

enum TicketStatus: string
{
    case Open = 'OPEN';
    case Assigned = 'ASSIGNED';
    case InProgress = 'IN_PROGRESS';
    case WaitingUser = 'WAITING_USER';
    case Resolved = 'RESOLVED';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::Assigned, self::Cancelled],
            self::Assigned => [self::InProgress, self::Cancelled],
            self::InProgress => [self::WaitingUser, self::Resolved],
            self::WaitingUser => [self::InProgress],
            self::Resolved => [self::Closed],
            self::Closed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
