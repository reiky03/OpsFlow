<?php

namespace App;

enum TicketActivityType: string
{
    case Created = 'CREATED';
    case Assigned = 'ASSIGNED';
    case StatusChanged = 'STATUS_CHANGED';
    case PriorityChanged = 'PRIORITY_CHANGED';
    case CommentAdded = 'COMMENT_ADDED';
    case AttachmentAdded = 'ATTACHMENT_ADDED';
    case Updated = 'UPDATED';
}
