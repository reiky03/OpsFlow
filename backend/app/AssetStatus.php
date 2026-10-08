<?php

namespace App;

enum AssetStatus: string
{
    case Available = 'AVAILABLE';
    case Assigned = 'ASSIGNED';
    case Maintenance = 'MAINTENANCE';
    case Retired = 'RETIRED';
    case Lost = 'LOST';
}
