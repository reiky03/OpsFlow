<?php

namespace App;

enum PermissionName: string
{
    case TicketCreate = 'ticket.create';
    case TicketViewOwn = 'ticket.view_own';
    case TicketViewAssigned = 'ticket.view_assigned';
    case TicketViewUnassigned = 'ticket.view_unassigned';
    case TicketViewAll = 'ticket.view_all';
    case TicketAssign = 'ticket.assign';
    case TicketUpdateStatus = 'ticket.update_status';
    case TicketUpdatePriority = 'ticket.update_priority';
    case TicketComment = 'ticket.comment';
    case TicketInternalNote = 'ticket.internal_note';

    case AssetViewOwn = 'asset.view_own';
    case AssetViewAll = 'asset.view_all';
    case AssetCreate = 'asset.create';
    case AssetUpdate = 'asset.update';
    case AssetAssign = 'asset.assign';

    case UserView = 'user.view';
    case UserCreate = 'user.create';
    case UserUpdate = 'user.update';
    case UserAssignRole = 'user.assign_role';
    case RoleManage = 'role.manage';

    case DepartmentManage = 'department.manage';
    case TicketCategoryManage = 'ticket_category.manage';
    case AuditView = 'audit.view';
}
