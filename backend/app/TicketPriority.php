<?php

namespace App;

enum TicketPriority: string
{
    case Low = 'LOW';
    case Medium = 'MEDIUM';
    case High = 'HIGH';
    case Critical = 'CRITICAL';
}
