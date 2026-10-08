<?php

namespace App;

enum SystemRole: string
{
    case Employee = 'EMPLOYEE';
    case Technician = 'TECHNICIAN';
    case ItManager = 'IT_MANAGER';
    case Admin = 'ADMIN';
}
