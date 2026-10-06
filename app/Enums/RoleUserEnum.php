<?php

namespace App\Enums;

enum RoleUserEnum: string
{
    case SUPER_ADMIN = 'super_admin';

    case ADMIN = 'admin';

    case MEMBER = 'member';
}
