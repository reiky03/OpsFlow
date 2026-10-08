<?php

namespace App;

enum AuditAction: string
{
    case UserLogin = 'USER_LOGIN';
    case TicketCreated = 'TICKET_CREATED';
    case TicketAssigned = 'TICKET_ASSIGNED';
    case TicketPriorityChanged = 'TICKET_PRIORITY_CHANGED';
    case TicketStatusChanged = 'TICKET_STATUS_CHANGED';
    case AssetCreated = 'ASSET_CREATED';
    case AssetAssigned = 'ASSET_ASSIGNED';
    case AssetReturned = 'ASSET_RETURNED';
    case UserRoleChanged = 'USER_ROLE_CHANGED';
    case RolePermissionsChanged = 'ROLE_PERMISSIONS_CHANGED';
    case UserDeactivated = 'USER_DEACTIVATED';
}
